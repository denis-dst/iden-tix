@php
    $event = $event ?? $ticket->event;
    $transaction = $transaction ?? $ticket->transaction;
    $category = $category ?? $ticket->category;
    $tenant = $event->tenant ?? $transaction->tenant ?? null;

    $isPromo100 = $transaction->payment_method === 'promo' || ($transaction->total_amount == 0 && ($transaction->discount_amount ?? 0) > 0);
    $isFree = $transaction->payment_method === 'free' || $isPromo100 || (bool) $event->is_free;

    // Banner image resolution & fallback
    $bannerUrl = null;
    if ($event->banner_image) {
        $bannerUrl = str_starts_with($event->banner_image, 'http') ? $event->banner_image : asset('storage/' . $event->banner_image);
    } elseif ($event->background_image) {
        $bannerUrl = str_starts_with($event->background_image, 'http') ? $event->background_image : asset('storage/' . $event->background_image);
    }
    if (!$bannerUrl) {
        $bannerUrl = asset('images/concert.webp');
    }

    // Tenant / Promotor logo
    $tenantLogoUrl = null;
    if ($tenant && $tenant->logo) {
        $tenantLogoUrl = str_starts_with($tenant->logo, 'http') ? $tenant->logo : asset('storage/' . $tenant->logo);
    }

    // Identix brand logo & icon from settings
    $identixLogo = $global_settings['app_logo'] ?? \App\Models\Setting::get('app_logo');
    $identixIcon = $global_settings['app_icon'] ?? \App\Models\Setting::get('app_icon');
    $identixLogoUrl = $identixLogo ? (str_starts_with($identixLogo, 'http') ? $identixLogo : asset('storage/' . $identixLogo)) : null;
    $identixIconUrl = $identixIcon ? (str_starts_with($identixIcon, 'http') ? $identixIcon : asset('storage/' . $identixIcon)) : null;

    // Custom attendee questions & answers
    $customFields = method_exists($event, 'getCustomFields') ? $event->getCustomFields() : [];
    $visitorAnswers = is_array($ticket->visitor_data['custom_answers'] ?? null) ? $ticket->visitor_data['custom_answers'] : [];

    // Fallback: Check transaction payment_reference if visitor_data was not populated
    if (empty($visitorAnswers) && !empty($transaction->payment_reference)) {
        $decodedRef = json_decode($transaction->payment_reference, true);
        if (is_array($decodedRef) && !empty($decodedRef['custom_answers']) && is_array($decodedRef['custom_answers'])) {
            $visitorAnswers = $decodedRef['custom_answers'];
        }
    }

    // Fallback split answers if answer was stored as a single pipe-delimited string
    $rawSummary = $ticket->visitor_data['umroh_answer'] ?? ($transaction->customer_umroh_answer ?? null);
    $splitAnswers = [];
    if (is_string($rawSummary) && str_contains($rawSummary, ' | ')) {
        $splitAnswers = array_map('trim', explode(' | ', $rawSummary));
    }

    // Fallback standard 9-point promotor terms if event terms not customized
    $defaultTerms = "1. E-ticket yang sah adalah yang dibeli melalui website resmi identix.\n"
        . "2. Pembelian tiket dapat dibatasi per NIK / transaksi sesuai ketentuan penyelenggara.\n"
        . "3. Harap membawa kartu identitas asli dan E-ticket pada saat memasuki venue acara.\n"
        . "4. E-ticket ini sebagai TIKET TANDA MASUK dan selanjutnya akan di-scan barcode di gate stadion / venue sesuai jenis kategori tiket.\n"
        . "5. E-ticket hanya berlaku untuk sekali masuk (1x use).\n"
        . "6. Penyelenggara berhak menolak masuk pengunjung jika E-ticket telah digunakan orang lain, tidak valid, atau didapatkan dengan cara tidak sah.\n"
        . "7. Pihak penyelenggara berhak menindak tegas penonton apabila tidak mematuhi protokol kesehatan dan peraturan tata tertib.\n"
        . "8. E-ticket yang sudah dibeli / didaftarkan tidak dapat dikembalikan (non-refundable).\n"
        . "9. Dilarang membawa dan menggunakan segala obat-obatan terlarang, narkoba, miras, senjata tajam/api, flare, atau barang berbahaya lainnya.";

    $termsContent = $event->terms_conditions ?: ($tenant->terms_conditions ?? $defaultTerms);
