<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voucher - {{ $transaction->reference_no }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            -webkit-font-smoothing: antialiased;
        }
        .font-outfit { font-family: 'Outfit', sans-serif; }
        .font-mono { font-family: 'Space Mono', monospace; }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            .no-print { display: none !important; }
            body {
                background-color: white !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .evoucher-page {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                width: 210mm !important;
                min-height: 297mm !important;
                page-break-after: always !important;
                break-after: page !important;
            }
            .evoucher-page:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
        }

        .terms-content { font-size: 0.72rem; line-height: 1.45; }
        .terms-content p, .terms-content div { margin-bottom: 0.25rem; }
        .terms-content ul { list-style-type: disc; list-style-position: outside; padding-left: 1.1rem; margin: 0.25rem 0 0.35rem; }
        .terms-content ol { list-style-type: decimal; list-style-position: outside; padding-left: 1.1rem; margin: 0.25rem 0 0.35rem; }
        .terms-content li { display: list-item; margin-bottom: 0.2rem; }
        .terms-content b, .terms-content strong { font-weight: 800; color: #0f172a; }
        .terms-content em, .terms-content i { font-style: italic; }
        .terms-content *:last-child { margin-bottom: 0; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-4 sm:py-8 px-2 sm:px-4">

    @php
        $activeTickets = $transaction->tickets->where('status', '!=', 'void')->values();
        $voidTicketsCount = $transaction->tickets->where('status', 'void')->count();
    @endphp

    @php
        $identixLogo = $global_settings['app_logo'] ?? \App\Models\Setting::get('app_logo');
        $identixIcon = $global_settings['app_icon'] ?? \App\Models\Setting::get('app_icon');
        $identixLogoUrl = $identixLogo ? (str_starts_with($identixLogo, 'http') ? $identixLogo : asset('storage/' . $identixLogo)) : null;
        $identixIconUrl = $identixIcon ? (str_starts_with($identixIcon, 'http') ? $identixIcon : asset('storage/' . $identixIcon)) : null;
    @endphp

    <!-- Print / Action Toolbar -->
    <div class="no-print max-w-[210mm] mx-auto mb-6 flex justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-3">
            @if($identixLogoUrl)
                <div class="flex items-center shrink-0">
                    <img src="{{ $identixLogoUrl }}" alt="{{ $global_settings['app_name'] ?? 'IdenTix' }}" class="h-9 w-auto max-w-[140px] object-contain">
                </div>
            @elseif($identixIconUrl)
                <div class="w-9 h-9 bg-black rounded-xl flex items-center justify-center overflow-hidden p-1 shrink-0 shadow-sm">
                    <img src="{{ $identixIconUrl }}" alt="{{ $global_settings['app_name'] ?? 'IdenTix' }}" class="w-full h-full object-contain">
                </div>
            @else
                <div class="w-9 h-9 bg-black rounded-xl flex items-center justify-center text-white shrink-0">
                    <svg class="w-5 h-5 text-orange-500 fill-current" viewBox="0 0 24 24">
                        <path d="M4 19h4L14 5H10L4 19zm6 0h4L20 5h-4L10 19z"/>
                    </svg>
                </div>
            @endif
            <div>
                <h1 class="text-sm font-black text-slate-800 font-outfit uppercase">Cetak E-Voucher</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Invoice #{{ $transaction->reference_no }} • {{ $activeTickets->count() }} Tiket Aktif
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()"
                    class="px-5 py-2.5 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-black uppercase tracking-wider transition flex items-center gap-2 shadow-lg shadow-slate-900/10 active:scale-95">
                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print / Simpan PDF
            </button>
        </div>
    </div>

    <!-- If no active tickets -->
    @if($activeTickets->isEmpty())
        <div class="max-w-[210mm] mx-auto p-12 text-center bg-white rounded-3xl border border-slate-200 shadow-sm">
            <div class="mx-auto w-16 h-16 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mb-5">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" /></svg>
            </div>
            <h2 class="text-2xl font-black text-slate-800 font-outfit">E-Voucher Tidak Berlaku</h2>
            <p class="text-sm font-semibold text-slate-500 mt-2">Semua tiket pada invoice {{ $transaction->reference_no }} sudah dibatalkan (void).</p>
        </div>
    @else
        <!-- Render each active ticket with the new template -->
        @foreach($activeTickets as $index => $ticket)
            @include('tickets.partials.evoucher-template', [
                'ticket' => $ticket,
                'ticketIndex' => $index + 1,
                'totalTickets' => $activeTickets->count()
            ])
        @endforeach
    @endif

</body>
</html>
