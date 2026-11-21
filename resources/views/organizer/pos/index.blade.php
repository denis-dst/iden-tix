<x-app-layout>
    <x-slot name="title">Penjualan Langsung</x-slot>

    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-black text-orange-500 uppercase tracking-[0.25em] mb-2">Tenant POS</p>
                <h2 class="text-3xl font-black text-slate-800 font-outfit">Penjualan Langsung</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Pilih event untuk membuat transaksi yang ditangani petugas tenant.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('organizer.kiosk.launcher') }}" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold shadow-sm transition text-center flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                    Mode KIOSK Mandiri
                </a>
                <a href="{{ route('organizer.transactions.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition shadow-sm text-center text-sm">
                    Lihat Laporan
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-100 text-rose-700 px-4 py-3 rounded-2xl text-sm font-bold">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse($events as $event)
                @php
                    $flowLabels = [
                        'redeem' => 'Perlu Redeem',
                        'evoucher' => 'E-Voucher',
                        'print' => 'Cetak Ticket',
                        'both' => 'Evoucher & Cetak',
                    ];
                @endphp
                <a href="{{ route('organizer.pos.create', $event) }}" class="block bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:border-orange-200 hover:shadow-lg hover:shadow-orange-100 transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="text-lg font-black text-slate-800 leading-tight">{{ $event->name }}</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase mt-1">{{ $event->venue }} · {{ $event->city }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-lg bg-orange-50 text-orange-600 text-[9px] font-black uppercase tracking-wider shrink-0">
                            {{ $flowLabels[$event->purchase_flow ?? 'redeem'] ?? 'Perlu Redeem' }}
                        </span>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50">
                            <div class="font-black text-slate-700">{{ $event->event_start_date?->format('d M Y H:i') ?? '-' }}</div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase mt-1">Tanggal Event</div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50">
                            <div class="font-black text-slate-700">{{ $event->ticket_categories_count }}</div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase mt-1">Kategori</div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="md:col-span-2 xl:col-span-3 bg-white border border-slate-100 rounded-3xl p-12 text-center">
                    <p class="text-sm font-bold text-slate-400">Belum ada event yang bisa dijual.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
