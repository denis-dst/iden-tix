<x-app-layout>
    <x-slot name="title">Kustomisasi Template Photobooth - {{ $event->name }}</x-slot>

    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Breadcrumb & Top Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-black text-orange-500 uppercase tracking-widest mb-1">
                    <a href="{{ route('organizer.events.index') }}" class="hover:underline">Events</a>
                    <span>/</span>
                    <span>Photobooth Kiosk</span>
                </div>
                <h2 class="text-3xl font-black text-slate-800 font-outfit">{{ $event->name }}</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Sesuaikan tema, warna klub, header, dan bingkai Matchday Collectible Card untuk Kiosk.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('kiosk.match', ['tenant_slug' => $event->tenant->slug ?? 'persita', 'event_slug' => $event->slug]) }}" 
                   target="_blank" 
                   class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold shadow-lg shadow-orange-500/20 transition flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                    Buka Mode Kiosk
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-bold flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-sm font-bold">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form and Live Preview Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left: Settings Form (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100">
                <form action="{{ route('organizer.photobooth.update', $event) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Status Toggle -->
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div>
                            <span class="block text-sm font-black text-slate-800">Aktifkan Photobooth Experience di Kiosk</span>
                            <span class="text-xs text-slate-500 font-medium">Suporter ditawarkan sesi foto Matchday Collectible setelah pembelian tiket.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $template->is_active ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                        </label>
                    </div>

                    <!-- Template Name -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Nama Template</label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    </div>

                    <!-- Club Colors Palette -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Warna Primer Klub</label>
                            <div class="flex items-center gap-3">
                                <input type="color" id="primaryColorInput" name="primary_color" value="{{ old('primary_color', $template->primary_color ?? '#5A2D82') }}"
                                       class="w-12 h-12 p-1 rounded-xl border border-slate-200 cursor-pointer" oninput="updateLivePreview()">
                                <input type="text" id="primaryColorText" value="{{ old('primary_color', $template->primary_color ?? '#5A2D82') }}" 
                                       class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-sm font-mono font-bold text-slate-800 uppercase"
                                       oninput="syncColorInput('primary', this.value)">
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Contoh: #5A2D82 (Ungu Persita), #F95700 (Oranye Persija)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Warna Aksen / Sekunder</label>
                            <div class="flex items-center gap-3">
                                <input type="color" id="secondaryColorInput" name="secondary_color" value="{{ old('secondary_color', $template->secondary_color ?? '#F95700') }}"
                                       class="w-12 h-12 p-1 rounded-xl border border-slate-200 cursor-pointer" oninput="updateLivePreview()">
                                <input type="text" id="secondaryColorText" value="{{ old('secondary_color', $template->secondary_color ?? '#F95700') }}" 
                                       class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-sm font-mono font-bold text-slate-800 uppercase"
                                       oninput="syncColorInput('secondary', this.value)">
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Warna highlight border & badge</span>
                        </div>
                    </div>

                    <!-- Typography & Match Slogans -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Header Liga / Turnamen</label>
                            <input type="text" id="headerTitleInput" name="header_title" value="{{ old('header_title', $template->header_title) }}"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                   placeholder="Contoh: I LEAGUE 2026/2027" oninput="updateLivePreview()">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Sub-Slogan Atas</label>
                            <input type="text" id="headerSubtitleInput" name="header_subtitle" value="{{ old('header_subtitle', $template->header_subtitle) }}"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                   placeholder="Contoh: FOOTBALL UNITES US" oninput="updateLivePreview()">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Badge Text</label>
                            <input type="text" id="badgeTextInput" name="badge_text" value="{{ old('badge_text', $template->badge_text) }}"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                   placeholder="Contoh: MATCHDAY COLLECTIBLE" oninput="updateLivePreview()">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Footer Slogan</label>
                            <input type="text" id="footerTextInput" name="footer_text" value="{{ old('footer_text', $template->footer_text) }}"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                   placeholder="Contoh: SATU LIGA, SATU SEMANGAT" oninput="updateLivePreview()">
                        </div>
                    </div>

                    <!-- Custom Graphic Assets Upload -->
                    <div class="border-t border-slate-100 pt-6 space-y-4">
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Aset Grafis Custom (Opsional)</h4>
                        
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Custom Frame Overlay (PNG Transparan)</label>
                            <input type="file" name="frame_overlay" accept="image/png"
                                   class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 cursor-pointer">
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Format PNG rasio 2:3 (disarankan 1024 x 1536 px dengan area tengah transparan).</span>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Background Stadion / Lapangan</label>
                            <input type="file" name="background_image" accept="image/jpeg,image/png,image/webp"
                                   class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Latar belakang atmosfer stadion untuk kartu foto.</span>
                        </div>
                    </div>

                    <!-- Toggles -->
                    <div class="border-t border-slate-100 pt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer">
                            <input type="checkbox" name="show_qr" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ $template->show_qr ? 'checked' : '' }}>
                            <span class="text-xs font-bold text-slate-700">Tampilkan QR Code Scan</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer">
                            <input type="checkbox" name="show_fan_name" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ $template->show_fan_name ? 'checked' : '' }}>
                            <span class="text-xs font-bold text-slate-700">Tampilkan Nama Suporter</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="submit" class="px-6 py-3 bg-slate-900 hover:bg-black text-white font-black text-sm rounded-xl shadow-lg transition">
                            Simpan Perubahan Template
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Interactive Live Preview (5 cols) -->
            <div class="lg:col-span-5 flex flex-col items-center">
                <div class="sticky top-6 w-full max-w-sm">
                    <div class="flex items-center justify-between mb-3 px-2">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-500">Live Card Preview</span>
                        <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full border border-emerald-200">REALTIME</span>
                    </div>

                    <!-- Visual Card Preview -->
                    <div id="previewCard" class="w-full rounded-2xl overflow-hidden shadow-2xl border-4 transition-all duration-300" 
                         style="background-color: {{ $template->primary_color ?? '#5A2D82' }}; border-color: {{ $template->secondary_color ?? '#F95700' }}; aspect-ratio: 2/3; position: relative; color: #ffffff;">
                        
                        <!-- Top Header -->
                        <div class="pt-5 pb-2 text-center">
                            <div id="previewHeaderTitle" class="text-base font-black tracking-wider uppercase drop-shadow-sm">{{ $template->header_title ?? 'I LEAGUE 2026/2027' }}</div>
                            <div id="previewHeaderSubtitle" class="text-[10px] font-bold tracking-widest uppercase drop-shadow-sm" style="color: {{ $template->secondary_color ?? '#F95700' }};">{{ $template->header_subtitle ?? 'FOOTBALL UNITES US' }}</div>
                        </div>

                        <!-- Fan Photo Placeholder -->
                        <div class="mx-4 my-1 rounded-xl overflow-hidden bg-slate-900 border border-white/20 relative" style="height: 58%;">
                            <img src="{{ asset('images/kiosk/matchday_card_sample.jpg') }}" alt="Card Preview" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex items-end p-3">
                                <span class="text-[10px] font-bold text-white/90 uppercase tracking-widest bg-black/60 px-2 py-1 rounded">Area Foto Suporter</span>
                            </div>
                        </div>

                        <!-- Card Footer Banner -->
                        <div class="mx-3 mt-1.5 p-3 rounded-xl bg-black/80 border border-white/10 text-center">
                            <div class="text-sm font-black uppercase text-white tracking-wide">
                                {{ $event->home_team_name ?? 'PERSITA' }} VS {{ $event->away_team_name ?? 'PERSIJA' }}
                            </div>
                            <div class="text-[9px] font-bold text-slate-300 uppercase mt-0.5">
                                {{ $event->event_start_date ? $event->event_start_date->format('d M Y') : '20 OKTOBER 2026' }} · {{ $event->venue ?? 'INDOMILK ARENA' }}
                            </div>

                            <!-- Fan Name Badge -->
                            <div id="previewNameBadge" class="mt-2 py-1 px-3 rounded-lg text-xs font-black uppercase text-white shadow-sm" style="background-color: {{ $template->primary_color ?? '#5A2D82' }};">
                                BRIAN ARIBOWO
                            </div>

                            <div id="previewBadgeText" class="text-[9px] font-black uppercase mt-1 tracking-wider" style="color: {{ $template->secondary_color ?? '#F95700' }};">
                                {{ $template->badge_text ?? 'MATCHDAY COLLECTIBLE' }}
                            </div>

                            <div id="previewFooterText" class="text-[8px] font-bold text-slate-400 uppercase mt-1">
                                {{ $template->footer_text ?? 'SATU LIGA, SATU SEMANGAT' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-medium">
                        <strong>Tips:</strong> Desain kartu ini otomatis di-render oleh KIOSK saat suporter mengambil foto di venue pertandingan.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateLivePreview() {
            const primaryColor = document.getElementById('primaryColorInput').value;
            const secondaryColor = document.getElementById('secondaryColorInput').value;
            const headerTitle = document.getElementById('headerTitleInput').value || 'I LEAGUE 2026/2027';
            const headerSubtitle = document.getElementById('headerSubtitleInput').value || 'FOOTBALL UNITES US';
            const badgeText = document.getElementById('badgeTextInput').value || 'MATCHDAY COLLECTIBLE';
            const footerText = document.getElementById('footerTextInput').value || 'SATU LIGA, SATU SEMANGAT';

            // Sync texts
            document.getElementById('primaryColorText').value = primaryColor;
            document.getElementById('secondaryColorText').value = secondaryColor;

            // Update Preview Card styles
            const card = document.getElementById('previewCard');
            card.style.backgroundColor = primaryColor;
            card.style.borderColor = secondaryColor;

            document.getElementById('previewHeaderTitle').innerText = headerTitle;
            document.getElementById('previewHeaderSubtitle').innerText = headerSubtitle;
            document.getElementById('previewHeaderSubtitle').style.color = secondaryColor;

            document.getElementById('previewNameBadge').style.backgroundColor = primaryColor;
            document.getElementById('previewBadgeText').innerText = badgeText;
            document.getElementById('previewBadgeText').style.color = secondaryColor;
            document.getElementById('previewFooterText').innerText = footerText;
        }

        function syncColorInput(type, val) {
            if (/^#[0-9A-F]{6}$/i.test(val)) {
                if (type === 'primary') {
                    document.getElementById('primaryColorInput').value = val;
                } else {
                    document.getElementById('secondaryColorInput').value = val;
                }
                updateLivePreview();
            }
        }
    </script>
</x-app-layout>
