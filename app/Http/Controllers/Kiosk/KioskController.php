<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\KioskPhoto;
use App\Models\PhotoboothTemplate;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\Transaction;
use App\Services\PhotoboothService;
use App\Services\TicketNotificationService;
use App\Services\WagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KioskController extends Controller
{
    /**
     * Show the Kiosk interface for a club/tenant.
     */
    public function index(Request $request, ?string $tenant_slug = 'persita', ?string $event_slug = null)
    {
        $tenant = Tenant::where('slug', $tenant_slug)->first();
        if (!$tenant) {
            $tenant = Tenant::firstOrFail();
        }

        // Find match/event
        if ($event_slug) {
            $event = Event::where('tenant_id', $tenant->id)
                ->where('slug', $event_slug)
                ->with([
                    'ticketCategories' => function ($q) {
                        $q->where('is_active', true)->orderBy('sort_order')->orderBy('price');
                    }
                ])
                ->firstOrFail();
        } else {
            $event = Event::where('tenant_id', $tenant->id)
                ->whereIn('status', ['published', 'private'])
                ->with([
                    'ticketCategories' => function ($q) {
                        $q->where('is_active', true)->orderBy('sort_order')->orderBy('price');
                    }
                ])
                ->orderByDesc('event_start_date')
                ->first();

            if (!$event) {
                // Fallback to any event in the tenant or system
                $event = Event::where('tenant_id', $tenant->id)->with('ticketCategories')->first()
                    ?: Event::with('ticketCategories')->firstOrFail();
            }
        }

        // Active ticket categories
        $categories = $event->ticketCategories;

        // Photobooth template for this match
        $photoboothTemplate = $event->getEffectivePhotoboothTemplate();
        if (!$photoboothTemplate) {
            // Default template if none configured yet
            $photoboothTemplate = new PhotoboothTemplate([
                'name' => 'Default Matchday Collectible',
                'primary_color' => '#5A2D82',
                'secondary_color' => '#F95700',
                'header_title' => $event->season_name ?: 'I LEAGUE 2026/2027',
                'header_subtitle' => 'FOOTBALL UNITES US',
                'badge_text' => 'MATCHDAY COLLECTIBLE',
                'footer_text' => 'SATU LIGA, SATU SEMANGAT',
                'show_qr' => true,
                'show_fan_name' => true,
                'show_match_info' => true,
                'show_team_logos' => true,
            ]);
        }

        // Other active matches for this club (for quick match switching if needed)
        $otherEvents = Event::where('tenant_id', $tenant->id)
            ->where('id', '!=', $event->id)
            ->whereIn('status', ['published', 'private'])
            ->orderByDesc('event_start_date')
            ->take(5)
            ->get();

        $maxTickets = $event->getMaxTicketsPerTransaction();
        $kioskIdentityMode = $event->meta['kiosk_identity_mode'] ?? ($tenant->meta['kiosk_identity_mode'] ?? 'both');

        return view('kiosk.index', compact('tenant', 'event', 'categories', 'photoboothTemplate', 'otherEvents', 'maxTickets', 'kioskIdentityMode'));
    }

    /**
     * Process Kiosk ticket purchase and generate QRIS.
     */
    public function checkout(Request $request, string $tenant_slug)
    {
        $tenant = Tenant::where('slug', $tenant_slug)->firstOrFail();

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'ticket_category_id' => 'nullable|exists:ticket_categories,id',
            'quantity' => 'nullable|integer|min:1',
            'items' => 'nullable|array',
            'items.*.category_id' => 'required_with:items|exists:ticket_categories,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'visitors' => 'nullable|array',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_nik' => 'nullable|string|max:20',
            'customer_gender' => 'nullable|string|in:Pria,Wanita',
            'customer_birthdate' => 'nullable|string|max:20',
        ]);

        $event = Event::where('tenant_id', $tenant->id)->findOrFail($validated['event_id']);
        $maxAllowed = $event->getMaxTicketsPerTransaction();

        // Normalize items array
        $items = [];
        $totalQuantity = 0;
        $totalAmount = 0;
        $totalServiceFee = 0;
        $primaryCategoryId = null;

        if (!empty($validated['items']) && is_array($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $qty = (int) ($item['quantity'] ?? 0);
                if ($qty <= 0)
                    continue;

                $cat = TicketCategory::where('event_id', $event->id)->findOrFail($item['category_id']);

                // Check quota per category
                if (($cat->sold_count + $qty) > $cat->quota) {
                    return response()->json([
                        'success' => false,
                        'message' => "Maaf, kuota tiket kategori {$cat->name} tidak mencukupi (sisa " . max(0, $cat->quota - $cat->sold_count) . ' tiket).',
                    ], 422);
                }

                $items[] = [
                    'category_id' => $cat->id,
                    'category_name' => $cat->name,
                    'price' => (int) $cat->price,
                    'service_fee' => (float) ($cat->service_fee ?? 0),
                    'quantity' => $qty,
                    'subtotal' => (int) (($cat->price + ($cat->service_fee ?? 0)) * $qty),
                ];

                $totalQuantity += $qty;
                $totalServiceFee += (float) ($cat->service_fee ?? 0) * $qty;
                $totalAmount += (int) (($cat->price + ($cat->service_fee ?? 0)) * $qty);

                if (!$primaryCategoryId) {
                    $primaryCategoryId = $cat->id;
                }
            }
        } elseif (!empty($validated['ticket_category_id'])) {
            $cat = TicketCategory::where('event_id', $event->id)->findOrFail($validated['ticket_category_id']);
            $qty = (int) ($validated['quantity'] ?? 1);

            if (($cat->sold_count + $qty) > $cat->quota) {
                return response()->json([
                    'success' => false,
                    'message' => "Maaf, kuota tiket kategori {$cat->name} tidak mencukupi.",
                ], 422);
            }

            $items[] = [
                'category_id' => $cat->id,
                'category_name' => $cat->name,
                'price' => (int) $cat->price,
                'service_fee' => (float) ($cat->service_fee ?? 0),
                'quantity' => $qty,
                'subtotal' => (int) (($cat->price + ($cat->service_fee ?? 0)) * $qty),
            ];

            $totalQuantity = $qty;
            $totalServiceFee = (float) ($cat->service_fee ?? 0) * $qty;
            $totalAmount = (int) (($cat->price + ($cat->service_fee ?? 0)) * $qty);
            $primaryCategoryId = $cat->id;
        }

        if ($totalQuantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan pilih minimal 1 tiket untuk melanjutkan.',
            ], 422);
        }

        if ($totalQuantity > $maxAllowed) {
            return response()->json([
                'success' => false,
                'message' => "Maksimal pembelian adalah {$maxAllowed} tiket per transaksi.",
            ], 422);
        }

        // Format phone
        $phone = preg_replace('/[^0-9]/', '', $validated['customer_phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        $referenceNo = 'KIOSK-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        // Compact items for reference storage
        $compactItems = array_map(function ($it) {
            return [
                'category_id' => (int) $it['category_id'],
                'quantity' => (int) $it['quantity'],
            ];
        }, $items);

        // Create transaction
        $transaction = Transaction::create([
            'tenant_id' => $tenant->id,
            'event_id' => $event->id,
            'ticket_category_id' => $primaryCategoryId,
            'quantity' => $totalQuantity,
            'reference_no' => $referenceNo,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $phone,
            'customer_nik' => $validated['customer_nik'] ?? null,
            'customer_gender' => $validated['customer_gender'] ?? null,
            'discount_amount' => 0,
            'service_fee' => $totalServiceFee,
            'total_amount' => $totalAmount,
            'payment_status' => 'pending',
            'payment_method' => 'QRIS',
            'channel' => 'kiosk',
            'payment_reference' => json_encode([
                'items' => $compactItems,
                'visitors' => $validated['visitors'] ?? [],
            ]),
        ]);

        // Create WAGO QRIS Payment
        $wagoService = new WagoService();
        $wagoResult = $wagoService->createPayment([
            'order_id' => $referenceNo,
            'nominal' => $totalAmount,
            'callback_url' => route('wago.notification'),
            'return_url' => route('checkout.success', $referenceNo),
            'payment_method' => 'QRIS',
        ], [
            'name' => $validated['customer_name'],
            'email' => $validated['customer_email'],
            'phone' => $phone,
        ]);

        // Generate base64 QR code image for kiosk screen
        $qrisDataString = $wagoResult['payment_url'] ?? $referenceNo;
        $qrSvg = QrCode::format('svg')
            ->size(280)
            ->errorCorrection('H')
            ->generate($qrisDataString);

        return response()->json([
            'success' => true,
            'reference_no' => $referenceNo,
            'transaction_id' => $transaction->id,
            'total_amount' => $totalAmount,
            'formatted_total' => 'Rp ' . number_format($totalAmount, 0, ',', '.'),
            'payment_url' => $wagoResult['payment_url'] ?? null,
            'qr_svg' => (string) $qrSvg,
            'qris_data' => $qrisDataString,
        ]);
    }

    /**
     * Poll transaction status from Kiosk.
     */
    public function checkStatus(Request $request, string $tenant_slug, string $reference)
    {
        $transaction = Transaction::where('reference_no', $reference)->with('event')->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'not_found',
                'paid' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        // If paid, ensure tickets are generated and email sent
        if ($transaction->payment_status === 'paid' && $transaction->tickets()->count() === 0) {
            $this->issueTicketsForTransaction($transaction);
        }

        $tickets = ($transaction->payment_status === 'paid') ? $this->formatTicketsForKiosk($transaction) : [];

        return response()->json([
            'status' => $transaction->payment_status,
            'paid' => $transaction->payment_status === 'paid',
            'reference_no' => $transaction->reference_no,
            'transaction_id' => $transaction->id,
            'customer_name' => $transaction->customer_name,
            'customer_email' => $transaction->customer_email,
            'customer_phone' => $transaction->customer_phone,
            'paid_at_formatted' => $transaction->paid_at ? $transaction->paid_at->format('d M Y H:i') : ($transaction->created_at ? $transaction->created_at->format('d M Y H:i') : now()->format('d M Y H:i')),
            'purchase_flow' => $transaction->event->purchase_flow ?? 'redeem',
            'quantity' => $transaction->quantity,
            'tickets' => $tickets,
        ]);
    }

    /**
     * Simulate payment success for Kiosk testing.
     */
    public function simulatePayment(Request $request, string $tenant_slug, string $reference)
    {
        return DB::transaction(function () use ($reference) {
            $transaction = Transaction::where('reference_no', $reference)->with('event')->lockForUpdate()->firstOrFail();

            if ($transaction->payment_status !== 'paid') {
                $transaction->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => 'QRIS (Simulasi)',
                ]);

                $this->issueTicketsForTransaction($transaction);
            }

            $tickets = $this->formatTicketsForKiosk($transaction);

            return response()->json([
                'success' => true,
                'status' => 'paid',
                'paid' => true,
                'reference_no' => $transaction->reference_no,
                'transaction_id' => $transaction->id,
                'customer_name' => $transaction->customer_name,
                'customer_email' => $transaction->customer_email,
                'customer_phone' => $transaction->customer_phone,
                'paid_at_formatted' => $transaction->paid_at ? $transaction->paid_at->format('d M Y H:i') : ($transaction->created_at ? $transaction->created_at->format('d M Y H:i') : now()->format('d M Y H:i')),
                'purchase_flow' => $transaction->event->purchase_flow ?? 'redeem',
                'quantity' => $transaction->quantity,
                'tickets' => $tickets,
                'message' => 'Pembayaran simulasi berhasil diverifikasi.',
            ]);
        });
    }

    /**
     * Helper to format tickets for Kiosk display (including individual QR codes and thermal metadata).
     */
    protected function formatTicketsForKiosk(Transaction $transaction)
    {
        return $transaction->tickets()->with('category')->where('status', '!=', 'void')->take($transaction->quantity)->get()->map(function ($ticket, $idx) use ($transaction) {
            $qrSvg = (string) QrCode::format('svg')
                ->size(180)
                ->margin(0)
                ->errorCorrection('H')
                ->generate($ticket->ticket_code);

            return [
                'id' => $ticket->id,
                'ticket_number' => $idx + 1,
                'total_tickets' => $transaction->quantity,
                'ticket_code' => $ticket->ticket_code,
                'category_name' => $ticket->category->name ?? 'Regular',
                'visitor_name' => $ticket->visitor_data['name'] ?? $transaction->customer_name ?? 'Pengunjung',
                'visitor_phone' => $ticket->visitor_data['phone'] ?? $transaction->customer_phone ?? '-',
                'visitor_email' => $ticket->visitor_data['email'] ?? $transaction->customer_email ?? '-',
                'qr_svg' => $qrSvg,
                'status' => $ticket->status,
                'is_redeemed' => $ticket->status === 'redeemed',
                'redeemed_at' => $ticket->redeemed_at ? $ticket->redeemed_at->format('d M Y H:i') : null,
            ];
        });
    }

    /**
     * Public Kiosk standalone thermal print page (matches Organizer POS thermal layout).
     */
    public function printThermal(Request $request, string $tenant_slug, string $reference)
    {
        $tenant = Tenant::where('slug', $tenant_slug)->firstOrFail();

        $transaction = Transaction::where('reference_no', $reference)
            ->where('tenant_id', $tenant->id)
            ->with(['event', 'tenant', 'tickets.category'])
            ->firstOrFail();

        if ($transaction->payment_status !== 'paid' && !in_array($transaction->payment_method, ['free', 'promo'], true)) {
            abort(403, 'Transaksi belum dibayar.');
        }

        if (!in_array($transaction->event->purchase_flow ?? 'redeem', ['print', 'both'], true)) {
            abort(403, 'Cetak termal hanya tersedia untuk event dengan flow Cetak Ticket atau Evoucher + Cetak.');
        }

        $tickets = $transaction->tickets->where('status', '!=', 'void');
        if ($request->filled('tickets')) {
            $codes = array_filter(array_map('trim', explode(',', (string) $request->query('tickets'))));
            if (!empty($codes)) {
                $tickets = $tickets->whereIn('ticket_code', $codes);
            }
        }

        return view('kiosk.print', compact('transaction', 'tickets', 'tenant'));
    }

    /**
     * Save photobooth capture and send email.
     */
    public function savePhoto(Request $request, string $tenant_slug)
    {
        $tenant = Tenant::where('slug', $tenant_slug)->firstOrFail();

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'transaction_id' => 'nullable|exists:transactions,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'category_name' => 'nullable|string|max:100',
            'card_data' => 'required|string', // base64 composite card
            'raw_photo_data' => 'nullable|string', // base64 camera capture
        ]);

        $photoboothService = new PhotoboothService();
        $photo = $photoboothService->saveKioskPhoto([
            'tenant_id' => $tenant->id,
            'event_id' => $validated['event_id'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'category_name' => $validated['category_name'] ?? null,
            'card_data' => $validated['card_data'],
            'raw_photo_data' => $validated['raw_photo_data'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'token' => $photo->token,
            'photo_url' => asset('storage/' . $photo->card_photo),
            'download_url' => route('kiosk.photo.view', $photo->token),
            'message' => 'Foto berhasil disimpan dan dikirim ke email.',
        ]);
    }

    /**
     * Public page to view and download collectible photo via QR scan.
     */
    public function viewPhoto(string $token)
    {
        $photo = KioskPhoto::where('token', $token)->with(['event', 'tenant'])->firstOrFail();
        $photo->increment('download_count');

        return view('kiosk.photo_view', compact('photo'));
    }

    /**
     * Direct file download for collectible photo.
     */
    public function downloadPhoto(string $token)
    {
        $photo = KioskPhoto::where('token', $token)->firstOrFail();
        $path = Storage::disk('public')->path($photo->card_photo);

        if (!file_exists($path)) {
            abort(404, 'File foto tidak ditemukan.');
        }

        $filename = 'Matchday-Collectible-' . Str::slug($photo->customer_name) . '-' . substr($token, 0, 6) . '.jpg';
        return response()->download($path, $filename, ['Content-Type' => 'image/jpeg']);
    }

    /**
     * Extract clean ticket code or invoice reference from raw manual/QR input.
     */
    protected function extractLookupCandidates(string $rawCode): array
    {
        $rawCode = trim($rawCode);
        $extracted = $rawCode;
        if (preg_match('/(GTX-[A-Za-z0-9_-]+)/i', $rawCode, $matches)) {
            $extracted = $matches[1];
        } else {
            $pathPart = basename(parse_url($rawCode, PHP_URL_PATH) ?: $rawCode);
            if ($pathPart !== '') {
                $extracted = $pathPart;
            }
        }
        $cleanRaw = preg_replace('/[\s]+/', '', $rawCode);
        $cleanExtracted = preg_replace('/[\s]+/', '', $extracted);

        return array_values(array_unique(array_filter([$rawCode, $cleanRaw, $extracted, $cleanExtracted])));
    }

    /**
     * Format single Ticket model into Kiosk payload (for redeem & thermal print).
     */
    protected function formatSingleTicketPayload(Ticket $ticket): array
    {
        $tx = $ticket->transaction;
        $qrSvg = (string) QrCode::format('svg')
            ->size(180)
            ->margin(0)
            ->errorCorrection('H')
            ->generate($ticket->ticket_code);

        return [
            'id' => $ticket->id,
            'ticket_code' => $ticket->ticket_code,
            'category_name' => $ticket->category->name ?? 'Regular',
            'visitor_name' => $ticket->visitor_data['name'] ?? $tx->customer_name ?? 'Pengunjung',
            'visitor_phone' => $ticket->visitor_data['phone'] ?? $tx->customer_phone ?? '-',
            'visitor_email' => $ticket->visitor_data['email'] ?? $tx->customer_email ?? '-',
            'qr_svg' => $qrSvg,
            'status' => $ticket->status,
            'is_redeemed' => $ticket->status === 'redeemed',
            'redeemed_at' => $ticket->redeemed_at ? $ticket->redeemed_at->format('d M Y H:i') : null,
        ];
    }

    /**
     * Handle Ticket Exchange / Penukaran Tiket lookup on Kiosk.
     */
    public function redeemLookup(Request $request, string $tenant_slug)
    {
        $tenant = Tenant::where('slug', $tenant_slug)->firstOrFail();

        $rawInput = trim((string) $request->input('code', ''));
        if ($rawInput === '') {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masukkan kode tiket, nomor invoice, atau scan QR voucher.',
            ], 422);
        }

        $candidates = $this->extractLookupCandidates($rawInput);
        $tickets = collect();
        $transaction = null;

        // 1. Try direct ticket_code or wristband_qr lookup first
        $directTicket = Ticket::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($candidates) {
                $q->whereIn('ticket_code', $candidates)
                  ->orWhereIn('wristband_qr', $candidates);
            })
            ->with(['event', 'category', 'transaction'])
            ->first();

        if ($directTicket) {
            $transaction = $directTicket->transaction;
            $tickets = collect([$directTicket]);
        } else {
            // 2. Try transaction reference_no lookup (returns all non-void tickets in the invoice)
            $transaction = Transaction::where('tenant_id', $tenant->id)
                ->whereIn('reference_no', array_map('strtoupper', $candidates))
                ->with(['event', 'category', 'tickets.category'])
                ->first();

            if (!$transaction) {
                $transaction = Transaction::where('tenant_id', $tenant->id)
                    ->whereIn('reference_no', $candidates)
                    ->with(['event', 'category', 'tickets.category'])
                    ->first();
            }

            if ($transaction) {
                $tickets = $transaction->tickets->where('status', '!=', 'void')->values();
            }
        }

        if ($tickets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket atau nomor invoice tersebut tidak ditemukan.',
            ], 404);
        }

        // Verify payment status
        if ($transaction && $transaction->payment_status !== 'paid' && !in_array($transaction->payment_method, ['free', 'promo'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran transaksi ini belum lunas (Status: ' . strtoupper($transaction->payment_status) . ').',
            ], 422);
        }

        $activeTickets = $tickets->filter(fn ($t) => $t->status !== 'void')->values();
        if ($activeTickets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket ini sudah dibatalkan (void).',
            ], 422);
        }

        $primaryTicket = $activeTickets->first();
        $event = $primaryTicket->event ?? $transaction?->event;

        // Validate against currently opened event match on /kiosk/{slug}/match/{event_slug} if provided
        if ($request->filled('event_id') && $event && (int) $request->input('event_id') !== (int) $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket ini terdaftar untuk pertandingan "' . $event->name . '", bukan pertandingan yang sedang aktif di terminal ini.',
            ], 422);
        }

        $formattedTickets = $activeTickets->map(fn ($t) => $this->formatSingleTicketPayload($t))->values();
        $eventVenueCity = $event ? trim(($event->venue ?? '') . ($event->city ? ' · ' . $event->city : '')) : '-';

        return response()->json([
            'success' => true,
            'reference_no' => $transaction->reference_no ?? $primaryTicket->ticket_code,
            'event_id' => $event->id ?? null,
            'event_name' => $event->name ?? '-',
            'event_venue_city' => $eventVenueCity,
            'event_start_formatted' => ($event && $event->event_start_date) ? $event->event_start_date->format('d M Y H:i') : '-',
            'purchase_flow' => $event->purchase_flow ?? 'redeem',
            'thermal_width_mm' => (int) ($event->thermal_paper_width_mm ?? 80),
            'thermal_height_mm' => (int) ($event->thermal_paper_height_mm ?? 160),
            'customer_name' => $transaction->customer_name ?? ($primaryTicket->visitor_data['name'] ?? 'Pengunjung'),
            'customer_phone' => $transaction->customer_phone ?? '-',
            'customer_email' => $transaction->customer_email ?? '-',
            'paid_at_formatted' => ($transaction && $transaction->paid_at)
                ? $transaction->paid_at->format('d M Y H:i')
                : ($transaction && $transaction->created_at ? $transaction->created_at->format('d M Y H:i') : now()->format('d M Y H:i')),
            'tickets' => $formattedTickets,
            // Legacy scalar fields for backward compatibility
            'ticket_code' => $primaryTicket->ticket_code,
            'status' => $primaryTicket->status,
            'category_name' => $primaryTicket->category->name ?? 'Regular',
            'visitor_name' => $primaryTicket->visitor_data['name'] ?? $transaction->customer_name ?? 'Pengunjung',
            'is_redeemed' => $primaryTicket->status === 'redeemed',
            'redeemed_at' => $primaryTicket->redeemed_at ? $primaryTicket->redeemed_at->format('d M Y H:i') : null,
        ]);
    }

    /**
     * Confirm ticket exchange / thermal print on Kiosk (prevents duplicate printing/redeem).
     */
    public function redeemProcess(Request $request, string $tenant_slug)
    {
        $tenant = Tenant::where('slug', $tenant_slug)->firstOrFail();

        $validated = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer',
            'event_id' => 'nullable|integer',
        ]);

        return DB::transaction(function () use ($tenant, $validated) {
            $tickets = Ticket::where('tenant_id', $tenant->id)
                ->whereIn('id', $validated['ticket_ids'])
                ->with(['event', 'category', 'transaction'])
                ->lockForUpdate()
                ->get();

            if ($tickets->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tiket yang dipilih tidak ditemukan.',
                ], 404);
            }

            $primaryTicket = $tickets->first();
            $event = $primaryTicket->event;
            $transaction = $primaryTicket->transaction;
            $flow = $event->purchase_flow ?? 'redeem';

            if (!empty($validated['event_id']) && $event && (int) $validated['event_id'] !== (int) $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tiket tidak sesuai dengan pertandingan di Kiosk ini.',
                ], 422);
            }

            if (!in_array($flow, ['print', 'both'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penukaran cetak termal di Kiosk hanya tersedia untuk flow Cetak Ticket atau Evoucher + Cetak.',
                ], 422);
            }

            $validToRedeem = [];
            foreach ($tickets as $ticket) {
                $tx = $ticket->transaction;
                if ($tx && $tx->payment_status !== 'paid' && !in_array($tx->payment_method, ['free', 'promo'], true)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Tiket {$ticket->ticket_code} belum berstatus lunas.",
                    ], 422);
                }
                if ($ticket->status === 'void') {
                    return response()->json([
                        'success' => false,
                        'message' => "Tiket {$ticket->ticket_code} telah dibatalkan.",
                    ], 422);
                }
                if ($ticket->status === 'redeemed') {
                    return response()->json([
                        'success' => false,
                        'message' => "Tiket {$ticket->ticket_code} sudah ditukar / dicetak pada " . ($ticket->redeemed_at ? $ticket->redeemed_at->format('d M Y H:i') : '-'),
                    ], 422);
                }
                $validToRedeem[] = $ticket;
            }

            $now = now();
            $formattedTickets = [];
            foreach ($validToRedeem as $ticket) {
                $ticket->update([
                    'status' => 'redeemed',
                    'redeemed_at' => $now,
                ]);
                $formattedTickets[] = $this->formatSingleTicketPayload($ticket->fresh(['category', 'transaction']));
            }

            $eventVenueCity = $event ? trim(($event->venue ?? '') . ($event->city ? ' · ' . $event->city : '')) : '-';

            return response()->json([
                'success' => true,
                'message' => count($formattedTickets) . ' tiket berhasil ditukar dan siap dicetak.',
                'reference_no' => $transaction->reference_no ?? $primaryTicket->ticket_code,
                'event_name' => $event->name ?? '-',
                'event_venue_city' => $eventVenueCity,
                'event_start_formatted' => ($event && $event->event_start_date) ? $event->event_start_date->format('d M Y H:i') : '-',
                'thermal_width_mm' => (int) ($event->thermal_paper_width_mm ?? 80),
                'thermal_height_mm' => (int) ($event->thermal_paper_height_mm ?? 160),
                'paid_at_formatted' => ($transaction && $transaction->paid_at)
                    ? $transaction->paid_at->format('d M Y H:i')
                    : ($transaction && $transaction->created_at ? $transaction->created_at->format('d M Y H:i') : $now->format('d M Y H:i')),
                'tickets' => $formattedTickets,
            ]);
        });
    }

    /**
     * Issue tickets and send notification upon successful payment.
     */
    protected function issueTicketsForTransaction(Transaction $transaction): void
    {
        if ($transaction->tickets()->exists()) {
            return;
        }

        DB::transaction(function () use ($transaction) {
            // Strict idempotency check within transaction lock
            if (Ticket::where('transaction_id', $transaction->id)->exists()) {
                return;
            }
            $event = $transaction->event;
            $category = $transaction->category;

            $meta = json_decode($transaction->payment_reference ?? '', true) ?: [];
            $items = $meta['items'] ?? null;
            $visitors = $meta['visitors'] ?? [];

            $tickets = collect();
            $ticketIndex = 0;

            if (!empty($items) && is_array($items)) {
                foreach ($items as $item) {
                    $catId = $item['category_id'] ?? $transaction->ticket_category_id;
                    $qty = (int) ($item['quantity'] ?? 1);
                    $cat = TicketCategory::find($catId);

                    for ($k = 0; $k < $qty; $k++) {
                        $visitor = $visitors[$ticketIndex] ?? [];
                        $vName = !empty($visitor['name']) ? $visitor['name'] : $transaction->customer_name;
                        $vEmail = !empty($visitor['email']) ? $visitor['email'] : $transaction->customer_email;
                        $vPhone = !empty($visitor['phone']) ? $visitor['phone'] : $transaction->customer_phone;
                        $vNik = !empty($visitor['nik']) ? $visitor['nik'] : $transaction->customer_nik;
                        $vGender = !empty($visitor['gender']) ? $visitor['gender'] : $transaction->customer_gender;

                        $tickets->push(Ticket::create([
                            'tenant_id' => $transaction->tenant_id,
                            'event_id' => $transaction->event_id,
                            'transaction_id' => $transaction->id,
                            'ticket_category_id' => $catId,
                            'ticket_code' => Ticket::generateTicketCode(),
                            'status' => 'sold',
                            'visitor_data' => [
                                'name' => $vName,
                                'email' => $vEmail,
                                'phone' => $vPhone,
                                'nik' => $vNik,
                                'gender' => $vGender,
                                'purchase_flow' => $event->purchase_flow ?? 'redeem',
                                'channel' => 'kiosk',
                            ],
                        ]));
                        $ticketIndex++;
                    }

                    if ($cat) {
                        $cat->increment('sold_count', $qty);
                    }
                }
            } else {
                for ($i = 0; $i < $transaction->quantity; $i++) {
                    $visitor = $visitors[$i] ?? [];
                    $vName = !empty($visitor['name']) ? $visitor['name'] : $transaction->customer_name;
                    $vEmail = !empty($visitor['email']) ? $visitor['email'] : $transaction->customer_email;
                    $vPhone = !empty($visitor['phone']) ? $visitor['phone'] : $transaction->customer_phone;
                    $vNik = !empty($visitor['nik']) ? $visitor['nik'] : $transaction->customer_nik;
                    $vGender = !empty($visitor['gender']) ? $visitor['gender'] : $transaction->customer_gender;

                    $tickets->push(Ticket::create([
                        'tenant_id' => $transaction->tenant_id,
                        'event_id' => $transaction->event_id,
                        'transaction_id' => $transaction->id,
                        'ticket_category_id' => $transaction->ticket_category_id,
                        'ticket_code' => Ticket::generateTicketCode(),
                        'status' => 'sold',
                        'visitor_data' => [
                            'name' => $vName,
                            'email' => $vEmail,
                            'phone' => $vPhone,
                            'nik' => $vNik,
                            'gender' => $vGender,
                            'purchase_flow' => $event->purchase_flow ?? 'redeem',
                            'channel' => 'kiosk',
                        ],
                    ]));
                }

                if ($category) {
                    $category->increment('sold_count', $transaction->quantity);
                }
            }

            // Send E-Voucher notification
            try {
                if ($tickets->isNotEmpty()) {
                    app(TicketNotificationService::class)->sendEVoucher($tickets->first());
                }
            } catch (\Throwable $e) {
                Log::warning('Failed sending e-voucher from kiosk: ' . $e->getMessage());
            }
        });
    }
}
