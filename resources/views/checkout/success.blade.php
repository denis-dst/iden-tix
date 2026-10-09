<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($transaction->payment_method === 'free')
            Registrasi Berhasil
        @elseif($transaction->payment_status === 'paid')
            Pembayaran Berhasil
        @elseif($transaction->payment_status === 'pending')
            Menunggu Pembayaran
        @else
            Pembayaran Tidak Berhasil
        @endif
        - IdenTix
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        @keyframes floatIn {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-float-in { animation: floatIn 0.5s ease-out forwards; }
        @keyframes customBlink {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.85); }
        }
        .animate-blink { animation: customBlink 1.2s infinite ease-in-out; }

        /* Modal animations */
        @keyframes modalBackdropIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes modalCardIn {
            from { opacity: 0; transform: scale(0.85) translateY(30px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-backdrop { animation: modalBackdropIn 0.3s ease-out forwards; }
        .modal-card { animation: modalCardIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.1s both; }

        @keyframes successPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
            50% { box-shadow: 0 0 0 16px rgba(16,185,129,0); }
        }
        .success-pulse { animation: successPulse 2s infinite; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    @php
        $isFree    = in_array($transaction->payment_method, ['free', 'promo']) || $transaction->total_amount == 0;
        $isPaid    = $isFree || $transaction->payment_status === 'paid';
        $isPending = !$isPaid && $transaction->payment_status === 'pending';
        $isFailed  = !$isPaid && in_array($transaction->payment_status, ['failed', 'expired', 'refunded', 'cancelled']);
        $purchaseFlow = $transaction->event->purchase_flow ?? 'redeem';
    @endphp

    {{-- Modal: Terima Kasih (Free event, Promo 100% & Paid Transactions) --}}
    @if($isPaid)
    <div id="thankYouModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop"
         style="background: rgba(0,0,0,0.6); backdrop-filter: blur(6px);">

        <div class="modal-card bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full overflow-hidden max-h-[90vh] flex flex-col">
            <div class="h-2 bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-500 shrink-0"></div>

            <div class="p-8 overflow-y-auto">
                <div class="flex justify-center mb-5">
                    <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center success-pulse">
                        <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                <h2 class="text-2xl font-black text-slate-900 text-center mb-2" style="font-family: 'Outfit', sans-serif;">
                    🎉 Terima Kasih!
                </h2>
                <p class="text-center text-slate-500 text-sm font-medium mb-6">
                    {{ $isFree ? 'Pendaftaran' : 'Pembayaran' }} Anda untuk <span class="font-bold text-emerald-600">{{ $transaction->event->name }}</span> telah berhasil!
                    <br>
                    Silakan periksa WhatsApp atau Email Anda untuk mendapatkan E-Voucher atau ikuti petunjuk di bawah ini.
                </p>

                <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-5 mb-5 space-y-3">
                    <p class="text-xs font-black text-amber-700 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Petunjuk Masuk Acara
                    </p>

                    <ol class="space-y-2.5">
                        <li class="flex items-start gap-3">
                            <div class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-[10px] font-black shrink-0 mt-0.5">1</div>
                            <p class="text-sm text-slate-700 font-medium leading-snug">
                                <strong>Buka E-Voucher</strong> dengan menekan tombol hijau di bawah
                            </p>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-[10px] font-black shrink-0 mt-0.5">2</div>
                            <p class="text-sm text-slate-700 font-medium leading-snug">
                                <strong>Screenshot / Simpan sebagai PDF</strong> untuk ditunjukkan di hari H
                            </p>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-[10px] font-black shrink-0 mt-0.5">3</div>
                            <p class="text-sm text-slate-700 font-medium leading-snug">
                                @if($purchaseFlow === 'redeem')
                                    <strong>Wajib Ditukar (Redeem):</strong> Tunjukkan E-Voucher di booth resmi untuk ditukar dengan tiket fisik/gelang sebelum masuk.
                                @else
                                    <strong>Langsung Masuk (Tanpa Redeem):</strong> Tunjukkan QR Code E-Voucher langsung di pintu/gate masuk untuk dipindai oleh kru.
                                @endif
                            </p>
                        </li>
                    </ol>
                </div>

                @forelse($transaction->tickets as $ticket)
                <div class="mb-4">
                    @if($transaction->tickets->count() > 1)
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Tiket #{{ $loop->iteration }} - {{ $ticket->category->name ?? 'Tiket' }}</div>
                    @endif
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 mb-2">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <span class="flex-1 text-xs font-mono text-slate-600 truncate" id="evoucher-url-{{ $loop->index }}">{{ route('tickets.view', $ticket->ticket_code) }}</span>
                        <button onclick="copyEvoucherUrl('{{ route('tickets.view', $ticket->ticket_code) }}', 'copy-btn-{{ $loop->index }}')"
                                id="copy-btn-{{ $loop->index }}"
                                class="shrink-0 text-[10px] font-black text-white bg-emerald-500 hover:bg-emerald-600 px-3 py-1.5 rounded-xl transition flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Salin
                        </button>
                    </div>

                    <a href="{{ route('tickets.view', $ticket->ticket_code) }}" target="_blank"
                       class="flex items-center justify-center gap-2 w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-black rounded-2xl transition shadow-lg shadow-emerald-500/30 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                        Buka E-Voucher & Screenshot
                    </a>
                </div>
                @empty
                <div class="text-center py-3 text-sm text-slate-400 font-medium">E-Voucher sedang diproses...</div>
                @endforelse

                <button onclick="document.getElementById('thankYouModal').style.display='none'"
                        class="w-full mt-2 py-3 rounded-2xl border-2 border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">
                    Tutup & Lihat Detail
                </button>
            </div>
        </div>
    </div>

    <script>
        function copyEvoucherUrl(url, btnId) {
            navigator.clipboard.writeText(url).then(() => {
                const btn = document.getElementById(btnId);
                const orig = btn.innerHTML;
                btn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Tersalin!`;
                btn.classList.add('bg-teal-500');
                btn.classList.remove('bg-emerald-500');
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.classList.remove('bg-teal-500');
                    btn.classList.add('bg-emerald-500');
                }, 2500);
            }).catch(() => {
                const el = document.createElement('textarea');
                el.value = url;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                alert('URL berhasil disalin!');
            });
        }
    </script>
    @endif
    {{-- ============================================================ --}}

    <div class="max-w-xl w-full bg-white rounded-[3rem] shadow-2xl shadow-blue-900/10 border border-slate-100 overflow-hidden animate-float-in">
        <!-- Top Accent Bar -->
        @if($isPaid)
            <div class="h-2 {{ $isFree ? 'bg-gradient-to-r from-emerald-400 to-teal-500' : 'bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500' }}"></div>
        @elseif($isPending)
            <div class="h-2 bg-gradient-to-r from-amber-400 via-yellow-400 to-orange-500"></div>
        @else
            <div class="h-2 bg-gradient-to-r from-rose-500 via-red-500 to-pink-500"></div>
        @endif

        <div class="p-10 text-center">
            <!-- Icon -->
            @if($isPaid)
                <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                
                <h1 class="text-3xl font-black text-slate-900 mb-2 font-outfit">
                    {{ $isFree ? 'Registrasi Berhasil! 🎉' : 'Pembayaran Berhasil! 🎉' }}
                </h1>
                <p class="text-slate-500 mb-8 font-medium">
                    @if($isFree)
                        Pendaftaran Anda telah berhasil. E-Voucher sudah siap untuk 
                        <span class="font-bold text-emerald-600">{{ $transaction->event->name }}</span>.
                    @else
                        Pembayaran Anda telah terkonfirmasi. Siapkan diri Anda untuk 
                        <span class="font-bold text-emerald-600">{{ $transaction->event->name }}</span>.
                    @endif
                </p>
            @elseif($isPending)
                <div class="w-20 h-20 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                
                <h1 class="text-3xl font-black text-slate-900 mb-2 font-outfit">
                    Menunggu Pembayaran ⏳
                </h1>
                <p class="text-slate-500 mb-8 font-medium">
                    Pembayaran untuk transaksi <span class="font-bold text-slate-800">{{ $transaction->reference_no }}</span> sedang menunggu penyelesaian atau verifikasi dari bank.
                </p>
            @else
                <div class="w-20 h-20 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                
                <h1 class="text-3xl font-black text-slate-900 mb-2 font-outfit">
                    Pembayaran Tidak Berhasil
                </h1>
                <p class="text-slate-500 mb-8 font-medium">
                    Transaksi ini telah dibatalkan atau waktu pembayaran telah kedaluwarsa.
                </p>
            @endif

            <!-- Info Card -->
            <div class="bg-slate-50 rounded-3xl p-6 mb-6 text-left space-y-3 border border-slate-100">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">No. Referensi</span>
                    <span class="text-sm font-black text-slate-900 font-mono">{{ $transaction->reference_no }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Nama</span>
                    <span class="text-sm font-bold text-slate-700">{{ $transaction->customer_name }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Jumlah</span>
                    <span class="text-sm font-bold text-slate-700">{{ $transaction->quantity }} {{ $isFree ? 'Peserta' : 'Tiket' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Status Pembayaran</span>
                    @if($isPaid)
                        <span class="px-3 py-1 rounded-xl text-xs font-black bg-emerald-100 text-emerald-700 uppercase tracking-wider">LUNAS / BERHASIL</span>
                    @elseif($isPending)
                        <span class="px-3 py-1 rounded-xl text-xs font-black bg-amber-100 text-amber-700 uppercase tracking-wider">MENUNGGU PEMBAYARAN</span>
                    @else
                        <span class="px-3 py-1 rounded-xl text-xs font-black bg-rose-100 text-rose-700 uppercase tracking-wider">{{ strtoupper($transaction->payment_status) }}</span>
                    @endif
                </div>
                @if($isFree)
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Gender</span>
                        <span class="text-sm font-bold {{ $transaction->customer_gender === 'ikhwan' ? 'text-blue-600' : 'text-pink-600' }} capitalize">
                            {{ $transaction->customer_gender === 'ikhwan' ? '🧔 Ikhwan' : '🧕 Akhwat' }}
                        </span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Biaya</span>
                        <span class="text-sm font-black text-emerald-600">GRATIS</span>
                    </div>
                @else
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Metode</span>
                        <span class="text-sm font-bold text-slate-700 uppercase">{{ str_replace('_', ' ', $transaction->payment_method) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Bayar</span>
                        <span class="text-sm font-black text-blue-600 font-outfit">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            @if($isPaid && $transaction->event->evoucher_info)
                <div class="bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-3xl p-6 mb-6 shadow-sm text-left flex items-start gap-4">
                    <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center shrink-0 text-white shadow-inner animate-blink">
                        @if(Str::contains(strtolower($transaction->event->evoucher_info), ['whatsapp', 'wa.me', 'chat.whatsapp']))
                            <svg class="w-5 h-5 text-white fill-current" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-white">Informasi Penting Peserta</p>
                        <p class="text-[11px] text-orange-50 font-medium leading-relaxed mt-1">
                            {!! preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" class="underline text-yellow-200 hover:text-white font-black">$1</a>', nl2br(e($transaction->event->evoucher_info))) !!}
                        </p>
                    </div>
                </div>
            @endif

            <!-- E-Voucher Access (ONLY FOR PAID / FREE TRANSACTIONS) -->
            @if($isPaid)
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4 text-left">
                        {{ $isFree ? '🎟️ E-Voucher Anda' : 'Akses E-Voucher' }}
                    </h3>
                    
                    <p class="text-[11px] text-amber-600 font-bold text-left mb-4 bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span><strong>PETUNJUK PENTING:</strong> Silakan klik / buka salah satu E-Voucher di bawah ini, kemudian simpan baik-baik (lakukan <strong>Cetak / Screenshot / Simpan sebagai PDF</strong>) untuk ditunjukkan kepada petugas saat masuk ke lokasi acara.</span>
                    </p>
                    <div class="grid grid-cols-1 gap-4">
                        @forelse($transaction->tickets->take(1) as $ticket)
                            <a href="{{ route('tickets.view', $ticket->ticket_code) }}" target="_blank" 
                               class="flex items-center justify-between p-6 bg-white border-2 {{ $isFree ? 'border-emerald-100 hover:border-emerald-500 hover:bg-emerald-50/30' : 'border-slate-100 hover:border-emerald-600 hover:bg-emerald-50/30' }} rounded-[2rem] transition-all group shadow-sm">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 {{ $isFree ? 'bg-emerald-600 shadow-emerald-600/20' : 'bg-emerald-600 shadow-emerald-600/20' }} text-white rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-[10px] font-black text-slate-400 uppercase">{{ $isFree ? 'E-Voucher Peserta' : 'Tiket' }}</p>
                                        <p class="text-sm font-black text-slate-900 font-outfit">{{ $transaction->event->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $ticket->ticket_code }}</p>
                                    </div>
                                </div>
                                <span class="text-emerald-600 font-black text-xs uppercase tracking-widest group-hover:translate-x-1 transition">Buka &rarr;</span>
                            </a>
                        @empty
                            <div class="p-8 bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-200 text-center space-y-4">
                                <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center mx-auto animate-pulse">
                                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-slate-600">Sedang Menerbitkan Tiket...</p>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-widest">Mohon tunggu sebentar atau muat ulang halaman</p>
                                </div>
                                <button onclick="window.location.reload()" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                                    Muat Ulang
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>
            @elseif($isPending)
                <div class="p-8 bg-amber-50/50 rounded-[2rem] border border-amber-200 text-center space-y-4">
                    <div class="space-y-2">
                        <p class="text-sm font-bold text-slate-800">Menunggu Verifikasi Pembayaran</p>
                        <p class="text-xs text-slate-600 leading-relaxed max-w-md mx-auto">
                            Jika Anda telah menyelesaikan pembayaran, sistem akan memverifikasi status pembayaran Anda secara otomatis. E-Voucher akan otomatis dikirim ke Email & WhatsApp Anda setelah pembayaran dikonfirmasi lunas.
                        </p>
                    </div>
                    <button onclick="window.location.reload()" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl text-xs font-black transition shadow-lg shadow-amber-500/20 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Segarkan Status Pembayaran
                    </button>
                    <script>
                        // Auto-refresh setiap 3.5 detik untuk mengecek status pembayaran secara realtime
                        let pollAttempt = 0;
                        const maxPoll = 40;
                        const pollTimer = setInterval(() => {
                            pollAttempt++;
                            if (pollAttempt > maxPoll) {
                                clearInterval(pollTimer);
                                return;
                            }
                            window.location.reload();
                        }, 3500);
                    </script>
                </div>
            @else
                <div class="p-8 bg-rose-50/50 rounded-[2rem] border border-rose-200 text-center space-y-4">
                    <div class="space-y-2">
                        <p class="text-sm font-bold text-slate-800">Transaksi Gagal atau Daluwarsa</p>
                        <p class="text-xs text-slate-600 leading-relaxed max-w-md mx-auto">
                            E-Voucher tidak dapat diterbitkan untuk transaksi yang belum atau gagal diselesaikan. Silakan lakukan pemesanan ulang tiket Anda jika masih ingin menghadiri event ini.
                        </p>
                    </div>
                    <a href="{{ route('events.show', $transaction->event->slug) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl text-xs font-black transition shadow-lg shadow-rose-600/20 active:scale-95">
                        Pesan Ulang Tiket &rarr;
                    </a>
                </div>
            @endif

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                @if(!$isPaid)
                    <a href="{{ route('events.show', $transaction->event->slug) }}" class="w-full sm:w-auto py-3 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl font-bold text-xs transition">
                        ← Kembali ke Halaman Event
                    </a>
                @endif
                <a href="{{ url('/') }}" class="w-full sm:w-auto py-3 px-8 bg-slate-900 text-white rounded-2xl font-bold text-xs hover:bg-slate-800 transition transform active:scale-95 shadow-xl shadow-slate-900/20">
                    Ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
