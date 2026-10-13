<x-app-layout>
    <x-slot name="title">Laporan Penjualan Tiket</x-slot>

    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-black text-orange-500 uppercase tracking-[0.25em] mb-2">Finance & Sales</p>
                <h2 class="text-3xl font-black text-slate-800 font-outfit">Laporan Penjualan</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Cari dan kelola transaksi tiket, serta cetak ulang e-voucher pembeli.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-bold space-y-3">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
                @if(session('active_evoucher_url'))
                    <div class="rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-orange-700 space-y-2">
                        <p class="text-xs font-black uppercase tracking-wider">Kirimkan EVoucher terbaru ini kepada Pelanggan</p>
                        <div class="flex flex-col md:flex-row gap-2 md:items-center">
                            <input type="text" readonly value="{{ session('active_evoucher_url') }}" class="flex-1 rounded-lg border-orange-200 bg-white text-xs font-bold text-slate-600">
                            <button type="button" onclick="copyToClipboard(@js(session('active_evoucher_url')), this)" class="px-4 py-2 bg-orange-600 text-white rounded-lg text-xs font-black uppercase tracking-wider hover:bg-orange-700 transition">Salin URL</button>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Sales Monitoring Dashboard Widget (Kuota, Terjual, Belum Terjual, Total Pendapatan, Sales Throughput, Kategori Tiket) --}}
        <x-transactions.sales-dashboard-chart 
            :total-quota="$totalQuota"
            :total-sold="$totalSold"
            :total-unsold="$totalUnsold"
            :total-revenue="$totalRevenue"
            :category-stats="$categoryStats"
            :throughput-chart="$throughputChart"
        />

        <form method="GET" action="{{ route('organizer.transactions.index') }}" class="flex flex-col xl:flex-row gap-3 bg-white p-4 rounded-3xl border border-slate-100 shadow-sm">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Email, Nama, atau No. Invoice..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:border-orange-500 focus:ring-orange-500" />
            </div>
            
            <select name="event_id" class="rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:border-orange-500 focus:ring-orange-500 xl:w-64">
                <option value="">Semua Event</option>
                @foreach($eventOptions as $event)
                    <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                @endforeach
            </select>

            <select name="ticket_category_id" class="rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:border-orange-500 focus:ring-orange-500 xl:w-64">
                <option value="">Semua Kategori Tiket</option>
                @foreach($ticketCategories as $cat)
                    <option value="{{ $cat->id }}" {{ request('ticket_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <select name="channel" class="rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:border-orange-500 focus:ring-orange-500 xl:w-56">
                <option value="">Semua Saluran</option>
                <option value="online" {{ request('channel') === 'online' ? 'selected' : '' }}>Penjualan Online</option>
                <option value="pos" {{ request('channel') === 'pos' ? 'selected' : '' }}>Penjualan Langsung (POS)</option>
            </select>

            <button type="submit" class="px-8 py-2.5 bg-orange-600 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-orange-700 transition shadow-lg shadow-orange-200">Cari Transaksi</button>
            
            @if(request()->anyFilled(['q', 'event_id', 'ticket_category_id', 'channel']))
                <a href="{{ route('organizer.transactions.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-slate-200 transition text-center">Reset</a>
            @endif
        </form>

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100">
                            <th class="w-10 px-4 py-5"></th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Waktu & Invoice</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Tiket</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Promo</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Total Bayar</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Bukti</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($transactions as $tx)
                            @php
                                $activeTicketsCount = $tx->tickets->where('status', '!=', 'void')->count();
                                $voidTicketsCount = $tx->tickets->where('status', 'void')->count();
                                $proofTicket = $tx->tickets->first(function ($ticket) {
                                    $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : [];
                                    return !empty($visitorData['proof_ig']) || !empty($visitorData['proof_review']) || !empty($visitorData['proofs']);
                                });
                                $proofData = $proofTicket && is_array($proofTicket->visitor_data) ? $proofTicket->visitor_data : [];
                                $cancelableTickets = $tx->tickets
                                    ->where('status', '!=', 'void')
                                    ->map(function ($ticket) use ($tx) {
                                        $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : [];

                                        return [
                                            'id' => $ticket->id,
                                            'code' => $ticket->ticket_code,
                                            'name' => $visitorData['name'] ?? $tx->customer_name,
                                            'category' => $ticket->category->name ?? '-',
                                        ];
                                    })
                                    ->values();
                                $cancelableTicketsPayload = base64_encode($cancelableTickets->toJson());
                            @endphp
                            <tr class="hover:bg-slate-50/40 transition group">
                                <td class="px-4 py-5 text-center">
                                    <button type="button" onclick="toggleRow('tickets-{{ $tx->id }}')" class="p-1 hover:bg-slate-100 rounded-lg transition text-slate-400 hover:text-slate-600 focus:outline-none">
                                        <svg id="icon-{{ $tx->id }}" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-black text-slate-600 font-mono">{{ $tx->reference_no }}</span>
                                        @if($tx->channel === 'pos' || str_starts_with($tx->reference_no, 'POS-'))
                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-wider border border-amber-200/50">POS</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[9px] font-black uppercase tracking-wider border border-blue-200/50">Online</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-400 mt-1 uppercase">{{ $tx->created_at->format('d M Y H:i') }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-sm font-black text-slate-800">{{ $tx->customer_name }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tight mt-0.5">{{ $tx->customer_email }}</div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="text-sm font-black text-slate-700">{{ $activeTicketsCount }} / {{ $tx->tickets->count() }}</div>
                                    <div class="text-[9px] font-bold text-orange-500 uppercase">{{ $tx->tickets->first()->category->name ?? 'Mixed' }}</div>
                                    @if($voidTicketsCount > 0)
                                        <div class="text-[9px] font-black text-rose-500 uppercase mt-0.5">{{ $voidTicketsCount }} dibatalkan</div>
                                    @endif
                                </td>
                                <td class="px-8 py-5 text-right">
                                    @if($tx->promoCode)
                                        <div class="text-xs font-black text-orange-600 font-mono">{{ $tx->promoCode->code }}</div>
                                        <div class="text-[9px] font-bold text-slate-400 uppercase">-Rp {{ number_format($tx->discount_amount, 0, ',', '.') }}</div>
                                    @else
                                        <div class="text-xs font-bold text-slate-300">-</div>
                                    @endif
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="text-sm font-black text-green-600">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase">{{ $tx->payment_method ?? 'Unknown' }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    @php
                                        $visitorProofs = $proofData['proofs'] ?? [];
                                        if (empty($visitorProofs)) {
                                            if (!empty($proofData['proof_ig'])) $visitorProofs['proof_ig'] = $proofData['proof_ig'];
                                            if (!empty($proofData['proof_review'])) $visitorProofs['proof_review'] = $proofData['proof_review'];
                                        }
                                    @endphp
                                    @if(!empty($visitorProofs))
                                        <div class="flex flex-col gap-1.5">
                                            @foreach($visitorProofs as $proofId => $path)
                                                @php
                                                    $proofLabel = 'Proof';
                                                    if ($proofId === 'proof_ig') $proofLabel = 'IG';
                                                    elseif ($proofId === 'proof_review') $proofLabel = 'Review';
                                                    else {
                                                        $eventProofs = $tx->event->getRegistrationProofs();
                                                        $matchedProof = collect($eventProofs)->firstWhere('id', $proofId);
                                                        $proofLabel = $matchedProof['label'] ?? 'Proof';
                                                        if (strlen($proofLabel) > 10) {
                                                            $proofLabel = substr($proofLabel, 0, 8) . '..';
                                                        }
                                                    }
                                                @endphp
                                                <a href="{{ asset('storage/' . $path) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-wider hover:bg-blue-600 hover:text-white transition">{{ $proofLabel }}</a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs font-bold text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="px-8 py-5">
                                    @if($tx->payment_status === 'paid')
                                        <span class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-wider">PAID</span>
                                    @elseif($tx->payment_status === 'refunded')
                                        <span class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-[9px] font-black uppercase tracking-wider">REFUNDED</span>
                                    @else
                                        <span class="px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-wider">{{ strtoupper($tx->payment_status) }}</span>
                                    @endif
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2">
                                        @if($tx->payment_status !== 'paid' && $tx->payment_status !== 'refunded')
                                            <form method="POST" action="{{ route('organizer.transactions.sync-wago', $tx) }}" class="inline">
                                                @csrf
                                                <button type="submit" title="Cek Status Pembayaran ke Gateway WAGO" class="p-2.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition shadow-sm border border-blue-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('organizer.transactions.mark-as-paid', $tx) }}" onsubmit="return confirm('PENTING: Pastikan uang pembayaran sudah diterima secara manual sebelum melakukan verifikasi ini. Lanjutkan?')">
                                                @csrf
                                                <button type="submit" title="Konfirmasi Pembayaran Manual" class="p-2.5 bg-orange-500 text-black rounded-xl hover:bg-orange-600 transition shadow-sm border border-orange-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </button>
                                            </form>
                                        @elseif($tx->payment_status === 'paid')
                                            <form method="POST" action="{{ route('organizer.transactions.resend-evoucher', $tx) }}" class="inline">
                                                @csrf
                                                <button type="submit" title="Kirim Ulang Email" class="p-2.5 bg-orange-500 text-black rounded-xl hover:bg-orange-600 transition shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                                </button>
                                            </form>
                                             @if(($global_settings['global_wa_notifications_enabled'] ?? '1') !== '0')
                                                 <form method="POST" action="{{ route('organizer.transactions.send-whatsapp', $tx) }}" class="inline">
                                                     @csrf
                                                     <button type="submit" title="Kirim E-Voucher via WhatsApp (Fonnte)" class="p-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition shadow-sm">
                                                         <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                             <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.717-1.458L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436.002 9.859-4.42 9.863-9.864.002-2.637-1.023-5.115-2.887-6.979C16.584 1.898 14.1 8.75 11.466 8.75 6.03 8.748 1.608 13.17 1.605 18.613c-.001 1.666.452 3.284 1.312 4.708L1.875 22.25l4.772-1.258a10.06 10.06 0 004.825 1.451zm9.957-6.808c-.27-.135-1.597-.788-1.845-.878-.247-.09-.427-.135-.607.135-.18.27-.697.878-.855 1.058-.158.18-.315.202-.585.067-.27-.135-1.14-.42-2.172-1.34-.803-.715-1.345-1.6-1.503-1.871-.158-.27-.017-.416.118-.551.121-.122.27-.315.405-.472.135-.158.18-.27.27-.45.09-.18.045-.337-.023-.472-.068-.135-.607-1.463-.832-2.003-.22-.529-.44-.457-.607-.466-.157-.008-.337-.01-.517-.01-.18 0-.472.067-.72.337-.248.27-.945.923-.945 2.25 0 1.328.968 2.61 1.103 2.79.135.18 1.905 2.91 4.613 4.078.644.278 1.147.444 1.54.569.647.206 1.236.177 1.701.108.519-.078 1.598-.653 1.823-1.283.225-.63.225-1.17.157-1.283-.067-.113-.247-.18-.517-.315z"/>
                                                         </svg>
                                                     </button>
                                                 </form>
                                             @endif
                                            @if(($tx->event->purchase_flow ?? '') === 'print')
                                                <a href="{{ route('organizer.pos.print', $tx) }}" target="_blank" title="Cetak Tiket Termal" class="p-2.5 bg-orange-50 text-orange-600 rounded-xl hover:bg-orange-600 hover:text-white transition shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                                </a>
                                            @else
                                                <a href="{{ route('organizer.transactions.print-evoucher', $tx) }}" target="_blank" title="Cetak E-Voucher" class="p-2.5 bg-orange-50 text-orange-600 rounded-xl hover:bg-orange-600 hover:text-white transition shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                                </a>
                                            @endif
                                        @endif
                                        
                                        @if(!in_array($tx->payment_status, ['failed', 'expired', 'refunded']))
                                            <x-transactions.partials.cancel-tickets-popover
                                                :action="route('organizer.transactions.cancel-tickets', $tx)"
                                                :cancelable-tickets="$cancelableTickets"
                                                :reference-no="$tx->reference_no"
                                                summary-class="p-2.5 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition border border-rose-100 shadow-sm"
                                                confirm-message="Apakah Anda yakin ingin membatalkan tiket yang dipilih? Kuota akan otomatis dikembalikan ke stok."
                                            />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <!-- Collapsible Ticket Detail Row -->
                            <tr id="tickets-{{ $tx->id }}" class="hidden bg-slate-50/50">
                                <td></td>
                                <td colspan="8" class="px-8 py-5 border-b border-slate-100">
                                    <div class="p-5 bg-white rounded-3xl border border-slate-100 shadow-sm space-y-4">
                                        <div class="flex items-center justify-between border-b border-slate-50 pb-3">
                                            <div>
                                                <h4 class="text-xs font-black text-slate-700 uppercase tracking-widest">Detail Rincian Tiket Peserta</h4>
                                                <p class="text-[10px] font-bold text-slate-400 mt-0.5">Daftar e-voucher untuk pemesanan ini</p>
                                            </div>
                                            @if($tx->payment_status === 'paid' && $tx->tickets->where('status', '!=', 'void')->count() > 0)
                                                <x-transactions.partials.cancel-tickets-popover
                                                    :action="route('organizer.transactions.cancel-tickets', $tx)"
                                                    :cancelable-tickets="$cancelableTickets"
                                                    :reference-no="$tx->reference_no"
                                                    summary-class="px-4 py-2 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-rose-600 hover:text-white transition"
                                                    summary-text="Batal Transaksi (Pilih Tiket)"
                                                    confirm-message="Apakah Anda yakin ingin membatalkan tiket yang dipilih? Kuota akan otomatis dikembalikan ke stok."
                                                />
                                            @endif
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left border-collapse text-xs">
                                                <thead>
                                                    <tr class="text-[9px] font-black text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                                        <th class="py-2.5">Kode Tiket</th>
                                                        <th class="py-2.5">Pemegang/Pengunjung</th>
                                                        <th class="py-2.5">Kategori</th>
                                                        <th class="py-2.5">Bukti</th>
                                                        <th class="py-2.5">Status</th>
                                                        <th class="py-2.5 text-right">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-50">
                                                    @foreach($tx->tickets as $ticket)
                                                        @php
                                                            $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : [];
                                                        @endphp
                                                        <tr class="hover:bg-slate-50/50 transition">
                                                            <td class="py-3 font-mono font-black text-slate-600">{{ $ticket->ticket_code }}</td>
                                                            <td class="py-3">
                                                                <div class="font-black text-slate-800 uppercase">
                                                                    {{ $visitorData['name'] ?? $tx->customer_name }}
                                                                </div>
                                                                <div class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">
                                                                    NIK: {{ $visitorData['nik'] ?? $tx->customer_nik ?? '-' }}
                                                                    | Gender: {{ $visitorData['gender'] ?? $tx->customer_gender ?? '-' }}
                                                                </div>
                                                            </td>
                                                            <td class="py-3 text-slate-500 font-bold">{{ $ticket->category->name ?? '-' }}</td>
                                                            <td class="py-3">
                                                                @php
                                                                    $tProofs = $visitorData['proofs'] ?? [];
                                                                    if (empty($tProofs)) {
                                                                        if (!empty($visitorData['proof_ig'])) $tProofs['proof_ig'] = $visitorData['proof_ig'];
                                                                        if (!empty($visitorData['proof_review'])) $tProofs['proof_review'] = $visitorData['proof_review'];
                                                                    }
                                                                @endphp
                                                                @if(!empty($tProofs))
                                                                    <div class="flex flex-wrap gap-1.5">
                                                                        @foreach($tProofs as $proofId => $path)
                                                                            @php
                                                                                $proofLabel = 'Proof';
                                                                                if ($proofId === 'proof_ig') $proofLabel = 'IG';
                                                                                elseif ($proofId === 'proof_review') $proofLabel = 'Review';
                                                                                else {
                                                                                    $eventProofs = $tx->event->getRegistrationProofs();
                                                                                    $matchedProof = collect($eventProofs)->firstWhere('id', $proofId);
                                                                                    $proofLabel = $matchedProof['label'] ?? 'Proof';
                                                                                    if (strlen($proofLabel) > 10) {
                                                                                        $proofLabel = substr($proofLabel, 0, 8) . '..';
                                                                                    }
                                                                                }
                                                                            @endphp
                                                                            <a href="{{ asset('storage/' . $path) }}" target="_blank" rel="noopener noreferrer" class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-wider hover:bg-blue-600 hover:text-white transition">{{ $proofLabel }}</a>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <span class="text-[9px] text-slate-300 font-black uppercase">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="py-3">
                                                                @if($ticket->status === 'sold')
                                                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 font-bold text-[9px] uppercase tracking-wide">SOLD</span>
                                                                @elseif($ticket->status === 'redeemed')
                                                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 font-bold text-[9px] uppercase tracking-wide">REDEEMED</span>
                                                                @elseif($ticket->status === 'void')
                                                                    <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-600 font-bold text-[9px] uppercase tracking-wide">VOID (CANCELED)</span>
                                                                @else
                                                                    <span class="px-2 py-0.5 rounded bg-slate-50 text-slate-500 font-bold text-[9px] uppercase tracking-wide">{{ strtoupper($ticket->status) }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="py-3 text-right">
                                                                @if($ticket->status !== 'void')
                                                                    <form method="POST" action="{{ route('organizer.tickets.cancel', $ticket) }}" class="inline" onsubmit="return confirm('PENTING: Anda yakin ingin membatalkan TIKET SATUAN ini? Kuota akan otomatis dikembalikan ke stok. Lanjutkan?')">
                                                                        @csrf
                                                                        <button type="submit" class="px-3 py-1.5 bg-rose-50 border border-rose-200 text-rose-500 hover:bg-rose-500 hover:text-white rounded-lg text-[9px] font-black uppercase tracking-wider transition">
                                                                            Cancel Tiket
                                                                        </button>
                                                                    </form>
                                                                @else
                                                                    <span class="text-[9px] text-slate-300 font-black uppercase">BATAL (CANCELED)</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-4">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                        </div>
                                        <p class="text-sm font-bold text-slate-400">Tidak ada transaksi yang ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Cancel Tickets Modal -->
    <div id="cancel-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCancelModal()"></div>
        <!-- Modal Content -->
        <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-xl w-full mx-4 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <form id="cancel-modal-form" method="POST" action="" onsubmit="return validateCancelForm()">
                @csrf
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-lg font-black text-slate-800 font-outfit">Batalkan Tiket Peserta</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5" id="cancel-modal-subtitle">Invoice: -</p>
                    </div>
                    <button type="button" onclick="closeCancelModal()" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="p-6 space-y-4 max-h-[350px] overflow-y-auto custom-scrollbar">
                    <p class="text-xs font-semibold text-slate-500">Pilih tiket peserta yang ingin dibatalkan. Kuota tiket akan dikembalikan secara otomatis.</p>
                    
                    <!-- Select All Option -->
                    <div class="flex items-center gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                        <input type="checkbox" id="cancel-select-all" onchange="toggleSelectAllTickets(this)" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500 h-4 w-4">
                        <label for="cancel-select-all" class="text-xs font-black text-slate-700 uppercase cursor-pointer select-none">Pilih Semua Tiket</label>
                    </div>

                    <!-- Tickets List Container -->
                    <div id="cancel-tickets-list" class="divide-y divide-slate-100 border border-slate-100 rounded-2xl overflow-hidden bg-white">
                        <!-- Dynamic check lists will be inserted here -->
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeCancelModal()" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-black uppercase tracking-wider transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow-lg shadow-rose-200">Konfirmasi Pembatalan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function copyToClipboard(text, button) {
            const done = function () {
                const originalText = button.textContent;
                button.textContent = 'Tersalin';
                setTimeout(function () {
                    button.textContent = originalText;
                }, 1500);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done);
                return;
            }

            const input = document.createElement('textarea');
            input.value = text;
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            done();
        }

        (function () {
            function bootCancelModal() {
                document.querySelectorAll('[data-cancel-trigger]').forEach(function (button) {
                    if (button.dataset.cancelBound === '1') {
                        return;
                    }

                    button.dataset.cancelBound = '1';
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        triggerCancelModal(button);
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootCancelModal);
            } else {
                bootCancelModal();
            }
        })();

        function triggerCancelModal(button) {
            const txId = button.getAttribute('data-tx-id');
            const referenceNo = button.getAttribute('data-reference-no');
            const actionUrl = button.getAttribute('data-action-url');
            let tickets = [];

            try {
                tickets = JSON.parse(atob(button.getAttribute('data-tickets-payload') || 'W10='));
            } catch (error) {
                console.error('Gagal membaca data tiket untuk pembatalan.', error);
            }

            openCancelModal(txId, referenceNo, tickets, actionUrl);
        }

        function toggleRow(id) {
            const row = document.getElementById(id);
            const btnIcon = document.getElementById('icon-' + id.split('-')[1]);
            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
                btnIcon.classList.add('rotate-90');
            } else {
                row.classList.add('hidden');
                btnIcon.classList.remove('rotate-90');
            }
        }

        function openCancelModal(txId, referenceNo, tickets, actionUrl) {
            document.getElementById('cancel-modal-subtitle').textContent = 'Invoice: ' + referenceNo;
            document.getElementById('cancel-modal-form').action = actionUrl;

            const listContainer = document.getElementById('cancel-tickets-list');
            listContainer.innerHTML = '';

            // Reset Select All checkbox
            document.getElementById('cancel-select-all').checked = false;

            if (tickets.length === 0) {
                listContainer.innerHTML = '<div class="p-4 text-center text-xs font-bold text-slate-400">Tidak ada tiket aktif yang dapat dibatalkan.</div>';
                document.getElementById('cancel-modal').classList.remove('hidden');
                return;
            }

            tickets.forEach(ticket => {
                const item = document.createElement('div');
                item.className = 'flex items-start gap-3 p-3.5 hover:bg-slate-50/50 transition';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'ticket_ids[]';
                checkbox.value = ticket.id;
                checkbox.id = 'ticket-cb-' + ticket.id;
                checkbox.className = 'ticket-checkbox mt-0.5 rounded border-slate-300 text-orange-600 focus:ring-orange-500 h-4 w-4';
                checkbox.addEventListener('change', updateSelectAllState);

                const label = document.createElement('label');
                label.htmlFor = checkbox.id;
                label.className = 'flex-1 cursor-pointer select-none';

                const header = document.createElement('div');
                header.className = 'flex items-center justify-between';

                const code = document.createElement('span');
                code.className = 'text-xs font-mono font-black text-slate-700';
                code.textContent = ticket.code || '-';

                const category = document.createElement('span');
                category.className = 'px-2 py-0.5 rounded bg-orange-50 text-orange-600 font-black text-[9px] uppercase tracking-wider';
                category.textContent = ticket.category || '-';

                const name = document.createElement('div');
                name.className = 'text-xs font-black text-slate-800 uppercase mt-1';
                name.textContent = ticket.name || '-';

                header.appendChild(code);
                header.appendChild(category);
                label.appendChild(header);
                label.appendChild(name);
                item.appendChild(checkbox);
                item.appendChild(label);
                listContainer.appendChild(item);
            });

            document.getElementById('cancel-modal').classList.remove('hidden');
        }

        function closeCancelModal() {
            document.getElementById('cancel-modal').classList.add('hidden');
        }

        function toggleSelectAllTickets(master) {
            const checkboxes = document.querySelectorAll('.ticket-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = master.checked;
            });
        }

        function updateSelectAllState() {
            const checkboxes = document.querySelectorAll('.ticket-checkbox');
            const checkedCount = document.querySelectorAll('.ticket-checkbox:checked').length;
            document.getElementById('cancel-select-all').checked = checkboxes.length === checkedCount && checkboxes.length > 0;
        }

        function validateCancelForm() {
            const checkedCount = document.querySelectorAll('.ticket-checkbox:checked').length;
            if (checkedCount === 0) {
                alert('Silakan pilih minimal satu tiket untuk dibatalkan.');
                return false;
            }
            return confirm('Apakah Anda yakin ingin membatalkan ' + checkedCount + ' tiket yang dipilih? Kuota akan otomatis dikembalikan ke stok.');
        }
    </script>
</x-app-layout>
