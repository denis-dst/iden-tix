<?php

namespace App\Http\Controllers\Fan;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\TenantMember;
use App\Models\MembershipTier;
use App\Models\SeasonPass;
use App\Models\SeasonPassClaim;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\TicketCategory;
use App\Models\MatchPrediction;
use App\Models\FanQuiz;
use App\Models\FanQuizParticipant;
use App\Models\FanPointsLedger;
use App\Models\KorwilMemberConsent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FanPortalController extends Controller
{
    private function getCurrentMember(?Tenant $tenant = null): ?TenantMember
    {
        $user = Auth::user();
        if (!$user) return null;

        $tenantId = $tenant ? $tenant->id : ($user->tenant_id ?: Tenant::first()?->id);
        if (!$tenantId) return null;

        return TenantMember::firstOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $user->id],
            [
                'member_number' => 'FAN-' . strtoupper(Str::random(6)),
                'full_name_ktp' => $user->name,
                'phone' => $user->phone,
                'status' => 'active',
                'kyc_status' => 'unverified',
                'points_balance' => 0,
            ]
        );
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $member = $this->getCurrentMember();

        if (!$member) {
            return redirect()->route('login');
        }

        $member->load(['tier', 'tenant', 'seasonPasses.category', 'seasonPasses.claims']);

        // Upcoming home matches of the club
        $upcomingMatches = Event::where('tenant_id', $member->tenant_id)
            ->where('event_start_date', '>=', now()->subHours(6))
            ->where('status', 'published')
            ->orderBy('event_start_date')
            ->take(5)
            ->get();

        // Pending Korwil Consents
        $pendingConsents = KorwilMemberConsent::where('tenant_member_id', $member->id)
            ->where('consent_status', 'pending')
            ->with(['allocation.event', 'allocation.korwil', 'allocation.category'])
            ->get();

        // Recent point history
        $pointHistories = FanPointsLedger::where('tenant_member_id', $member->id)
            ->latest()
            ->take(5)
            ->get();

        return view('fan.dashboard', compact('member', 'upcomingMatches', 'pendingConsents', 'pointHistories'));
    }

    public function showKyc()
    {
        $member = $this->getCurrentMember();
        return view('fan.kyc', compact('member'));
    }

    public function submitKyc(Request $request)
    {
        $member = $this->getCurrentMember();

        $validated = $request->validate([
            'full_name_ktp' => 'required|string|max:255',
            'nik' => 'required|digits:16',
            'phone' => 'required|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string|max:500',
            'ktp_photo' => 'required_without:member.ktp_photo|image|max:3072',
            'face_photo' => 'required_without:member.face_photo|image|max:3072',
        ]);

        $updateData = [
            'full_name_ktp' => $validated['full_name_ktp'],
            'nik' => $validated['nik'],
            'phone' => $validated['phone'],
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'kyc_status' => 'pending',
            'kyc_reject_reason' => null,
        ];

        if ($request->hasFile('ktp_photo')) {
            if ($member->ktp_photo) {
                Storage::disk('public')->delete($member->ktp_photo);
            }
            $updateData['ktp_photo'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('ktp_photo'), 'kyc/ktp', 1200, 80);
        }

        if ($request->hasFile('face_photo')) {
            if ($member->face_photo) {
                Storage::disk('public')->delete($member->face_photo);
            }
            $updateData['face_photo'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('face_photo'), 'kyc/face', 800, 80);
        }

        $member->update($updateData);

        return redirect()->route('fan.dashboard')->with('success', 'Data KYC & NIK berhasil dikirimkan! Menunggu verifikasi admin klub.');
    }

    public function seasonPass()
    {
        $member = $this->getCurrentMember();
        $seasonPasses = SeasonPass::where('tenant_member_id', $member->id)
            ->with(['category', 'claims.event', 'claims.ticket'])
            ->get();

        // Available upcoming matches for season pass holders
        $eligibleMatches = Event::where('tenant_id', $member->tenant_id)
            ->where('status', 'published')
            ->where('allow_season_pass', true)
            ->where('event_start_date', '>=', now())
            ->orderBy('event_start_date')
            ->get();

        return view('fan.season-pass', compact('member', 'seasonPasses', 'eligibleMatches'));
    }

    public function claimTicket(Request $request, Event $event, SeasonPass $seasonPass)
    {
        $member = $this->getCurrentMember();

        if ($seasonPass->tenant_member_id !== $member->id || $seasonPass->status !== 'active') {
            return back()->with('error', 'Season Pass tidak valid atau sudah tidak aktif.');
        }

        if ($seasonPass->hasClaimedForEvent($event->id)) {
            return back()->with('error', 'Anda sudah melakukan klaim tiket untuk pertandingan ini.');
        }

        // Check Claim Window (H-X days before kick-off)
        $claimDays = $event->season_pass_claim_days_before ?: $seasonPass->claim_window_days_before ?: 5;
        $claimWindowStart = $event->event_start_date->subDays($claimDays);

        if (now()->lt($claimWindowStart)) {
            return back()->with('error', "Link klaim tiket pertandingan ini baru dapat diakses mulai {$claimWindowStart->format('d M Y H:i')} (H-{$claimDays} sebelum match).");
        }

        // Determine ticket category
        $category = $seasonPass->category ?: $event->ticketCategories()->first();
        if (!$category) {
            return back()->with('error', 'Kategori tiket untuk pertandingan ini belum tersedia.');
        }

        // Create Transaction & Official Ticket Instance (Compatible with Direct Gate & Wristband Redemption)
        $referenceNo = 'TX-SP-' . strtoupper(Str::random(10));
        $ticketCode  = Ticket::generateTicketCode();

        $transaction = Transaction::create([
            'tenant_id' => $event->tenant_id,
            'event_id' => $event->id,
            'ticket_category_id' => $category->id,
            'quantity' => 1,
            'reference_no' => $referenceNo,
            'customer_name' => $member->full_name_ktp ?: Auth::user()->name,
            'customer_email' => Auth::user()->email,
            'customer_phone' => $member->phone ?: Auth::user()->phone ?: '-',
            'customer_nik' => $member->nik ?: '-',
            'discount_amount' => 0,
            'total_amount' => 0,
            'payment_status' => 'paid',
            'payment_method' => 'Season Pass',
            'paid_at' => now(),
            'channel' => 'season_pass',
        ]);

        $ticket = Ticket::create([
            'tenant_id' => $event->tenant_id,
            'event_id' => $event->id,
            'transaction_id' => $transaction->id,
            'ticket_category_id' => $category->id,
            'ticket_code' => $ticketCode,
            'status' => 'sold',
            'visitor_data' => [
                'name' => $member->full_name_ktp ?: Auth::user()->name,
                'nik' => $member->nik,
                'season_pass_code' => $seasonPass->pass_code,
                'seat_number' => $seasonPass->seat_number,
            ],
        ]);

        // Record Season Pass Claim
        SeasonPassClaim::create([
            'tenant_id' => $event->tenant_id,
            'season_pass_id' => $seasonPass->id,
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'claimed_at' => now(),
            'claim_ip' => $request->ip(),
            'status' => 'claimed',
        ]);

        $seasonPass->increment('claimed_matches');

        return redirect()->route('checkout.success', $referenceNo)->with('success', 'E-Ticket matchday berhasil diklaim!');
    }

    public function gameZone()
    {
        $member = $this->getCurrentMember();

        $upcomingMatches = Event::where('tenant_id', $member->tenant_id)
            ->whereNotNull('home_team_name')
            ->where('event_start_date', '>=', now())
            ->orderBy('event_start_date')
            ->get();

        $myPredictions = MatchPrediction::where('tenant_member_id', $member->id)->pluck('event_id')->toArray();

        $quizzes = FanQuiz::where('tenant_id', $member->tenant_id)
            ->where('is_active', true)
            ->where('expires_at', '>=', now())
            ->with(['participants' => fn($q) => $q->where('tenant_member_id', $member->id)])
            ->get();

        return view('fan.game-zone', compact('member', 'upcomingMatches', 'myPredictions', 'quizzes'));
    }

    public function submitPrediction(Request $request, Event $event)
    {
        $member = $this->getCurrentMember();

        $validated = $request->validate([
            'home_score' => 'required|integer|min:0|max:20',
            'away_score' => 'required|integer|min:0|max:20',
            'first_goal_scorer' => 'nullable|string|max:100',
        ]);

        if (now()->gt($event->event_start_date)) {
            return back()->with('error', 'Pertandingan sudah dimulai, tebakan skor telah ditutup.');
        }

        MatchPrediction::updateOrCreate(
            ['event_id' => $event->id, 'tenant_member_id' => $member->id],
            [
                'tenant_id' => $event->tenant_id,
                'predicted_home_score' => $validated['home_score'],
                'predicted_away_score' => $validated['away_score'],
                'predicted_first_goal_scorer' => $validated['first_goal_scorer'] ?? null,
                'is_calculated' => false,
                'is_correct' => false,
            ]
        );

        return back()->with('success', 'Tebakan skor Anda berhasil disimpan! Poin akan diundi setelah pertandingan berakhir.');
    }

    public function submitQuiz(Request $request, FanQuiz $quiz)
    {
        $member = $this->getCurrentMember();

        $already = FanQuizParticipant::where('fan_quiz_id', $quiz->id)->where('tenant_member_id', $member->id)->first();
        if ($already) {
            return back()->with('error', 'Anda sudah pernah mengerjakan kuis ini.');
        }

        $answers = $request->input('answers', []);
        $questions = $quiz->questions ?: [];
        $correct = 0;

        foreach ($questions as $idx => $q) {
            if (isset($answers[$idx]) && (int)$answers[$idx] === (int)$q['correct_index']) {
                $correct++;
            }
        }

        $total = count($questions);
        $score = $total > 0 ? round(($correct / $total) * 100) : 0;
        $isPassed = $score >= 70;
        $pointsEarned = $isPassed ? $quiz->points_reward : 10; // 10 pts for participation

        FanQuizParticipant::create([
            'fan_quiz_id' => $quiz->id,
            'tenant_member_id' => $member->id,
            'score' => $score,
            'is_passed' => $isPassed,
            'points_earned' => $pointsEarned,
            'completed_at' => now(),
        ]);

        $member->increment('points_balance', $pointsEarned);
        FanPointsLedger::create([
            'tenant_id' => $quiz->tenant_id,
            'tenant_member_id' => $member->id,
            'type' => 'earn',
            'source' => 'quiz',
            'points' => $pointsEarned,
            'balance_after' => $member->points_balance,
            'description' => "Menyelesaikan Kuis: {$quiz->title} (Skor: {$score}%)",
        ]);

        return back()->with('success', "Kuis selesai! Skor Anda: {$score}%. Anda mendapatkan +{$pointsEarned} Poin.");
    }

    public function respondKorwilConsent(Request $request, KorwilMemberConsent $consent)
    {
        $member = $this->getCurrentMember();

        if ($consent->tenant_member_id !== $member->id && $consent->nik !== $member->nik) {
            abort(403);
        }

        $action = $request->input('action'); // approve or reject

        if ($action === 'approve') {
            $consent->update([
                'consent_status' => 'approved_by_member',
                'responded_at' => now(),
            ]);
            return back()->with('success', 'Persetujuan NIK berhasil dikonfirmasi untuk rombongan Korwil.');
        } else {
            $consent->update([
                'consent_status' => 'rejected_by_member',
                'responded_at' => now(),
            ]);
            return back()->with('info', 'Anda menolak alokasi dari Korwil. Anda dapat memesan tiket pertandingan secara mandiri.');
        }
    }
}
