<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketViewController extends Controller
{
    /**
     * Display the e-voucher for the visitor.
     * Supports:
     * 1. Ticket code (e.g. 8181078897)
     * 2. Transaction reference number (e.g. KIOSK-20261122-ZRBSHJ)
     * 3. Event slug (e.g. persita-vs-persib-5193)
     */
    public function show($code)
    {
        // 1. Direct ticket code match
        $ticket = Ticket::with(['event.tenant', 'category', 'transaction.tickets.category'])
            ->where('ticket_code', $code)
            ->first();

        // 2. Transaction reference number match
        if (!$ticket) {
            $ticket = Ticket::with(['event.tenant', 'category', 'transaction.tickets.category'])
                ->whereHas('transaction', function ($q) use ($code) {
                    $q->where('reference_no', $code);
                })
                ->first();
        }

        // 3. Event slug match (latest valid ticket for this event)
        if (!$ticket) {
            $ticket = Ticket::with(['event.tenant', 'category', 'transaction.tickets.category'])
                ->whereHas('event', function ($q) use ($code) {
                    $q->where('slug', $code);
                })
                ->latest()
                ->first();
        }

        // 4. Fallback: Lookup event by slug directly
        if (!$ticket) {
            $event = Event::where('slug', $code)->first();
            if ($event) {
                $ticket = Ticket::with(['event.tenant', 'category', 'transaction.tickets.category'])
                    ->where('event_id', $event->id)
                    ->latest()
                    ->first();
            }
        }

        if (!$ticket) {
            abort(404, 'Tiket tidak ditemukan.');
        }

        if ($ticket->status === 'void' || ($ticket->transaction && $ticket->transaction->payment_method !== 'free' && $ticket->transaction->payment_status !== 'paid')) {
            return response()->view('tickets.invalid', compact('ticket'), 410);
        }

        return view('tickets.view', compact('ticket'));
    }
}

