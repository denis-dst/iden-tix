<x-app-layout>
    <x-slot name="title">Jual Tiket: {{ $event->name }}</x-slot>

    @php
        $flowLabels = [
            'redeem' => 'Perlu Redeem',
            'evoucher' => 'E-Voucher',
            'print' => 'Cetak Ticket Langsung',
            'both' => 'Evoucher & Cetak Ticket',
        ];
        $flow = $event->purchase_flow ?? 'redeem';
    @endphp

    <div class="max-w-5xl mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-black text-orange-500 uppercase tracking-[0.25em] mb-2">{{ $flowLabels[$flow] ?? 'Perlu Redeem' }}</p>
                <h2 class="text-3xl font-black text-slate-800 font-outfit">{{ $event->name }}</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">{{ $event->venue }} · {{ $event->city }}</p>
            </div>
            <a href="{{ route('organizer.pos.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition shadow-sm text-center">
                Ganti Event
            </a>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-bold space-y-3">
                <div>{{ session('success') }}</div>
                @if(session('active_evoucher_url'))
                    <div class="flex flex-col md:flex-row gap-2 md:items-center">
                        <input type="text" readonly value="{{ session('active_evoucher_url') }}" class="flex-1 rounded-lg border-emerald-200 bg-white text-xs font-bold text-slate-600">
                        <a href="{{ session('active_evoucher_url') }}" target="_blank" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-black uppercase tracking-wider hover:bg-emerald-700 transition text-center">Buka QR</a>
                        @if(($global_settings['global_wa_notifications_enabled'] ?? '1') !== '0')
                            @if(session('active_transaction_id'))
                                <button type="button" id="btn-send-wa" data-action="{{ route('organizer.transactions.send-whatsapp', session('active_transaction_id')) }}" class="px-4 py-2 bg-emerald-500 text-white rounded-lg text-xs font-black uppercase tracking-wider hover:bg-emerald-600 transition text-center flex items-center justify-center gap-1.5 focus:outline-none">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.717-1.458L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436.002 9.859-4.42 9.863-9.864.002-2.637-1.023-5.115-2.887-6.979C16.584 1.898 14.1 8.75 11.466 8.75 6.03 8.748 1.608 13.17 1.605 18.613c-.001 1.666.452 3.284 1.312 4.708L1.875 22.25l4.772-1.258a10.06 10.06 0 004.825 1.451zm9.957-6.808c-.27-.135-1.597-.788-1.845-.878-.247-.09-.427-.135-.607.135-.18.27-.697.878-.855 1.058-.158.18-.315.202-.585.067-.27-.135-1.14-.42-2.172-1.34-.803-.715-1.345-1.6-1.503-1.871-.158-.27-.017-.416.118-.551.121-.122.27-.315.405-.472.135-.158.18-.27.27-.45.09-.18.045-.337-.023-.472-.068-.135-.607-1.463-.832-2.003-.22-.529-.44-.457-.607-.466-.157-.008-.337-.01-.517-.01-.18 0-.472.067-.72.337-.248.27-.945.923-.945 2.25 0 1.328.968 2.61 1.103 2.79.135.18 1.905 2.91 4.613 4.078.644.278 1.147.444 1.54.569.647.206 1.236.177 1.701.108.519-.078 1.598-.653 1.823-1.283.225-.63.225-1.17.157-1.283-.067-.113-.247-.18-.517-.315z"/>
                                    </svg>
                                    <span id="wa-btn-text">Kirim WA</span>
                                </button>
                            @elseif(session('active_whatsapp_url'))
                                <a href="{{ session('active_whatsapp_url') }}" target="_blank" class="px-4 py-2 bg-emerald-500 text-white rounded-lg text-xs font-black uppercase tracking-wider hover:bg-emerald-600 transition text-center flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.717-1.458L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436.002 9.859-4.42 9.863-9.864.002-2.637-1.023-5.115-2.887-6.979C16.584 1.898 14.1 8.75 11.466 8.75 6.03 8.748 1.608 13.17 1.605 18.613c-.001 1.666.452 3.284 1.312 4.708L1.875 22.25l4.772-1.258a10.06 10.06 0 004.825 1.451zm9.957-6.808c-.27-.135-1.597-.788-1.845-.878-.247-.09-.427-.135-.607.135-.18.27-.697.878-.855 1.058-.158.18-.315.202-.585.067-.27-.135-1.14-.42-2.172-1.34-.803-.715-1.345-1.6-1.503-1.871-.158-.27-.017-.416.118-.551.121-.122.27-.315.405-.472.135-.158.18-.27.27-.45.09-.18.045-.337-.023-.472-.068-.135-.607-1.463-.832-2.003-.22-.529-.44-.457-.607-.466-.157-.008-.337-.01-.517-.01-.18 0-.472.067-.72.337-.248.27-.945.923-.945 2.25 0 1.328.968 2.61 1.103 2.79.135.18 1.905 2.91 4.613 4.078.644.278 1.147.444 1.54.569.647.206 1.236.177 1.701.108.519-.078 1.598-.653 1.823-1.283.225-.63.225-1.17.157-1.283-.067-.113-.247-.18-.517-.315z"/>
                                    </svg>
                                    Kirim WA
                                </a>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-100 text-rose-700 px-4 py-3 rounded-2xl text-sm font-bold">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-2xl">
                <h3 class="text-sm font-bold text-red-800">Terdapat kesalahan pengisian form:</h3>
                <ul class="mt-2 list-disc list-inside text-xs text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <form action="{{ route('organizer.pos.store', $event) }}" method="POST" class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kategori Tiket</label>
                    <select name="ticket_category_id" class="w-full rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500 text-sm font-bold" required>
                        <option value="">Pilih kategori</option>
                        @foreach($event->ticketCategories as $category)
                            <option value="{{ $category->id }}" {{ old('ticket_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }} · Rp {{ number_format($category->price, 0, ',', '.') }}@if(($category->service_fee ?? 0) > 0) (+ Fee Rp {{ number_format($category->service_fee, 0, ',', '.') }})@endif · Sisa {{ max(0, $category->quota - $category->sold_count) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Jumlah</label>
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="100" class="w-full rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500 font-bold" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Metode Bayar</label>
                        <select name="payment_method" class="w-full rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500 text-sm font-bold" required>
                            <option value="Tunai" {{ old('payment_method', 'Tunai') === 'Tunai' ? 'selected' : '' }}>Tunai (Cash)</option>
                            <option value="QRIS" {{ old('payment_method') === 'QRIS' ? 'selected' : '' }}>QRIS (WAGO Payment)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Pembeli</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="w-full rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">No HP</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="w-full rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="w-full rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">NIK (Opsional)</label>
                        <input type="text" name="customer_nik" value="{{ old('customer_nik') }}" class="w-full rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500">
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-orange-600 text-white rounded-xl font-black hover:bg-orange-700 transition shadow-lg shadow-orange-200 uppercase tracking-wider">
                    Proses Transaksi
                </button>
            </form>

            <aside class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Ringkasan Flow</h3>
                <div class="p-4 rounded-2xl bg-orange-50 border border-orange-100">
                    <div class="text-sm font-black text-orange-700">{{ $flowLabels[$flow] ?? 'Perlu Redeem' }}</div>
                    <p class="text-xs text-orange-700/70 mt-1 leading-relaxed">
                        @if($flow === 'redeem')
                            Setelah dibeli, e-voucher perlu diredeem menjadi wristband sebelum dipakai di gate.
                        @elseif($flow === 'evoucher')
                            Tiket langsung memiliki QR yang bisa discan di gate seperti event gratis.
                        @elseif($flow === 'both')
                            Tiket langsung memiliki QR yang bisa discan di gate, dan E-Voucher dapat dikirim ke pembeli via WhatsApp.
                        @else
                            Setelah transaksi, sistem membuka halaman cetak termal berisi QR scan gate.
                        @endif
                    </p>
                </div>
                @if($flow === 'print')
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ukuran Kertas</div>
                        <div class="text-xl font-black text-slate-800 mt-1">{{ $event->thermal_paper_width_mm ?? 80 }} x {{ $event->thermal_paper_height_mm ?? 160 }} mm</div>
                    </div>
                @endif
            </aside>
        </div>
    </div>

    @if(session('active_transaction_id'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const btnSendWa = document.getElementById('btn-send-wa');
                const waBtnText = document.getElementById('wa-btn-text');
                
                if (btnSendWa) {
                    btnSendWa.addEventListener('click', function (e) {
                        e.preventDefault();
                        
                        btnSendWa.disabled = true;
                        btnSendWa.classList.add('opacity-50', 'cursor-not-allowed');
                        waBtnText.textContent = 'Mengirim...';
                        
                        fetch(btnSendWa.dataset.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => {
                            return response.json().then(data => ({
                                status: response.status,
                                body: data
                            }));
                        })
                        .then(res => {
                            if (res.status === 200 && res.body.success) {
                                waBtnText.textContent = 'WA Terkirim!';
                                btnSendWa.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
                                btnSendWa.classList.add('bg-blue-600');
                                alert(res.body.message || 'WhatsApp berhasil dikirim!');
                            } else {
                                throw new Error(res.body.message || 'Gagal mengirim WhatsApp.');
                            }
                        })
                        .catch(err => {
                            alert(err.message || 'Terjadi kesalahan saat mengirim WhatsApp.');
                            btnSendWa.disabled = false;
                            btnSendWa.classList.remove('opacity-50', 'cursor-not-allowed');
                            waBtnText.textContent = 'Kirim WA';
                        });
                    });
                }
            });
        </script>
    @endif
</x-app-layout>
