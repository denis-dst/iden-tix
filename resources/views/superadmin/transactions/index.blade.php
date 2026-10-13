<x-app-layout>
    <x-slot name="title">Laporan Penjualan Global</x-slot>

    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-black text-orange-500 uppercase tracking-[0.25em] mb-2">Global Sales Monitoring</p>
                <h2 class="text-3xl font-black text-slate-800 font-outfit">Semua Transaksi</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Pantau dan kelola seluruh transaksi dari semua Organizer di platform.</p>
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

        {{-- Global Sales Monitoring Dashboard Widget (Kuota, Terjual, Belum Terjual, Total Pendapatan, Sales Throughput, Kategori Tiket) --}}
        <x-transactions.sales-dashboard-chart 
            :total-quota="$totalQuota"
            :total-sold="$totalSold"
            :total-unsold="$totalUnsold"
            :total-revenue="$totalRevenue"
            :category-stats="$categoryStats"
            :throughput-chart="$throughputChart"
        />

        <form method="GET" action="{{ route('superadmin.transactions.index') }}" class="flex flex-col xl:flex-row gap-3 bg-white p-4 rounded-3xl border border-slate-100 shadow-sm">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Email, Nama, atau No. Invoice..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:border-orange-500 focus:ring-orange-500" />
            </div>
            
            <select name="tenant_id" class="rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:border-orange-500 focus:ring-orange-500 xl:w-48">
                <option value="">Semua Organizer</option>
                @foreach($tenantOptions as $tenant)
                    <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                @endforeach
            </select>

            <select name="event_id" class="rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:border-orange-500 focus:ring-orange-500 xl:w-48">
                <option value="">Semua Event</option>
                @foreach($eventOptions as $event)
                    <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-8 py-2.5 bg-orange-600 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-orange-700 transition shadow-lg shadow-orange-200">Filter</button>
            
            @if(request()->anyFilled(['q', 'tenant_id', 'event_id']))
                <a href="{{ route('superadmin.transactions.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-slate-200 transition text-center">Reset</a>
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
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Organizer</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Total</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($transactions as $tx)
                            @php
                                $activeTicketsCount = $tx->tickets->where('status', '!=', 'void')->count();
                                $voidTicketsCount = $tx->tickets->where('status', 'void')->count();
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
                                    <div class="text-xs font-black text-slate-600 font-mono">{{ $tx->reference_no }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 mt-1 uppercase">{{ $tx->created_at->format('d M Y H:i') }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-sm font-black text-slate-800">{{ $tx->customer_name }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tight mt-0.5">{{ $tx->customer_email }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-xs font-black text-slate-600">{{ $tx->tenant->name ?? '-' }}</div>
                                    <div class="text-[9px] font-bold text-orange-500 uppercase mt-0.5">{{ $tx->event->name ?? '-' }}</div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="text-sm font-black text-green-600">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase">{{ $activeTicketsCount }} / {{ $tx->tickets->count() }} TIKET AKTIF</div>
                                    @if($voidTicketsCount > 0)
                                        <div class="text-[9px] font-black text-rose-500 uppercase mt-0.5">{{ $voidTicketsCount }} dibatalkan</div>
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
                                            <form method="POST" action="{{ route('superadmin.transactions.mark-as-paid', $tx) }}" onsubmit="return confirm('KONFIRMASI SUPERADMIN: Anda akan menandai transaksi ini sebagai LUNAS secara manual. Tindakan ini tidak dapat dibatalkan. Lanjutkan?')">
                                                @csrf
                                                <button type="submit" title="Konfirmasi Lunas (SuperAdmin)" class="p-2.5 bg-orange-500 text-black rounded-xl hover:bg-orange-600 transition shadow-sm border border-orange-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </button>
                                            </form>
                                        @elseif($tx->payment_status === 'paid')
                                            <form method="POST" action="{{ route('superadmin.transactions.resend-evoucher', $tx) }}" class="inline">
                                                @csrf
                                                <button type="submit" title="Kirim Ulang Email" class="p-2.5 bg-orange-500 text-black rounded-xl hover:bg-orange-600 transition shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                                </button>
                                            </form>
                                            <a href="{{ route('superadmin.transactions.print-evoucher', $tx) }}" target="_blank" title="Cetak E-Voucher" class="p-2.5 bg-orange-50 text-orange-600 rounded-xl hover:bg-orange-600 hover:text-white transition shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                            </a>
                                        @endif
                                        
                                        @if(!in_array($tx->payment_status, ['failed', 'expired', 'refunded']))
                                            <x-transactions.partials.cancel-tickets-popover
                                                :action="route('superadmin.transactions.cancel-tickets', $tx)"
                                                :cancelable-tickets="$cancelableTickets"
                                                heading="Batalkan Tiket Peserta (SuperAdmin)"
                                                :reference-no="$tx->reference_no"
                                                summary-class="p-2.5 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition border border-rose-100 shadow-sm"
                                                confirm-message="KONFIRMASI SUPERADMIN: Apakah Anda yakin ingin membatalkan tiket yang dipilih? Kuota akan otomatis dikembalikan ke stok."
                                            />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <!-- Collapsible Ticket Detail Row -->
                            <tr id="tickets-{{ $tx->id }}" class="hidden bg-slate-50/50">
                                <td></td>
                                <td colspan="6" class="px-8 py-5 border-b border-slate-100">
                                    <div class="p-5 bg-white rounded-3xl border border-slate-100 shadow-sm space-y-4">
                                        <div class="flex items-center justify-between border-b border-slate-50 pb-3">
                                            <div>
                                                <h4 class="text-xs font-black text-slate-700 uppercase tracking-widest">Detail Rincian Tiket Peserta (SuperAdmin)</h4>
                                                <p class="text-[10px] font-bold text-slate-400 mt-0.5">Daftar e-voucher untuk pemesanan ini</p>
                                            </div>
                                            @if($tx->payment_status === 'paid' && $tx->tickets->where('status', '!=', 'void')->count() > 0)
                                                <x-transactions.partials.cancel-tickets-popover
                                                    :action="route('superadmin.transactions.cancel-tickets', $tx)"
                                                    :cancelable-tickets="$cancelableTickets"
                                                    heading="Batalkan Tiket Peserta (SuperAdmin)"
                                                    :reference-no="$tx->reference_no"
                                                    summary-class="px-4 py-2 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-rose-600 hover:text-white transition"
                                                    summary-text="Batal Transaksi (Pilih Tiket)"
                                                    confirm-message="KONFIRMASI SUPERADMIN: Apakah Anda yakin ingin membatalkan tiket yang dipilih? Kuota akan otomatis dikembalikan ke stok."
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
                                                        <th class="py-2.5">Status</th>
                                                        <th class="py-2.5 text-right">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-50">
                                                    @foreach($tx->tickets as $ticket)
                                                        <tr class="hover:bg-slate-50/50 transition">
                                                            <td class="py-3 font-mono font-black text-slate-600">{{ $ticket->ticket_code }}</td>
                                                            <td class="py-3">
                                                                <div class="font-black text-slate-800 uppercase">
                                                                    {{ $ticket->visitor_data['name'] ?? $tx->customer_name }}
                                                                </div>
                                                                <div class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">
                                                                    NIK: {{ $ticket->visitor_data['nik'] ?? $tx->customer_nik ?? '-' }} 
                                                                    | Gender: {{ $ticket->visitor_data['gender'] ?? $tx->customer_gender ?? '-' }}
                                                                </div>
                                                            </td>
                                                            <td class="py-3 text-slate-500 font-bold">{{ $ticket->category->name ?? '-' }}</td>
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
                                                                    <form method="POST" action="{{ route('superadmin.tickets.cancel', $ticket) }}" class="inline" onsubmit="return confirm('KONFIRMASI SUPERADMIN: Anda yakin ingin membatalkan TIKET SATUAN ini? Kuota akan otomatis dikembalikan ke stok. Lanjutkan?')">
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
                                <td colspan="7" class="px-8 py-20 text-center">
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
                        <h3 class="text-lg font-black text-slate-800 font-outfit">Batalkan Tiket Peserta (SuperAdmin)</h3>
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
            return confirm('KONFIRMASI SUPERADMIN: Apakah Anda yakin ingin membatalkan ' + checkedCount + ' tiket yang dipilih? Kuota akan otomatis dikembalikan ke stok.');
        }
    </script>
</x-app-layout>
