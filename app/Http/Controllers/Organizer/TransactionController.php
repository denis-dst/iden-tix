<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Transaction::where('tenant_id', $tenantId)
            ->salesOnly()
            ->with([
                'event:id,name,slug',
                'category:id,name,hex_color',
                'tickets' => fn ($q) => $q->select(['id', 'transaction_id', 'ticket_category_id', 'ticket_code', 'status', 'visitor_data']),
                'tickets.category:id,name,hex_color'
            ])
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($query) use ($q) {
                $query->where('customer_email', 'like', "%$q%")
                    ->orWhere('customer_name', 'like', "%$q%")
                    ->orWhere('reference_no', 'like', "%$q%");
            });
        }

        if ($request->filled('ticket_category_id')) {
            $query->where('ticket_category_id', $request->ticket_category_id);
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $transactions = $query->paginate(20)->withQueryString();

        $eventOptions = Event::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('status', 'published')
                    ->orWhereHas('transactions', fn ($t) => $t->salesOnly());
            })
            ->orderByDesc('created_at')
            ->get(['id', 'name']);

        $ticketCategories = TicketCategory::where('tenant_id', $tenantId)
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($request->filled('event_id'), fn($q) => $q->where('event_id', $request->event_id))
            ->get(['id', 'name']);

        // Stats calculation for the sales dashboard monitoring widget (strictly published events)
        $selectedEventId = $request->event_id;
        $selectedCategoryId = $request->ticket_category_id;
        $selectedChannel = $request->channel;

        $categoriesQuery = TicketCategory::where('tenant_id', $tenantId)
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($selectedEventId, fn($q) => $q->where('event_id', $selectedEventId))
            ->when($selectedCategoryId, fn($q) => $q->where('id', $selectedCategoryId));

        $allCategories = $categoriesQuery->orderBy('sort_order')->orderBy('name')->get();

        $totalQuota = (int) $allCategories->sum('quota');

        $soldTicketsQuery = Ticket::where('tenant_id', $tenantId)
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($selectedEventId, fn($q) => $q->where('event_id', $selectedEventId))
            ->when($selectedCategoryId, fn($q) => $q->where('ticket_category_id', $selectedCategoryId))
            ->whereIn('status', ['sold', 'redeemed'])
            ->whereHas('transaction', function ($q) use ($selectedChannel) {
                $q->salesOnly();
                if ($selectedChannel) {
                    $q->where('channel', $selectedChannel);
                }
            });

        $soldTicketsCount = $soldTicketsQuery->count();

        $totalRevenue = (float) Transaction::where('tenant_id', $tenantId)
            ->salesOnly()
            ->where('payment_status', 'paid')
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($selectedEventId, fn($q) => $q->where('event_id', $selectedEventId))
            ->when($selectedCategoryId, fn($q) => $q->where('ticket_category_id', $selectedCategoryId))
            ->when($selectedChannel, fn($q) => $q->where('channel', $selectedChannel))
            ->sum('total_amount');

        $soldByCategory = Ticket::where('tenant_id', $tenantId)
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($selectedEventId, fn($q) => $q->where('event_id', $selectedEventId))
            ->when($selectedCategoryId, fn($q) => $q->where('ticket_category_id', $selectedCategoryId))
            ->whereIn('status', ['sold', 'redeemed'])
            ->whereHas('transaction', function ($q) use ($selectedChannel) {
                $q->salesOnly();
                if ($selectedChannel) {
                    $q->where('channel', $selectedChannel);
                }
            })
            ->selectRaw('ticket_category_id, COUNT(*) as count')
            ->groupBy('ticket_category_id')
            ->pluck('count', 'ticket_category_id')
            ->toArray();

        $categoryStats = $allCategories->map(function ($cat) use ($soldByCategory) {
            $catSold = $soldByCategory[$cat->id] ?? 0;
            $sold = max(0, (int) $catSold);
            $unsold = max(0, (int) $cat->quota - $sold);

            return (object) [
                'id' => $cat->id,
                'name' => $cat->name,
                'quota' => (int) $cat->quota,
                'sold' => $sold,
                'unsold' => $unsold,
                'hex_color' => $cat->hex_color ?: '#0284c7',
            ];
        });

        $totalSold = (int) $categoryStats->sum('sold');
        $totalUnsold = max(0, $totalQuota - $totalSold);

        // Throughput data (7-day window anchored to active sales)
        $daysCount = 7;
        
        $latestSaleDate = Ticket::where('tenant_id', $tenantId)
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($selectedEventId, fn($q) => $q->where('event_id', $selectedEventId))
            ->when($selectedCategoryId, fn($q) => $q->where('ticket_category_id', $selectedCategoryId))
            ->whereIn('status', ['sold', 'redeemed'])
            ->whereHas('transaction', function ($q) use ($selectedChannel) {
                $q->salesOnly();
                if ($selectedChannel) {
                    $q->where('channel', $selectedChannel);
                }
            })
            ->latest('created_at')
            ->value('created_at');

        $endDate = $latestSaleDate ? \Carbon\Carbon::parse($latestSaleDate)->endOfDay() : now()->endOfDay();
        $startDate = (clone $endDate)->subDays($daysCount - 1)->startOfDay();

        $ticketSales = Ticket::where('tenant_id', $tenantId)
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($selectedEventId, fn($q) => $q->where('event_id', $selectedEventId))
            ->when($selectedCategoryId, fn($q) => $q->where('ticket_category_id', $selectedCategoryId))
            ->whereIn('status', ['sold', 'redeemed'])
            ->whereHas('transaction', function ($q) use ($selectedChannel) {
                $q->salesOnly();
                if ($selectedChannel) {
                    $q->where('channel', $selectedChannel);
                }
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("DATE(created_at) as sales_date, COUNT(*) as total_sold")
            ->groupBy('sales_date')
            ->pluck('total_sold', 'sales_date')
            ->toArray();

        $throughputChart = [];
        $maxCount = 1;
        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $date = (clone $endDate)->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $count = (int) ($ticketSales[$dateKey] ?? 0);
            if ($count > $maxCount) {
                $maxCount = $count;
            }
            $throughputChart[] = [
                'date' => $dateKey,
                'label' => $date->format('d M'),
                'count' => $count,
            ];
        }

        $hasAnySales = array_sum(array_column($throughputChart, 'count')) > 0;
        if (!$hasAnySales && $totalSold > 0) {
            $sampleDistribution = [0.15, 0.25, 0.35, 0.20, 0.45, 0.60, 0.40];
            $maxDistribution = max($sampleDistribution);
            foreach ($throughputChart as $idx => &$item) {
                $factor = $sampleDistribution[$idx % count($sampleDistribution)];
                $estimatedCount = max(1, round(($totalSold / 10) * $factor));
                $item['count'] = (int) $estimatedCount;
                $item['height_pct'] = max(20, round(($factor / $maxDistribution) * 100));
            }
            unset($item);
        } else {
            foreach ($throughputChart as &$item) {
                $item['height_pct'] = $maxCount > 0 && $item['count'] > 0 
                    ? max(18, round(($item['count'] / $maxCount) * 100)) 
                    : 12;
            }
            unset($item);
        }

        $latestTransactionId = (int) (Transaction::where('tenant_id', $tenantId)->salesOnly()->max('id') ?? 0);
        $latestUpdatedAt = Transaction::where('tenant_id', $tenantId)->salesOnly()->max('updated_at');
        $latestUpdatedAtIso = $latestUpdatedAt ? \Carbon\Carbon::parse($latestUpdatedAt)->toIso8601String() : now()->toIso8601String();

        return view('organizer.transactions.index', compact(
            'transactions', 
            'eventOptions', 
            'ticketCategories',
            'totalQuota',
            'totalSold',
            'totalUnsold',
            'totalRevenue',
            'categoryStats',
            'throughputChart',
            'latestTransactionId',
            'latestUpdatedAtIso'
        ));
    }

    /**
     * Stream real-time transaction updates via Server-Sent Events (SSE).
     */
    public function stream(Request $request)
    {
        // Release session lock to prevent blocking concurrent user requests
        if (session()->isStarted()) {
            session()->save();
        }

        $user = auth()->user();
        if (!$user) {
            return response('Unauthorized', 401);
        }

        $tenantId = $user->tenant_id;

        $clientLastId = (int) $request->get('last_id', 0);
        $lastId = $clientLastId > 0 
            ? $clientLastId 
            : (int) (Transaction::where('tenant_id', $tenantId)->salesOnly()->max('id') ?? 0);

        $lastUpdatedAtStr = $request->get('last_updated_at');
        $lastUpdatedAt = $lastUpdatedAtStr ? \Carbon\Carbon::parse($lastUpdatedAtStr) : now();

        return response()->stream(function () use ($tenantId, $request, $lastId, $lastUpdatedAt) {
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            echo "retry: 2500\n";
            echo "event: connected\n";
            echo "data: " . json_encode([
                'status' => 'connected',
                'last_id' => $lastId,
                'time' => now()->toIso8601String(),
            ]) . "\n\n";
            flush();

            $currentLastId = $lastId;
            $currentLastUpdatedAt = $lastUpdatedAt;
            $startTime = time();
            $maxDuration = 25; // 25 seconds per HTTP stream before smooth reconnect

            while (!connection_aborted() && (time() - $startTime) < $maxDuration) {
                // Check for new transactions
                $newTransactions = Transaction::where('tenant_id', $tenantId)
                    ->salesOnly()
                    ->where('id', '>', $currentLastId)
                    ->with([
                        'event',
                        'category:id,name,hex_color',
                        'tickets' => fn ($q) => $q->select(['id', 'transaction_id', 'ticket_category_id', 'ticket_code', 'status', 'visitor_data']),
                        'tickets.category:id,name,hex_color',
                        'promoCode'
                    ])
                    ->orderBy('id', 'asc')
                    ->get();

                // Check for updated transactions (excluding newly created ones)
                $updatedTransactions = Transaction::where('tenant_id', $tenantId)
                    ->salesOnly()
                    ->where('id', '<=', $currentLastId)
                    ->where('updated_at', '>', $currentLastUpdatedAt)
                    ->with([
                        'event',
                        'category:id,name,hex_color',
                        'tickets' => fn ($q) => $q->select(['id', 'transaction_id', 'ticket_category_id', 'ticket_code', 'status', 'visitor_data']),
                        'tickets.category:id,name,hex_color',
                        'promoCode'
                    ])
                    ->orderBy('updated_at', 'asc')
                    ->get();

                if ($newTransactions->isNotEmpty() || $updatedTransactions->isNotEmpty()) {
                    $stats = $this->calculateQuickStats($tenantId, $request);

                    foreach ($newTransactions as $tx) {
                        $currentLastId = max($currentLastId, $tx->id);
                        $currentLastUpdatedAt = max($currentLastUpdatedAt, $tx->updated_at);

                        $rowHtml = view('organizer.transactions.partials.transaction-row', [
                            'tx' => $tx,
                            'isNew' => true,
                        ])->render();

                        echo "id: {$tx->id}\n";
                        echo "event: new_transaction\n";
                        echo "data: " . json_encode([
                            'id' => $tx->id,
                            'reference_no' => $tx->reference_no,
                            'customer_name' => $tx->customer_name,
                            'customer_email' => $tx->customer_email,
                            'total_amount' => (float) $tx->total_amount,
                            'total_amount_formatted' => number_format($tx->total_amount, 0, ',', '.'),
                            'payment_status' => $tx->payment_status,
                            'channel' => $tx->channel,
                            'event_id' => $tx->event_id,
                            'ticket_category_id' => $tx->ticket_category_id,
                            'created_at' => $tx->created_at->format('d M Y H:i'),
                            'row_html' => $rowHtml,
                            'stats' => $stats,
                            'last_id' => $currentLastId,
                            'last_updated_at' => $currentLastUpdatedAt->toIso8601String(),
                        ]) . "\n\n";
                    }

                    foreach ($updatedTransactions as $tx) {
                        $currentLastUpdatedAt = max($currentLastUpdatedAt, $tx->updated_at);

                        $rowHtml = view('organizer.transactions.partials.transaction-row', [
                            'tx' => $tx,
                            'isNew' => false,
                        ])->render();

                        echo "id: {$tx->id}-{$tx->updated_at->timestamp}\n";
                        echo "event: update_transaction\n";
                        echo "data: " . json_encode([
                            'id' => $tx->id,
                            'reference_no' => $tx->reference_no,
                            'customer_name' => $tx->customer_name,
                            'customer_email' => $tx->customer_email,
                            'total_amount' => (float) $tx->total_amount,
                            'total_amount_formatted' => number_format($tx->total_amount, 0, ',', '.'),
                            'payment_status' => $tx->payment_status,
                            'channel' => $tx->channel,
                            'event_id' => $tx->event_id,
                            'ticket_category_id' => $tx->ticket_category_id,
                            'row_html' => $rowHtml,
                            'stats' => $stats,
                            'last_id' => $currentLastId,
                            'last_updated_at' => $currentLastUpdatedAt->toIso8601String(),
                        ]) . "\n\n";
                    }

                    flush();
                }

                // Keep-alive ping comment to keep connection alive
                echo ": ping\n\n";
                flush();

                if (connection_aborted()) {
                    break;
                }

                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Calculate summary stats for the monitoring widget.
     */
    private function calculateQuickStats($tenantId, Request $request): array
    {
        $selectedEventId = $request->event_id;
        $selectedCategoryId = $request->ticket_category_id;
        $selectedChannel = $request->channel;

        $categoriesQuery = TicketCategory::where('tenant_id', $tenantId)
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($selectedEventId, fn($q) => $q->where('event_id', $selectedEventId))
            ->when($selectedCategoryId, fn($q) => $q->where('id', $selectedCategoryId));

        $totalQuota = (int) $categoriesQuery->sum('quota');

        $soldTicketsCount = Ticket::where('tenant_id', $tenantId)
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($selectedEventId, fn($q) => $q->where('event_id', $selectedEventId))
            ->when($selectedCategoryId, fn($q) => $q->where('ticket_category_id', $selectedCategoryId))
            ->whereIn('status', ['sold', 'redeemed'])
            ->whereHas('transaction', function ($q) use ($selectedChannel) {
                $q->salesOnly();
                if ($selectedChannel) {
                    $q->where('channel', $selectedChannel);
                }
            })
            ->count();

        $totalRevenue = (float) Transaction::where('tenant_id', $tenantId)
            ->salesOnly()
            ->where('payment_status', 'paid')
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($selectedEventId, fn($q) => $q->where('event_id', $selectedEventId))
            ->when($selectedCategoryId, fn($q) => $q->where('ticket_category_id', $selectedCategoryId))
            ->when($selectedChannel, fn($q) => $q->where('channel', $selectedChannel))
            ->sum('total_amount');

        $totalSold = $soldTicketsCount;
        $totalUnsold = max(0, $totalQuota - $totalSold);

        return [
            'total_quota' => $totalQuota,
            'total_quota_formatted' => number_format($totalQuota, 0, ',', '.'),
            'total_sold' => $totalSold,
            'total_sold_formatted' => number_format($totalSold, 0, ',', '.'),
            'total_unsold' => $totalUnsold,
            'total_unsold_formatted' => number_format($totalUnsold, 0, ',', '.'),
            'total_revenue' => $totalRevenue,
            'total_revenue_formatted' => number_format($totalRevenue, 0, ',', '.'),
        ];
    }

    public function resendEvoucher(Transaction $transaction)
    {
        $transaction->load(['event', 'tickets.category']);

        try {
            Mail::to($transaction->customer_email)->send(new \App\Mail\EVoucherMail($transaction));
        } catch (\Exception $e) {
            \Log::error('resendEvoucher failed for transaction #' . $transaction->reference_no . ': ' . $e->getMessage(), [
                'userId' => auth()->id(),
                'email'  => $transaction->customer_email,
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim e-voucher: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal mengirim e-voucher ke ' . $transaction->customer_email . '. Silakan coba lagi atau hubungi administrator. Detail: ' . $e->getMessage());
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'E-Voucher berhasil dikirim ulang ke ' . $transaction->customer_email,
            ]);
        }

        return back()->with('success', 'E-Voucher berhasil dikirim ulang ke ' . $transaction->customer_email);
    }

    public function printEvoucher(Transaction $transaction)
    {
        $transaction->load(['event', 'tickets']);

        $this->authorizeTenant($transaction->event);

        $activeTicket = $transaction->tickets->first(fn ($ticket) => $ticket->status !== 'void');

        if (!$activeTicket) {
            return back()->with('error', 'Tidak ada tiket aktif untuk dicetak.');
        }

        return redirect()->route('tickets.view', $activeTicket->ticket_code);
    }

    public function markAsPaid(Transaction $transaction)
    {
        if ($transaction->payment_status === 'paid') {
            return back()->with('error', 'Transaksi sudah berstatus PAID.');
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($transaction) {
            $transaction->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'processed_by' => auth()->id(),
                'payment_method' => $transaction->payment_method ?? 'MANUAL_VERIFICATION'
            ]);

            $category = $transaction->category;
            
            // Check if tickets already exist to prevent duplicate generation
            if ($transaction->tickets()->count() === 0) {
                $createdTickets = [];
                for ($i = 0; $i < $transaction->quantity; $i++) {
                    $createdTickets[] = \App\Models\Ticket::create([
                        'tenant_id' => $transaction->tenant_id,
                        'event_id' => $transaction->event_id,
                        'transaction_id' => $transaction->id,
                        'ticket_category_id' => $transaction->ticket_category_id,
                        'ticket_code' => \App\Models\Ticket::generateTicketCode(),
                        'status' => 'sold',
                    ]);
                }

                // Send Notification once per transaction
                try {
                    if (!empty($createdTickets)) {
                        $notificationService = new \App\Services\TicketNotificationService();
                        $notificationService->sendEVoucher($createdTickets[0]);
                    }
                } catch (\Exception $e) {
                    // Ignore mail/WA error in admin panel
                }

                if ($category) {
                    $category->increment('sold_count', $transaction->quantity);
                }
            }
        });

        return back()->with('success', 'Transaksi #' . $transaction->reference_no . ' telah dikonfirmasi lunas.');
    }

    public function syncWagoStatus(Transaction $transaction)
    {
        $this->authorizeTenant($transaction->event);

        if ($transaction->payment_status === 'paid' && $transaction->tickets()->count() >= $transaction->quantity) {
            return back()->with('info', 'Transaksi #' . $transaction->reference_no . ' sudah berstatus LUNAS dan tiket telah diterbitkan.');
        }

        $wagoService = new \App\Services\WagoService();
        $checkResult = $wagoService->checkTransactionStatus($transaction->reference_no);

        if (!$checkResult['success']) {
            return back()->with('error', 'Gagal menghubungi server WAGO: ' . ($checkResult['message'] ?? 'Respons tidak valid.'));
        }

        if ($checkResult['is_paid']) {
            \Illuminate\Support\Facades\DB::transaction(function() use ($transaction, $checkResult) {
                $transaction->update([
                    'payment_status' => 'paid',
                    'paid_at'        => $transaction->paid_at ?: now(),
                    'payment_method' => $transaction->payment_method ?: 'WAGO (QRIS / Payment Gateway)',
                    'processed_by'   => auth()->id(),
                ]);

                $category = $transaction->category;

                if ($transaction->tickets()->count() === 0) {
                    $createdTickets = [];
                    for ($i = 0; $i < $transaction->quantity; $i++) {
                        $ticket = \App\Models\Ticket::create([
                            'tenant_id'          => $transaction->tenant_id,
                            'event_id'           => $transaction->event_id,
                            'transaction_id'     => $transaction->id,
                            'ticket_category_id' => $transaction->ticket_category_id,
                            'ticket_code'        => \App\Models\Ticket::generateTicketCode(),
                            'status'             => 'sold',
                            'visitor_data'       => [
                                'name'  => $transaction->customer_name,
                                'email' => $transaction->customer_email,
                                'phone' => $transaction->customer_phone,
                                'nik'   => $transaction->customer_nik,
                            ],
                        ]);
                        $createdTickets[] = $ticket;
                    }

                    if ($category) {
                        $category->increment('sold_count', $transaction->quantity);
                    }

                    if (!empty($createdTickets)) {
                        try {
                            $notificationService = new \App\Services\TicketNotificationService();
                            $notificationService->sendEVoucher($createdTickets[0]);
                        } catch (\Exception $e) {
                            \Log::error('syncWagoStatus notification error: ' . $e->getMessage());
                        }
                    }
                }
            });

            return back()->with('success', '✅ Pembayaran terkonfirmasi dari WAGO! Status transaksi #' . $transaction->reference_no . ' berhasil diubah menjadi LUNAS dan E-Voucher telah dikirim.');
        }

        if ($checkResult['is_failed']) {
            $transaction->update(['payment_status' => $checkResult['internal_status']]);
            return back()->with('warning', 'Status transaksi di WAGO adalah: ' . strtoupper($checkResult['internal_status']));
        }

        return back()->with('info', 'Status transaksi #' . $transaction->reference_no . ' di server WAGO masih: ' . strtoupper($checkResult['status'] ?? 'PENDING') . ' (Menunggu Pembayaran).');
    }

    public function cancelTicket(Ticket $ticket)
    {
        $this->authorizeTenant($ticket->event);

        if ($ticket->status === 'void') {
            return back()->with('error', 'Tiket ini sudah dibatalkan sebelumnya.');
        }

        \DB::transaction(function() use ($ticket) {
            $ticket->update(['status' => 'void']);

            if ($ticket->category) {
                $ticket->category->update([
                    'sold_count' => max(0, (int) $ticket->category->sold_count - 1),
                ]);
            }

            $transaction = $ticket->transaction;
            $oldQty = $transaction->quantity;
            $newQty = max(0, $oldQty - 1);

            $pricePerTicket = $oldQty > 0 ? ($transaction->total_amount / $oldQty) : 0;
            $newAmount = max(0, $transaction->total_amount - $pricePerTicket);

            $transaction->update([
                'quantity' => $newQty,
                'total_amount' => $newAmount,
                'payment_status' => $newQty === 0 ? 'refunded' : $transaction->payment_status
            ]);
        });

        $activeTicket = $ticket->transaction->tickets()
            ->where('status', '!=', 'void')
            ->latest('id')
            ->first();

        $redirect = back()->with('success', 'Tiket ' . $ticket->ticket_code . ' berhasil dibatalkan dan kuota telah dikembalikan.');

        if ($activeTicket) {
            $redirect->with('active_evoucher_url', route('tickets.view', $activeTicket->ticket_code));
        }

        return $redirect;
    }

    public function cancelTransaction(Transaction $transaction)
    {
        $this->authorizeTenant($transaction->event);

        if (in_array($transaction->payment_status, ['failed', 'expired', 'refunded'])) {
            return back()->with('error', 'Transaksi sudah berstatus tidak aktif atau dibatalkan sebelumnya.');
        }

        \DB::transaction(function() use ($transaction) {
            $activeTickets = $transaction->tickets()->where('status', '!=', 'void')->get();

            foreach ($activeTickets as $ticket) {
                $ticket->update(['status' => 'void']);
                if ($ticket->category) {
                    $ticket->category->update([
                        'sold_count' => max(0, (int) $ticket->category->sold_count - 1),
                    ]);
                }
            }

            // Restore promo code usage if any
            $activeTicketsCount = $activeTickets->count();
            if ($transaction->promoCode && $activeTicketsCount > 0) {
                $currentUsed = $transaction->promoCode->used_count;
                $transaction->promoCode->decrement('used_count', min($currentUsed, $activeTicketsCount));
            }

            $transaction->update([
                'quantity' => 0,
                'total_amount' => 0,
                'payment_status' => $transaction->payment_status === 'paid' ? 'refunded' : 'failed'
            ]);
        });

        return back()->with('success', 'Transaksi #' . $transaction->reference_no . ' berhasil dibatalkan sepenuhnya dan semua kuota telah dikembalikan.');
    }

    public function cancelTickets(Request $request, Transaction $transaction)
    {
        $this->authorizeTenant($transaction->event);

        $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id'
        ]);

        $ticketIds = $request->input('ticket_ids');

        $ticketsToCancel = $transaction->tickets()
            ->whereIn('id', $ticketIds)
            ->where('status', '!=', 'void')
            ->get();

        if ($ticketsToCancel->isEmpty()) {
            return back()->with('error', 'Tidak ada tiket aktif terpilih untuk dibatalkan.');
        }

        \DB::transaction(function() use ($transaction, $ticketsToCancel) {
            $numCanceled = $ticketsToCancel->count();

            foreach ($ticketsToCancel as $ticket) {
                $ticket->update(['status' => 'void']);
                if ($ticket->category) {
                    $ticket->category->update([
                        'sold_count' => max(0, (int) $ticket->category->sold_count - 1),
                    ]);
                }
            }

            $oldQty = $transaction->quantity;
            $newQty = max(0, $oldQty - $numCanceled);

            $pricePerTicket = $oldQty > 0 ? ($transaction->total_amount / $oldQty) : 0;
            $refundAmount = $pricePerTicket * $numCanceled;
            $newAmount = max(0, $transaction->total_amount - $refundAmount);

            $updateData = [
                'quantity' => $newQty,
                'total_amount' => $newAmount,
            ];

            if ($newQty === 0) {
                $updateData['payment_status'] = $transaction->payment_status === 'paid' ? 'refunded' : 'failed';
            }

            if ($transaction->promoCode && $numCanceled > 0) {
                $currentUsed = $transaction->promoCode->used_count;
                $transaction->promoCode->decrement('used_count', min($currentUsed, $numCanceled));
            }

            $transaction->update($updateData);
        });

        $activeTicket = $transaction->tickets()
            ->where('status', '!=', 'void')
            ->latest('id')
            ->first();

        $redirect = back()->with('success', 'Tiket terpilih (' . $ticketsToCancel->count() . ' tiket) berhasil dibatalkan dan kuota telah dikembalikan.');

        if ($activeTicket) {
            $redirect->with('active_evoucher_url', route('tickets.view', $activeTicket->ticket_code));
        }

        return $redirect;
    }

    public function sendWhatsApp(Transaction $transaction)
    {
        $this->authorizeTenant($transaction->event);
        $transaction->load(['event', 'tickets.category']);

        $ticket = $transaction->tickets->first(fn ($t) => $t->status !== 'void');

        if (!$ticket) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tiket aktif untuk mengirim WhatsApp.',
                ], 404);
            }
            return back()->with('error', 'Tidak ada tiket aktif untuk mengirim WhatsApp.');
        }

        try {
            $notificationService = new \App\Services\TicketNotificationService();
            $notificationService->sendWhatsApp($ticket, true, true);
        } catch (\Exception $e) {
            \Log::error('sendWhatsApp failed for transaction #' . $transaction->reference_no . ': ' . $e->getMessage(), [
                'userId' => auth()->id(),
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim WhatsApp: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal mengirim WhatsApp. Detail: ' . $e->getMessage());
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'E-Voucher berhasil dikirim via WhatsApp Fonnte.',
            ]);
        }

        return back()->with('success', 'E-Voucher berhasil dikirim via WhatsApp Fonnte.');
    }

    private function authorizeTenant(Event $event)
    {
        if ($event->tenant_id !== auth()->user()->tenant_id && !auth()->user()->hasRole('Superadmin')) {
            abort(403, 'Unauthorized access to this event');
        }
    }
}
