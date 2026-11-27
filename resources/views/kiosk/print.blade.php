<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Tiket Kiosk - {{ $transaction->reference_no }}</title>
    @php
        $paperWidth = $transaction->event->thermal_paper_width_mm ?? 80;
        $paperHeight = $transaction->event->thermal_paper_height_mm ?? 160;
    @endphp
    <style>
        @page {
            size: {{ $paperWidth . 'mm' }} {{ $paperHeight . 'mm' }};
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f1f5f9;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
        }

        .toolbar {
            position: sticky;
            top: 0;
            display: flex;
            gap: 8px;
            justify-content: center;
            padding: 12px;
            background: #0f172a;
            z-index: 5;
        }

        .toolbar button {
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 800;
            font-size: 12px;
            cursor: pointer;
        }

        .btn-print-main {
            background: #ea580c;
            color: #fff;
        }

        .btn-close-tab {
            background: #fff;
            color: #0f172a;
        }

        .sheet {
            width: {{ $paperWidth . 'mm' }};
            min-height: {{ $paperHeight . 'mm' }};
            margin: 16px auto;
            background: #fff;
            padding: 5mm;
            page-break-after: always;
            overflow: hidden;
        }

        .sheet:last-child {
            page-break-after: auto;
        }

        .center {
            text-align: center;
        }

        .event {
            font-size: 15px;
            line-height: 1.15;
            font-weight: 900;
            text-transform: uppercase;
        }

        .muted {
            color: #4b5563;
            font-size: 10px;
            line-height: 1.35;
        }

        .divider {
            border-top: 1px dashed #111827;
            margin: 8px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 10px;
            line-height: 1.4;
            margin: 3px 0;
        }

        .label {
            color: #4b5563;
            white-space: nowrap;
        }

        .value {
            font-weight: 800;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .qr {
            width: 38mm;
            height: 38mm;
            margin: 8px auto 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr svg {
            width: 100%;
            height: 100%;
        }

        .code {
            font-family: "Courier New", monospace;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 1px;
            overflow-wrap: anywhere;
        }

        .footer {
            margin-top: 6px;
            font-size: 9px;
            color: #4b5563;
            text-align: center;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .sheet {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    @if(!request()->boolean('embedded'))
        <div class="toolbar">
            <button type="button" class="btn-close-tab" onclick="window.close()">Tutup</button>
            <button type="button" class="btn-print-main" onclick="window.print()">Print Ulang</button>
        </div>
    @endif

    @include('tickets.partials.thermal-sheets', ['transaction' => $transaction, 'tickets' => $tickets])

    @if(!request()->boolean('embedded'))
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 300);
            });
        </script>
    @endif
</body>

</html>
