<x-app-layout>
    <x-slot name="title">KIOSK & Photobooth Launcher</x-slot>

    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-black text-orange-500 uppercase tracking-[0.25em] mb-2">Club Terminal</p>
                <h2 class="text-3xl font-black text-slate-800 font-outfit">KIOSK & Photobooth Launcher</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Jalankan terminal penjualan mandiri (Self-Service Kiosk) dan Photo Experience untuk stadion klub.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('organizer.photobooth.master') }}"
                   class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl font-bold shadow-sm transition flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-orange-500 fill-none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Master Template Fotobooth
                </a>
                <a href="{{ route('kiosk.index', $tenant->slug ?? 'persita') }}" target="_blank"
                   class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold shadow-lg shadow-orange-500/25 transition flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                    Buka KIOSK Terkini
                </a>
            </div>
        </div>

        <!-- Matches Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($events as $event)
                @php
                    $template = $event->photoboothTemplate;
                    $kioskUrl = route('kiosk.match', ['tenant_slug' => $tenant->slug ?? 'persita', 'event_slug' => $event->slug]);
                @endphp
                <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm hover:border-orange-200 hover:shadow-xl hover:shadow-orange-500/5 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $template && $template->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-500' }}">
                                {{ $template && $template->is_active ? 'Photobooth Aktif' : 'Photobooth Nonaktif' }}
                            </span>
                            <span class="text-xs font-bold text-slate-400">
                                {{ $event->ticketCategories->count() }} Kategori
                            </span>
                        </div>

                        <h3 class="text-xl font-black text-slate-800 leading-snug mb-1">{{ $event->name }}</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-4">{{ $event->venue }} · {{ $event->city }}</p>

                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 mb-5 text-xs space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-bold">Kick-off:</span>
                                <span class="font-extrabold text-slate-800">{{ $event->event_start_date ? $event->event_start_date->format('d M Y H:i') : '-' }} WIB</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-bold">Tema Warna:</span>
                                <div class="flex items-center gap-1.5 font-extrabold text-slate-800">
                                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300" style="background-color: {{ $template->primary_color ?? '#5A2D82' }};"></span>
                                    <span>{{ $template->primary_color ?? '#5A2D82' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <a href="{{ $kioskUrl }}" target="_blank"
                           class="w-full py-2.5 px-4 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold text-xs uppercase tracking-wider text-center transition block shadow-sm">
                            Buka Layar Kiosk Pertandingan
                        </a>

                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('organizer.photobooth.edit', $event) }}" 
                               class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-[11px] text-center transition">
                                Edit Template Foto
                            </a>
                            <a href="{{ route('organizer.pos.create', $event) }}" 
                               class="py-2 px-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl font-bold text-[11px] text-center transition">
                                Kasir POS Biasa
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-dashed border-slate-200">
                    <p class="text-slate-500 font-bold mb-3">Belum ada pertandingan aktif untuk klub Anda.</p>
                    <a href="{{ route('organizer.events.create') }}" class="px-5 py-2.5 bg-orange-500 text-white rounded-xl font-bold text-sm">
                        Buat Pertandingan Baru
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Hardware & Deployment Guidelines (High-Contrast & Ultra-Clear) -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                <span class="text-xs font-black uppercase tracking-widest text-orange-600">Petunjuk Teknis</span>
            </div>
            <h3 class="text-xl font-black text-slate-900 font-outfit uppercase tracking-wide mb-2">Panduan Operasional Terminal KIOSK</h3>
            <p class="text-sm text-slate-500 font-medium leading-relaxed mb-6">
                Untuk performa optimal pada perangkat kiosk touchscreen fisik yang diletakkan di venue atau stadion:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Card 1: Fullscreen -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 hover:border-orange-300 transition">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    </div>
                    <div class="font-black text-slate-800 text-sm mb-1.5">Mode Fullscreen Otomatis</div>
                    <p class="text-xs text-slate-600 leading-relaxed font-medium">
                        Jalankan browser Google Chrome dengan flag: <code class="bg-slate-200 text-orange-700 px-1.5 py-0.5 rounded font-mono font-bold text-[11px]">--kiosk --disable-pinch</code> agar layar sentuh terkunci di antarmuka kiosk.
                    </p>
                </div>

                <!-- Card 2: Kamera -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 hover:border-purple-300 transition">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="font-black text-slate-800 text-sm mb-1.5">Kamera HD / Webcam</div>
                    <p class="text-xs text-slate-600 leading-relaxed font-medium">
                        Gunakan webcam minimal 1080p dengan pencahayaan booth yang merata agar kartu matchday collectible suporter tajam dan jernih.
                    </p>
                </div>

                <!-- Card 3: Reset Antrean -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 hover:border-emerald-300 transition">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div class="font-black text-slate-800 text-sm mb-1.5">Reset Antrean Otomatis</div>
                    <p class="text-xs text-slate-600 leading-relaxed font-medium">
                        Layar 09 otomatis kembali ke menu awal dalam 5 detik setelah transaksi dan foto selesai agar suporter berikutnya langsung bisa memesan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
