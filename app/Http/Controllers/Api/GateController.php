<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Gate;
use App\Models\GateLog;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GateController extends Controller
{
    /**
     * List Gates for an Event
     */
    public function listGates(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id'
        ]);

        $gates = Gate::where('event_id', $request->event_id)
            ->where('is_active', true)
            ->with(['ticketCategories' => function ($q) {
                $q->select('ticket_categories.id', 'name');
            }])
            ->get();

        return response()->json([
            'status' => 'SUCCESS',
            'data' => $gates
        ]);
    }

    public function downloadData(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $eventId = (int) $request->event_id;
        $event = Event::with('ticketCategories')->findOrFail($eventId);
        $isRedeemFlow = ($event->purchase_flow === 'redeem');

        $ticketsCollection = collect();

        if ($isRedeemFlow) {
            // 1. Tipe Redeem: Ambil tiket yang sudah pernah ditukarkan / diredeem ke wristband
            $redeemedTickets = Ticket::query()
                ->leftJoin('ticket_categories', 'ticket_categories.id', '=', 'tickets.ticket_category_id')
                ->leftJoin('transactions', 'transactions.id', '=', 'tickets.transaction_id')
                ->where('tickets.event_id', $eventId)
                ->where('tickets.status', 'redeemed')
                ->whereNotNull('tickets.wristband_qr')
                ->select([
                    'tickets.id as ticket_id',
                    'tickets.event_id',
                    'tickets.tenant_id',
                    'tickets.ticket_category_id',
                    'tickets.ticket_code',
                    'tickets.wristband_qr',
                    'tickets.visitor_data',
                    'ticket_categories.name as category_name',
                    'transactions.customer_name',
                    'transactions.customer_email',
                    'transactions.customer_umroh_answer',
                    'transactions.reference_no',
                ])
                ->get()
                ->map(function ($ticket) use ($event) {
                    $visitorData = $this->visitorDataArray($ticket->visitor_data);
                    $customQuestion = $this->customQuestionPayload($event, $visitorData, $ticket->customer_umroh_answer);

                    return [
                        'ticket_id' => $ticket->ticket_id,
                        'event_id' => $ticket->event_id,
                        'tenant_id' => $ticket->tenant_id,
                        'ticket_category_id' => $ticket->ticket_category_id,
                        'ticket_code' => $ticket->wristband_qr ?: $ticket->ticket_code,
                        'wristband_qr' => $ticket->wristband_qr,
                        'category_name' => $ticket->category_name ?? '-',
                        'customer_name' => $visitorData['name'] ?? $ticket->customer_name ?? '-',
                        'customer_email' => $ticket->customer_email ?? '-',
                        'custom_question_label' => $customQuestion['label'],
                        'custom_question_answer' => $customQuestion['answer'],
                        'custom_question' => $customQuestion,
                        'reference_no' => $ticket->reference_no ?? '-',
                    ];
                });

            $ticketsCollection = $ticketsCollection->concat($redeemedTickets);

            // 2. Tipe Redeem: Ambil / generate data seluruh wristband cetak per kategori berdasarkan kuota
            foreach ($event->ticketCategories as $cat) {
                $quota = (int) ($cat->quota > 0 ? $cat->quota : 100);
                $limit = min($quota, 5000);
                for ($i = 1; $i <= $limit; $i++) {
                    $wbCode = sprintf('WB-C%d-%04d', $cat->id, $i);
                    $syntheticId = 9000000 + ($cat->id * 10000) + $i;
                    $ticketsCollection->push([
                        'ticket_id' => $syntheticId,
                        'event_id' => $event->id,
                        'tenant_id' => $event->tenant_id,
                        'ticket_category_id' => $cat->id,
                        'ticket_code' => $wbCode,
                        'wristband_qr' => $wbCode,
                        'category_name' => $cat->name,
                        'customer_name' => 'Gelang Fisik #' . $i . ' (' . $cat->name . ')',
                        'customer_email' => '-',
                        'custom_question_label' => '-',
                        'custom_question_answer' => '-',
                        'custom_question' => ['label' => '-', 'answer' => '-'],
                        'reference_no' => 'WB-STOCK-' . $cat->id . '-' . $i,
                    ]);
                }
            }
        } else {
            // Tipe Direct: Unduh seluruh tiket terjual/redeemed (E-Voucher QR code dan/atau tiket penjualan langsung)
            $directTickets = Ticket::query()
                ->leftJoin('ticket_categories', 'ticket_categories.id', '=', 'tickets.ticket_category_id')
                ->leftJoin('transactions', 'transactions.id', '=', 'tickets.transaction_id')
                ->where('tickets.event_id', $eventId)
                ->whereIn('status', ['sold', 'redeemed'])
                ->select([
                    'tickets.id as ticket_id',
                    'tickets.event_id',
                    'tickets.tenant_id',
                    'tickets.ticket_category_id',
                    'tickets.ticket_code',
                    'tickets.wristband_qr',
                    'tickets.visitor_data',
                    'ticket_categories.name as category_name',
                    'transactions.customer_name',
                    'transactions.customer_email',
                    'transactions.customer_umroh_answer',
                    'transactions.reference_no',
                ])
                ->get()
                ->map(function ($ticket) use ($event) {
                    $visitorData = $this->visitorDataArray($ticket->visitor_data);
                    $customQuestion = $this->customQuestionPayload($event, $visitorData, $ticket->customer_umroh_answer);

                    return [
                        'ticket_id' => $ticket->ticket_id,
                        'event_id' => $ticket->event_id,
                        'tenant_id' => $ticket->tenant_id,
                        'ticket_category_id' => $ticket->ticket_category_id,
                        'ticket_code' => $ticket->ticket_code,
                        'wristband_qr' => $ticket->wristband_qr,
                        'category_name' => $ticket->category_name ?? '-',
                        'customer_name' => $visitorData['name'] ?? $ticket->customer_name ?? '-',
                        'customer_email' => $ticket->customer_email ?? '-',
                        'custom_question_label' => $customQuestion['label'],
                        'custom_question_answer' => $customQuestion['answer'],
                        'custom_question' => $customQuestion,
                        'reference_no' => $ticket->reference_no ?? '-',
                    ];
                });

            $ticketsCollection = $ticketsCollection->concat($directTickets);
        }

        $gates = Gate::where('event_id', $eventId)
            ->where('is_active', true)
            ->with(['ticketCategories' => function ($query) {
                $query->select('ticket_categories.id', 'name');
            }])
            ->get()
            ->map(function ($gate) {
                return [
                    'gate_id' => $gate->id,
                    'event_id' => $gate->event_id,
                    'gate_name' => $gate->name,
                    'allowed_category_ids' => $gate->ticketCategories->pluck('id')->values(),
                ];
            })
            ->values();

        return response()->json([
            'status' => 'SUCCESS',
            'event_id' => $eventId,
            'purchase_flow' => $event->purchase_flow ?? 'direct',
            'tickets' => $ticketsCollection->values(),
            'gates' => $gates,
        ]);
    }

    /**
     * Pemindaian Berkecepatan Tinggi & Anti-Passback
     */
    public function scan(Request $request)
    {
        $request->validate([
            'wristband_qr' => 'required',
            'type' => 'required|in:IN,OUT',
            'gate_id' => 'nullable|exists:gates,id',
            'gate_name' => 'nullable',
            'device_id' => 'nullable'
        ]);

        $scanCode = trim($request->wristband_qr);
        if (preg_match('/(GTX-[A-Za-z0-9_-]+)/', $scanCode, $matches)) {
            $extractedCode = $matches[1];
        } else {
            $extractedCode = basename(parse_url($scanCode, PHP_URL_PATH) ?: $scanCode);
        }

        // Fetch ticket with event, category, and transaction in a single query
        $ticket = Ticket::with([
                'category:id,name,hex_color',
                'transaction:id,customer_name,customer_email,customer_phone,reference_no,customer_umroh_answer',
                'event:id,tenant_id,purchase_flow,name,umroh_question_enabled,meta'
            ])
            ->where(function ($q) use ($scanCode, $extractedCode) {
                $q->where('wristband_qr', $scanCode)
                  ->orWhere('ticket_code', $scanCode)
                  ->orWhere('ticket_code', $extractedCode)
                  ->orWhere('wristband_qr', $extractedCode);
            })
            ->first();

        if (!$ticket) {
            // Check if this is a valid category wristband code format (e.g. WB-C26-0001, WB-CAT26-0001, WB26-0001)
            if (preg_match('/^WB[-_]?(?:C|CAT)?(\d+)[-_](\d+)$/i', $scanCode, $wbMatches)) {
                $categoryId = (int) $wbMatches[1];
                $wristbandIndex = (int) $wbMatches[2];

                $category = \App\Models\TicketCategory::with('event')->find($categoryId);
                if ($category && $category->event) {
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
                                'transaction:id,customer_name,customer_email,customer_phone,reference_no,customer_umroh_answer',
                                'event:id,tenant_id,purchase_flow,name,umroh_question_enabled,meta'
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
                                'transaction:id,customer_name,customer_email,customer_phone,reference_no,customer_umroh_answer',
                                'event:id,tenant_id,purchase_flow,name,umroh_question_enabled,meta'
                            ]);
                        }
                    }
                }
            }
        }

        if (!$ticket) {
            return response()->json([
                'status' => 'REJECT',
                'message' => 'Invalid Wristband / Ticket Code',
                'color' => 'pink',
                'ticket_code' => $scanCode,
                'category' => '-',
                'email' => '-',
                'reference_no' => '-',
            ], 404);
        }

        $event = $ticket->event;

        if ($ticket->status === 'void') {
            return response()->json([
                'status' => 'REJECT',
                'message' => 'Tiket sudah dibatalkan.',
                'color' => 'pink',
                'visitor' => $ticket->transaction->customer_name ?? '-',
                'category' => $ticket->category->name ?? '-',
                'ticket_code' => $ticket->ticket_code,
                'email' => $ticket->transaction->customer_email ?? '-',
                'reference_no' => $ticket->transaction->reference_no ?? '-',
            ], 403);
        }

        // Access Control: Check Gate Mapping
        $gateName = $request->gate_name;
        if ($request->gate_id) {
            $gate = Gate::with('ticketCategories:id')->find($request->gate_id);
            if ($gate) {
                $allowedCategoryIds = $gate->ticketCategories->pluck('id')->toArray();
                if (!empty($allowedCategoryIds) && !in_array($ticket->ticket_category_id, $allowedCategoryIds)) {
                    return response()->json([
                        'status' => 'REJECT',
                        'message' => 'Wrong Gate! Access Denied for ' . ($ticket->category->name ?? 'Category'),
                        'color' => 'pink',
                        'visitor' => $ticket->transaction->customer_name ?? '-',
                        'category' => $ticket->category->name ?? '-',
                        'ticket_code' => $ticket->ticket_code,
                        'email' => $ticket->transaction->customer_email ?? '-',
                        'reference_no' => $ticket->transaction->reference_no ?? '-',
                    ], 403);
                }
                $gateName = $gate->name;
            }
        }

        // Group check-in logic
        $transaction = $ticket->transaction;
        if ($transaction) {
            $ticketsInGroup = Ticket::where('transaction_id', $transaction->id)
                ->with([
                    'category:id,name,hex_color',
                    'gateLogs' => fn ($q) => $q->with('scanner:id,name')->orderBy('scanned_at', 'desc'),
                    'event:id,umroh_question_enabled,meta'
                ])
                ->get();

            $totalGroupCount = $ticketsInGroup->count();

            if ($totalGroupCount > 1) {
                $checkedInCount = 0;
                $checkedOutCount = 0;
                $neverCheckedInCount = 0;
                $latestLog = null;

                foreach ($ticketsInGroup as $t) {
                    $tLastLog = $t->gateLogs->first();
                    if ($tLastLog) {
                        if ($tLastLog->type === 'IN') {
                            $checkedInCount++;
                        } else {
                            $checkedOutCount++;
                        }
                        if (!$latestLog || ($tLastLog->scanned_at && $latestLog->scanned_at && $tLastLog->scanned_at->gt($latestLog->scanned_at))) {
                            $latestLog = $tLastLog;
                        }
                    } else {
                        $neverCheckedInCount++;
                        $checkedOutCount++;
                    }
                }

                if ($request->type === 'IN') {
                    if ($checkedInCount === $totalGroupCount) {
                        $operatorName = $latestLog && $latestLog->scanner ? $latestLog->scanner->name : ($latestLog->gate_name ?? 'System');
                        $timeString = $latestLog && $latestLog->scanned_at ? $latestLog->scanned_at->timezone('Asia/Jakarta')->format('H:i:s d-m-Y') : '-';
                        return response()->json([
                            'status' => 'REJECT',
                            'message' => "Seluruh peserta ({$totalGroupCount} orang) sudah Checkin. Checkin terakhir pada {$timeString} oleh Operator {$operatorName}.",
                            'color' => 'pink',
                            'visitor' => $transaction->customer_name ?? '-',
                            'category' => $ticket->category->name ?? '-',
                            'ticket_code' => $ticket->ticket_code,
                            'email' => $transaction->customer_email ?? '-',
                            'reference_no' => $transaction->reference_no ?? '-',
                        ], 403);
                    }
                } else {
                    if ($checkedOutCount === $totalGroupCount) {
                        $message = $neverCheckedInCount === $totalGroupCount
                            ? "Seluruh peserta ({$totalGroupCount} orang) belum pernah Check-in!"
                            : "Seluruh peserta ({$totalGroupCount} orang) sudah berada di luar area!";
                        return response()->json([
                            'status' => 'REJECT',
                            'message' => $message,
                            'color' => 'pink',
                            'visitor' => $transaction->customer_name ?? '-',
                            'category' => $ticket->category->name ?? '-',
                            'ticket_code' => $ticket->ticket_code,
                            'email' => $transaction->customer_email ?? '-',
                            'reference_no' => $transaction->reference_no ?? '-',
                        ], 403);
                    }
                }

                $attendeesList = $ticketsInGroup->map(function ($t) {
                    $lastLog = $t->gateLogs->first();
                    $isCheckedIn = $lastLog && $lastLog->type === 'IN';
                    $visitorData = $this->visitorDataArray($t->visitor_data);
                    $customQuestion = $this->ticketCustomQuestionPayload($t);

                    return [
                        'ticket_id' => $t->id,
                        'ticket_category_id' => $t->ticket_category_id,
                        'ticket_code' => $t->ticket_code,
                        'name' => $visitorData['name'] ?? $t->transaction->customer_name,
                        'gender' => $visitorData['gender'] ?? null,
                        'custom_question_label' => $customQuestion['label'],
                        'custom_question_answer' => $customQuestion['answer'],
                        'custom_question' => $customQuestion,
                        'is_checked_in' => $isCheckedIn,
                        'category' => $t->category->name ?? '-',
                        'checked_in_at' => ($isCheckedIn && $lastLog->scanned_at) ? $lastLog->scanned_at->timezone('Asia/Jakarta')->format('H:i:s d-m-Y') : null,
                        'checked_in_by' => ($isCheckedIn && $lastLog->scanner) ? $lastLog->scanner->name : ($lastLog->gate_name ?? null),
                    ];
                });

                $customQuestion = $this->ticketCustomQuestionPayload($ticket);

                return response()->json([
                    'status' => 'SUCCESS',
                    'is_group' => true,
                    'message' => 'Detail grup ditemukan',
                    'visitor' => $transaction->customer_name ?? '-',
                    'category' => $ticket->category->name ?? '-',
                    'attendees' => $attendeesList,
                    'custom_question_label' => $customQuestion['label'],
                    'custom_question_answer' => $customQuestion['answer'],
                    'custom_question' => $customQuestion,
                    'scanned_ticket_id' => $ticket->id,
                    'ticket_code' => $ticket->ticket_code,
                    'email' => $transaction->customer_email ?? '-',
                    'reference_no' => $transaction->reference_no ?? '-',
                ]);
            }
        }

        // Anti-passback lookup using indexed query
        $lastLog = GateLog::where('ticket_id', $ticket->id)
            ->with('scanner:id,name')
            ->orderBy('scanned_at', 'desc')
            ->first();

        $alreadyInState = false;
        if ($request->type === 'IN') {
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
            $visitorData = $this->visitorDataArray($ticket->visitor_data);
            $visitorName = $visitorData['name'] ?? ($ticket->transaction->customer_name ?? '-');

            $actionText = $request->type === 'IN' ? 'Checkin' : 'Checkout';
            return response()->json([
                'status' => 'REJECT',
                'message' => "Sudah {$actionText} pada waktu {$timeString} oleh Operator {$operatorName} dengan QR {$ticket->ticket_code} atas nama {$visitorName}",
                'color' => 'pink',
                'visitor' => $visitorName,
                'category' => $ticket->category->name ?? '-',
                'ticket_code' => $ticket->ticket_code,
                'email' => $ticket->transaction->customer_email ?? '-',
                'reference_no' => $ticket->transaction->reference_no ?? '-',
            ], 403);
        }

        if ($request->type === 'OUT' && (!$lastLog || $lastLog->type !== 'IN')) {
            $visitorData = $this->visitorDataArray($ticket->visitor_data);
            $visitorName = $visitorData['name'] ?? ($ticket->transaction->customer_name ?? '-');
            return response()->json([
                'status' => 'REJECT',
                'message' => $lastLog && $lastLog->type === 'OUT' ? 'Tiket sudah berada di luar area!' : 'Tiket belum pernah Check-in!',
                'color' => 'pink',
                'visitor' => $visitorName,
                'category' => $ticket->category->name ?? '-',
                'ticket_code' => $ticket->ticket_code,
                'email' => $ticket->transaction->customer_email ?? '-',
                'reference_no' => $ticket->transaction->reference_no ?? '-',
            ], 403);
        }

        // Insert scan log
        $visitorData = $this->visitorDataArray($ticket->visitor_data);
        $customQuestion = $this->ticketCustomQuestionPayload($ticket);

        GateLog::create([
            'tenant_id' => $ticket->tenant_id,
            'event_id' => $ticket->event_id,
            'ticket_id' => $ticket->id,
            'gate_name' => $gateName ?? $request->gate_name,
            'type' => $request->type,
            'scanned_at' => now(),
            'device_id' => $request->device_id,
            'scanned_by' => auth()->id()
        ]);

        if ($ticket->status === 'sold' && $request->type === 'IN') {
            $ticket->update([
                'status' => 'redeemed',
                'redeemed_at' => now(),
            ]);
        }

        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Access Granted: ' . $request->type,
            'visitor' => $visitorData['name'] ?? $ticket->transaction->customer_name ?? '-',
            'category' => $ticket->category->name ?? '-',
            'color' => 'green',
            'ticket_code' => $ticket->ticket_code,
            'email' => $ticket->transaction->customer_email ?? '-',
            'custom_question_label' => $customQuestion['label'],
            'custom_question_answer' => $customQuestion['answer'],
            'custom_question' => $customQuestion,
            'reference_no' => $ticket->transaction->reference_no ?? '-',
        ]);
    }

    /**
     * Background Sync (Hibrida Offline-First)
     */
    public function syncLogs(Request $request)
    {
        $request->validate([
            'logs' => 'required|array',
        ]);

        $logs = $request->input('logs', []);
        $authId = auth()->id();
        $syncedNow = now();

        foreach ($logs as $logData) {
            if (empty($logData['offline_id']) || empty($logData['ticket_id']) || empty($logData['type'])) {
                continue;
            }

            GateLog::updateOrCreate(
                ['meta->offline_id' => $logData['offline_id']],
                [
                    'tenant_id' => $logData['tenant_id'],
                    'event_id' => $logData['event_id'],
                    'ticket_id' => $logData['ticket_id'],
                    'gate_name' => $logData['gate_name'],
                    'type' => $logData['type'],
                    'scanned_at' => $logData['scanned_at'],
                    'device_id' => $logData['device_id'] ?? null,
                    'scanned_by' => $authId,
                    'meta' => [
                        'offline_id' => $logData['offline_id'],
                        'synced_at' => $syncedNow,
                    ],
                ]
            );
        }

        return response()->json(['message' => count($logs) . ' logs synced successfully']);
    }

    /**
     * Bulk Check-in/Check-out for Group Scan
     */
    public function bulkCheckin(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id',
            'type' => 'required|in:IN,OUT',
            'gate_id' => 'nullable|exists:gates,id',
            'gate_name' => 'required|string',
            'device_id' => 'nullable'
        ]);

        try {
            $ticketIds = array_unique($request->ticket_ids);
            $now = now();
            $authId = auth()->id();

            $allowedCategoryIds = [];
            if ($request->gate_id) {
                $gate = Gate::with('ticketCategories:id')->find($request->gate_id);
                $allowedCategoryIds = $gate
                    ? $gate->ticketCategories->pluck('id')->map(fn ($id) => (int) $id)->all()
                    : [];
            }

            // Fetch all tickets with relations in 1 batch query
            $tickets = Ticket::whereIn('id', $ticketIds)
                ->with([
                    'category:id,name,hex_color',
                    'transaction:id,customer_name,customer_email,customer_phone,reference_no,customer_umroh_answer',
                    'event:id,tenant_id,umroh_question_enabled,meta'
                ])
                ->get()
                ->keyBy('id');

            // Fetch all latest logs for these tickets in 1 batch query
            $existingLogs = GateLog::whereIn('ticket_id', $ticketIds)
                ->orderBy('scanned_at', 'desc')
                ->get()
                ->groupBy('ticket_id');

            $insertRows = [];
            $processedTickets = [];

            foreach ($ticketIds as $ticketId) {
                $ticket = $tickets->get($ticketId);
                if (!$ticket) {
                    continue;
                }

                if (!empty($allowedCategoryIds) && !in_array((int) $ticket->ticket_category_id, $allowedCategoryIds, true)) {
                    continue;
                }

                $ticketLogs = $existingLogs->get($ticketId);
                $lastLog = $ticketLogs ? $ticketLogs->first() : null;
                $alreadyInState = $lastLog && $lastLog->type === $request->type;

                if ($request->type === 'IN') {
                    if ($alreadyInState) {
                        continue;
                    }
                } else {
                    $hasIn = $ticketLogs && $ticketLogs->contains('type', 'IN');
                    if (!$hasIn || $alreadyInState) {
                        continue;
                    }
                }

                $insertRows[] = [
                    'tenant_id' => $ticket->tenant_id,
                    'event_id' => $ticket->event_id,
                    'ticket_id' => $ticketId,
                    'gate_name' => $request->gate_name,
                    'type' => $request->type,
                    'scanned_at' => $now,
                    'device_id' => $request->device_id,
                    'scanned_by' => $authId,
                    'meta' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $processedTickets[] = $ticket;
            }

            if (!empty($insertRows)) {
                DB::transaction(function () use ($insertRows) {
                    GateLog::insert($insertRows);
                });
            }

            if (empty($processedTickets) && !empty($ticketIds)) {
                $firstTicket = $tickets->first();
                if ($firstTicket) {
                    $processedTickets[] = $firstTicket;
                }
            }

            if (!empty($processedTickets)) {
                $firstTicket = $processedTickets[0];
                $transaction = $firstTicket->transaction;

                $visitorNames = [];
                $ticketCodes = [];
                $customQuestions = [];

                foreach ($processedTickets as $t) {
                    $visitorData = $this->visitorDataArray($t->visitor_data);
                    $visitorNames[] = $visitorData['name'] ?? ($transaction->customer_name ?? '-');
                    $ticketCodes[] = $t->ticket_code;

                    $customQ = $this->ticketCustomQuestionPayload($t);
                    if ($customQ['label'] !== '-' && $customQ['answer'] !== '-') {
                        $customQuestions[] = $customQ['label'] . ': ' . $customQ['answer'];
                    }
                }

                $visitorNameString = implode(', ', array_unique($visitorNames));
                $ticketCodeString = implode(', ', array_unique($ticketCodes));
                $customQAnswerString = implode('; ', array_unique($customQuestions));

                return response()->json([
                    'status' => 'SUCCESS',
                    'message' => 'Berhasil memproses check-in masal',
                    'visitor' => $visitorNameString,
                    'category' => $firstTicket->category->name ?? '-',
                    'ticket_code' => $ticketCodeString,
                    'email' => $transaction->customer_email ?? '-',
                    'reference_no' => $transaction->reference_no ?? '-',
                    'custom_question_label' => count($customQuestions) > 0 ? 'Pertanyaan Custom' : '-',
                    'custom_question_answer' => count($customQuestions) > 0 ? $customQAnswerString : '-',
                ]);
            }

            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'Berhasil memproses check-in masal',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function ticketCustomQuestionPayload(Ticket $ticket): array
    {
        $visitorData = $this->visitorDataArray($ticket->visitor_data);

        return $this->customQuestionPayload(
            $ticket->event,
            $visitorData,
            $ticket->transaction->customer_umroh_answer ?? null
        );
    }

    private function customQuestionPayload(?Event $event, array $visitorData, ?string $fallbackAnswer = null): array
    {
        $label = '-';

        if ($event && $event->umroh_question_enabled) {
            $eventLabel = trim((string) ($event->meta['custom_question_text'] ?? ''));
            $label = $eventLabel !== '' ? $eventLabel : 'Pertanyaan Custom';
        }

        $answer = $visitorData['umroh_answer'] ?? $fallbackAnswer;

        return [
            'label' => $label,
            'answer' => $answer ?: '-',
        ];
    }

    private function visitorDataArray(mixed $visitorData): array
    {
        if (is_array($visitorData)) {
            return $visitorData;
        }

        if (is_string($visitorData) && $visitorData !== '') {
            $decoded = json_decode($visitorData, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
