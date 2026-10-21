<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RedeemController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $events = Event::where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->select(['id', 'name', 'slug', 'venue', 'city', 'event_start_date', 'background_image'])
            ->orderBy('event_start_date', 'desc')
            ->get();

        return view('organizer.redeem.index', compact('events'));
    }

    public function verifyForm(Event $event)
    {
        return view('organizer.redeem.verify', compact('event'));
    }

    public function verify(Request $request, Event $event)
    {
        $request->validate([
            'security_code' => 'required',
            'role' => 'required|in:redeem,gate'
        ]);

        if ($event->security_code && $request->security_code !== $event->security_code) {
            return back()->with('error', 'Kode Keamanan Salah!');
        }

        // Store session for current session work
        session(['redeem_event_id' => $event->id]);
        session(['redeem_role' => $request->role]);

        return redirect()->route('organizer.redeem.scan', $event);
    }

    public function scan(Event $event)
    {
        if (session('redeem_event_id') != $event->id) {
            return redirect()->route('organizer.redeem.verify', $event);
        }

        return view('organizer.redeem.scan', compact('event'));
    }

    public function check(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required',
            'event_id' => 'required|exists:events,id'
        ]);

        $rawCode = trim($request->ticket_code);
        $cleanCode = preg_replace('/[\s\-]/', '', $rawCode);
        if (preg_match('/(GTX-[A-Za-z0-9_-]+)/', $rawCode, $matches)) {
            $extractedCode = $matches[1];
        } else {
            $extractedCode = basename(parse_url($rawCode, PHP_URL_PATH) ?: $rawCode);
        }
        $cleanExtracted = preg_replace('/[\s\-]/', '', $extractedCode);
        $possibleCodes = array_values(array_unique(array_filter([$rawCode, $cleanCode, $extractedCode, $cleanExtracted])));

        $ticket = Ticket::where('event_id', $request->event_id)
            ->where(function ($q) use ($possibleCodes) {
                $q->whereIn('ticket_code', $possibleCodes)
                  ->orWhereIn('wristband_qr', $possibleCodes);
            })
            ->with(['transaction:id,customer_name', 'category:id,name', 'redeemer:id,name'])
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Tiket tidak ditemukan!',
                'sound' => 'error',
                'color' => 'red'
            ]);
        }

        if ($ticket->status === 'redeemed') {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'GAGAL! Tiket Sudah Digunakan.',
                'sub_message' => 'Tiket ini telah di-redeem sebelumnya.',
                'sound' => 'error',
                'color' => 'red',
                'details' => [
                    'redeemed_at' => $ticket->redeemed_at ? $ticket->redeemed_at->format('d M Y H:i') : null,
                    'redeemed_by' => $ticket->redeemer->name ?? 'System',
                    'photo' => $ticket->redeem_photo ? Storage::url($ticket->redeem_photo) : null,
                    'customer' => $ticket->transaction->customer_name ?? '-',
                    'category' => $ticket->category->name ?? '-'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Tiket Valid!',
            'sub_message' => 'Silahkan ambil foto pengunjung untuk menyelesaikan redeem.',
            'sound' => 'success',
            'color' => 'green',
            'customer_name' => $ticket->transaction->customer_name ?? '-',
            'category_name' => $ticket->category->name ?? '-'
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required',
            'photo' => 'required', // Base64 photo
            'event_id' => 'required|exists:events,id'
        ]);

        $event = Event::findOrFail($request->event_id);
        $rawCode = trim($request->ticket_code);
        $cleanCode = preg_replace('/[\s\-]/', '', $rawCode);
        if (preg_match('/(GTX-[A-Za-z0-9_-]+)/', $rawCode, $matches)) {
            $extractedCode = $matches[1];
        } else {
            $extractedCode = basename(parse_url($rawCode, PHP_URL_PATH) ?: $rawCode);
        }
        $cleanExtracted = preg_replace('/[\s\-]/', '', $extractedCode);
        $possibleCodes = array_values(array_unique(array_filter([$rawCode, $cleanCode, $extractedCode, $cleanExtracted])));

        $ticket = Ticket::where('event_id', $event->id)
            ->where(function ($q) use ($possibleCodes) {
                $q->whereIn('ticket_code', $possibleCodes)
                  ->orWhereIn('wristband_qr', $possibleCodes);
            })
            ->with(['transaction:id,customer_name', 'category:id,name'])
            ->first();

        // 1. Check if Valid (Exists)
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'reason' => 'invalid',
                'message' => 'E-Voucher Tidak Valid (Tidak ditemukan di database)'
            ]);
        }

        // 2. Check if Already Redeemed
        if ($ticket->status === 'redeemed') {
            return response()->json([
                'success' => false,
                'reason' => 'already_redeemed',
                'message' => 'Sudah pernah di-redeem!',
                'redeemed_at' => $ticket->redeemed_at ? $ticket->redeemed_at->format('d M Y H:i') : null,
                'redeem_photo' => $ticket->redeem_photo ? Storage::url($ticket->redeem_photo) : null,
                'customer' => $ticket->transaction->customer_name ?? '-'
            ]);
        }

        // 3. Process Success
        try {
            // Save Photo
            $photoData = $request->photo;
            $photoData = str_replace('data:image/jpeg;base64,', '', $photoData);
            $photoData = str_replace(' ', '+', $photoData);
            $photoName = 'redeem_' . $ticket->ticket_code . '_' . time() . '.jpg';
            $photoPath = 'redeem_photos/' . $photoName;
            Storage::disk('public')->put($photoPath, base64_decode($photoData));

            $ticket->update([
                'status' => 'redeemed',
                'redeemed_at' => now(),
                'redeemed_by' => auth()->id(),
                'redeem_photo' => $photoPath
            ]);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Redeem Berhasil!',
                'sub_message' => 'Tiket telah berhasil di-redeem.',
                'sound' => 'success',
                'color' => 'green',
                'customer_name' => $ticket->transaction->customer_name ?? '-',
                'category_name' => $ticket->category->name ?? '-',
                'ticket_code' => $ticket->ticket_code,
                'photo_url' => Storage::url($photoPath)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses redeem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadData(Event $event)
    {
        if ($event->tenant_id !== auth()->user()->tenant_id && !auth()->user()->hasRole('Superadmin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tickets = Ticket::where('event_id', $event->id)
            ->whereIn('status', ['sold', 'redeemed'])
            ->select(['id', 'ticket_code', 'status', 'transaction_id', 'ticket_category_id', 'redeemed_at', 'redeem_photo'])
            ->with(['transaction:id,customer_name', 'category:id,name'])
            ->get()
            ->map(function($ticket) {
                return [
                    'code' => $ticket->ticket_code,
                    'status' => $ticket->status,
                    'customer' => $ticket->transaction->customer_name ?? '-',
                    'category' => $ticket->category->name ?? '-',
                    'redeemed_at' => $ticket->redeemed_at ? $ticket->redeemed_at->format('d M Y H:i') : null,
                    'redeem_photo' => $ticket->redeem_photo ? Storage::url($ticket->redeem_photo) : null,
                ];
            });

        return response()->json([
            'event_name' => $event->name,
            'event_id' => $event->id,
            'tickets' => $tickets,
            'downloaded_at' => now()->format('d M Y H:i:s')
        ]);
    }
}
