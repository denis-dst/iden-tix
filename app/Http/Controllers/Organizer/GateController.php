<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Gate;
use App\Models\GateLog;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GateController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $events = Event::where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->select(['id', 'name', 'slug', 'venue', 'city', 'event_start_date', 'background_image'])
            ->orderBy('event_start_date', 'desc')
            ->get();

        return view('organizer.gate.index', compact('events'));
    }

    public function verifyForm(Event $event)
    {
        return view('organizer.gate.verify', compact('event'));
    }

    public function verify(Request $request, Event $event)
    {
        $request->validate([
            'security_code' => 'required',
        ]);

        if ($event->security_code && $request->security_code !== $event->security_code) {
            return back()->with('error', 'Kode Keamanan Salah!');
        }

        session(['gate_event_id' => $event->id]);
        session()->forget(['gate_name', 'gate_mode']);

        return redirect()->route('organizer.gate.setup', $event);
    }

    public function setupForm(Event $event)
    {
        if (session('gate_event_id') != $event->id) {
            return redirect()->route('organizer.gate.verify', $event);
        }

        $gates = $event->gates()->where('is_active', true)->with('ticketCategories:id,name')->get();
        $categories = $event->ticketCategories;

        $currentGateId = session('gate_id') ? (string) session('gate_id') : (session('gate_category_id') ? 'cat_' . session('gate_category_id') : 'all');
        $currentMode = session('gate_mode', 'IN');
        $currentAutoTimer = session('gate_auto_timer', true);

        return view('organizer.gate.setup', compact('event', 'gates', 'categories', 'currentGateId', 'currentMode', 'currentAutoTimer'));
    }

    public function setup(Request $request, Event $event)
    {
        $request->validate([
            'gate_id' => 'nullable|string',
            'gate_category_id' => 'nullable',
            'gate_mode' => 'required|in:IN,OUT',
            'gate_auto_timer' => 'nullable',
        ]);

        $gateId = $request->input('gate_id');
        $categoryId = $request->input('gate_category_id');

        if ($gateId === 'all' || $gateId === '0' || (empty($gateId) && empty($categoryId))) {
            // All Gate: matches Flutter mobile app (GateModel id: 0, name: 'All Gate', allowedCategoryIds: [])
            session(['gate_id' => null]);
            session(['gate_name' => 'All Gate']);
            session(['gate_allowed_categories' => []]);
            session()->forget('gate_category_id');
        } elseif (is_string($gateId) && str_starts_with($gateId, 'cat_')) {
            $catId = (int) str_replace('cat_', '', $gateId);
            $category = TicketCategory::findOrFail($catId);
            session(['gate_category_id' => $category->id]);
            session(['gate_name' => $category->name]);
            session(['gate_allowed_categories' => [$category->id]]);
            session()->forget('gate_id');
        } elseif (!empty($gateId)) {
            $gate = Gate::with('ticketCategories:id')->findOrFail($gateId);
            session(['gate_id' => $gate->id]);
            session(['gate_name' => $gate->name]);
            session(['gate_allowed_categories' => $gate->ticketCategories->pluck('id')->toArray()]);
            session()->forget('gate_category_id');
        } elseif (!empty($categoryId)) {
            $category = TicketCategory::findOrFail($categoryId);
            session(['gate_category_id' => $category->id]);
            session(['gate_name' => $category->name]);
            session(['gate_allowed_categories' => [$category->id]]);
            session()->forget('gate_id');
        }

        session(['gate_mode' => $request->input('gate_mode', 'IN')]);
        session(['gate_auto_timer' => filter_var($request->input('gate_auto_timer', true), FILTER_VALIDATE_BOOLEAN)]);

        return redirect()->route('organizer.gate.scan', $event);
    }

    public function scan(Event $event)
    {
        if (session('gate_event_id') != $event->id || !session('gate_name')) {
            return redirect()->route('organizer.gate.setup', $event);
        }

        // Single optimized aggregate query for initial counts
        $counts = GateLog::where('event_id', $event->id)
            ->selectRaw("
                SUM(CASE WHEN type = 'IN' THEN 1 ELSE 0 END) as in_count,
                SUM(CASE WHEN type = 'OUT' THEN 1 ELSE 0 END) as out_count
            ")
            ->first();

        $inCount = (int) ($counts->in_count ?? 0);
        $outCount = (int) ($counts->out_count ?? 0);

        return view('organizer.gate.scan', compact('event', 'inCount', 'outCount'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string',
            'event_id' => 'required|exists:events,id',
            'mode' => 'required|in:IN,OUT',
            'gate_name' => 'required|string'
        ]);

        $scanCode = trim($request->ticket_code);

        // Fetch ticket with event, transaction, and category in a single query
        $ticket = Ticket::where('event_id', $request->event_id)
            ->where(function ($q) use ($scanCode) {
                $q->where('ticket_code', $scanCode)
                  ->orWhere('wristband_qr', $scanCode);
            })
            ->with([
                'category:id,name,hex_color',
                'transaction:id,customer_name,customer_email,customer_phone,reference_no',
                'event:id,tenant_id,purchase_flow,name'
            ])
            ->first();

        if (!$ticket) {
            if (preg_match('/^WB[-_]?(?:C|CAT)?(\d+)[-_](\d+)$/i', $scanCode, $wbMatches)) {
                $categoryId = (int) $wbMatches[1];
                $wristbandIndex = (int) $wbMatches[2];

                $category = \App\Models\TicketCategory::with('event')->find($categoryId);
                if ($category && $category->event && (int)$category->event_id === (int)$request->event_id) {
                    $maxQuota = max((int) $category->quota, 5000);
                    if ($wristbandIndex <= $maxQuota) {
                        $unlinkedTicket = Ticket::where('ticket_category_id', $category->id)
                            ->where('status', 'sold')
                            ->whereNull('wristband_qr')
                            ->first();

                        if ($unlinkedTicket) {
                            $unlinkedTicket->update([
                                'wristband_qr' => $scanCode,
                                'status' => 'redeemed',
                                'redeemed_at' => now(),
                                'redeemed_by' => auth()->id(),
                            ]);
                            $ticket = $unlinkedTicket->fresh([
                                'category:id,name,hex_color',
                                'transaction:id,customer_name,customer_email,customer_phone,reference_no',
                                'event:id,tenant_id,purchase_flow,name'
                            ]);
                        } else {
                            $stockTx = \App\Models\Transaction::firstOrCreate(
                                [
                                    'reference_no' => 'WB-' . $category->id . '-' . $wristbandIndex,
                                ],
                                [
                                    'tenant_id' => $category->tenant_id,
                                    'event_id' => $category->event_id,
                                    'ticket_category_id' => $category->id,
                                    'customer_name' => 'Gelang Fisik #' . $wristbandIndex . ' (' . $category->name . ')',
                                    'customer_email' => 'wristband@iden-tix.com',
                                    'customer_phone' => '-',
                                    'quantity' => 1,
                                    'total_amount' => $category->price ?? 0,
                                    'payment_status' => 'paid',
                                    'payment_method' => 'OFFLINE_WRISTBAND',
                                    'paid_at' => now(),
                                ]
                            );

                            $newTicket = Ticket::create([
                                'tenant_id' => $category->tenant_id,
                                'event_id' => $category->event_id,
                                'transaction_id' => $stockTx->id,
                                'ticket_category_id' => $category->id,
                                'ticket_code' => 'GTX-WB-' . strtoupper(\Illuminate\Support\Str::random(8)),
                                'wristband_qr' => $scanCode,
                                'status' => 'redeemed',
                                'redeemed_at' => now(),
                                'redeemed_by' => auth()->id(),
                            ]);
                            $ticket = $newTicket->fresh([
                                'category:id,name,hex_color',
                                'transaction:id,customer_name,customer_email,customer_phone,reference_no',
                                'event:id,tenant_id,purchase_flow,name'
                            ]);
                        }
                    }
                }
            }
        }

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket Tidak Valid!'
            ], 404);
        }

        $event = $ticket->event;

        if ($ticket->status === 'void') {
            return response()->json([
                'success' => false,
                'message' => 'Tiket sudah dibatalkan.',
                'customer' => $ticket->transaction->customer_name ?? '-',
                'category' => $ticket->category->name ?? '-',
            ], 403);
        }

        // Access Control: Check if ticket category is allowed for the gate
        $allowedCategories = session('gate_allowed_categories', []);
        if (!empty($allowedCategories) && !in_array($ticket->ticket_category_id, $allowedCategories)) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori Tiket Salah! (Tidak diizinkan di Gate ' . session('gate_name') . ')',
                'customer' => $ticket->transaction->customer_name ?? '-',
                'category' => $ticket->category->name ?? '-'
            ], 403);
        }

        // Fetch latest log with scanner info using indexed lookup
        $lastLog = GateLog::where('ticket_id', $ticket->id)
            ->with('scanner:id,name')
            ->orderBy('scanned_at', 'desc')
            ->first();

        $alreadyInState = false;
        if ($request->mode === 'IN') {
            if ($lastLog && $lastLog->type === 'IN') {
                $alreadyInState = true;
            }
        } else {
            if ($lastLog && $lastLog->type === 'OUT') {
                $alreadyInState = true;
            }
        }

        if ($alreadyInState) {
            $operatorName = $lastLog->scanner ? $lastLog->scanner->name : ($lastLog->gate_name ?? 'System');
            $timeString = $lastLog->scanned_at ? $lastLog->scanned_at->timezone('Asia/Jakarta')->format('H:i:s d-m-Y') : '-';
            $visitorName = $ticket->visitor_data['name'] ?? ($ticket->transaction->customer_name ?? '-');

            return response()->json([
                'success' => false,
                'message' => "Sudah Checkin pada waktu {$timeString} oleh Operator {$operatorName} dengan QR {$ticket->ticket_code} atas nama {$visitorName}",
                'customer' => $visitorName,
                'category' => $ticket->category->name ?? '-'
            ], 400);
        }

        if ($request->mode === 'OUT' && (!$lastLog || $lastLog->type !== 'IN')) {
            return response()->json([
                'success' => false,
                'message' => $lastLog && $lastLog->type === 'OUT' ? 'Tiket sudah berada di luar area!' : 'Tiket belum pernah Check-in!',
                'customer' => $ticket->visitor_data['name'] ?? ($ticket->transaction->customer_name ?? '-'),
                'category' => $ticket->category->name ?? '-'
            ], 400);
        }

        // Group checking: Load group tickets in a single eager query if transaction exists
        $transaction = $ticket->transaction;
        if ($transaction) {
            $ticketsInGroup = Ticket::where('transaction_id', $transaction->id)
                ->with([
                    'category:id,name,hex_color',
                    'gateLogs' => fn ($q) => $q->with('scanner:id,name')->orderBy('scanned_at', 'desc')
                ])
                ->get();

            if ($ticketsInGroup->count() > 1) {
                $attendeesList = $ticketsInGroup->map(function ($t) use ($event, $transaction) {
                    $tLastLog = $t->gateLogs->first();
                    $isCheckedIn = $tLastLog && $tLastLog->type === 'IN';
                    return [
                        'ticket_id' => $t->id,
                        'ticket_code' => $t->ticket_code,
                        'name' => $t->visitor_data['name'] ?? $transaction->customer_name,
                        'gender' => $t->visitor_data['gender'] ?? null,
                        'is_checked_in' => $isCheckedIn,
                        'category' => $t->category->name ?? '-',
                        'checked_in_at' => ($isCheckedIn && $tLastLog->scanned_at) ? $tLastLog->scanned_at->timezone('Asia/Jakarta')->format('H:i:s d-m-Y') : null,
                        'checked_in_by' => ($isCheckedIn && $tLastLog->scanner) ? $tLastLog->scanner->name : ($tLastLog->gate_name ?? null),
                        'custom_fields' => $this->formatCustomFields($event, $t),
                    ];
                });

                return response()->json([
                    'success' => true,
                    'is_group' => true,
                    'message' => 'Detail grup ditemukan',
                    'customer' => $transaction->customer_name,
                    'category' => $ticket->category->name ?? '-',
                    'attendees' => $attendeesList,
                    'scanned_ticket_id' => $ticket->id
                ]);
            }
        }

        try {
            DB::transaction(function () use ($request, $ticket, $event) {
                GateLog::create([
                    'tenant_id' => $event->tenant_id,
                    'event_id' => $event->id,
                    'ticket_id' => $ticket->id,
                    'gate_name' => $request->gate_name,
                    'type' => $request->mode,
                    'scanned_at' => now(),
                    'scanned_by' => auth()->id()
                ]);

                if ($ticket->status === 'sold' && $request->mode === 'IN') {
                    $ticket->update([
                        'status' => 'redeemed',
                        'redeemed_at' => now(),
                    ]);
                }
            });

            // Single aggregate query for live counts
            $counts = GateLog::where('event_id', $event->id)
                ->selectRaw("
                    SUM(CASE WHEN type = 'IN' THEN 1 ELSE 0 END) as in_count,
                    SUM(CASE WHEN type = 'OUT' THEN 1 ELSE 0 END) as out_count
                ")
                ->first();

            $inCount = (int) ($counts->in_count ?? 0);
            $outCount = (int) ($counts->out_count ?? 0);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil ' . ($request->mode === 'IN' ? 'Check-in' : 'Check-out'),
                'customer' => $ticket->visitor_data['name'] ?? $ticket->transaction->customer_name,
                'category' => $ticket->category->name ?? '-',
                'ticket_code' => $ticket->ticket_code,
                'reference_no' => $ticket->transaction->reference_no ?? null,
                'custom_fields' => $this->formatCustomFields($event, $ticket),
                'in_count' => $inCount,
                'out_count' => $outCount,
                'occupancy' => max(0, $inCount - $outCount)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkCheckin(Request $request, Event $event)
    {
        $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id',
            'mode' => 'required|in:IN,OUT',
            'gate_name' => 'required|string'
        ]);

        try {
            $now = now();
            $authId = auth()->id();
            $ticketIds = array_unique($request->ticket_ids);

            // Fetch latest logs for all tickets in a single batch query
            $existingLogs = GateLog::whereIn('ticket_id', $ticketIds)
                ->orderBy('scanned_at', 'desc')
                ->get()
                ->groupBy('ticket_id');

            $insertRows = [];
            $redeemedTicketIds = [];

            foreach ($ticketIds as $ticketId) {
                $ticketLogs = $existingLogs->get($ticketId);
                $lastLog = $ticketLogs ? $ticketLogs->first() : null;
                $alreadyInState = $lastLog && $lastLog->type === $request->mode;

                if ($request->mode === 'IN') {
                    if ($alreadyInState) {
                        continue;
                    }
                    $redeemedTicketIds[] = $ticketId;
                } else {
                    $hasIn = $ticketLogs && $ticketLogs->contains('type', 'IN');
                    if (!$hasIn || $alreadyInState) {
                        continue;
                    }
                }

                $insertRows[] = [
                    'tenant_id' => $event->tenant_id,
                    'event_id' => $event->id,
                    'ticket_id' => $ticketId,
                    'gate_name' => $request->gate_name,
                    'type' => $request->mode,
                    'scanned_at' => $now,
                    'device_id' => null,
                    'scanned_by' => $authId,
                    'meta' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($insertRows)) {
                DB::transaction(function () use ($insertRows, $redeemedTicketIds, $now) {
                    GateLog::insert($insertRows);

                    if (!empty($redeemedTicketIds)) {
                        Ticket::whereIn('id', $redeemedTicketIds)
                            ->where('status', 'sold')
                            ->update([
                                'status' => 'redeemed',
                                'redeemed_at' => $now,
                            ]);
                    }
                });
            }

            // Single aggregate query for live counts
            $counts = GateLog::where('event_id', $event->id)
                ->selectRaw("
                    SUM(CASE WHEN type = 'IN' THEN 1 ELSE 0 END) as in_count,
                    SUM(CASE WHEN type = 'OUT' THEN 1 ELSE 0 END) as out_count
                ")
                ->first();

            $inCount = (int) ($counts->in_count ?? 0);
            $outCount = (int) ($counts->out_count ?? 0);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil memproses check-in masal',
                'in_count' => $inCount,
                'out_count' => $outCount,
                'occupancy' => max(0, $inCount - $outCount)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format all participant registration data and custom fields for gate scanner display.
     */
    private function formatCustomFields(Event $event, Ticket $ticket): array
    {
        $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : [];
        $customAnswers = $visitorData['custom_answers'] ?? [];
        $transaction = $ticket->transaction;
        $formatted = [];

        // 1. WhatsApp / Phone
        $phone = $visitorData['phone'] ?? ($transaction->customer_phone ?? null);
        if (!empty($phone) && $phone !== '-') {
            $formatted[] = [
                'label' => 'WhatsApp / No. HP',
                'value' => (string) $phone,
            ];
        }

        // 2. Email
        $email = $visitorData['email'] ?? ($transaction->customer_email ?? null);
        if (!empty($email) && $email !== '-') {
            $formatted[] = [
                'label' => 'Email',
                'value' => (string) $email,
            ];
        }

        // 3. NIK / No. Identitas
        $nik = $visitorData['nik'] ?? ($transaction->customer_nik ?? null);
        if (!empty($nik) && $nik !== '-') {
            $formatted[] = [
                'label' => 'NIK / Identitas',
                'value' => (string) $nik,
            ];
        }

        // 4. Gender (if enabled and present)
        $genderSetting = $event->getAttendeeGenderSetting();
        $gender = $visitorData['gender'] ?? ($transaction->customer_gender ?? null);
        if ($genderSetting !== 'disabled' && !empty($gender)) {
            $g = strtolower(trim($gender));
            $genderLabel = match($g) {
                'ikhwan' => '🧔 Ikhwan',
                'akhwat' => '🧕 Akhwat',
                'laki-laki' => '👨 Laki-laki',
                'perempuan' => '👩 Perempuan',
                default => ucfirst($gender)
            };
            $formatted[] = [
                'label' => 'Gender',
                'value' => $genderLabel
            ];
        }

        // 5. Dynamic Custom Registration Fields (e.g. Nama Kucing, Ras, Umur, etc.)
        $processedFieldIds = [];
        $customFields = $event->getCustomFields();
        if (!empty($customFields)) {
            foreach ($customFields as $field) {
                $fId = $field['id'];
                $processedFieldIds[] = $fId;
                $val = $customAnswers[$fId] ?? null;
                if (($val === null || $val === '') && $fId === 'umroh_answer') {
                    $val = $visitorData['umroh_answer'] ?? ($transaction->customer_umroh_answer ?? null);
                }
                if ($val !== null && $val !== '') {
                    $formatted[] = [
                        'label' => $field['label'],
                        'value' => is_array($val) ? implode(', ', $val) : (string) $val
                    ];
                }
            }
        } elseif (!empty($visitorData['umroh_answer']) || !empty($transaction->customer_umroh_answer)) {
            $ans = $visitorData['umroh_answer'] ?? $transaction->customer_umroh_answer;
            $formatted[] = [
                'label' => $event->meta['custom_question_text'] ?? 'Jawaban Khusus',
                'value' => (string) $ans
            ];
            $processedFieldIds[] = 'umroh_answer';
        }

        // 6. Any other custom answers in visitor_data not explicitly defined in customFields
        if (is_array($customAnswers)) {
            foreach ($customAnswers as $key => $val) {
                if (!in_array($key, $processedFieldIds) && $val !== null && $val !== '') {
                    $label = ucwords(str_replace(['_', '-'], ' ', $key));
                    $formatted[] = [
                        'label' => $label,
                        'value' => is_array($val) ? implode(', ', $val) : (string) $val
                    ];
                }
            }
        }

        // 7. Uploaded Proofs (e.g. Bukti Review Google, Bukti Follow IG)
        $proofs = $visitorData['proofs'] ?? [];
        if (!empty($proofs) && is_array($proofs)) {
            foreach ($proofs as $pKey => $pPath) {
                if (!empty($pPath)) {
                    $label = match($pKey) {
                        'proof_ig' => 'Bukti Follow IG',
                        'proof_review' => 'Bukti Review Google',
                        default => ucwords(str_replace(['_', '-'], ' ', $pKey))
                    };
                    $formatted[] = [
                        'label' => $label,
                        'value' => basename($pPath),
                        'url' => asset('storage/' . $pPath),
                        'is_file' => true,
                    ];
                }
            }
        } else {
            if (!empty($visitorData['proof_ig'])) {
                $formatted[] = [
                    'label' => 'Bukti Follow IG',
                    'value' => basename($visitorData['proof_ig']),
                    'url' => asset('storage/' . $visitorData['proof_ig']),
                    'is_file' => true,
                ];
            }
            if (!empty($visitorData['proof_review'])) {
                $formatted[] = [
                    'label' => 'Bukti Review Google',
                    'value' => basename($visitorData['proof_review']),
                    'url' => asset('storage/' . $visitorData['proof_review']),
                    'is_file' => true,
                ];
            }
        }

        // 8. Ticket Code & Invoice Reference
        if (!empty($ticket->ticket_code)) {
            $formatted[] = [
                'label' => 'Kode Tiket',
                'value' => $ticket->ticket_code,
            ];
        }
        if (!empty($transaction?->reference_no)) {
            $formatted[] = [
                'label' => 'No. Invoice',
                'value' => $transaction->reference_no,
            ];
        }

        return $formatted;
    }
}
