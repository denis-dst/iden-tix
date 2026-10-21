<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketViewController extends Controller
{
    /**
     * Display the e-voucher for the visitor.
     */
    public function show($code)
    {
        $ticket = Ticket::with(['event.tenant', 'category', 'transaction.tickets.category'])
            ->where('ticket_code', $code)
            ->firstOrFail();

        if ($ticket->status === 'void' || ($ticket->transaction && $ticket->transaction->payment_method !== 'free' && $ticket->transaction->payment_status !== 'paid')) {
            return response()->view('tickets.invalid', compact('ticket'), 410);
        }

        return view('tickets.view', compact('ticket'));
    }
}
