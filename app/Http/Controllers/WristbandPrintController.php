<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WristbandPrintController extends Controller
{
    /**
     * Display a printable view of wristbands for a specific category based on quota/stock.
     */
    public function print(Request $request, TicketCategory $category)
    {
        // Check authorization
        $this->authorizeAccess($category->event);

        $category->load(['event.tenant', 'wristbandTemplate', 'event.wristbandTemplate']);
        $event = $category->event;
        $wristbandTemplate = $category->getEffectiveWristbandTemplate()
            ?: ($event ? \App\Models\WristbandTemplate::where('event_id', $event->id)->latest()->first() : null);

        if (!$wristbandTemplate && $event) {
            $wristbandTemplate = new \App\Models\WristbandTemplate([
                'tenant_id' => $event->tenant_id,
                'event_id' => $event->id,
                'name' => 'Wristband ' . $event->name,
                'mode' => 'default',
                'columns_config' => \App\Models\WristbandTemplate::getDefaultColumns(),
            ]);
        }

        $requestedMode = $request->get('mode');
        $activeMode = in_array($requestedMode, ['default', 'custom']) ? $requestedMode : ($wristbandTemplate?->mode ?? 'default');

        $requestedPaper = strtolower($request->get('paper', 'f4'));
        $paperSize = in_array($requestedPaper, ['f4', 'a3']) ? $requestedPaper : 'f4';

        // Total count based on category stock/quota
        $quota = (int) ($category->quota > 0 ? $category->quota : 50);
        $defaultCount = min($quota, 260);
        $count = (int) $request->get('count', $defaultCount);
        $count = max(1, min($count, 2000));
        $start = max(1, (int) $request->get('start', 1));

        $wristbands = collect();
        for ($i = $start; $i < $start + $count; $i++) {
            $code = sprintf('WB-C%d-%04d', $category->id, $i);
            $wristbands->push((object) [
                'ticket_code' => $code,
                'wristband_code' => $code,
                'category' => $category,
                'event' => $event,
                'index' => $i
            ]);
        }

        return view('wristbands.print', [
            'tickets' => $wristbands,
            'category' => $category,
            'event' => $event,
            'wristbandTemplate' => $wristbandTemplate,
            'activeMode' => $activeMode,
            'paperSize' => $paperSize,
            'totalCount' => $count,
            'startNumber' => $start,
            'categoryQuota' => $quota
        ]);
    }

    /**
     * Export wristbands to Microsoft Word (.docx) format with choice of F4 or A3+.
     */
    public function exportDocx(Request $request, TicketCategory $category, \App\Services\WristbandExportService $exportService)
    {
        // Increase memory and execution time limits for document generation
        @ini_set('max_execution_time', '300');
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        // Check authorization
        $this->authorizeAccess($category->event);

        $category->load(['event.tenant', 'wristbandTemplate', 'event.wristbandTemplate']);
        $event = $category->event;
        $wristbandTemplate = $category->getEffectiveWristbandTemplate()
            ?: ($event ? \App\Models\WristbandTemplate::where('event_id', $event->id)->latest()->first() : null);

        $requestedMode = $request->get('mode');
        $activeMode = in_array($requestedMode, ['default', 'custom']) ? $requestedMode : ($wristbandTemplate?->mode ?? 'default');

        $requestedPaper = strtolower($request->get('paper', 'f4'));
        $paperSize = in_array($requestedPaper, ['f4', 'a3']) ? $requestedPaper : 'f4';

        $quota = (int) ($category->quota > 0 ? $category->quota : 50);
        $defaultCount = min($quota, 260); // 260 is 10 sheets A3+ (26/sheet) or ~17 sheets F4 (15/sheet)
        $count = (int) $request->get('count', $defaultCount);
        $count = max(1, min($count, 500)); // Limit maximum single file batch to 500
        $start = max(1, (int) $request->get('start', 1));

        $wristbands = collect();
        for ($i = $start; $i < $start + $count; $i++) {
            $code = sprintf('WB-C%d-%04d', $category->id, $i);
            $wristbands->push((object) [
                'ticket_code' => $code,
                'wristband_code' => $code,
                'category' => $category,
                'event' => $event,
                'index' => $i
            ]);
        }

        $filePath = $exportService->exportDocx(
            $wristbands,
            $category,
            $event,
            $wristbandTemplate,
            $paperSize,
            $activeMode
        );

        $categorySlug = Str::slug($category->name) ?: 'category-' . $category->id;
        $paperLabel = strtoupper($paperSize === 'a3' ? 'A3-Plus' : 'F4');
        $fileName = sprintf('Gelang-Tiket-%s-%s-%s-%d-%d.docx', $categorySlug, $paperLabel, ucfirst($activeMode), $start, $start + $count - 1);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Reset/clear dummy offline stock from category.
     */
    public function resetOfflineStock(TicketCategory $category)
    {
        $this->authorizeAccess($category->event);

        $offlineTransactions = Transaction::where('ticket_category_id', $category->id)
            ->where(function($q) {
                $q->where('customer_name', 'OFFLINE STOCK')
                  ->orWhere('payment_method', 'OFFLINE STOCK')
                  ->orWhere('reference_no', 'like', 'STOCK-%');
            })
            ->get();

        $deletedTicketsCount = 0;
        foreach ($offlineTransactions as $tx) {
            $deletedTicketsCount += $tx->tickets()->delete();
            $tx->delete();
        }

        // Recalculate real sold count from legitimate transactions
        $realSoldCount = Ticket::where('ticket_category_id', $category->id)
            ->whereIn('status', ['sold', 'redeemed'])
            ->whereHas('transaction', function($q) {
                $q->where('customer_name', '!=', 'OFFLINE STOCK')
                  ->where('payment_status', 'paid');
            })
            ->count();

        $category->update(['sold_count' => $realSoldCount]);

        return back()->with('success', "Berhasil mereset {$deletedTicketsCount} tiket stok dummy offline. Kuota penjualan online untuk kategori {$category->name} telah dipulihkan!");
    }

    private function authorizeAccess(Event $event)
    {
        if (auth()->user()->hasRole('Superadmin')) {
            return;
        }

        if ($event->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this event');
        }
    }
}