@endphp

<div class="evoucher-page bg-white text-slate-900 border border-slate-200 shadow-2xl rounded-2xl overflow-hidden mx-auto my-3 sm:my-6 flex flex-row relative max-w-[210mm] w-full"
     style="page-break-after: always; break-after: page;">
    
    {{-- =========================================================
         LEFT VERTICAL BLACK STRIP (IDENTIX BRANDING)
         ========================================================= --}}
    <div class="w-9 sm:w-12 print:w-12 bg-black text-white shrink-0 flex flex-col justify-between items-center py-4 sm:py-6 print:py-6 select-none relative z-10"
         style="-webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: #000000 !important;">
        
        <!-- Top Website URL (Rotated 90 deg) -->
        <div class="pt-2">
            <span style="writing-mode: vertical-rl; transform: rotate(180deg);"
                  class="text-[8.5px] sm:text-[9px] print:text-[9px] font-black tracking-[0.25em] sm:tracking-[0.3em] text-slate-300 font-mono uppercase inline-block">
                WWW.IDEN-TIX.COM
            </span>
        </div>

        <!-- Spacer -->
        <div class="flex-1"></div>

        <!-- Bottom Identix Brand Logo (Rotated 90 deg reading upward) -->
        <div class="pb-2">
            <div class="flex items-center gap-1.5 sm:gap-2 print:gap-2" style="writing-mode: vertical-rl; transform: rotate(180deg);">
                @if($identixLogoUrl)
                    <div class="flex items-center py-1">
                        <img src="{{ $identixLogoUrl }}" alt="IDENTIX" class="h-5 sm:h-7 print:h-7 w-auto max-w-[100px] sm:max-w-[130px] object-contain">
                    </div>
                @else
                    <div class="flex items-center gap-1.5">
                        @if($identixIconUrl)
                            <img src="{{ $identixIconUrl }}" alt="IDENTIX" class="w-3.5 h-3.5 sm:w-4 sm:h-4 print:w-4 print:h-4 object-contain shrink-0">
                        @else
                            <!-- Stylized Identix Orange Slashes -->
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 print:w-4 print:h-4 text-orange-500 fill-current shrink-0" viewBox="0 0 24 24">
                                <path d="M4 19h4L14 5H10L4 19zm6 0h4L20 5h-4L10 19z"/>
                            </svg>
                        @endif
                        <span class="text-[10px] sm:text-xs print:text-xs font-black tracking-widest text-white uppercase font-outfit">IDENTIX</span>
                    </div>
                    <span class="text-[6px] sm:text-[6.5px] print:text-[6.5px] font-black tracking-[0.15em] sm:tracking-[0.2em] text-orange-400 uppercase">
                        TICKETING & FAN ENGAGEMENT
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- =========================================================
         MAIN TICKET CONTENT (RIGHT OF THE STRIP)
         ========================================================= --}}
    <div class="flex-1 flex flex-col min-w-0 bg-white">
        
        {{-- =====================================================
             1. TOP SECTION: BANNER ETIKET DARI PROMOTOR
             ===================================================== --}}
        <div class="relative w-full h-48 sm:h-56 bg-slate-950 overflow-hidden shrink-0 flex items-center justify-center"
             style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
            <!-- Ambient blurred backdrop (fills width/height seamlessly with event artwork colors) -->
            <img src="{{ $bannerUrl }}" 
                 alt=""
                 aria-hidden="true"
                 class="absolute inset-0 w-full h-full object-cover object-center blur-lg scale-110 opacity-50 brightness-75 pointer-events-none"
                 loading="eager" decoding="async">

            <!-- Vignette & Gradient for text contrast -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-black/30 pointer-events-none z-10"></div>

            <!-- Main sharp poster/thumbnail image (100% visible, complete, never cropped) -->
            <img src="{{ $bannerUrl }}" 
                 alt="{{ $event->name }}"
                 class="relative z-10 max-h-full max-w-full w-auto h-auto object-contain object-center drop-shadow-2xl"
                 loading="eager" decoding="async">

            <!-- Matchup Header (Soccer / Sport / Team Match) -->
            @if($event->home_team_name && $event->away_team_name)
                <div class="absolute top-4 left-6 right-6 flex items-center justify-between text-white z-20">
                    <div class="flex items-center gap-3">
                        @if($event->home_team_logo)
                            <img src="{{ str_starts_with($event->home_team_logo, 'http') ? $event->home_team_logo : asset('storage/' . $event->home_team_logo) }}" 
                                 class="w-10 h-10 object-contain drop-shadow-md">
                        @endif
                        <span class="text-xs sm:text-sm font-black tracking-wider uppercase font-outfit drop-shadow">{{ $event->home_team_name }}</span>
                    </div>

                    <span class="px-2 py-0.5 bg-rose-600 text-white font-black text-[10px] rounded uppercase tracking-widest shadow-lg">VS</span>

                    <div class="flex items-center gap-3">
                        <span class="text-xs sm:text-sm font-black tracking-wider uppercase font-outfit drop-shadow">{{ $event->away_team_name }}</span>
                        @if($event->away_team_logo)
                            <img src="{{ str_starts_with($event->away_team_logo, 'http') ? $event->away_team_logo : asset('storage/' . $event->away_team_logo) }}" 
                                 class="w-10 h-10 object-contain drop-shadow-md">
                        @endif
                    </div>
                </div>
            @endif

            <!-- Bottom Banner Badges: Venue, Date, Time -->
            <div class="absolute bottom-3 left-3 right-3 sm:left-6 sm:right-6 space-y-1.5 sm:space-y-2 text-white z-20">
                <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-lg bg-black/60 backdrop-blur-sm border border-white/20 text-[10px] sm:text-xs font-bold text-white shadow-sm max-w-full">
                    <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="uppercase tracking-wide font-extrabold truncate">{{ $event->venue }}, {{ $event->city }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <div class="inline-flex items-center gap-1.5 px-2 sm:px-2.5 py-0.5 rounded-md bg-blue-950/80 border border-blue-400/40 text-[9px] sm:text-[10px] font-black uppercase text-blue-100 tracking-wider">
                        <svg class="w-3 h-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ $event->event_start_date ? $event->event_start_date->translatedFormat('l, d F Y') : '-' }}</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-2 sm:px-2.5 py-0.5 rounded-md bg-rose-950/80 border border-rose-400/40 text-[9px] sm:text-[10px] font-black uppercase text-rose-100 tracking-wider">
                        <svg class="w-3 h-3 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>
                            @if($event->gate_open_at)
                                KICK OFF / OPEN: {{ $event->gate_open_at->format('H:i') }} WIB
                            @elseif($event->event_start_date)
                                START: {{ $event->event_start_date->format('H:i') }} WIB
                            @else
                                KICK OFF: 20:00 WIB
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- =====================================================
             2. MIDDLE SECTION: DETAIL ETIKET & QR CODE
             ===================================================== --}}
        <div class="p-3.5 sm:p-6 print:p-6 flex-1 flex flex-col justify-between">
            
            <!-- Top bar: E-TICKET label & Redeem Info Pill -->
            <div class="flex flex-col sm:flex-row print:flex-row sm:items-center print:items-center justify-between gap-2.5 pb-3 border-b border-dashed border-slate-300">
                <div class="flex items-center gap-2.5 shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center shrink-0">
                        <!-- Stub Ticket Icon -->
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-base font-black text-slate-900 tracking-wider font-outfit uppercase leading-tight whitespace-nowrap">E-TICKET</div>
                        <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest truncate">{{ $category->name ?? 'GENERAL' }}</div>
                    </div>
                </div>

                <div class="border border-slate-300 bg-slate-50/70 rounded-xl px-3 py-1.5 text-left sm:text-right print:text-right w-full sm:w-auto print:w-auto">
                    <div class="text-[8.5px] font-black text-slate-700 uppercase tracking-wider leading-snug">
                        @if(($event->purchase_flow ?? 'redeem') === 'redeem')
                            WAKTU MULAI PENUKARAN: {{ $event->gate_open_at ? $event->gate_open_at->translatedFormat('d F Y • H:i') . ' WIB' : ($event->event_start_date ? $event->event_start_date->translatedFormat('d F Y • H:i') . ' WIB' : '-') }}
                        @else
                            WAKTU ACARA: {{ $event->event_start_date ? $event->event_start_date->translatedFormat('d F Y • H:i') . ' WIB' : ($event->gate_open_at ? $event->gate_open_at->translatedFormat('d F Y • H:i') . ' WIB' : '-') }}
                        @endif
                    </div>
                    <div class="text-[8px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">
                        {{ $event->city }} • {{ ($event->purchase_flow ?? 'redeem') === 'redeem' ? 'PENUKARAN WRISTBAND' : 'DIRECT SCAN AT GATE' }}
                    </div>
                </div>
            </div>

            <!-- Main Content: Left details + Right QR -->
            <div class="flex flex-col sm:grid print:grid sm:grid-cols-12 print:grid-cols-12 gap-5 pt-3">
                
                <!-- Left Details (7 cols on desktop/print, full width on mobile) -->
                <div class="w-full sm:col-span-7 print:col-span-7 space-y-3">
                    
                    @php
                        $gateName = trim((string) ($category?->gates?->first()?->name ?? $event?->gates?->first()?->name ?? ''));
                    @endphp

                    <!-- Kategori & Gate Boxes -->
                    <div class="grid {{ $gateName !== '' ? 'grid-cols-2' : 'grid-cols-1' }} gap-2.5">
                        <div class="border border-slate-200 bg-slate-50/70 rounded-xl p-2">
                            <div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">KATEGORI</div>
                            <div class="text-xs font-black text-slate-900 uppercase font-outfit truncate">{{ $category->name ?? 'REGULAR' }}</div>
                        </div>
                        @if($gateName !== '')
                            <div class="border border-slate-200 bg-slate-50/70 rounded-xl p-2">
                                <div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">GATE</div>
                                <div class="text-xs font-black text-slate-900 uppercase font-outfit truncate">
                                    {{ $gateName }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Event Name / Match Title -->
                    <div class="pt-0.5">
                        <h2 class="text-lg sm:text-xl font-black text-slate-950 font-outfit uppercase tracking-tight leading-snug">
                            @if($event->home_team_name && $event->away_team_name)
                                {{ $event->home_team_name }} VS {{ $event->away_team_name }}
                            @else
                                {{ $event->name }}
                            @endif
                        </h2>
                    </div>

                    <!-- Key-Value rows with rounded black icons -->
                    <div class="space-y-2 pt-1 border-t border-slate-100">
                        
                        <!-- Nama Pembeli / Peserta -->
                        <div class="flex items-start gap-2.5 text-xs">
                            <div class="w-5 h-5 rounded-md bg-slate-900 text-white flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">NAMA PEMBELI / PESERTA</div>
                                <div class="text-xs font-black text-slate-900 uppercase break-words">
                                    {{ $ticket->visitor_data['name'] ?? $transaction->customer_name }}
                                </div>
                            </div>
                        </div>

                        <!-- No. Order / Invoice -->
                        <div class="flex items-start gap-2.5 text-xs">
                            <div class="w-5 h-5 rounded-md bg-slate-900 text-white flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">NO. ORDER / INVOICE</div>
                                <div class="text-xs font-black text-slate-900 font-mono tracking-wider break-all sm:break-normal">
                                    {{ $transaction->reference_no }}
                                </div>
                            </div>
                        </div>

                        <!-- Waktu Transaksi Pembelian -->
                        @php
                            $txDate = $transaction->paid_at ?? $transaction->created_at;
                            if (is_string($txDate)) {
                                try {
                                    $txDate = \Carbon\Carbon::parse($txDate);
                                } catch (\Exception $e) {}
                            }
                        @endphp
                        @if($txDate instanceof \Carbon\CarbonInterface || $txDate instanceof \DateTimeInterface)
                            <div class="flex items-start gap-2.5 text-xs">
                                <div class="w-5 h-5 rounded-md bg-slate-900 text-white flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">WAKTU TRANSAKSI PEMBELIAN</div>
                                    <div class="text-xs font-black text-slate-900 uppercase break-words">
                                        {{ $txDate->translatedFormat('d F Y • H:i') }} WIB
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Tanggal Acara -->
                        <div class="flex items-start gap-2.5 text-xs">
                            <div class="w-5 h-5 rounded-md bg-slate-900 text-white flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">TANGGAL ACARA</div>
                                <div class="text-xs font-black text-slate-900 uppercase break-words">
                                    {{ $event->event_start_date ? $event->event_start_date->translatedFormat('d F Y • H:i') : '-' }} WIB
                                </div>
                            </div>
                        </div>

                        {{-- =============================================
                             DYNAMIC CUSTOM DATA & QUESTIONS (ISIAN KUSTOM)
                             ============================================= --}}
                        @php
                            $phoneRenderedInCustom = false;
                            $genderRenderedInCustom = false;
                            $mainAttendeeName = strtolower(trim((string)($ticket->visitor_data['name'] ?? $transaction->customer_name)));
                        @endphp

                        @if(!empty($customFields))
                            @foreach($customFields as $idx => $cf)
                                @php
                                    $showInEvoucher = isset($cf['show_in_evoucher']) ? (bool) $cf['show_in_evoucher'] : true;
                                    if (!$showInEvoucher) {
                                        continue;
                                    }

                                    $ans = null;
                                    if (isset($visitorAnswers[$cf['id']])) {
                                        $ans = $visitorAnswers[$cf['id']];
                                    } elseif (isset($visitorAnswers[$cf['label']])) {
                                        $ans = $visitorAnswers[$cf['label']];
                                    } elseif (!empty($splitAnswers) && isset($splitAnswers[$idx])) {
                                        $ans = $splitAnswers[$idx];
                                    } else {
                                        $labelLower = strtolower(trim($cf['label'] ?? ''));
                                        if (($cf['type'] ?? '') === 'name' || preg_match('/^(nama|name)$/i', $labelLower)) {
                                            $ans = $ticket->visitor_data['name'] ?? $transaction->customer_name;
                                        } elseif (($cf['type'] ?? '') === 'email' || preg_match('/^(email|surel)$/i', $labelLower)) {
                                            $ans = $ticket->visitor_data['email'] ?? $transaction->customer_email;
                                        } elseif (($cf['type'] ?? '') === 'phone' || preg_match('/^(no\.?\s*(hp|wa|telepon|telpon|telp|whatsapp)|nomor\s*(hp|wa|telepon|telpon|telp|whatsapp))$/i', $labelLower)) {
                                            $ans = $ticket->visitor_data['phone'] ?? $transaction->customer_phone;
                                        } elseif (preg_match('/^(nik|nomor\s*identitas|no\.?\s*ktp|no\.?\s*identitas)$/i', $labelLower)) {
                                            $ans = $ticket->visitor_data['nik'] ?? $transaction->customer_nik;
                                        } elseif (preg_match('/^(gender|jenis\s*kelamin)$/i', $labelLower)) {
                                            $ans = $ticket->visitor_data['gender'] ?? $transaction->customer_gender;
                                        } elseif (count($customFields) === 1) {
                                            $ans = $rawSummary;
                                        }
                                    }

                                    $displayAns = is_array($ans) ? implode(', ', $ans) : (string) $ans;
                                    if (is_string($displayAns) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($displayAns))) {
                                        try {
                                            $displayAns = \Carbon\Carbon::parse(trim($displayAns))->translatedFormat('d F Y');
                                        } catch (\Exception $e) {}
                                    }

                                    $cfLabel = trim($cf['label'] ?? '');
                                    $cfType = $cf['type'] ?? '';

                                    // Skip redundant identical Name if already shown at the top
                                    $isGenericName = ($cfType === 'name' || preg_match('/^(nama|name|nama\s*lengkap|nama\s*peserta)$/i', $cfLabel));
                                    if ($isGenericName && strtolower(trim((string)$displayAns)) === $mainAttendeeName) {
                                        continue;
                                    }

                                    // Track if phone or gender was displayed in custom fields
                                    if ($cfType === 'phone' || preg_match('/wa|whatsapp|hp|handphone|phone|telp|telepon|telpon|ponsel|seluler|kontak/i', $cfLabel)) {
                                        $phoneRenderedInCustom = true;
                                    }
                                    if (preg_match('/gender|jenis\s*kelamin/i', $cfLabel)) {
                                        $genderRenderedInCustom = true;
                                    }
                                @endphp
                                @if($ans !== null && trim((string)$ans) !== '')
                                    <div class="flex items-start gap-2.5 text-xs">
                                        <div class="w-5 h-5 rounded-md bg-amber-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-[8px] font-bold text-amber-600 uppercase tracking-wider">{{ $cf['label'] }}</div>
                                            <div class="text-xs font-black text-slate-900 uppercase break-words">
                                                {{ $displayAns }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @elseif(isset($ticket->visitor_data['umroh_answer']) && $ticket->visitor_data['umroh_answer'])
                            <div class="flex items-start gap-2.5 text-xs">
                                <div class="w-5 h-5 rounded-md bg-amber-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[8px] font-bold text-amber-600 uppercase tracking-wider">
                                        {{ $event->meta['custom_question_text'] ?? 'Jawaban Kustom' }}
                                    </div>
                                    <div class="text-xs font-black text-slate-900 uppercase break-words">
                                        {{ $ticket->visitor_data['umroh_answer'] }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Check if phone or gender questions exist in organizer configuration -->
                        @php
                            $hasPhoneFieldConfigured = collect($customFields)->contains(function($cf) {
                                $lbl = strtolower(trim($cf['label'] ?? ''));
                                return ($cf['type'] ?? '') === 'phone' || preg_match('/wa|whatsapp|hp|handphone|phone|telp|telepon|telpon|ponsel|seluler|kontak/i', $lbl);
                            });

                            $hasGenderFieldConfigured = collect($customFields)->contains(function($cf) {
                                $lbl = strtolower(trim($cf['label'] ?? ''));
                                return preg_match('/gender|jenis\s*kelamin/i', $lbl);
                            });
                        @endphp

                        <!-- Optional Gender (only if not configured or already displayed in custom questions) -->
                        @if(!$genderRenderedInCustom && !$hasGenderFieldConfigured && !empty($ticket->visitor_data['gender']))
                            <div class="flex items-start gap-2.5 text-xs">
                                <div class="w-5 h-5 rounded-md bg-slate-900 text-white flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">GENDER</div>
                                    <div class="text-xs font-black text-slate-900 uppercase break-words">{{ $ticket->visitor_data['gender'] }}</div>
                                </div>
                            </div>
                        @endif

                        <!-- Optional Phone / WhatsApp (only if not configured or already displayed in custom questions) -->
                        @if(!$phoneRenderedInCustom && !$hasPhoneFieldConfigured && (!empty($ticket->visitor_data['phone']) || !empty($transaction->customer_phone)))
                            <div class="flex items-start gap-2.5 text-xs">
                                <div class="w-5 h-5 rounded-md bg-slate-900 text-white flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">NO. TELEPON / WHATSAPP</div>
                                    <div class="text-xs font-black text-slate-900 uppercase break-words">
                                        {{ $ticket->visitor_data['phone'] ?? $transaction->customer_phone }}
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <!-- Right Details: Price, QR Code, Code string, 1X pill (5 cols on desktop / print, centered card on mobile) -->
                <div class="w-full sm:col-span-5 print:col-span-5 flex flex-col items-center justify-center sm:justify-start print:justify-start text-center p-4 sm:p-0 print:p-0 bg-slate-50/80 sm:bg-transparent print:bg-transparent border border-slate-200 sm:border-0 print:border-0 rounded-2xl sm:rounded-none print:rounded-none sm:pl-2 print:pl-2">
                    
                    <!-- Category Name & Price -->
                    <div class="text-center w-full mb-2">
                        <div class="text-xs font-black text-slate-900 uppercase font-outfit truncate">
                            {{ $category->name ?? 'VIP' }}
                        </div>
                        <div class="text-sm font-black text-slate-900 font-outfit">
                            @if($isFree)
                                <span class="text-emerald-600 font-black">GRATIS</span>
                            @else
                                Rp {{ number_format($category->price ?? 0, 0, ',', '.') }}
                            @endif
                        </div>
                    </div>

                    <!-- QR Code Box -->
                    <div class="bg-white border-2 border-slate-300 rounded-2xl p-2.5 shadow-sm inline-block mb-2"
                         style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        {!! QrCode::size(140)->margin(0)->generate($ticket->ticket_code) !!}
                    </div>

                    <!-- Code String & 1X pill (Tightly placed directly under QR Code) -->
                    <div class="w-full text-center space-y-1.5">
                        <div class="font-mono font-black text-xs sm:text-sm print:text-sm text-slate-950 tracking-[0.2em]">
                            {{ $ticket->ticket_code }}
                        </div>
                        <div class="inline-block px-3 py-1 bg-slate-100 border border-slate-300 rounded-lg text-[8.5px] font-black text-slate-700 uppercase tracking-wider text-center whitespace-nowrap"
                             style="-webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: #f1f5f9 !important;">
                            TIKET HANYA DAPAT DIGUNAKAN 1X
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notice / Evoucher Info if configured on event -->
            @if($event->evoucher_info)
                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5 text-xs text-amber-900">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-[10px] font-medium leading-relaxed">
                        <strong class="font-black uppercase tracking-wider block text-amber-950 mb-0.5">Informasi Tambahan:</strong>
                        {!! nl2br(e($event->evoucher_info)) !!}
                    </div>
                </div>
            @endif

            {{-- =====================================================
                 3. BOTTOM SECTION: SYARAT & KETENTUAN + PROMOTOR LOGO
                 ===================================================== --}}
            <div class="mt-4 pt-3 border-t border-dashed border-slate-300 flex flex-col sm:grid print:grid sm:grid-cols-12 print:grid-cols-12 gap-4 items-stretch sm:items-start print:items-start">
                
                <!-- Left: Terms and Conditions List (8 cols on desktop / print) -->
                <div class="w-full order-2 sm:order-1 print:order-1 sm:col-span-8 print:col-span-8">
                    <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-wider font-outfit mb-1.5">
                        SYARAT DAN KETENTUAN
                    </h3>

                    @if($event->terms_conditions)
                        <div class="text-[8px] text-slate-600 leading-tight terms-content">
                            @include('components.terms-content', ['terms' => $event->terms_conditions])
                        </div>
                    @else
                        <ol class="list-decimal pl-3 space-y-0.5 text-[8px] text-slate-600 leading-tight">
                            <li>E-ticket yang sah adalah yang dibeli melalui website resmi identix.</li>
                            <li>Pembelian tiket dapat dibatasi per NIK / transaksi sesuai ketentuan penyelenggara.</li>
                            <li>Harap membawa kartu identitas asli dan E-ticket pada saat memasuki venue acara.</li>
                            <li>E-ticket ini sebagai TIKET TANDA MASUK dan selanjutnya akan di-scan barcode di gate stadion / venue sesuai jenis kategori tiket.</li>
                            <li>E-ticket hanya berlaku untuk sekali masuk (1x use).</li>
                            <li>Penyelenggara memiliki hak untuk:
                                <ul class="list-disc pl-3 mt-0.5 space-y-0.5">
                                    <li>Melarang masuk pengunjung jika E-ticket telah dipergunakan oleh orang lain.</li>
                                    <li>Melarang masuk pengunjung ke dalam stadion/venue jika E-ticket yang digunakan tidak valid.</li>
                                    <li>Memproses hukum pengunjung yang memalsukan atau mendapatkan tiket secara ilegal.</li>
                                </ul>
                            </li>
                            <li>Pihak penyelenggara berhak menindak tegas penonton apabila tidak mematuhi protokol dan tata tertib.</li>
                            <li>E-ticket yang sudah dibeli / didaftarkan tidak dapat dikembalikan atau dibatalkan (non-refundable).</li>
                            <li>Dilarang membawa senjata tajam, obat-obatan terlarang, narkoba, miras, flare, atau barang berbahaya lainnya.</li>
                        </ol>
                    @endif
                </div>

                <!-- Right: Organized By Promotor Box (4 cols on desktop / print, full width on mobile) -->
                <div class="w-full order-1 sm:order-2 print:order-2 sm:col-span-4 print:col-span-4 border border-slate-300 rounded-xl overflow-hidden bg-white text-center flex flex-col justify-between"
                     style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                    <div class="bg-slate-50 border-b border-slate-200 py-1 px-2 text-[7.5px] font-black uppercase text-slate-500 tracking-widest">
                        ORGANIZED BY :
                    </div>
                    
                    <div class="p-3 min-h-[60px] sm:min-h-[70px] flex items-center justify-center">
                        @if($tenantLogoUrl)
                            <img src="{{ $tenantLogoUrl }}" 
                                 alt="{{ $tenant->name ?? 'Promotor' }}" 
                                 class="max-h-12 sm:max-h-14 max-w-full object-contain mx-auto">
                        @else
                            <div class="space-y-1">
                                <div class="w-8 h-8 rounded-full bg-slate-900 text-white font-black text-xs flex items-center justify-center mx-auto shadow-sm">
                                    {{ strtoupper(substr($tenant->name ?? 'ORG', 0, 2)) }}
                                </div>
                                <div class="text-[9px] font-black text-slate-900 uppercase font-outfit truncate px-1">
                                    {{ $tenant->name ?? ($event->tenant->name ?? 'PROMOTOR') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
