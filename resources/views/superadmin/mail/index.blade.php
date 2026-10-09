<x-app-layout>
    <x-slot name="title">Monitor Email Masuk & Keluar</x-slot>

    <div class="space-y-6">
        <!-- Top Banner / Header Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border-2 border-slate-200 shadow-sm relative overflow-hidden">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2.5">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-900 border border-indigo-200 rounded-xl text-xs font-black uppercase tracking-widest">Webmail Monitor</span>
                        <span class="flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-900 border border-emerald-200 rounded-xl text-xs font-black">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            SSL Encrypted
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black font-outfit text-slate-950">Monitor Email Masuk & Keluar</h1>
                    <p class="text-slate-700 font-semibold text-xs sm:text-sm mt-1.5 leading-relaxed">
                        Kelola, uji koneksi, baca kotak masuk (IMAP 993 / POP3 995), dan pantau email keluar (SMTP 465).
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
                    <button type="button" onclick="switchTab('tester')" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-2xl transition shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Tes Kirim Email
                    </button>
                    <button type="button" onclick="switchTab('inbox'); loadInbox();" class="px-5 py-3 bg-slate-900 hover:bg-slate-800 text-white text-xs font-black rounded-2xl transition shadow-md shadow-slate-900/20 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Muat Kotak Masuk
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6">
            <!-- Outgoing SMTP Card -->
            <div class="bg-white rounded-3xl p-6 border-2 border-slate-200 shadow-sm flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-700">Server Keluar (Outgoing)</p>
                    <h3 class="text-base sm:text-lg font-black text-slate-950 font-outfit mt-0.5 truncate">{{ $smtpConfig['host'] }}</h3>
                    <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs font-mono">
                        <span class="px-2.5 py-1 bg-blue-100 text-blue-900 font-black rounded-lg">Port {{ $smtpConfig['port'] }} (SSL)</span>
                        <span class="font-bold text-slate-800 truncate">{{ $smtpConfig['username'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Incoming IMAP Card -->
            <div class="bg-white rounded-3xl p-6 border-2 border-slate-200 shadow-sm flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-700">Server Masuk (Incoming)</p>
                    <h3 class="text-base sm:text-lg font-black text-slate-950 font-outfit mt-0.5 truncate">{{ $incomingConfig['host'] }}</h3>
                    <div class="mt-2.5 flex items-center gap-2 text-xs font-mono">
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-900 font-black rounded-lg">IMAP 993 / POP3 995</span>
                    </div>
                </div>
            </div>

            <!-- Global Setting Status Card -->
            <div class="bg-white rounded-3xl p-6 border-2 border-slate-200 shadow-sm flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-700">Status Notifikasi Global</p>
                    <h3 class="text-base sm:text-lg font-black text-slate-950 font-outfit mt-0.5">E-Voucher Email</h3>
                    <div class="mt-2.5 flex items-center gap-2">
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-900 text-xs font-black rounded-lg uppercase tracking-wider">Aktif</span>
                        <a href="{{ route('superadmin.settings.index') }}" class="text-xs font-black text-orange-600 hover:text-orange-700 underline">Ubah di Pengaturan &rarr;</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Responsive Unified Segmented Tab Navigation -->
        <div class="w-full bg-slate-200/80 p-1.5 rounded-2xl flex flex-col sm:flex-row gap-1.5 border border-slate-300">
            <button type="button" onclick="switchTab('inbox')" id="tab-btn-inbox" class="tab-btn flex-1 py-3 px-4 rounded-xl text-xs font-black uppercase tracking-wider transition text-center justify-center flex items-center gap-2 bg-orange-600 text-white shadow-md">
                📥 Kotak Masuk (Inbox)
            </button>
            <button type="button" onclick="switchTab('outbox')" id="tab-btn-outbox" class="tab-btn flex-1 py-3 px-4 rounded-xl text-xs font-black uppercase tracking-wider transition text-center justify-center flex items-center gap-2 bg-white text-slate-800 hover:bg-slate-100">
                📤 Kotak Keluar & E-Voucher
            </button>
            <button type="button" onclick="switchTab('tester')" id="tab-btn-tester" class="tab-btn flex-1 py-3 px-4 rounded-xl text-xs font-black uppercase tracking-wider transition text-center justify-center flex items-center gap-2 bg-white text-slate-800 hover:bg-slate-100">
                ⚡ Uji Coba & Diagnostik
            </button>
        </div>

        <!-- TAB 1: INBOX (KOTAK MASUK) -->
        <div id="tab-content-inbox" class="space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border-2 border-slate-200 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 pb-6 border-b border-slate-200">
                    <div class="space-y-1">
                        <h2 class="text-xl sm:text-2xl font-black text-slate-950 font-outfit">Kotak Masuk (IMAP / POP3)</h2>
                        <p class="text-xs text-slate-700 font-bold">Membaca email yang masuk ke akun <span class="text-orange-600 font-mono">{{ $incomingConfig['username'] }}</span> secara live dari server cPanel.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
                        <div class="relative min-w-[220px]">
                            <input type="password" id="imap-pass-input" placeholder="Password email cPanel" class="w-full text-xs rounded-xl border-2 border-slate-300 text-slate-950 font-bold bg-white pl-3.5 pr-9 py-2.5 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 font-mono placeholder:text-slate-500 shadow-sm">
                            <button type="button" onclick="togglePassVisibility('imap-pass-input')" class="absolute right-2.5 top-2.5 text-slate-600 hover:text-slate-900 text-sm">👁️</button>
                        </div>
                        <button type="button" onclick="loadInbox()" class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-black transition shadow-sm flex items-center justify-center gap-2 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Refresh Inbox
                        </button>
                    </div>
                </div>

                <div id="inbox-loading" class="hidden py-12 text-center">
                    <div class="w-10 h-10 border-4 border-orange-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                    <p class="text-xs font-black text-slate-800">Menghubungi server IMAP mail.iden-tix.com:993...</p>
                </div>

                <div id="inbox-error" class="hidden p-6 bg-rose-50 border-2 border-rose-200 rounded-2xl text-center space-y-2 mb-6">
                    <p class="text-xs font-black text-rose-800 uppercase tracking-wider">Gagal Mengambil Email</p>
                    <p id="inbox-error-msg" class="text-xs text-rose-900 font-bold"></p>
                </div>

                <div id="inbox-container" class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-100 border-b border-slate-200 text-xs font-black uppercase text-slate-900 tracking-wider">
                                <th class="py-3.5 px-4">#</th>
                                <th class="py-3.5 px-4">Pengirim</th>
                                <th class="py-3.5 px-4">Subjek</th>
                                <th class="py-3.5 px-4">Tanggal & Waktu</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="inbox-table-body" class="divide-y divide-slate-100 text-xs font-medium">
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-700 font-bold text-sm bg-slate-50/50">
                                    Silakan masukkan password email di atas lalu klik tombol <strong class="text-orange-600">Refresh Inbox</strong> untuk membaca kotak masuk live dari server.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: OUTBOX / RIWAYAT TRANSAKSI E-VOUCHER -->
        <div id="tab-content-outbox" class="hidden space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border-2 border-slate-200 shadow-sm">
                <div class="text-center mb-6 pb-6 border-b border-slate-200">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-950 font-outfit">Kotak Keluar & Riwayat E-Voucher</h2>
                    <p class="text-xs text-slate-700 font-bold mt-1">Daftar email E-Voucher yang telah diterbitkan dan dikirimkan otomatis ke pembeli tiket.</p>
                </div>

                <!-- Filter & Search Bar -->
                <form method="GET" action="{{ route('superadmin.mail.index') }}" class="mb-6 p-4 sm:p-5 bg-slate-50 rounded-2xl border-2 border-slate-200">
                    <input type="hidden" name="tab" value="outbox">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                        <!-- Search Name / Email / Reference -->
                        <div class="lg:col-span-6">
                            <label class="block text-xs font-black uppercase text-slate-900 tracking-wider mb-1.5">Pencarian Pembeli / Email / Invoice</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500 font-bold text-sm">🔍</span>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pembeli, email, No. HP, atau No. Invoice..." class="w-full text-xs rounded-xl border-2 border-slate-300 text-slate-950 font-bold bg-white pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 placeholder:text-slate-500 shadow-sm">
                            </div>
                        </div>

                        <!-- Filter Event -->
                        <div class="lg:col-span-4">
                            <label class="block text-xs font-black uppercase text-slate-900 tracking-wider mb-1.5">Filter Event</label>
                            <select name="event_id" class="w-full text-xs rounded-xl border-2 border-slate-300 text-slate-950 font-black bg-white px-4 py-2.5 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 shadow-sm">
                                <option value="">Semua Event</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                        {{ $event->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="lg:col-span-2 flex items-center gap-2">
                            <button type="submit" class="w-full py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow-md shadow-orange-600/20 flex items-center justify-center gap-1.5">
                                <span>Cari</span>
                            </button>
                            @if(request('search') || request('event_id'))
                                <a href="{{ route('superadmin.mail.index', ['tab' => 'outbox']) }}" class="px-3.5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-900 rounded-xl text-xs font-black transition text-center shrink-0" title="Reset Filter">
                                    ✕
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-100 border-b border-slate-200 text-xs font-black uppercase text-slate-900 tracking-wider">
                                <th class="py-3.5 px-4">Invoice</th>
                                <th class="py-3.5 px-4">Penerima</th>
                                <th class="py-3.5 px-4">Event</th>
                                <th class="py-3.5 px-4">Status Pembayaran</th>
                                <th class="py-3.5 px-4">Waktu Kirim</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($sentTransactions as $tx)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-4 px-4 font-mono font-black text-slate-950 whitespace-nowrap">{{ $tx->reference_no }}</td>
                                    <td class="py-4 px-4">
                                        <p class="font-black text-slate-950">{{ $tx->customer_name }}</p>
                                        <p class="text-xs text-slate-700 font-bold font-mono">{{ $tx->customer_email }}</p>
                                        @if($tx->customer_phone)
                                            <p class="text-[11px] text-slate-500 font-mono font-semibold">{{ $tx->customer_phone }}</p>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 font-black text-slate-900">{{ $tx->event->name ?? '-' }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-xl text-xs font-black bg-emerald-100 text-emerald-900 border border-emerald-200 uppercase tracking-wider">
                                            {{ strtoupper($tx->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-slate-800 font-bold font-mono text-xs whitespace-nowrap">{{ $tx->paid_at ? $tx->paid_at->format('d M Y H:i') : $tx->updated_at->format('d M Y H:i') }}</td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <form action="{{ route('superadmin.transactions.resend-evoucher', $tx->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Kirim ulang email E-Voucher ke {{ $tx->customer_email }}?')" class="px-3.5 py-2 bg-slate-900 hover:bg-orange-600 text-white rounded-xl font-black text-xs uppercase tracking-wider transition shadow-sm">
                                                Kirim Ulang ✉️
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-700 font-bold text-sm bg-slate-50/50">
                                        @if(request('search') || request('event_id'))
                                            Tidak ditemukan data transaksi yang sesuai dengan filter pencarian.
                                        @else
                                            Belum ada riwayat email transaksi lunas.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div class="mt-6">
                    {{ $sentTransactions->links() }}
                </div>
            </div>
        </div>

        <!-- TAB 3: TESTER & DIAGNOSTIK -->
        <div id="tab-content-tester" class="hidden space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Outgoing SMTP Live Tester -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border-2 border-slate-200 shadow-sm space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xl shadow-sm">📤</div>
                        <div>
                            <h3 class="text-xl font-black text-slate-950 font-outfit">Uji Coba Kirim Email (SMTP)</h3>
                            <p class="text-xs text-slate-700 font-bold mt-0.5">Kirim email pengujian langsung ke alamat email tujuan.</p>
                        </div>
                    </div>

                    <form id="form-test-smtp" onsubmit="handleSendTestEmail(event)" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-900 tracking-wider mb-2">Email Penerima</label>
                            <input type="email" name="to_email" id="test-to-email" required placeholder="contoh: namaanda@gmail.com" class="w-full text-xs rounded-xl border-2 border-slate-300 text-slate-950 font-bold bg-white px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder:text-slate-500">
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-slate-900 tracking-wider mb-2">Subjek Email</label>
                            <input type="text" name="subject" value="Tes Koneksi SMTP IdenTix" class="w-full text-xs rounded-xl border-2 border-slate-300 text-slate-950 font-bold bg-white px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-slate-900 tracking-wider mb-2">Isi Pesan</label>
                            <textarea name="body" rows="3" class="w-full text-xs rounded-xl border-2 border-slate-300 text-slate-950 font-bold bg-white px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">Halo, ini adalah pesan uji coba dari sistem IdenTix untuk memverifikasi fungsionalitas pengiriman email SMTP cPanel.</textarea>
                        </div>

                        <div id="smtp-test-result" class="hidden p-4 rounded-xl text-xs font-black"></div>

                        <button type="submit" id="smtp-test-btn" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow-md shadow-indigo-600/20 active:scale-95 flex items-center justify-center gap-2">
                            <span>Kirim Email Pengujian</span>
                        </button>
                    </form>
                </div>

                <!-- Incoming IMAP/POP3 Connection Tester -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border-2 border-slate-200 shadow-sm space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xl shadow-sm">📥</div>
                        <div>
                            <h3 class="text-xl font-black text-slate-950 font-outfit">Uji Autentikasi IMAP / POP3</h3>
                            <p class="text-xs text-slate-700 font-bold mt-0.5">Uji koneksi socket dan kredensial login ke server mail masuk.</p>
                        </div>
                    </div>

                    <form id="form-test-incoming" onsubmit="handleTestIncoming(event)" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-900 tracking-wider mb-2">Protokol</label>
                                <select name="protocol" id="incoming-protocol" class="w-full text-xs rounded-xl border-2 border-slate-300 text-slate-950 font-black bg-white px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="imap">IMAP (Port 993 SSL)</option>
                                    <option value="pop3">POP3 (Port 995 SSL)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-900 tracking-wider mb-2">Host Server</label>
                                <input type="text" name="host" value="mail.iden-tix.com" class="w-full text-xs rounded-xl border-2 border-slate-300 text-slate-950 font-bold bg-white px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-slate-900 tracking-wider mb-2">Username / Email</label>
                            <input type="text" name="username" value="no-reply@iden-tix.com" class="w-full text-xs rounded-xl border-2 border-slate-300 text-slate-950 font-bold bg-white px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-slate-900 tracking-wider mb-2">Password Akun Email</label>
                            <input type="password" name="password" id="incoming-test-pass" required placeholder="Masukkan password email cPanel" class="w-full text-xs rounded-xl border-2 border-slate-300 text-slate-950 font-bold bg-white px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-mono placeholder:text-slate-500">
                        </div>

                        <div id="incoming-test-result" class="hidden p-4 rounded-xl text-xs font-black"></div>

                        <button type="submit" id="incoming-test-btn" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow-md shadow-emerald-600/20 active:scale-95 flex items-center justify-center gap-2">
                            <span>Uji Koneksi & Autentikasi</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Configuration Reference Box -->
            <div class="bg-slate-100 rounded-3xl p-6 sm:p-8 border-2 border-slate-300 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">📋</span>
                    <h3 class="text-lg font-black font-outfit uppercase tracking-wider text-slate-950">Referensi Parameter Konfigurasi Mail Server</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6 text-xs text-slate-900">
                    <div class="bg-white rounded-2xl p-5 sm:p-6 space-y-2.5 border border-slate-300 font-mono shadow-sm">
                        <p class="text-orange-600 font-black uppercase tracking-widest text-xs">Server Masuk (Incoming)</p>
                        <p><strong class="text-slate-600">Server:</strong> <span class="text-slate-950 font-bold">mail.iden-tix.com</span></p>
                        <p><strong class="text-slate-600">IMAP Port:</strong> <span class="text-slate-950 font-bold">993 (SSL)</span></p>
                        <p><strong class="text-slate-600">POP3 Port:</strong> <span class="text-slate-950 font-bold">995 (SSL)</span></p>
                        <p><strong class="text-slate-600">Username:</strong> <span class="text-slate-950 font-bold">no-reply@iden-tix.com</span></p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 sm:p-6 space-y-2.5 border border-slate-300 font-mono shadow-sm">
                        <p class="text-blue-600 font-black uppercase tracking-widest text-xs">Server Keluar (Outgoing)</p>
                        <p><strong class="text-slate-600">Server:</strong> <span class="text-slate-950 font-bold">mail.iden-tix.com</span></p>
                        <p><strong class="text-slate-600">SMTP Port:</strong> <span class="text-slate-950 font-bold">465 (SSL / SMTPS)</span></p>
                        <p><strong class="text-slate-600">Alternatif Port:</strong> <span class="text-slate-950 font-bold">587 (TLS / STARTTLS)</span></p>
                        <p><strong class="text-slate-600">Username:</strong> <span class="text-slate-950 font-bold">no-reply@iden-tix.com</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal View Email Body -->
    <div id="emailDetailModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden animate-float-in border-2 border-slate-300">
            <div class="p-5 sm:p-6 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div>
                    <span class="text-xs font-black uppercase tracking-wider text-orange-600">Detail Pesan Masuk</span>
                    <h3 id="modal-email-subject" class="text-base sm:text-lg font-black text-slate-950 truncate mt-0.5">Memuat...</h3>
                </div>
                <button type="button" onclick="closeEmailModal()" class="w-9 h-9 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-900 flex items-center justify-center font-black text-base">&times;</button>
            </div>
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1 text-xs">
                <div class="bg-slate-100 p-4 rounded-2xl space-y-1.5 font-mono text-slate-900 border border-slate-200">
                    <p><strong class="text-slate-600">Dari:</strong> <span id="modal-email-from" class="font-bold text-slate-950">-</span></p>
                    <p><strong class="text-slate-600">Waktu:</strong> <span id="modal-email-date" class="font-bold text-slate-950">-</span></p>
                </div>
                <div class="space-y-2">
                    <label class="font-black uppercase tracking-wider text-slate-800 text-xs">Isi Pesan:</label>
                    <div id="modal-email-body" class="p-4 sm:p-5 rounded-2xl border-2 border-slate-200 bg-slate-50 font-mono text-slate-950 whitespace-pre-wrap leading-relaxed max-h-80 overflow-y-auto font-medium">
                        Memuat isi pesan...
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-end">
                <button type="button" onclick="closeEmailModal()" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-black transition shadow-sm">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            ['inbox', 'outbox', 'tester'].forEach(t => {
                const content = document.getElementById(`tab-content-${t}`);
                const btn = document.getElementById(`tab-btn-${t}`);
                if (t === tab) {
                    content.classList.remove('hidden');
                    btn.className = "tab-btn flex-1 py-3 px-4 rounded-xl text-xs font-black uppercase tracking-wider transition text-center justify-center flex items-center gap-2 bg-orange-600 text-white shadow-md";
                } else {
                    content.classList.add('hidden');
                    btn.className = "tab-btn flex-1 py-3 px-4 rounded-xl text-xs font-black uppercase tracking-wider transition text-center justify-center flex items-center gap-2 bg-white text-slate-800 hover:bg-slate-100";
                }
            });
        }

        function togglePassVisibility(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        function loadInbox() {
            const pass = document.getElementById('imap-pass-input').value;
            const loading = document.getElementById('inbox-loading');
            const errorDiv = document.getElementById('inbox-error');
            const errorMsg = document.getElementById('inbox-error-msg');
            const tableBody = document.getElementById('inbox-table-body');

            loading.classList.remove('hidden');
            errorDiv.classList.add('hidden');
            tableBody.innerHTML = '';

            fetch("{{ route('superadmin.mail.inbox') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ password: pass })
            })
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');
                if (!data.success) {
                    errorDiv.classList.remove('hidden');
                    errorMsg.innerText = data.message || 'Gagal memuat kotak masuk.';
                    tableBody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-rose-700 font-bold">${data.message}</td></tr>`;
                    return;
                }

                if (!data.messages || data.messages.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="py-12 text-center text-slate-700 font-bold">Kotak masuk kosong (0 pesan).</td></tr>`;
                    return;
                }

                let html = '';
                data.messages.forEach((msg, idx) => {
                    html += `
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                            <td class="py-4 px-4 font-mono text-slate-700 font-black">${msg.id}</td>
                            <td class="py-4 px-4 font-black text-slate-950">${escapeHtml(msg.from)}</td>
                            <td class="py-4 px-4">
                                <span class="font-black text-slate-950">${escapeHtml(msg.subject)}</span>
                            </td>
                            <td class="py-4 px-4 font-mono text-xs text-slate-800 font-bold whitespace-nowrap">${escapeHtml(msg.date)}</td>
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <button type="button" onclick="viewEmailDetail(${msg.id}, '${escapeHtml(msg.subject)}', '${escapeHtml(msg.from)}', '${escapeHtml(msg.date)}')" class="px-3.5 py-2 bg-orange-100 hover:bg-orange-600 hover:text-white text-orange-950 rounded-xl text-xs font-black uppercase tracking-wider transition shadow-sm">
                                    Buka Pesan 📖
                                </button>
                            </td>
                        </tr>
                    `;
                });
                tableBody.innerHTML = html;
            })
            .catch(err => {
                loading.classList.add('hidden');
                errorDiv.classList.remove('hidden');
                errorMsg.innerText = err.message || 'Gagal terhubung ke endpoint server.';
            });
        }

        function viewEmailDetail(id, subject, from, date) {
            const pass = document.getElementById('imap-pass-input').value;
            const modal = document.getElementById('emailDetailModal');
            document.getElementById('modal-email-subject').innerText = subject;
            document.getElementById('modal-email-from').innerText = from;
            document.getElementById('modal-email-date').innerText = date;
            document.getElementById('modal-email-body').innerText = 'Mengambil isi pesan dari server IMAP...';
            modal.classList.remove('hidden');

            fetch(`{{ url('/superadmin/mail/message') }}/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ password: pass })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('modal-email-body').innerText = data.body || data.raw || '(Pesan kosong)';
                } else {
                    document.getElementById('modal-email-body').innerText = 'Gagal memuat pesan: ' + data.message;
                }
            })
            .catch(err => {
                document.getElementById('modal-email-body').innerText = 'Error: ' + err.message;
            });
        }

        function closeEmailModal() {
            document.getElementById('emailDetailModal').classList.add('hidden');
        }

        function handleSendTestEmail(e) {
            e.preventDefault();
            const btn = document.getElementById('smtp-test-btn');
            const resDiv = document.getElementById('smtp-test-result');
            const form = e.target;
            const formData = new FormData(form);

            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin">⏳</span> Mengirim...';
            resDiv.className = "hidden";

            fetch("{{ route('superadmin.mail.test-smtp') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Kirim Email Pengujian';
                resDiv.classList.remove('hidden');
                if (data.success) {
                    resDiv.className = "p-4 rounded-xl text-xs font-black bg-emerald-100 text-emerald-900 border border-emerald-300";
                    resDiv.innerText = "✅ " + data.message;
                } else {
                    resDiv.className = "p-4 rounded-xl text-xs font-black bg-rose-100 text-rose-900 border border-rose-300";
                    resDiv.innerText = "❌ " + data.message;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Kirim Email Pengujian';
                resDiv.classList.remove('hidden');
                resDiv.className = "p-4 rounded-xl text-xs font-black bg-rose-100 text-rose-900 border border-rose-300";
                resDiv.innerText = "❌ Gagal: " + err.message;
            });
        }

        function handleTestIncoming(e) {
            e.preventDefault();
            const btn = document.getElementById('incoming-test-btn');
            const resDiv = document.getElementById('incoming-test-result');
            const form = e.target;
            const formData = new FormData(form);

            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin">⏳</span> Menguji...';
            resDiv.className = "hidden";

            fetch("{{ route('superadmin.mail.test-incoming') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Uji Koneksi & Autentikasi';
                resDiv.classList.remove('hidden');
                if (data.success) {
                    resDiv.className = "p-4 rounded-xl text-xs font-black bg-emerald-100 text-emerald-900 border border-emerald-300";
                    resDiv.innerText = "✅ " + data.message + (data.server_banner ? " (" + data.server_banner + ")" : "");
                } else {
                    resDiv.className = "p-4 rounded-xl text-xs font-black bg-rose-100 text-rose-900 border border-rose-300";
                    resDiv.innerText = "❌ " + data.message;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Uji Koneksi & Autentikasi';
                resDiv.classList.remove('hidden');
                resDiv.className = "p-4 rounded-xl text-xs font-black bg-rose-100 text-rose-900 border border-rose-300";
                resDiv.innerText = "❌ Gagal: " + err.message;
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        document.addEventListener('DOMContentLoaded', () => {
            switchTab('{{ $activeTab ?? "inbox" }}');
        });
    </script>
</x-app-layout>
