<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #ffffff;
        }
        .page { padding: 40px; }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 28px;
            border-bottom: 3px solid #f97316;
            margin-bottom: 28px;
        }
        .company-name {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #0f172a;
        }
        .company-name span { color: #f97316; }
        .company-sub { font-size: 10px; color: #64748b; margin-top: 3px; letter-spacing: 0.05em; }

        .invoice-label {
            text-align: right;
        }
        .invoice-label .label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #94a3b8;
        }
        .invoice-label .number {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            font-family: monospace;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .status-draft     { background: #f1f5f9; color: #64748b; }
        .status-sent      { background: #fef3c7; color: #92400e; }
        .status-paid      { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        /* Parties */
        .parties {
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 20px;
        }
        .party { flex: 1; }
        .party-label {
            font-size: 8.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .party-name { font-size: 13px; font-weight: 800; color: #0f172a; }
        .party-detail { font-size: 10px; color: #64748b; margin-top: 2px; }

        /* Dates */
        .dates-bar {
            display: flex;
            gap: 16px;
            background: #f8fafc;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 24px;
        }
        .date-block { flex: 1; }
        .date-label {
            font-size: 8.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #94a3b8;
            margin-bottom: 3px;
        }
        .date-value { font-size: 12px; font-weight: 700; color: #1e293b; }
        .date-overdue { color: #dc2626; }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table thead tr {
            background: #0f172a;
            color: #ffffff;
        }
        .items-table thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 8.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .items-table thead th.text-right { text-align: right; }
        .items-table thead th.text-center { text-align: center; }
        .items-table tbody tr { border-bottom: 1px solid #f1f5f9; }
        .items-table tbody tr:nth-child(even) { background: #fafafa; }
        .items-table tbody td {
            padding: 10px 14px;
            font-size: 11px;
            color: #334155;
        }
        .items-table tbody td.text-right { text-align: right; }
        .items-table tbody td.text-center { text-align: center; }
        .item-desc { font-weight: 600; color: #1e293b; }

        /* Totals */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 28px;
        }
        .totals-box { width: 260px; }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 11px;
            color: #64748b;
            border-bottom: 1px solid #f1f5f9;
        }
        .totals-row-total {
            display: flex;
            justify-content: space-between;
            padding: 10px 0 6px;
            border-top: 2px solid #0f172a;
            margin-top: 4px;
        }
        .totals-row-total .label { font-size: 12px; font-weight: 900; color: #0f172a; }
        .totals-row-total .value { font-size: 16px; font-weight: 900; color: #f97316; }

        /* Notes */
        .notes-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 24px;
        }
        .notes-label {
            font-size: 8.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #b45309;
            margin-bottom: 5px;
        }
        .notes-text { font-size: 10.5px; color: #92400e; line-height: 1.6; }

        /* Footer */
        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer-left { font-size: 9px; color: #94a3b8; }
        .footer-right { font-size: 9px; color: #94a3b8; text-align: right; }
        .paid-stamp {
            position: absolute;
            top: 200px;
            right: 60px;
            transform: rotate(-15deg);
            border: 5px solid #22c55e;
            color: #22c55e;
            font-size: 36px;
            font-weight: 900;
            letter-spacing: 0.15em;
            padding: 8px 20px;
            border-radius: 8px;
            opacity: 0.25;
        }
    </style>
</head>
<body>
<div class="page" style="position:relative;">

    @if($invoice->status === 'paid')
        <div class="paid-stamp">LUNAS</div>
    @endif

    {{-- Header --}}
    <div class="header">
        <div>
            @php
                $nameParts = explode(' ', $settings['app_name'] ?? 'IdenTix');
                $firstPart = $nameParts[0] ?? 'Iden';
                $restPart  = implode(' ', array_slice($nameParts, 1));
            @endphp
            <div class="company-name">{{ $firstPart }}<span>{{ $restPart ?: 'Tix' }}</span></div>
            <div class="company-sub">PLATFORM MANAJEMEN TIKET EVENT</div>
        </div>
        <div class="invoice-label">
            <div class="label">Invoice</div>
            <div class="number">{{ $invoice->invoice_number }}</div>
            <div style="margin-top:6px;">
                <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
            </div>
        </div>
    </div>

    {{-- Parties --}}
    <div class="parties">
        <div class="party">
            <div class="party-label">Dari</div>
            <div class="party-name">{{ $settings['app_name'] ?? 'IdenTix' }}</div>
            <div class="party-detail">Platform Manajemen Event</div>
            <div class="party-detail">Diterbitkan oleh: {{ $invoice->issuer->name }}</div>
        </div>
        <div class="party">
            <div class="party-label">Kepada</div>
            <div class="party-name">{{ $invoice->tenant->name }}</div>
            <div class="party-detail">{{ $invoice->tenant->email }}</div>
            @if($invoice->tenant->phone)
                <div class="party-detail">{{ $invoice->tenant->phone }}</div>
            @endif
            @if($invoice->tenant->address)
                <div class="party-detail">{{ $invoice->tenant->address }}</div>
            @endif
        </div>
    </div>

    {{-- Title --}}
    <div style="margin-bottom:18px;">
        <div style="font-size:14px; font-weight:800; color:#1e293b;">{{ $invoice->title }}</div>
        @if($invoice->description)
            <div style="font-size:10.5px; color:#64748b; margin-top:4px;">{{ $invoice->description }}</div>
        @endif
    </div>

    {{-- Dates --}}
    <div class="dates-bar">
        <div class="date-block">
            <div class="date-label">Tanggal Diterbitkan</div>
            <div class="date-value">{{ $invoice->issued_date->format('d F Y') }}</div>
        </div>
        <div class="date-block">
            <div class="date-label">Jatuh Tempo</div>
            <div class="date-value {{ $invoice->is_overdue ? 'date-overdue' : '' }}">
                {{ $invoice->due_date->format('d F Y') }}
            </div>
        </div>
        @if($invoice->payment_confirmed_at)
            <div class="date-block">
                <div class="date-label">Dikonfirmasi</div>
                <div class="date-value" style="color:#16a34a;">{{ $invoice->payment_confirmed_at->format('d F Y') }}</div>
            </div>
        @endif
    </div>

    {{-- Items --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:50%">Deskripsi</th>
                <th class="text-center" style="width:12%">Qty</th>
                <th class="text-right" style="width:19%">Harga Satuan</th>
                <th class="text-right" style="width:19%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td class="item-desc">{{ $item->description }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight:700;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals-section">
        <div class="totals-box">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>{{ $invoice->formatted_subtotal }}</span>
            </div>
            @if($invoice->tax_percent > 0)
                <div class="totals-row">
                    <span>Pajak ({{ $invoice->tax_percent }}%)</span>
                    <span>{{ $invoice->formatted_tax_amount }}</span>
                </div>
            @endif
            <div class="totals-row-total">
                <span class="label">TOTAL</span>
                <span class="value">{{ $invoice->formatted_total }}</span>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if($invoice->notes)
        <div class="notes-box">
            <div class="notes-label">Catatan & Instruksi Pembayaran</div>
            <div class="notes-text">{!! strip_tags($invoice->notes, '<b><strong><em><i><u><ul><ol><li><br><p>') !!}</div>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-left">
            <strong>{{ $settings['app_name'] ?? 'IdenTix' }}</strong><br>
            Dokumen ini diterbitkan secara elektronik dan sah tanpa tanda tangan basah.
        </div>
        <div class="footer-right">
            Dicetak: {{ now()->format('d F Y, H:i') }} WIB<br>
            {{ $invoice->invoice_number }}
        </div>
    </div>

</div>
</body>
</html>
