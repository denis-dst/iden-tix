@php
    $paperWidth = $transaction->event->thermal_paper_width_mm ?? 80;
    $paperHeight = $transaction->event->thermal_paper_height_mm ?? 160;
    $printableTickets = isset($tickets)
        ? collect($tickets)
        : $transaction->tickets->where('status', '!=', 'void');
@endphp

@foreach($printableTickets as $ticket)
    <section class="sheet">
        <div class="center">
            <div class="event">{{ $transaction->event->name }}</div>
            <div class="muted">{{ $transaction->event->venue }} &middot; {{ $transaction->event->city }}</div>
            <div class="muted">{{ $transaction->event->event_start_date?->format('d M Y H:i') }}</div>
        </div>

        <div class="divider"></div>

        <div class="row">
            <span class="label">Invoice</span>
            <span class="value">{{ $transaction->reference_no }}</span>
        </div>
        <div class="row">
            <span class="label">Pembelian</span>
            <span class="value">{{ $transaction->paid_at?->format('d M Y H:i') ?? $transaction->created_at->format('d M Y H:i') }}</span>
        </div>
        <div class="row">
            <span class="label">Kategori</span>
            <span class="value">{{ $ticket->category->name ?? '-' }}</span>
        </div>
        <div class="row">
            <span class="label">Nama</span>
            <span class="value">{{ $ticket->visitor_data['name'] ?? $transaction->customer_name }}</span>
        </div>
        <div class="row">
            <span class="label">No HP</span>
            <span class="value">{{ $ticket->visitor_data['phone'] ?? $transaction->customer_phone }}</span>
        </div>
        <div class="row">
            <span class="label">Email</span>
            <span class="value">{{ $ticket->visitor_data['email'] ?? $transaction->customer_email }}</span>
        </div>

        <div class="divider"></div>

        <div class="qr">
            {!! QrCode::size(180)->margin(0)->generate($ticket->ticket_code) !!}
        </div>
        <div class="center code">{{ $ticket->ticket_code }}</div>
        <div class="footer">Tunjukan QR kepada Petugas Penjaga untuk masuk gate. Simpan ticket sampai event selesai.</div>
    </section>
@endforeach
