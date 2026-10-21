<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voucher - {{ $ticket->ticket_code }}</title>
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
        $transaction = $ticket->transaction;
        $event = $ticket->event;
        $isPromo100 = $transaction->payment_method === 'promo' || ($transaction->total_amount == 0 && ($transaction->discount_amount ?? 0) > 0);
        $isFree = $transaction->payment_method === 'free' || $isPromo100 || (bool) $event->is_free;

        $activeGroupTickets = $transaction->tickets->where('status', '!=', 'void')->values();
        $groupTicketCount = $activeGroupTickets->count();
        $isMultiTicket = $groupTicketCount > 1;
    @endphp

    {{-- ============================================================
         SCREEN TOOLBAR (PRINT, SAVE PDF, NOTICE)
         ============================================================ --}}
    <div class="no-print max-w-[210mm] mx-auto mb-6 space-y-3">
        
        <!-- Notice if Free Event -->
        @if($isFree)
            <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border-2 border-orange-200 rounded-3xl p-5 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 bg-orange-500 rounded-2xl flex items-center justify-center shrink-0 shadow-lg shadow-orange-200 text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 font-outfit text-sm">Simpan E-Voucher Ini!</h4>
                        <p class="text-xs text-slate-600 mt-0.5 font-medium leading-relaxed">Pendaftaran berhasil! Silakan <strong>simpan sebagai PDF</strong> (<kbd class="px-1.5 py-0.5 bg-slate-200 border border-slate-300 rounded text-[10px]">Ctrl + P</kbd>) atau lakukan <strong>Screen Capture</strong> pada QR Code untuk ditunjukkan kepada petugas di hari pelaksanaan.</p>
                    </div>
                </div>
            </div>
        @endif

        @php
            $identixLogo = $global_settings['app_logo'] ?? \App\Models\Setting::get('app_logo');
            $identixIcon = $global_settings['app_icon'] ?? \App\Models\Setting::get('app_icon');
            $identixLogoUrl = $identixLogo ? (str_starts_with($identixLogo, 'http') ? $identixLogo : asset('storage/' . $identixLogo)) : null;
            $identixIconUrl = $identixIcon ? (str_starts_with($identixIcon, 'http') ? $identixIcon : asset('storage/' . $identixIcon)) : null;
        @endphp

        <!-- Floating Action Header -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
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
                    <h1 class="text-sm font-black text-slate-900 font-outfit uppercase tracking-wider">E-Voucher Resmi</h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        Invoice #{{ $transaction->reference_no }} • {{ $groupTicketCount }} Tiket
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button onclick="window.print()" 
                        class="flex-1 sm:flex-none px-5 py-2.5 bg-slate-900 hover:bg-black text-white font-black text-xs rounded-xl transition shadow-lg shadow-slate-900/10 flex items-center justify-center gap-2 active:scale-95">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print / Simpan PDF
                </button>
            </div>
        </div>

        <!-- Quick Jump Links if multi-ticket -->
        @if($isMultiTicket)
            <div class="bg-white p-3 rounded-2xl border border-slate-200 flex flex-wrap items-center gap-2 text-xs">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider pl-1">Daftar Tiket:</span>
                @foreach($activeGroupTickets as $idx => $gt)
                    <a href="#ticket-{{ $gt->ticket_code }}" 
                       class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition flex items-center gap-1.5 {{ $gt->ticket_code === $ticket->ticket_code ? 'ring-2 ring-orange-500 font-black' : '' }}">
                        <span>🎫</span>
                        <span>Tiket {{ $idx + 1 }}: {{ Str::limit($gt->visitor_data['name'] ?? $transaction->customer_name, 16) }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ============================================================
         RENDER TICKETS USING THE NEW TEMPLATE
         ============================================================ --}}
    @if($isMultiTicket)
        @foreach($activeGroupTickets as $idx => $gt)
            <div id="ticket-{{ $gt->ticket_code }}">
                @include('tickets.partials.evoucher-template', [
                    'ticket' => $gt,
                    'ticketIndex' => $idx + 1,
                    'totalTickets' => $groupTicketCount
                ])
            </div>
        @endforeach
    @else
        <div id="ticket-{{ $ticket->ticket_code }}">
            @include('tickets.partials.evoucher-template', [
                'ticket' => $ticket,
                'ticketIndex' => 1,
                'totalTickets' => 1
            ])
        </div>
    @endif

</body>
</html>
