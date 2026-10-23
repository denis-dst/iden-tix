<x-app-layout>
    <x-slot name="title">Partner Dashboard</x-slot>

    @php
        $pendingInvoices = collect();
        $showInvoiceWidget = false;
        if (auth()->user()->tenant_id) {
            $pendingInvoices = \App\Models\Invoice::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'sent')
                ->whereNull('payment_confirmed_at')
                ->orderBy('due_date', 'asc')
                ->get();
            
            $dismissedKey = 'invoice_modal_dismissed_' . auth()->id();
            $dismissedAt  = session($dismissedKey);
            $showInvoiceWidget = $pendingInvoices->isNotEmpty() 
                && ($dismissedAt === null || !now()->isSameDay(\Carbon\Carbon::parse($dismissedAt)));
        }
    @endphp

    <div class="space-y-6 md:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Welcome Hero -->
        <div x-data="{ showInvoice: @js($showInvoiceWidget) }"
             class="relative overflow-hidden bg-gradient-to-br from-orange-500 to-amber-600 rounded-3xl p-6 md:p-10 text-white shadow-xl shadow-orange-500/20">
            
            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                {{-- Left Content: Welcome Hero --}}
                <div :class="showInvoice ? 'lg:col-span-7' : 'lg:col-span-12 max-w-4xl'" class="transition-all duration-300">
                    <p class="text-[10px] md:text-xs font-black uppercase tracking-[0.3em] text-orange-100 mb-2 md:mb-4 opacity-80">Selamat Datang Kembali</p>
                    <h2 class="text-2xl md:text-5xl font-black mb-4 md:mb-6 leading-tight font-outfit">
                        Halo, <br class="hidden md:block">
                        <span class="text-orange-50">{{ Auth::user()->tenant->name ?? 'Partner' }}</span>!
                    </h2>
                    <p class="text-sm md:text-lg text-white/90 font-medium mb-6 md:mb-8 leading-relaxed max-w-xl">
                        Kelola event Anda, pantau penjualan tiket secara real-time, dan monitor alur kedatangan pengunjung di setiap gate.
                    </p>
                    <div class="flex flex-wrap gap-3 md:gap-4">
                        <a href="{{ route('organizer.events.create') }}" class="px-5 py-3 md:px-8 md:py-4 bg-orange-600 text-white rounded-2xl font-black text-xs md:text-sm uppercase tracking-wider hover:bg-orange-700 transition shadow-lg flex items-center gap-2 group">
                            <svg class="w-4 h-4 md:w-5 md:h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            Buat Event Baru
                        </a>
                        @if(Auth::user()->tenant)
                            <a href="{{ route('tenant.events', Auth::user()->tenant->slug) }}" target="_blank" class="px-5 py-3 md:px-8 md:py-4 bg-white/20 hover:bg-white/30 text-white rounded-2xl font-black text-xs md:text-sm uppercase tracking-wider transition shadow-lg flex items-center gap-2 border border-white/25 backdrop-blur">
                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Halaman Publik Event Saya
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Right Content: Invoice Alert Widget --}}
                <div x-show="showInvoice"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4 lg:translate-y-0 lg:translate-x-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0 lg:translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0 lg:translate-x-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-4 lg:translate-y-0 lg:translate-x-4"
                     class="lg:col-span-5 w-full">
                    
                    <div class="bg-white rounded-3xl p-6 text-slate-800 shadow-2xl border border-white/10">
                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center shrink-0 text-orange-500 shadow-inner">
                                <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Notifikasi Tagihan</p>
                                <h3 class="font-black text-slate-800 text-sm leading-tight truncate">
                                    {{ $pendingInvoices->count() > 1 ? $pendingInvoices->count() . ' Tagihan Baru' : 'Invoice Baru!' }}
                                </h3>
                            </div>
                        </div>

                        {{-- Invoices list --}}
                        <div class="space-y-2 mb-4 max-h-[140px] overflow-y-auto custom-scrollbar pr-1">
                            @foreach($pendingInvoices as $inv)
                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 flex items-center justify-between gap-3 text-xs hover:border-orange-100 transition">
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-700 truncate">{{ $inv->title }}</p>
                                        <p class="text-[10px] {{ $inv->is_overdue ? 'text-red-500 font-bold' : 'text-slate-400' }} mt-0.5">
                                            Jatuh tempo: {{ $inv->due_date->format('d M Y') }}
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="font-black text-slate-800">{{ $inv->formatted_total }}</p>
                                        @if($inv->payment_proof)
                                            <p class="text-[9px] text-blue-500 font-bold">Proses verifikasi</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Action Buttons --}}
                        <div class="space-y-2">
                            @if($pendingInvoices->count() === 1)
                                <a href="{{ route('organizer.invoices.show', $pendingInvoices->first()) }}"
                                   class="flex items-center justify-center gap-2 w-full py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold text-xs transition shadow-lg shadow-orange-500/20">
                                    Lihat & Bayar Invoice
                                </a>
                            @else
                                <a href="{{ route('organizer.invoices.index') }}"
                                   class="flex items-center justify-center gap-2 w-full py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold text-xs transition shadow-lg shadow-orange-500/20">
                                    Lihat Semua Invoice
                                </a>
                            @endif

                            <button @click="
                                fetch('{{ route('organizer.invoices.dismiss-modal') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json'
                                    }
                                }).then(() => {
                                    showInvoice = false;
                                });
                            "
                            class="w-full py-2 text-slate-400 text-xs font-semibold hover:text-slate-600 transition hover:bg-slate-50 rounded-xl">
                                Ingatkan lagi besok
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-20 -mr-20 w-64 md:w-96 h-64 md:h-96 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 -mb-20 -mr-20 w-48 md:w-64 h-48 md:h-64 bg-black/10 rounded-full blur-2xl"></div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6">
            <!-- Ticket Sales -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 group hover:shadow-md transition-all duration-300 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tiket Terjual</p>
                    <h4 class="text-2xl font-black text-slate-800 font-outfit">{{ number_format($totalTicketsSold ?? $events->sum('tickets_count'), 0, ',', '.') }}</h4>
                </div>
                <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500 group-hover:bg-orange-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                </div>
            </div>

            <!-- Total Events -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 group hover:shadow-md transition-all duration-300 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Event</p>
                    <h4 class="text-2xl font-black text-slate-800 font-outfit">{{ $events->count() }}</h4>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-500 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
            </div>

            <!-- Total Hit Publik -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 group hover:shadow-md transition-all duration-300 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Hit Publik</p>
                    <h4 class="text-2xl font-black text-purple-600 font-outfit">{{ number_format($totalViews ?? $events->sum('views_count'), 0, ',', '.') }}</h4>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
            </div>

            <!-- Total Scan Check-in -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 group hover:shadow-md transition-all duration-300 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Check-In</p>
                    <h4 class="text-2xl font-black text-emerald-600 font-outfit">{{ $totalCheckIn }}</h4>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>

            <!-- Total Aktivitas Scan -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 group hover:shadow-md transition-all duration-300 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Log Scan</p>
                    <h4 class="text-2xl font-black text-slate-800 font-outfit">{{ $totalScans }}</h4>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 group-hover:bg-amber-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
        </div>

        <!-- Early Arrival Spotlight (Rekor Tercepat Datang) -->
        @if($earliestScan)
            <div class="bg-gradient-to-r from-amber-50 via-orange-50 to-amber-100/60 border border-amber-200/80 rounded-3xl p-6 shadow-sm">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-amber-500 text-white rounded-2xl flex items-center justify-center shrink-0 shadow-md shadow-amber-500/30">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 bg-amber-500 text-white text-[10px] font-black uppercase rounded-full tracking-wider">
                                    Juara 1 Tercepat Masuk
                                </span>
                                <span class="text-xs font-bold text-amber-800">
                                    {{ $earliestScan->event->name ?? 'Event' }} &bull; {{ $earliestScan->gate_name ?: 'Main Gate' }}
                                </span>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 font-outfit mt-1">
                                {{ $earliestScan->ticket->visitor_data['name'] ?? $earliestScan->ticket->transaction->customer_name ?? 'Pengunjung' }}
                            </h3>
                            <p class="text-xs text-slate-600 mt-0.5 font-medium">
                                Tiket: <span class="font-bold text-slate-800">{{ $earliestScan->ticket->category->name ?? 'Reguler' }}</span> ({{ $earliestScan->ticket->ticket_code }})
                            </p>
                        </div>
                    </div>
                    <div class="bg-white/90 backdrop-blur-sm px-5 py-3 rounded-2xl border border-amber-200 text-right shrink-0 w-full md:w-auto">
                        <p class="text-[10px] font-black uppercase tracking-wider text-amber-700">Waktu Kedatangan Pertama</p>
                        <p class="text-base md:text-lg font-black text-slate-900 font-mono">
                            {{ $earliestScan->scanned_at->timezone('Asia/Jakarta')->format('H:i:s') }} <span class="text-xs font-sans text-slate-500">WIB</span>
                        </p>
                        <p class="text-[10px] text-slate-500 font-medium">
                            {{ $earliestScan->scanned_at->timezone('Asia/Jakarta')->format('d M Y') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Scan History Section (Tiap Event & Tiap Gate) -->
        <div id="scan-history" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            {{-- Header & Filter Toolbar --}}
            <div class="p-6 border-b border-slate-100 bg-white">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xl font-black text-slate-800 font-outfit leading-none">Riwayat Scan Tiap Event & Gate</h3>
                            <span class="px-2.5 py-0.5 bg-orange-50 text-orange-600 text-[10px] font-black rounded-full uppercase tracking-wider">
                                Deteksi Tercepat
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-1.5">
                            Pantau urutan waktu kehadiran pengunjung terawal dan log check-in/out di setiap gate.
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-slate-500">
                            Menampilkan <span class="font-black text-slate-800">{{ $scanLogs->total() }}</span> log scan
                        </span>
                    </div>
                </div>

                {{-- Filter Form --}}
                <form method="GET" action="{{ route('organizer.dashboard') }}#scan-history" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    {{-- Filter Event --}}
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Filter Event</label>
                        <select name="event_id" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl px-3 py-2.5 font-bold focus:ring-2 focus:ring-orange-500 focus:outline-none transition">
                            <option value="">Semua Event</option>
                            @foreach($eventOptions as $ev)
                                <option value="{{ $ev->id }}" {{ (string) $selectedEventId === (string) $ev->id ? 'selected' : '' }}>
                                    {{ $ev->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Gate --}}
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Filter Gate</label>
                        <select name="gate_name" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl px-3 py-2.5 font-bold focus:ring-2 focus:ring-orange-500 focus:outline-none transition">
                            <option value="">Semua Gate</option>
                            @foreach($gateOptions as $gate)
                                <option value="{{ $gate }}" {{ $selectedGate === $gate ? 'selected' : '' }}>
                                    {{ $gate }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Tipe Scan --}}
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Tipe Scan</label>
                        <select name="scan_type" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl px-3 py-2.5 font-bold focus:ring-2 focus:ring-orange-500 focus:outline-none transition">
                            <option value="">Semua (IN / OUT)</option>
                            <option value="IN" {{ $selectedType === 'IN' ? 'selected' : '' }}>Check-In (Masuk)</option>
                            <option value="OUT" {{ $selectedType === 'OUT' ? 'selected' : '' }}>Check-Out (Keluar)</option>
                        </select>
                    </div>

                    {{-- Filter Urutan / Sort --}}
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Urutan Waktu</label>
                        <select name="sort" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl px-3 py-2.5 font-bold focus:ring-2 focus:ring-orange-500 focus:outline-none transition">
                            <option value="earliest" {{ $sort === 'earliest' ? 'selected' : '' }}>Tercepat / Terawal Datang</option>
                            <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Terbaru Di-Scan</option>
                        </select>
                    </div>

                    {{-- Search Input & Actions --}}
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Cari Pengunjung</label>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Nama / Kode Tiket..."
                                   class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl px-3 py-2.5 font-medium focus:ring-2 focus:ring-orange-500 focus:outline-none transition">
                        </div>
                        <button type="submit" class="px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-black text-xs uppercase tracking-wider transition shadow-sm h-[42px] flex items-center justify-center">
                            Filter
                        </button>
                        @if($selectedEventId || $selectedGate || $selectedType || $search || $sort !== 'earliest')
                            <a href="{{ route('organizer.dashboard') }}#scan-history" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs transition h-[42px] flex items-center justify-center" title="Reset filter">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table & List --}}
            @if($scanLogs->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="text-base font-black text-slate-700 font-outfit">Belum ada riwayat scan</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-6">
                        @if($selectedEventId || $selectedGate || $selectedType || $search)
                            Tidak ada data scan yang cocok dengan filter yang dipilih. Silakan ubah filter atau reset pencarian.
                        @else
                            Aktivitas scan tiket di gate scanner akan tercatat di sini secara otomatis untuk mendeteksi siapa yang hadir paling awal.
                        @endif
                    </p>
                    @if($selectedEventId || $selectedGate || $selectedType || $search)
                        <a href="{{ route('organizer.dashboard') }}#scan-history" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition">
                            Reset Filter
                        </a>
                    @elseif($events->isNotEmpty())
                        <a href="{{ route('organizer.gate.scan', $events->first()) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold text-xs transition shadow-sm">
                            Buka Gate Scanner
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100">
                                <th class="p-4 md:px-6 md:py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Urutan / Rank</th>
                                <th class="p-4 md:px-6 md:py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Pengunjung</th>
                                <th class="p-4 md:px-6 md:py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tiket & Kategori</th>
                                <th class="p-4 md:px-6 md:py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Event & Gate</th>
                                <th class="p-4 md:px-6 md:py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Waktu Scan</th>
                                <th class="p-4 md:px-6 md:py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="p-4 md:px-6 md:py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($scanLogs as $index => $log)
                                @php
                                    $rankNumber = ($scanLogs->currentPage() - 1) * $scanLogs->perPage() + $loop->iteration;
                                    $visitorName = $log->ticket->visitor_data['name'] ?? $log->ticket->transaction->customer_name ?? '-';
                                    $visitorContact = $log->ticket->visitor_data['phone'] ?? $log->ticket->transaction->customer_phone ?? ($log->ticket->visitor_data['email'] ?? $log->ticket->transaction->customer_email ?? '-');
                                    $categoryColor = $log->ticket->category->hex_color ?? '#6366f1';
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    {{-- Rank / Urutan --}}
                                    <td class="p-4 md:px-6 md:py-4">
                                        @if($sort === 'earliest')
                                            @if($rankNumber === 1)
                                                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500 text-white rounded-lg text-xs font-black shadow-sm">
                                                    <span>#1</span>
                                                    <span class="text-[9px] uppercase tracking-wider font-bold">Tercepat</span>
                                                </div>
                                            @elseif($rankNumber === 2)
                                                <div class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-700 text-white rounded-lg text-xs font-black">
                                                    <span>#2</span>
                                                    <span class="text-[9px] uppercase tracking-wider font-bold">Ke-2</span>
                                                </div>
                                            @elseif($rankNumber === 3)
                                                <div class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-700 text-white rounded-lg text-xs font-black">
                                                    <span>#3</span>
                                                    <span class="text-[9px] uppercase tracking-wider font-bold">Ke-3</span>
                                                </div>
                                            @else
                                                <span class="text-xs font-black text-slate-600 font-mono">
                                                    #{{ $rankNumber }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs font-black text-slate-500 font-mono">
                                                #{{ $rankNumber }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Pengunjung --}}
                                    <td class="p-4 md:px-6 md:py-4">
                                        <div class="font-black text-slate-900 text-sm font-outfit leading-tight group-hover:text-orange-600 transition-colors">
                                            {{ $visitorName }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                            {{ $visitorContact }}
                                        </div>
                                    </td>

                                    {{-- Tiket & Kategori --}}
                                    <td class="p-4 md:px-6 md:py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $categoryColor }};"></span>
                                            <span class="text-xs font-bold text-slate-800">{{ $log->ticket->category->name ?? 'Reguler' }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                            {{ $log->ticket->ticket_code }}
                                            @if($log->ticket->wristband_qr)
                                                <span class="text-slate-400">&bull; {{ $log->ticket->wristband_qr }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Event & Gate --}}
                                    <td class="p-4 md:px-6 md:py-4">
                                        <div class="text-xs font-bold text-slate-800 truncate max-w-[160px]">
                                            {{ $log->event->name ?? '-' }}
                                        </div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-orange-600 mt-0.5">
                                            {{ $log->gate_name ?: 'Main Gate' }}
                                        </div>
                                    </td>

                                    {{-- Waktu Scan --}}
                                    <td class="p-4 md:px-6 md:py-4">
                                        <div class="text-xs font-black text-slate-900 font-mono">
                                            {{ $log->scanned_at->timezone('Asia/Jakarta')->format('H:i:s') }} <span class="text-[10px] text-slate-500 font-sans">WIB</span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-medium mt-0.5">
                                            {{ $log->scanned_at->timezone('Asia/Jakarta')->format('d M Y') }}
                                        </div>
                                    </td>

                                    {{-- Status IN/OUT --}}
                                    <td class="p-4 md:px-6 md:py-4">
                                        @if($log->type === 'IN')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                                Masuk (IN)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                                <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                                Keluar (OUT)
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Petugas Scanner --}}
                                    <td class="p-4 md:px-6 md:py-4">
                                        <span class="text-xs font-medium text-slate-700">
                                            {{ $log->scanner->name ?? 'Scanner Gate' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="p-4 md:px-6 border-t border-slate-100 bg-slate-50/50">
                    {{ $scanLogs->links() }}
                </div>
            @endif
        </div>

        <!-- Events List -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-50 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-black text-slate-800 font-outfit leading-none">Daftar Event Anda</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Kelola status dan penjualan tiket</p>
                </div>
                <span class="px-3 py-1 bg-orange-50 text-orange-600 text-[10px] font-black rounded-full uppercase tracking-wider">
                    {{ $events->count() }} Total
                </span>
            </div>

            @if($events->isEmpty())
                <div class="p-12 md:p-20 text-center">
                    <div class="w-16 h-16 md:w-24 md:h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-200">
                        <svg class="w-10 h-10 md:w-14 md:h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <h4 class="text-lg font-black text-slate-700 font-outfit">Belum ada event</h4>
                    <p class="text-sm text-slate-400 mb-8 max-w-xs mx-auto font-medium">Mulai buat event pertama Anda untuk menjangkau ribuan pembeli tiket.</p>
                    <a href="{{ route('organizer.events.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-orange-600 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow-lg shadow-orange-600/20 hover:bg-orange-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Buat Event Sekarang
                    </a>
                </div>
            @else
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="p-4 md:px-6 md:py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Detail Event</th>
                                <th class="p-4 md:px-6 md:py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Waktu & Lokasi</th>
                                <th class="p-4 md:px-6 md:py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="p-4 md:px-6 md:py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Statistik</th>
                                <th class="p-4 md:px-6 md:py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($events as $event)
                                <tr class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="p-4 md:px-6 md:py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl overflow-hidden shadow-sm shrink-0 border border-slate-100 bg-slate-950 relative flex items-center justify-center">
                                                @if($event->background_image)
                                                    <img src="{{ Storage::url($event->background_image) }}" alt="" aria-hidden="true" class="absolute inset-0 w-full h-full object-cover blur-xs scale-110 opacity-50 brightness-75 pointer-events-none">
                                                    <img src="{{ Storage::url($event->background_image) }}" alt="{{ $event->name }}" class="relative z-10 max-w-full max-h-full object-contain">
                                                @else
                                                    <div class="w-full h-full bg-orange-100 flex items-center justify-center text-orange-600 font-black font-outfit text-xl">
                                                        {{ substr($event->name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-black text-slate-800 text-sm md:text-base font-outfit truncate leading-tight group-hover:text-orange-600 transition-colors">{{ $event->name }}</div>
                                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1 truncate">{{ $event->slug }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 md:px-6 md:py-4">
                                        <div class="text-xs font-black text-slate-700 mb-1">{{ $event->event_start_date->format('d M Y') }}</div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider truncate max-w-[120px]">{{ $event->venue }}</div>
                                    </td>
                                    <td class="p-4 md:px-6 md:py-4">
                                        @php
                                            $statusColors = [
                                                'published' => 'bg-green-100 text-green-700',
                                                'draft' => 'bg-slate-100 text-slate-600',
                                                'cancelled' => 'bg-rose-100 text-rose-700',
                                            ];
                                            $color = $statusColors[$event->status] ?? 'bg-blue-100 text-blue-700';
                                        @endphp
                                        <span class="px-2.5 py-1 {{ $color }} text-[9px] font-black uppercase rounded-lg tracking-wider">
                                            {{ $event->status }}
                                        </span>
                                    </td>
                                    <td class="p-4 md:px-6 md:py-4">
                                        <div class="flex gap-4">
                                            <div>
                                                <div class="text-xs md:text-sm font-black text-slate-800 leading-none mb-1">{{ number_format($event->tickets_count, 0, ',', '.') }}</div>
                                                <div class="text-[9px] text-slate-400 uppercase font-black tracking-tighter">Laku</div>
                                            </div>
                                            <div class="h-6 w-px bg-slate-100"></div>
                                            <div>
                                                <div class="text-xs md:text-sm font-black text-slate-800 leading-none mb-1">{{ $event->ticket_categories_count }}</div>
                                                <div class="text-[9px] text-slate-400 uppercase font-black tracking-tighter">Kategori</div>
                                            </div>
                                            <div class="h-6 w-px bg-slate-100"></div>
                                            <div>
                                                <div class="text-xs md:text-sm font-black text-purple-600 leading-none mb-1 flex items-center gap-1" title="Jumlah kali event dibuka oleh publik">
                                                    <svg class="w-3.5 h-3.5 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span>{{ number_format($event->views_count ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="text-[9px] text-slate-400 uppercase font-black tracking-tighter">Hit Publik</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 md:px-6 md:py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('events.show', $event->slug) }}" target="_blank" class="p-2 md:p-3 bg-purple-50 text-purple-600 hover:text-purple-700 hover:bg-purple-100 rounded-xl transition-all shadow-sm border border-purple-100" title="Buka Halaman Publik Event">
                                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                            <a href="{{ route('organizer.events.edit', $event) }}" class="p-2 md:p-3 bg-orange-50 text-orange-500 hover:text-orange-700 hover:bg-orange-100 rounded-xl transition-all shadow-sm border border-orange-100" title="Edit Event">
                                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </a>
                                            <a href="{{ route('organizer.reports.index', ['event_id' => $event->id]) }}" class="p-2 md:p-3 bg-orange-50 text-orange-500 hover:text-orange-700 hover:bg-orange-100 rounded-xl transition-all shadow-sm border border-orange-100" title="Laporan">
                                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
