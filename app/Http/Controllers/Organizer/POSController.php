<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\Transaction;
use App\Services\TicketNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class POSController extends Controller
{
    public function index()
    {
        $events = Event::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'published')
            ->withCount('ticketCategories')
            ->orderByDesc('event_start_date')
            ->get();

        return view('organizer.pos.index', compact('events'));
    }

    public function create(Event $event)
    {
        $this->authorizeTenant($event);

        if ($event->status !== 'published') {
            return redirect()->route('organizer.pos.index')->with('error', 'Penjualan tiket POS hanya tersedia untuk event yang sudah dipublikasikan.');
        }

        $event->load(['ticketCategories' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order')->orderBy('price');
        }]);

        return view('organizer.pos.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeTenant($event);

        if ($event->status !== 'published') {
            return redirect()->route('organizer.pos.index')->with('error', 'Penjualan tiket POS hanya tersedia untuk event yang sudah dipublikasikan.');
        }

        $validated = $request->validate([
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'quantity' => 'required|integer|min:1|max:100',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'required|email|max:255',
            'customer_nik' => 'nullable|string|max:32',
            'payment_method' => 'required|in:Tunai,QRIS',
        ]);

        // Normalize phone number to Fonnte format (starts with 62)
        $phone = preg_replace('/[^0-9]/', '', $validated['customer_phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }
        $validated['customer_phone'] = $phone;

        $category = TicketCategory::where('event_id', $event->id)
            ->where('tenant_id', $event->tenant_id)
            ->where('is_active', true)
            ->findOrFail($validated['ticket_category_id']);

        if ($category->sold_count + $validated['quantity'] > $category->quota) {
            return back()->withInput()->with('error', 'Jumlah tiket melebihi sisa kuota kategori.');
        }

        $now = now();
        if ($category->sale_start_at && $now->lt($category->sale_start_at)) {
            return back()->withInput()->with('error', 'Tiket kategori ini belum tersedia.');
        }
        if ($category->sale_end_at && $now->gt($category->sale_end_at)) {
            return back()->withInput()->with('error', 'Penjualan tiket kategori ini sudah berakhir.');
        }

        // Handle QRIS Payment via WAGO Gateway
        if ($validated['payment_method'] === 'QRIS') {
            $referenceNo = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $totalAmount = (int) ($category->price * $validated['quantity']);

            $transaction = Transaction::create([
                'tenant_id'          => $event->tenant_id,
                'event_id'           => $event->id,
                'ticket_category_id' => $category->id,
                'quantity'           => $validated['quantity'],
                'reference_no'       => $referenceNo,
                'customer_name'      => $validated['customer_name'],
                'customer_email'     => $validated['customer_email'],
                'customer_phone'     => $validated['customer_phone'],
                'customer_nik'       => $validated['customer_nik'] ?? null,
                'discount_amount'    => 0,
                'total_amount'       => $totalAmount,
                'payment_status'     => 'pending',
                'payment_method'     => 'QRIS (WAGO)',
                'channel'            => 'pos',
                'processed_by'       => auth()->id(),
            ]);

            session([
                'last_checkout_reference'  => $referenceNo,
                'last_checkout_event_slug' => $event->slug,
            ]);

            $wagoService = new \App\Services\WagoService();
            $posReturnUrl = route('organizer.pos.create', $event);

            $wagoResult = $wagoService->createPayment([
                'order_id'        => $referenceNo,
                'nominal'         => $totalAmount,
                'callback_url'    => route('wago.notification'),
                'return_url'      => route('checkout.success', $referenceNo),
                'redirect_url'    => route('checkout.success', $referenceNo),
                'cancel_url'      => $posReturnUrl . '?status=cancelled&ref=' . urlencode($referenceNo),
                'back_url'        => $posReturnUrl . '?status=cancelled&ref=' . urlencode($referenceNo),
                'payment_method'  => 'QRIS',
                'payment_channel' => config('services.wago.payment_channel'),
            ], [
                'name'  => $validated['customer_name'],
                'email' => $validated['customer_email'],
                'phone' => $validated['customer_phone'],
            ]);

            if ($wagoResult['success']) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'      => true,
                        'redirect_url' => $wagoResult['payment_url'],
                        'reference_no' => $referenceNo,
                    ]);
                }
                return redirect($wagoResult['payment_url']);
            }

            return back()->withInput()->with('error', $wagoResult['message'] ?? 'Gagal membuat sesi pembayaran QRIS WAGO.');
        }

        // Handle Tunai (Cash) Payment
        $transaction = null;
        $tickets = collect();

        DB::transaction(function () use ($event, $category, $validated, &$transaction, &$tickets) {
            $transaction = Transaction::create([
                'tenant_id' => $event->tenant_id,
                'event_id' => $event->id,
                'ticket_category_id' => $category->id,
                'quantity' => $validated['quantity'],
                'reference_no' => 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'customer_nik' => $validated['customer_nik'] ?? null,
                'discount_amount' => 0,
                'total_amount' => $category->price * $validated['quantity'],
                'payment_status' => 'paid',
                'payment_method' => 'Tunai',
                'channel' => 'pos',
                'processed_by' => auth()->id(),
                'paid_at' => now(),
            ]);

            for ($i = 0; $i < $validated['quantity']; $i++) {
                $tickets->push(Ticket::create([
                    'tenant_id' => $event->tenant_id,
                    'event_id' => $event->id,
                    'transaction_id' => $transaction->id,
                    'ticket_category_id' => $category->id,
                    'ticket_code' => 'GTX-' . strtoupper(Str::random(10)),
                    'status' => 'sold',
                    'visitor_data' => [
                        'name' => $validated['customer_name'],
                        'email' => $validated['customer_email'],
                        'phone' => $validated['customer_phone'],
                        'nik' => $validated['customer_nik'] ?? null,
                        'purchase_flow' => $event->purchase_flow ?? 'redeem',
                    ],
                ]));
            }

            $category->increment('sold_count', $validated['quantity']);
        });

        foreach ($tickets as $ticket) {
            try {
                app(TicketNotificationService::class)->sendEVoucher($ticket);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $flow = $event->purchase_flow ?? 'redeem';

        if ($flow === 'print') {
            return redirect()->route('organizer.pos.print', $transaction);
        }

        if ($flow === 'both') {
            $message = 'Transaksi berhasil. E-Voucher telah dibuat dan siap dikirim via WhatsApp.';
        } elseif ($flow === 'evoucher') {
            $message = 'Transaksi berhasil. E-Voucher telah dibuat dan QR dapat discan di gate.';
        } else {
            $message = 'Transaksi berhasil. Lanjutkan proses redeem untuk pemasangan wristband.';
        }

        $activeTicket = $tickets->first();
        $waUrl = null;
        if ($activeTicket && ($flow === 'both' || $flow === 'evoucher')) {
            $ticketCode = $activeTicket->ticket_code;
            $categoryName = $category->name;
            $eventName = $event->name;
            $url = route('tickets.view', $ticketCode);
            $phone = $validated['customer_phone'];
            
            $waMessage = "*E-Voucher {$eventName}*\n\n"
                . "Halo, terima kasih telah melakukan pembelian tiket.\n\n"
                . "Detail Tiket:\n"
                . "Kategori: {$categoryName}\n"
                . "Kode Tiket: {$ticketCode}\n\n"
                . "Silakan tunjukkan QR Code pada link berikut:\n"
                . "{$url}\n\n"
                . "Sampai jumpa di lokasi!";
            
            $waUrl = "https://api.whatsapp.com/send?phone=" . urlencode($phone) . "&text=" . urlencode($waMessage);
        }

        return redirect()
            ->route('organizer.pos.create', $event)
            ->with('success', $message)
            ->with('active_transaction_id', $transaction->id)
            ->with('active_evoucher_url', $activeTicket ? route('tickets.view', $activeTicket->ticket_code) : null)
            ->with('active_whatsapp_url', $waUrl);
    }

    public function print(Transaction $transaction)
    {
        $transaction->load(['event', 'tenant', 'tickets.category']);
        $this->authorizeTenant($transaction->event);

        if (($transaction->event->purchase_flow ?? 'redeem') !== 'print') {
            abort(403, 'Cetak termal hanya tersedia untuk event dengan flow Cetak Ticket Langsung.');
        }

        return view('organizer.pos.print', compact('transaction'));
    }

    private function authorizeTenant(Event $event): void
    {
        if ($event->tenant_id !== auth()->user()->tenant_id && !auth()->user()->hasRole('Superadmin')) {
            abort(403, 'Unauthorized access to this event');
        }
    }
}
