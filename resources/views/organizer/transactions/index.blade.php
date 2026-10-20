<x-app-layout>
    <x-slot name="title">Laporan Penjualan Tiket</x-slot>

    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-black text-orange-500 uppercase tracking-[0.25em] mb-2">Finance & Sales</p>
                <h2 class="text-3xl font-black text-slate-800 font-outfit">Laporan Penjualan</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Cari dan kelola transaksi tiket, serta cetak ulang e-voucher pembeli.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <div id="sse-connection-badge" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm transition-all" title="Status koneksi real-time Server-Sent Events">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span id="sse-badge-text">Live Update</span>
                </div>
                <button type="button" id="toggle-sound-btn" onclick="toggleSseSound()" title="Suara Notifikasi: Aktif (Klik untuk ubah)" class="p-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition shadow-sm">
                    <svg id="sound-icon-on" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /></svg>
                    <svg id="sound-icon-off" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" /></svg>
                </button>
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

            <select name="status" class="rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:border-orange-500 focus:ring-orange-500 xl:w-48">
                <option value="">Semua Status</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>PAID</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>PENDING</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>EXPIRED</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>FAILED</option>
                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>REFUNDED</option>
            </select>

            <button type="submit" class="px-8 py-2.5 bg-orange-600 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-orange-700 transition shadow-lg shadow-orange-200">Cari Transaksi</button>
            
            @if(request()->anyFilled(['q', 'event_id', 'ticket_category_id', 'channel', 'status']))
                <a href="{{ route('organizer.transactions.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-slate-200 transition text-center">Reset</a>
            @endif
        </form>

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="transactions-table">
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
                    <tbody class="divide-y divide-slate-50" id="transactions-tbody">
                        @forelse($transactions as $tx)
                            @include('organizer.transactions.partials.transaction-row', ['tx' => $tx])
                        @empty
                            <tr id="empty-transactions-row">
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

        /* ========================================================
           SSE (Server-Sent Events) Real-Time Transaction Engine
           ======================================================== */
        let sseSoundEnabled = localStorage.getItem('sse_sound_enabled') !== '0';

        function updateSoundButtonUI() {
            const iconOn = document.getElementById('sound-icon-on');
            const iconOff = document.getElementById('sound-icon-off');
            const btn = document.getElementById('toggle-sound-btn');
            if (!iconOn || !iconOff) return;

            if (sseSoundEnabled) {
                iconOn.classList.remove('hidden');
                iconOff.classList.add('hidden');
                if (btn) btn.title = "Suara Notifikasi: Aktif (Klik untuk matikan)";
            } else {
                iconOn.classList.add('hidden');
                iconOff.classList.remove('hidden');
                if (btn) btn.title = "Suara Notifikasi: Mati (Klik untuk nyalakan)";
            }
        }

        function toggleSseSound() {
            sseSoundEnabled = !sseSoundEnabled;
            localStorage.setItem('sse_sound_enabled', sseSoundEnabled ? '1' : '0');
            updateSoundButtonUI();
            if (sseSoundEnabled) {
                playNotificationChime();
            }
        }

        function playNotificationChime() {
            if (!sseSoundEnabled) return;
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
                
                const now = ctx.currentTime;
                // Harmonic two-tone notification sound (D5 -> A5)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now);
                gain1.gain.setValueAtTime(0.12, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.35);

                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, now + 0.12);
                gain2.gain.setValueAtTime(0.12, now + 0.12);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.12);
                osc2.stop(now + 0.55);
            } catch (e) {
                // Audio not allowed or unavailable
            }
        }

        function showSseToast(title, message, options = {}) {
            const container = document.getElementById('sse-toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto bg-slate-900/95 text-white p-4 rounded-2xl shadow-2xl border border-orange-500/40 backdrop-blur-md max-w-sm w-full flex items-start gap-3 animate-toast-in transition-all';
            
            const isUpdate = options.type === 'update';
            const iconColor = isUpdate ? 'text-emerald-400' : 'text-orange-400';
            const badgeText = isUpdate ? 'STATUS UPDATE' : 'TRANSAKSI BARU';
            const badgeBg = isUpdate ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-orange-500/20 text-orange-300 border-orange-500/30';

            toast.innerHTML = `
                <div class="p-2 rounded-xl bg-slate-800 shrink-0 ${iconColor}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded border ${badgeBg}">${badgeText}</span>
                        <span class="text-[10px] text-slate-400 font-mono">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'})}</span>
                    </div>
                    <div class="text-xs font-black text-slate-100 truncate">${title}</div>
                    <div class="text-[11px] font-bold text-slate-300 mt-0.5 leading-snug">${message}</div>
                    ${options.actionText ? `
                        <div class="mt-2.5">
                            <button type="button" class="text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-500 text-white transition shadow-sm sse-toast-action-btn">
                                ${options.actionText}
                            </button>
                        </div>
                    ` : ''}
                </div>
                <button type="button" class="text-slate-400 hover:text-white shrink-0 p-1" onclick="this.closest('.pointer-events-auto').remove()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;

            if (options.actionCallback) {
                const btn = toast.querySelector('.sse-toast-action-btn');
                if (btn) {
                    btn.addEventListener('click', () => {
                        options.actionCallback();
                        toast.remove();
                    });
                }
            }

            container.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.classList.remove('animate-toast-in');
                    toast.classList.add('animate-toast-out');
                    setTimeout(() => toast.remove(), 350);
                }
            }, options.duration || 6000);
        }

        let eventSource = null;
        let currentLastId = {{ $latestTransactionId ?? 0 }};
        let currentLastUpdatedAt = @js($latestUpdatedAtIso ?? now()->toIso8601String());
        const currentPage = {{ $transactions->currentPage() ?? 1 }};

        const currentFilters = {
            q: @js(request('q', '')),
            event_id: @js(request('event_id', '')),
            ticket_category_id: @js(request('ticket_category_id', '')),
            channel: @js(request('channel', '')),
            status: @js(request('status', '')),
        };

        function updateSseStatus(status) {
            const badge = document.getElementById('sse-connection-badge');
            if (!badge) return;

            if (status === 'connected') {
                badge.className = 'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm transition-all';
                badge.innerHTML = `
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span id="sse-badge-text">Live Update</span>
                `;
            } else if (status === 'connecting') {
                badge.className = 'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200 shadow-sm transition-all';
                badge.innerHTML = `
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span id="sse-badge-text">Menghubungkan...</span>
                `;
            } else if (status === 'error') {
                badge.className = 'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200 shadow-sm transition-all';
                badge.innerHTML = `
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span id="sse-badge-text">Offline</span>
                `;
            }
        }

        function updateMetricCard(elementId, newValue) {
            const el = document.getElementById(elementId);
            if (!el || el.textContent.trim() === newValue) return;

            el.textContent = newValue;
            el.classList.add('stat-changed');
            setTimeout(() => {
                el.classList.remove('stat-changed');
            }, 1200);
        }

        function updateStatsUI(stats) {
            if (!stats) return;
            if (stats.total_quota_formatted) updateMetricCard('stat-total-quota', stats.total_quota_formatted);
            if (stats.total_sold_formatted) updateMetricCard('stat-total-sold', stats.total_sold_formatted);
            if (stats.total_unsold_formatted) updateMetricCard('stat-total-unsold', stats.total_unsold_formatted);
            if (stats.total_revenue_formatted) updateMetricCard('stat-total-revenue', 'Rp ' + stats.total_revenue_formatted);
        }

        function matchesActiveFilters(tx) {
            if (currentFilters.event_id && String(tx.event_id) !== String(currentFilters.event_id)) {
                return false;
            }
            if (currentFilters.ticket_category_id && String(tx.ticket_category_id) !== String(currentFilters.ticket_category_id)) {
                return false;
            }
            if (currentFilters.channel && String(tx.channel).toLowerCase() !== String(currentFilters.channel).toLowerCase()) {
                return false;
            }
            if (currentFilters.status && String(tx.payment_status).toLowerCase() !== String(currentFilters.status).toLowerCase()) {
                return false;
            }
            if (currentFilters.q) {
                const query = currentFilters.q.toLowerCase();
                const ref = (tx.reference_no || '').toLowerCase();
                const name = (tx.customer_name || '').toLowerCase();
                const email = (tx.customer_email || '').toLowerCase();
                if (!ref.includes(query) && !name.includes(query) && !email.includes(query)) {
                    return false;
                }
            }
            return true;
        }

        function initEventSource() {
            if (eventSource) {
                eventSource.close();
            }

            const params = new URLSearchParams({
                last_id: currentLastId,
                last_updated_at: currentLastUpdatedAt,
            });
            if (currentFilters.event_id) params.append('event_id', currentFilters.event_id);
            if (currentFilters.ticket_category_id) params.append('ticket_category_id', currentFilters.ticket_category_id);
            if (currentFilters.channel) params.append('channel', currentFilters.channel);
            if (currentFilters.status) params.append('status', currentFilters.status);
            if (currentFilters.q) params.append('q', currentFilters.q);

            const streamUrl = "{{ route('organizer.transactions.stream') }}?" + params.toString();
            updateSseStatus('connecting');

            eventSource = new EventSource(streamUrl);

            eventSource.addEventListener('connected', function (e) {
                updateSseStatus('connected');
            });

            eventSource.addEventListener('new_transaction', function (e) {
                try {
                    const data = JSON.parse(e.data);
                    if (!data || !data.id) return;

                    currentLastId = Math.max(currentLastId, data.id);
                    if (data.last_updated_at) {
                        currentLastUpdatedAt = data.last_updated_at;
                    }

                    if (data.stats) {
                        updateStatsUI(data.stats);
                    }

                    playNotificationChime();

                    const existingRow = document.getElementById('tx-row-' + data.id);
                    if (existingRow) {
                        replaceTransactionRow(data);
                        return;
                    }

                    const matchesFilter = matchesActiveFilters(data);

                    if (currentPage === 1 && matchesFilter) {
                        prependTransactionRow(data);

                        showSseToast(
                            '#' + data.reference_no + ' (' + (data.channel === 'pos' ? 'POS' : 'Online') + ')',
                            data.customer_name + ' — Rp ' + data.total_amount_formatted,
                            {
                                type: 'new',
                                actionText: 'Lihat Transaksi',
                                actionCallback: () => {
                                    const row = document.getElementById('tx-row-' + data.id);
                                    if (row) {
                                        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        row.classList.remove('animate-sse-new');
                                        void row.offsetWidth;
                                        row.classList.add('animate-sse-new');
                                    }
                                }
                            }
                        );
                    } else if (currentPage > 1) {
                        showSseToast(
                            '#' + data.reference_no,
                            data.customer_name + ' — Rp ' + data.total_amount_formatted + '. Anda di halaman ' + currentPage + '.',
                            {
                                type: 'new',
                                actionText: 'Ke Halaman 1',
                                actionCallback: () => {
                                    const url = new URL(window.location.href);
                                    url.searchParams.delete('page');
                                    window.location.href = url.toString();
                                }
                            }
                        );
                    } else if (!matchesFilter) {
                        showSseToast(
                            '#' + data.reference_no + ' (Di luar filter)',
                            data.customer_name + ' — Rp ' + data.total_amount_formatted,
                            {
                                type: 'new',
                                actionText: 'Reset Filter',
                                actionCallback: () => {
                                    window.location.href = "{{ route('organizer.transactions.index') }}";
                                }
                            }
                        );
                    }
                } catch (err) {
                    console.error('SSE Error processing new_transaction:', err);
                }
            });

            eventSource.addEventListener('update_transaction', function (e) {
                try {
                    const data = JSON.parse(e.data);
                    if (!data || !data.id) return;

                    if (data.last_updated_at) {
                        currentLastUpdatedAt = data.last_updated_at;
                    }

                    if (data.stats) {
                        updateStatsUI(data.stats);
                    }

                    const existingRow = document.getElementById('tx-row-' + data.id);
                    if (existingRow) {
                        replaceTransactionRow(data);

                        showSseToast(
                            'Pembaruan: #' + data.reference_no,
                            'Status: ' + (data.payment_status || '').toUpperCase() + ' (' + data.customer_name + ')',
                            { type: 'update' }
                        );
                    }
                } catch (err) {
                    console.error('SSE Error processing update_transaction:', err);
                }
            });

            eventSource.onerror = function (e) {
                updateSseStatus('connecting');
            };
        }

        function prependTransactionRow(data) {
            const tbody = document.getElementById('transactions-tbody');
            if (!tbody) return;

            const emptyRow = document.getElementById('empty-transactions-row');
            if (emptyRow) {
                emptyRow.remove();
            }

            const tempTable = document.createElement('table');
            const tempTbody = document.createElement('tbody');
            tempTable.appendChild(tempTbody);
            tempTbody.innerHTML = data.row_html;

            const newRows = Array.from(tempTbody.children);
            if (newRows.length > 0) {
                const mainRow = newRows[0];
                mainRow.classList.add('animate-sse-new');

                // Insert all elements (main row and tickets row) at the top of tbody
                for (let i = newRows.length - 1; i >= 0; i--) {
                    tbody.insertBefore(newRows[i], tbody.firstChild);
                }

                if (typeof bootCancelModal === 'function') {
                    bootCancelModal();
                }
            }
        }

        function replaceTransactionRow(data) {
            const existingRow = document.getElementById('tx-row-' + data.id);
            const existingDetails = document.getElementById('tickets-' + data.id);
            if (!existingRow) return;

            const tempTable = document.createElement('table');
            const tempTbody = document.createElement('tbody');
            tempTable.appendChild(tempTbody);
            tempTbody.innerHTML = data.row_html;

            const newRows = Array.from(tempTbody.children);
            if (newRows.length > 0) {
                const newMainRow = newRows[0];
                newMainRow.classList.add('animate-sse-update');

                existingRow.parentNode.replaceChild(newMainRow, existingRow);

                if (existingDetails && newRows[1]) {
                    if (!existingDetails.classList.contains('hidden')) {
                        newRows[1].classList.remove('hidden');
                        const btnIcon = document.getElementById('icon-' + data.id);
                        if (btnIcon) btnIcon.classList.add('rotate-90');
                    }
                    existingDetails.parentNode.replaceChild(newRows[1], existingDetails);
                }

                if (typeof bootCancelModal === 'function') {
                    bootCancelModal();
                }
            }
        }

        // Initialize SSE on page load
        if (window.EventSource) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    updateSoundButtonUI();
                    initEventSource();
                });
            } else {
                updateSoundButtonUI();
                initEventSource();
            }
        } else {
            console.warn('Browser Anda tidak mendukung Server-Sent Events (SSE).');
        }
    </script>

    <!-- SSE Notification Styles -->
    <style>
        @keyframes sse-glow-new {
            0% { background-color: rgba(249, 115, 22, 0.35); transform: translateY(-3px); }
            50% { background-color: rgba(249, 115, 22, 0.15); }
            100% { background-color: transparent; transform: translateY(0); }
        }
        @keyframes sse-glow-update {
            0% { background-color: rgba(16, 185, 129, 0.35); }
            50% { background-color: rgba(16, 185, 129, 0.15); }
            100% { background-color: transparent; }
        }
        @keyframes sse-toast-in {
            0% { opacity: 0; transform: translateY(20px) scale(0.95); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes sse-toast-out {
            0% { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(-20px) scale(0.95); }
        }
        .animate-sse-new {
            animation: sse-glow-new 3s ease-out forwards;
        }
        .animate-sse-update {
            animation: sse-glow-update 3s ease-out forwards;
        }
        .animate-toast-in {
            animation: sse-toast-in 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .animate-toast-out {
            animation: sse-toast-out 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .stat-changed {
            transition: transform 0.3s ease, color 0.3s ease;
            color: #f97316 !important;
            transform: scale(1.08);
        }
    </style>

    <!-- SSE Floating Toasts Container -->
    <div id="sse-toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>
</x-app-layout>
