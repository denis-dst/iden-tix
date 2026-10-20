<?php

namespace App\Http\Controllers\Organizer;

use App\Exports\OperationalReportExport;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\GateLog;
use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $baseEventsQuery = Event::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->when($request->filled('event_id'), fn ($query) => $query->where('id', $request->event_id))
            ->with(['ticketCategories' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')])
            ->orderByDesc('event_start_date');

        $events = (clone $baseEventsQuery)->paginate(8)->withQueryString();

        $eventOptions = Event::where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->orderByDesc('event_start_date')
            ->get(['id', 'name']);

        $reportRows = $this->buildBatchEventReports($events->getCollection());

        $matchingEventIds = (clone $baseEventsQuery)->pluck('id')->all();

        if (empty($matchingEventIds)) {
            $totals = [
                'sold' => 0,
                'redeemed' => 0,
                'checkin' => 0,
                'checkout' => 0,
                'inside' => 0,
                'revenue' => 0,
                'paid_transactions' => 0,
            ];
        } else {
            $ticketTotals = Ticket::whereIn('event_id', $matchingEventIds)
                ->whereHas('transaction', fn ($q) => $q->salesOnly())
                ->selectRaw("
                    SUM(CASE WHEN status IN ('sold', 'redeemed') THEN 1 ELSE 0 END) as sold,
                    SUM(CASE WHEN status = 'redeemed' THEN 1 ELSE 0 END) as redeemed
                ")
                ->first();

            $gateTotals = GateLog::whereIn('event_id', $matchingEventIds)
                ->selectRaw("
                    SUM(CASE WHEN type = 'IN' THEN 1 ELSE 0 END) as checkin,
                    SUM(CASE WHEN type = 'OUT' THEN 1 ELSE 0 END) as checkout
                ")
                ->first();

            $txnTotals = Transaction::whereIn('event_id', $matchingEventIds)
                ->salesOnly()
                ->where('payment_status', 'paid')
                ->selectRaw("
                    COUNT(*) as paid_transactions,
                    COALESCE(SUM(total_amount), 0) as revenue
                ")
                ->first();

            $checkin = (int) ($gateTotals->checkin ?? 0);
            $checkout = (int) ($gateTotals->checkout ?? 0);

            $totals = [
                'sold' => (int) ($ticketTotals->sold ?? 0),
                'redeemed' => (int) ($ticketTotals->redeemed ?? 0),
                'checkin' => $checkin,
                'checkout' => $checkout,
                'inside' => max(0, $checkin - $checkout),
                'revenue' => (float) ($txnTotals->revenue ?? 0),
                'paid_transactions' => (int) ($txnTotals->paid_transactions ?? 0),
            ];
        }

        $transactions = Transaction::where('tenant_id', $tenantId)
            ->salesOnly()
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($request->filled('event_id'), fn ($query) => $query->where('event_id', $request->event_id))
            ->with([
                'event:id,name,umroh_question_enabled,meta',
                'category:id,name',
                'tickets' => fn ($q) => $q->select(['id', 'transaction_id', 'ticket_category_id', 'ticket_code', 'status', 'redeemed_at', 'visitor_data']),
                'tickets.category:id,name',
                'tickets.gateLogs' => fn ($q) => $q->select(['id', 'ticket_id', 'type', 'scanned_at'])->orderBy('scanned_at')
            ])
            ->orderByDesc('created_at')
            ->get();

        $transactionReportRows = $this->buildTransactionReportRows($transactions);
        $ticketReportRows = $this->buildTicketReportRows($transactions);

        return view('organizer.reports.index', compact('events', 'eventOptions', 'reportRows', 'totals', 'transactions', 'transactionReportRows', 'ticketReportRows'));
    }

    public function duplicates(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $eventOptions = Event::where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->orderByDesc('event_start_date')
            ->get(['id', 'name']);

        $transactions = Transaction::where('tenant_id', $tenantId)
            ->salesOnly()
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($request->filled('event_id'), fn ($query) => $query->where('event_id', $request->event_id))
            ->select(['id', 'customer_name', 'customer_phone', 'customer_email', 'reference_no', 'created_at'])
            ->with([
                'tickets' => fn ($q) => $q->select(['id', 'transaction_id', 'status', 'visitor_data'])
            ])
            ->orderByDesc('created_at')
            ->get();

        $duplicateRows = $this->buildDuplicateRegistrantRows($transactions);

        return view('organizer.reports.duplicates', compact('eventOptions', 'duplicateRows'));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $transactions = Transaction::where('tenant_id', $tenantId)
            ->salesOnly()
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->when($request->filled('event_id'), fn ($query) => $query->where('event_id', $request->event_id))
            ->with([
                'event:id,name,umroh_question_enabled,meta',
                'category:id,name',
                'tickets' => fn ($q) => $q->select(['id', 'transaction_id', 'ticket_category_id', 'ticket_code', 'status', 'redeemed_at', 'visitor_data']),
                'tickets.category:id,name',
                'tickets.gateLogs' => fn ($q) => $q->select(['id', 'ticket_id', 'type', 'scanned_at'])->orderBy('scanned_at')
            ])
            ->orderByDesc('created_at')
            ->get();

        $rows = $this->buildTicketReportRows($transactions)
            ->filter(fn (array $row) => ($row['status'] ?? '') !== 'void');

        $filename = 'Laporan_Pendaftar_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new OperationalReportExport($rows), $filename);
    }

    private function buildDuplicateRegistrantRows(Collection $transactions): Collection
    {
        $participants = $transactions->flatMap(function ($transaction) {
            return $transaction->tickets
                ->filter(fn ($ticket) => $ticket->status !== 'void')
                ->map(function ($ticket) use ($transaction) {
                    $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : [];

                    return [
                        'name' => trim((string) ($visitorData['name'] ?? $transaction->customer_name ?? '')),
                        'phone' => trim((string) ($visitorData['phone'] ?? $transaction->customer_phone ?? '')),
                        'email' => trim((string) ($visitorData['email'] ?? $transaction->customer_email ?? '')),
                    ];
                });
        })->filter(function ($row) {
            return $this->normalizePhone($row['phone'])
                || $this->normalizeEmail($row['email'])
                || $this->normalizeName($row['name']);
        });

        $phoneCounts = $participants
            ->map(fn ($row) => $this->normalizePhone($row['phone']))
            ->filter()
            ->countBy();
        $emailCounts = $participants
            ->map(fn ($row) => $this->normalizeEmail($row['email']))
            ->filter()
            ->countBy();
        $nameCounts = $participants
            ->map(fn ($row) => $this->normalizeName($row['name']))
            ->filter()
            ->countBy();

        $duplicateParticipants = $participants->filter(function ($row) use ($phoneCounts, $emailCounts, $nameCounts) {
            $phone = $this->normalizePhone($row['phone']);
            $email = $this->normalizeEmail($row['email']);
            $name = $this->normalizeName($row['name']);

            return ($phone && ($phoneCounts[$phone] ?? 0) > 1)
                || ($email && ($emailCounts[$email] ?? 0) > 1)
                || ($name && ($nameCounts[$name] ?? 0) > 1);
        });

        return $duplicateParticipants
            ->groupBy(function ($row) {
                return $this->normalizeName($row['name']) . '|' . $this->normalizePhone($row['phone']) . '|' . $this->normalizeEmail($row['email']);
            })
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'name' => $first['name'] ?: '-',
                    'phone' => $first['phone'] ?: '-',
                    'email' => $first['email'] ?: '-',
                    'registration_count' => $group->count(),
                ];
            })
            ->filter(function ($row) use ($phoneCounts, $emailCounts, $nameCounts) {
                $phone = $this->normalizePhone($row['phone']);
                $email = $this->normalizeEmail($row['email']);
                $name = $this->normalizeName($row['name']);

                return ($phone && ($phoneCounts[$phone] ?? 0) > 1)
                    || ($email && ($emailCounts[$email] ?? 0) > 1)
                    || ($name && ($nameCounts[$name] ?? 0) > 1);
            })
            ->sortByDesc('registration_count')
            ->values();
    }

    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (!str_starts_with($digits, '62')) {
            return '62' . $digits;
        }

        return $digits;
    }

    private function normalizeEmail(string $email): ?string
    {
        $normalized = strtolower(trim($email));

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeName(string $name): ?string
    {
        $normalized = mb_strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? ''));

        return $normalized !== '' ? $normalized : null;
    }

    private function buildTransactionReportRows($transactions)
    {
        return $transactions
            ->map(function ($transaction) {
                $proofTicket = $transaction->tickets->first(function ($ticket) {
                    $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : [];

                    return !empty($visitorData['proof_ig']) || !empty($visitorData['proof_review']) || !empty($visitorData['proofs']);
                });

                $proofData = $proofTicket && is_array($proofTicket->visitor_data) ? $proofTicket->visitor_data : [];

                $nonVoidTickets      = $transaction->tickets->filter(fn ($t) => $t->status !== 'void');
                $totalTickets        = $nonVoidTickets->count();
                $checkedInTickets    = $nonVoidTickets->filter(fn ($t) => $t->gateLogs->where('type', 'IN')->isNotEmpty());
                $checkedInCount      = $checkedInTickets->count();
                $isPartialScan       = $totalTickets > 1 && $checkedInCount > 0 && $checkedInCount < $totalTickets;

                $firstTicket = $transaction->tickets->first();
                $firstVisitorData = $firstTicket && is_array($firstTicket->visitor_data) ? $firstTicket->visitor_data : [];
                $customFields = $this->resolveCustomFields($transaction->event, $firstVisitorData, $transaction);
                $customQuestionLabel = !empty($customFields)
                    ? collect($customFields)->pluck('label')->implode(' | ')
                    : '-';
                $customAnswerSummary = !empty($customFields)
                    ? collect($customFields)->pluck('value')->implode(' | ')
                    : ($transaction->customer_umroh_answer ?: '-');

                return [
                    'reference_no' => $transaction->reference_no,
                    'created_at' => $transaction->created_at,
                    'customer_name' => $transaction->customer_name,
                    'customer_nik' => $transaction->customer_nik,
                    'customer_email' => $transaction->customer_email,
                    'customer_phone' => $transaction->customer_phone,
                    'customer_gender' => $transaction->customer_gender,
                    'customer_umroh_answer' => $customAnswerSummary,
                    'custom_question_label' => $customQuestionLabel,
                    'custom_fields' => $customFields,
                    'event_name' => $transaction->event->name ?? '-',
                    'category_name' => $transaction->category->name ?? 'Mixed',
                    'quantity' => $transaction->quantity,
                    'total_amount' => $transaction->total_amount,
                    'payment_status' => $transaction->payment_status,
                    'payment_method' => $transaction->payment_method,
                    'proof_ig' => $proofData['proof_ig'] ?? null,
                    'proof_review' => $proofData['proof_review'] ?? null,
                    'proofs' => $proofData['proofs'] ?? [],
                    'total_tickets'    => $totalTickets,
                    'redeemed_tickets' => $checkedInCount,
                    'is_partial_scan'  => $isPartialScan,
                ];
            })
            ->values();
    }

    private function buildTicketReportRows($transactions)
    {
        return $transactions
            ->flatMap(function ($transaction) {
                $nonVoidTickets   = $transaction->tickets->filter(fn ($t) => $t->status !== 'void');
                $totalTickets     = $nonVoidTickets->count();
                $checkedInCount   = $nonVoidTickets->filter(fn ($t) => $t->gateLogs->where('type', 'IN')->isNotEmpty())->count();
                $isPartialTxn     = $totalTickets > 1 && $checkedInCount > 0 && $checkedInCount < $totalTickets;

                return $transaction->tickets->map(function ($ticket) use ($transaction, $isPartialTxn, $totalTickets, $checkedInCount) {
                    $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : [];

                    $inLogs        = $ticket->gateLogs->where('type', 'IN')->sortBy('scanned_at');
                    $hasCheckin    = $inLogs->isNotEmpty();
                    $firstCheckin  = $inLogs->first()?->scanned_at;

                    $customFields = $this->resolveCustomFields($transaction->event, $visitorData, $transaction);
                    $customQuestionLabel = !empty($customFields)
                        ? collect($customFields)->pluck('label')->implode(' | ')
                        : '-';
                    $customAnswerSummary = !empty($customFields)
                        ? collect($customFields)->pluck('value')->implode(' | ')
                        : ($visitorData['umroh_answer'] ?? ($transaction->customer_umroh_answer ?: '-'));

                    return [
                        'ticket_code' => $ticket->ticket_code,
                        'reference_no' => $transaction->reference_no,
                        'name' => $visitorData['name'] ?? $transaction->customer_name,
                        'nik' => $visitorData['nik'] ?? $transaction->customer_nik ?? '-',
                        'email' => $visitorData['email'] ?? $transaction->customer_email,
                        'phone' => $visitorData['phone'] ?? $transaction->customer_phone,
                        'gender' => $visitorData['gender'] ?? $transaction->customer_gender,
                        'umroh_answer' => $customAnswerSummary,
                        'custom_question_label' => $customQuestionLabel,
                        'custom_fields' => $customFields,
                        'event_name' => $transaction->event->name ?? '-',
                        'category_name' => $ticket->category->name ?? '-',
                        'status' => $ticket->status,
                        'redeemed_at' => $ticket->redeemed_at,
                        'has_checkin'       => $hasCheckin,
                        'first_checkin_at'  => $firstCheckin,
                        'is_partial_txn'    => $isPartialTxn,
                        'txn_total_tickets'  => $totalTickets,
                        'txn_redeemed'       => $checkedInCount,
                    ];
                });
            })
            ->values();
    }

    private function resolveCustomFields(?Event $event, array $visitorData, ?Transaction $transaction = null): array
    {
        if (!$event) {
            return [];
        }

        $formatted = [];
        $processedFieldIds = [];
        $customAnswers = is_array($visitorData['custom_answers'] ?? null) ? $visitorData['custom_answers'] : [];

        $customFields = method_exists($event, 'getCustomFields') ? $event->getCustomFields() : [];
        if (!empty($customFields)) {
            foreach ($customFields as $field) {
                $fId = (string) ($field['id'] ?? '');
                $processedFieldIds[] = $fId;
                $val = $customAnswers[$fId] ?? null;

                if (($val === null || $val === '') && $fId === 'umroh_answer') {
                    $val = $visitorData['umroh_answer'] ?? ($transaction->customer_umroh_answer ?? null);
                }

                if (($val === null || $val === '') && count($customFields) === 1) {
                    $val = $visitorData['umroh_answer'] ?? ($transaction->customer_umroh_answer ?? null);
                }

                $valueStr = is_array($val) ? implode(', ', array_filter($val)) : trim((string) $val);

                $formatted[] = [
                    'id' => $fId,
                    'label' => $field['label'] ?? 'Pertanyaan Custom',
                    'value' => $valueStr !== '' ? $valueStr : '-',
                ];
            }
        } elseif (!empty($visitorData['umroh_answer']) || !empty($transaction?->customer_umroh_answer)) {
            $ans = $visitorData['umroh_answer'] ?? $transaction->customer_umroh_answer;
            $formatted[] = [
                'id' => 'umroh_answer',
                'label' => $this->customQuestionLabel($event),
                'value' => trim((string) $ans) !== '' ? trim((string) $ans) : '-',
            ];
            $processedFieldIds[] = 'umroh_answer';
        }

        if (is_array($customAnswers)) {
            foreach ($customAnswers as $key => $val) {
                if (!in_array((string) $key, $processedFieldIds, true) && $val !== null && $val !== '') {
                    $label = ucwords(str_replace(['_', '-'], ' ', (string) $key));
                    $valueStr = is_array($val) ? implode(', ', array_filter($val)) : trim((string) $val);
                    $formatted[] = [
                        'id' => (string) $key,
                        'label' => $label,
                        'value' => $valueStr !== '' ? $valueStr : '-',
                    ];
                }
            }
        }

        return $formatted;
    }

    private function customQuestionLabel(?Event $event): string
    {
        if (!$event || !$event->umroh_question_enabled) {
            return '-';
        }

        $label = trim((string) ($event->meta['custom_question_text'] ?? ''));

        return $label !== '' ? $label : 'Pertanyaan Custom';
    }

    private function buildBatchEventReports(Collection $events): Collection
    {
        if ($events->isEmpty()) {
            return collect();
        }

        $eventIds = $events->pluck('id')->all();

        $ticketStats = Ticket::query()
            ->whereIn('event_id', $eventIds)
            ->whereHas('transaction', fn ($q) => $q->salesOnly())
            ->select('event_id', 'ticket_category_id')
            ->selectRaw("SUM(CASE WHEN status IN ('sold', 'redeemed') THEN 1 ELSE 0 END) as sold_count")
            ->selectRaw("SUM(CASE WHEN status = 'redeemed' THEN 1 ELSE 0 END) as redeemed_count")
            ->groupBy('event_id', 'ticket_category_id')
            ->get()
            ->groupBy('event_id');

        $gateStats = GateLog::query()
            ->join('tickets', 'tickets.id', '=', 'gate_logs.ticket_id')
            ->whereIn('gate_logs.event_id', $eventIds)
            ->select('gate_logs.event_id', 'tickets.ticket_category_id')
            ->selectRaw("SUM(CASE WHEN gate_logs.type = 'IN' THEN 1 ELSE 0 END) as checkin_count")
            ->selectRaw("SUM(CASE WHEN gate_logs.type = 'OUT' THEN 1 ELSE 0 END) as checkout_count")
            ->groupBy('gate_logs.event_id', 'tickets.ticket_category_id')
            ->get()
            ->groupBy('event_id');

        $rawTransactions = Transaction::query()
            ->whereIn('event_id', $eventIds)
            ->salesOnly()
            ->where('payment_status', 'paid')
            ->select(['id', 'event_id', 'ticket_category_id', 'total_amount'])
            ->with(['tickets:id,transaction_id,ticket_category_id'])
            ->get();

        $eventCategoriesMap = $events->keyBy('id')->map(fn ($e) => $e->ticketCategories->keyBy('id'));

        $transactionStats = $rawTransactions
            ->groupBy('event_id')
            ->map(function ($eventTxns, $eventId) use ($eventCategoriesMap) {
                $categories = $eventCategoriesMap->get($eventId, collect());
                $firstCatId = $categories->keys()->first();

                return $eventTxns->groupBy(function ($txn) use ($categories, $firstCatId) {
                    if ($txn->ticket_category_id && $categories->has($txn->ticket_category_id)) {
                        return $txn->ticket_category_id;
                    }
                    $ticketCat = $txn->tickets->first(fn ($t) => $t->ticket_category_id && $categories->has($t->ticket_category_id));
                    if ($ticketCat) {
                        return $ticketCat->ticket_category_id;
                    }
                    return $txn->ticket_category_id ?: $firstCatId;
                })->map(function ($catTxns) {
                    return (object) [
                        'paid_transactions_count' => $catTxns->count(),
                        'revenue' => (float) $catTxns->sum('total_amount'),
                    ];
                });
            });

        $eventTransactionTotals = $rawTransactions
            ->groupBy('event_id')
            ->map(function ($eventTxns) {
                return (object) [
                    'paid_transactions_count' => $eventTxns->count(),
                    'revenue' => (float) $eventTxns->sum('total_amount'),
                ];
            });

        $partialScanByEventAndCategory = Transaction::query()
            ->whereIn('event_id', $eventIds)
            ->salesOnly()
            ->where('payment_status', 'paid')
            ->select(['id', 'event_id', 'ticket_category_id'])
            ->with([
                'tickets' => fn ($q) => $q->where('status', '!=', 'void')->select(['id', 'transaction_id', 'ticket_category_id']),
                'tickets.gateLogs' => fn ($q) => $q->where('type', 'IN')->select(['id', 'ticket_id', 'type'])
            ])
            ->get()
            ->groupBy('event_id')
            ->map(function ($eventTxns) {
                return $eventTxns->groupBy('ticket_category_id')->map(function ($catTxns) {
                    return $catTxns->filter(function ($txn) {
                        $nonVoid = $txn->tickets;
                        $total = $nonVoid->count();
                        $checkedIn = $nonVoid->filter(fn ($t) => $t->gateLogs->isNotEmpty())->count();
                        return $total > 1 && $checkedIn > 0 && $checkedIn < $total;
                    })->count();
                });
            });

        return $events->map(function ($event) use ($ticketStats, $gateStats, $transactionStats, $eventTransactionTotals, $partialScanByEventAndCategory) {
            $eventTicketStats = $ticketStats->get($event->id, collect())->keyBy('ticket_category_id');
            $eventGateStats = $gateStats->get($event->id, collect())->keyBy('ticket_category_id');
            $eventTxStats = $transactionStats->get($event->id, collect());
            $eventPartialStats = $partialScanByEventAndCategory->get($event->id, collect());
            $eventTxTotal = $eventTransactionTotals->get($event->id);

            $categories = $event->ticketCategories->map(function ($category) use ($eventTicketStats, $eventGateStats, $eventTxStats, $eventPartialStats) {
                $ticket = $eventTicketStats->get($category->id);
                $gate = $eventGateStats->get($category->id);
                $transaction = $eventTxStats->get($category->id);
                $checkin = (int) ($gate->checkin_count ?? 0);
                $checkout = (int) ($gate->checkout_count ?? 0);

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'hex_color' => $category->hex_color ?? '#6366F1',
                    'sold_count' => (int) ($ticket->sold_count ?? 0),
                    'redeemed_count' => (int) ($ticket->redeemed_count ?? 0),
                    'checkin_count' => $checkin,
                    'checkout_count' => $checkout,
                    'inside_count' => max(0, $checkin - $checkout),
                    'paid_transactions_count' => (int) ($transaction->paid_transactions_count ?? 0),
                    'revenue' => (float) ($transaction->revenue ?? 0),
                    'partial_scan_count' => (int) ($eventPartialStats->get($category->id) ?? 0),
                ];
            });

            return [
                'event' => $event,
                'categories' => $categories,
                'sold_count' => $categories->sum('sold_count'),
                'redeemed_count' => $categories->sum('redeemed_count'),
                'checkin_count' => $categories->sum('checkin_count'),
                'checkout_count' => $categories->sum('checkout_count'),
                'inside_count' => $categories->sum('inside_count'),
                'paid_transactions_count' => (int) ($eventTxTotal->paid_transactions_count ?? $categories->sum('paid_transactions_count')),
                'revenue' => (float) ($eventTxTotal->revenue ?? $categories->sum('revenue')),
                'partial_scan_count' => $categories->sum('partial_scan_count'),
            ];
        });
    }
}
