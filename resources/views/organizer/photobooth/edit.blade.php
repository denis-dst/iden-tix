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
                <p class="text-sm text-slate-500 font-medium mt-1">Sesuaikan tema, warna klub, header, logo, dan bingkai Matchday Collectible Card untuk Kiosk.</p>
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
            <div class="lg:col-span-7 bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100 space-y-6">
                <!-- Match Context & Logos Badge -->
                <div class="p-4 rounded-2xl bg-orange-50/70 border border-orange-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="flex items-center -space-x-2 shrink-0">
                            @if($event->effective_home_team_logo_url)
                                <img src="{{ $event->effective_home_team_logo_url }}" alt="Home Team" class="w-11 h-11 rounded-full bg-white p-1 shadow-sm border border-orange-200 object-contain">
                            @else
                                <div class="w-11 h-11 rounded-full bg-orange-200 text-orange-800 font-black text-xs flex items-center justify-center border border-white">HOME</div>
                            @endif
                            @if($event->effective_away_team_logo_url)
                                <img src="{{ $event->effective_away_team_logo_url }}" alt="Away Team" class="w-11 h-11 rounded-full bg-white p-1 shadow-sm border border-orange-200 object-contain">
                            @else
                                <div class="w-11 h-11 rounded-full bg-slate-200 text-slate-700 font-black text-xs flex items-center justify-center border border-white">AWAY</div>
                            @endif
                        </div>
                        <div>
                            <div class="text-sm font-black uppercase text-slate-800 tracking-wide">
                                {{ $event->effective_home_team_name }} VS {{ $event->effective_away_team_name }}
                            </div>
                            <div class="text-xs text-slate-600 font-medium">
                                {{ $event->event_start_date ? $event->event_start_date->translatedFormat('d F Y | H:i') . ' WIB' : 'Jadwal belum ditentukan' }} &bull; {{ $event->venue ?? 'Stadion' }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('organizer.events.edit', $event) }}" class="text-xs font-bold text-orange-700 hover:text-orange-900 bg-white px-3.5 py-2 rounded-xl border border-orange-200 shadow-xs shrink-0 text-center transition">
                        Edit Logo & Jadwal &rarr;
                    </a>
                </div>

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
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Contoh: #5A2D82 (Ungu Persita), #F95700 (Oranye)</span>
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
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Format Label Kategori</label>
                            <input type="text" id="categoryLabelFormatInput" name="category_label_format" value="{{ old('category_label_format', $template->category_label_format ?? '{category} • MATCHDAY COLLECTIBLE') }}"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                   placeholder="{category} • MATCHDAY COLLECTIBLE" oninput="updateLivePreview()">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Footer Slogan</label>
                        <input type="text" id="footerTextInput" name="footer_text" value="{{ old('footer_text', $template->footer_text) }}"
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                               placeholder="Contoh: SATU LIGA, SATU SEMANGAT" oninput="updateLivePreview()">
                    </div>

                    <!-- Custom Graphic Assets Upload -->
                    <div class="border-t border-slate-100 pt-6 space-y-4">
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Aset Grafis Custom Pertandingan</h4>
                        
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Custom Frame Overlay (PNG Transparan)</label>
                                @if(!empty($template->frame_overlay))
                                    <span class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Frame Terpasang</span>
                                @else
                                    <span class="text-[10px] font-bold bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full">Belum Diupload</span>
                                @endif
                            </div>
                            
                            <input type="file" name="frame_overlay" id="frameOverlayInput" accept="image/png"
                                   onchange="handleFrameOverlayChange(this)"
                                   class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-500 file:text-white hover:file:bg-orange-600 cursor-pointer">
                            <div class="text-[11px] text-slate-500 font-medium leading-relaxed">
                                Format PNG rasio 2:3 transparan di tengah (seperti <em>Bingkai Matchday Football Unites Us</em>). Frame ini otomatis dipasang di kamera standby kiosk dan hasil foto suporter.
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Background Stadion / Lapangan</label>
                                @if(!empty($template->background_image))
                                    <span class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Background Terpasang</span>
                                @endif
                            </div>
                            <input type="file" name="background_image" id="backgroundImageInput" accept="image/jpeg,image/png,image/webp"
                                   onchange="handleBackgroundImageChange(this)"
                                   class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer">
                            <span class="text-[11px] text-slate-500 font-medium block">Latar belakang atmosfer stadion untuk kartu foto suporter.</span>
                        </div>
                    </div>

                    <!-- Display Toggles -->
                    <div class="border-t border-slate-100 pt-6 space-y-3">
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1">Pengaturan Tampilan Elemen Kartu</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-slate-100 transition">
                                <input type="checkbox" name="show_team_logos" id="toggleTeamLogos" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ $template->show_team_logos ? 'checked' : '' }} onchange="updateLivePreview()">
                                <span class="text-xs font-bold text-slate-700">Tampilkan Logo Klub (Home & Away)</span>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-slate-100 transition">
                                <input type="checkbox" name="show_match_info" id="toggleMatchInfo" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ $template->show_match_info ? 'checked' : '' }} onchange="updateLivePreview()">
                                <span class="text-xs font-bold text-slate-700">Tampilkan Nama Laga & Venue</span>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-slate-100 transition">
                                <input type="checkbox" name="show_fan_name" id="toggleFanName" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ $template->show_fan_name ? 'checked' : '' }} onchange="updateLivePreview()">
                                <span class="text-xs font-bold text-slate-700">Tampilkan Nama Suporter</span>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-slate-100 transition">
                                <input type="checkbox" name="show_category" id="toggleCategory" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ $template->show_category ? 'checked' : '' }} onchange="updateLivePreview()">
                                <span class="text-xs font-bold text-slate-700">Tampilkan Kategori Tiket</span>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-slate-100 transition sm:col-span-2">
                                <input type="checkbox" name="show_qr" id="toggleQr" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ $template->show_qr ? 'checked' : '' }} onchange="updateLivePreview()">
                                <span class="text-xs font-bold text-slate-700">Tampilkan QR Code Download Digital</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="submit" class="px-7 py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-black text-sm rounded-xl shadow-lg shadow-orange-500/20 transition min-h-[48px]">
                            Simpan Perubahan Template
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Interactive Live Card Preview (5 cols) -->
            <div class="lg:col-span-5 flex flex-col items-center">
                <div class="sticky top-6 w-full max-w-sm space-y-4">
                    <div class="flex items-center justify-between px-2">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-slate-700">Live Card Preview</span>
                            <div class="text-[11px] text-slate-500">Hasil real Matchday Collectible Card</div>
                        </div>
                        <span class="text-[10px] font-black bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-full border border-emerald-200">
                            REALTIME
                        </span>
                    </div>

                    <!-- Visual Portrait Matchday Card Preview (Strictly 2:3 Aspect Ratio) -->
                    <div id="previewCard" 
                         class="w-full rounded-3xl overflow-hidden shadow-2xl transition-all duration-300 border-4"
                         style="aspect-ratio: 2/3; position: relative; background: #000000; border-color: {{ $template->secondary_color ?? '#F95700' }};">
                        
                        <!-- Layer 1: Background Stadion / Gradient -->
                        <img id="previewBgImage" 
                             src="{{ !empty($template->background_image) ? asset('storage/' . $template->background_image) : asset('images/kiosk/stadium_bg.jpg') }}" 
                             alt="Background" 
                             class="absolute inset-0 w-full h-full object-cover">
                        <div id="previewBgGradient" class="absolute inset-0 bg-gradient-to-b from-black/70 via-transparent to-black/95"></div>

                        <!-- Layer 2: Fan Photo (Sample Supporter) -->
                        <div id="previewFanPhotoWrap" class="absolute inset-0 flex items-center justify-center p-6 z-0">
                            <img id="previewFanPhoto" src="{{ asset('images/kiosk/matchday_card_sample.jpg') }}" alt="Fan Photo" class="w-full h-full object-cover rounded-2xl shadow-inner">
                        </div>

                        <!-- Layer 3: Custom Frame Overlay (Rendered directly on top of photo) -->
                        @php
                            $frameUrl = !empty($template->frame_overlay) ? asset('storage/' . $template->frame_overlay) : '';
                        @endphp
                        <img id="previewFrameOverlay" 
                             src="{{ $frameUrl }}" 
                             alt="Custom Frame Overlay" 
                             class="absolute inset-0 w-full h-full object-fill pointer-events-none z-10"
                             style="{{ empty($frameUrl) ? 'display: none;' : '' }}">

                        <!-- Layer 4A: Top Header (Standard or when no custom frame) -->
                        <div id="previewTopHeader" class="absolute top-0 inset-x-0 pt-4 pb-2 text-center z-20 pointer-events-none" style="text-shadow: 0 2px 8px rgba(0,0,0,0.9);">
                            <div id="previewHeaderTitle" class="text-sm font-black tracking-wider uppercase text-white">{{ $template->header_title ?? ($event->season_name ?: 'I LEAGUE 2026/2027') }}</div>
                            <div id="previewHeaderSubtitle" class="text-[10px] font-black tracking-widest uppercase mt-0.5" style="color: {{ $template->secondary_color ?? '#F95700' }};">{{ $template->header_subtitle ?? 'FOOTBALL UNITES US' }}</div>
                        </div>

                        <!-- Layer 4B: Bottom Card Details (Logos, Matchday Info, Fan Name, Badge, QR) -->
                        <div id="previewBottomDetails" class="absolute bottom-0 inset-x-0 p-3.5 z-20 pointer-events-none">
                            <div class="rounded-2xl p-3 text-center backdrop-blur-md border border-white/10" style="background: rgba(10, 8, 20, 0.88); box-shadow: 0 8px 30px rgba(0,0,0,0.8);">
                                
                                <!-- Team Logos Row -->
                                <div id="previewLogosRow" class="flex items-center justify-center gap-3 mb-1.5" style="{{ $template->show_team_logos ? '' : 'display: none;' }}">
                                    @if($event->effective_home_team_logo_url)
                                        <img src="{{ $event->effective_home_team_logo_url }}" class="w-8 h-8 object-contain filter drop-shadow">
                                    @endif
                                    <span class="text-xs font-black text-amber-400 font-mono">VS</span>
                                    @if($event->effective_away_team_logo_url)
                                        <img src="{{ $event->effective_away_team_logo_url }}" class="w-8 h-8 object-contain filter drop-shadow">
                                    @endif
                                </div>

                                <!-- Match Title & Date / Venue -->
                                <div id="previewMatchInfoRow" style="{{ $template->show_match_info ? '' : 'display: none;' }}">
                                    <div class="text-xs font-black uppercase text-white tracking-wide" id="previewMatchTitle">
                                        {{ $event->effective_home_team_name }} VS {{ $event->effective_away_team_name }}
                                    </div>
                                    <div class="text-[9px] font-bold text-slate-300 uppercase mt-0.5">
                                        {{ $event->event_start_date ? $event->event_start_date->translatedFormat('d M Y') : '29 NOV 2026' }} &bull; {{ $event->venue ?? 'INDOMILK ARENA' }}
                                    </div>
                                </div>

                                <!-- Fan Name Ribbon -->
                                <div id="previewFanNameWrap" class="mt-2" style="{{ $template->show_fan_name ? '' : 'display: none;' }}">
                                    <div id="previewFanNameBadge" class="py-1 px-3 rounded-lg text-xs font-black uppercase text-white shadow-md inline-block min-w-[140px]" style="background-color: {{ $template->primary_color ?? '#5A2D82' }}; border: 1px solid {{ $template->secondary_color ?? '#F95700' }};">
                                        BRIAN ARIBOWO
                                    </div>
                                </div>

                                <!-- Category & Collectible Sub-badge -->
                                <div id="previewCategoryWrap" class="mt-1" style="{{ $template->show_category ? '' : 'display: none;' }}">
                                    <div id="previewBadgeText" class="text-[9px] font-black uppercase tracking-wider" style="color: {{ $template->secondary_color ?? '#F95700' }};">
                                        VIP BARAT &bull; {{ $template->badge_text ?? 'MATCHDAY COLLECTIBLE' }}
                                    </div>
                                </div>

                                <!-- Footer Slogan & QR Code Row -->
                                <div class="mt-2 pt-1.5 border-t border-white/10 flex items-center justify-between px-1">
                                    <div id="previewFooterText" class="text-[8px] font-bold text-slate-400 uppercase tracking-tight text-left">
                                        {{ $template->footer_text ?? 'SATU LIGA, SATU SEMANGAT' }}
                                    </div>
                                    <div id="previewQrWrap" class="flex items-center gap-1 shrink-0" style="{{ $template->show_qr ? '' : 'display: none;' }}">
                                        <div class="w-5 h-5 bg-white rounded p-0.5">
                                            <svg viewBox="0 0 100 100" fill="black"><rect width="100" height="100" fill="white"/><rect x="10" y="10" width="30" height="30" fill="black"/><rect x="15" y="15" width="20" height="20" fill="white"/><rect x="60" y="10" width="30" height="30" fill="black"/><rect x="10" y="60" width="30" height="30" fill="black"/><rect x="60" y="60" width="30" height="30" fill="black"/></svg>
                                        </div>
                                        <span class="text-[7px] font-black text-slate-400 uppercase">SCAN</span>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <!-- Guidance Info -->
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-950 text-xs space-y-1.5">
                        <div class="font-black flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            <span>Informasi Realtime Preview</span>
                        </div>
                        <p class="text-slate-600 leading-relaxed">
                            Preview di atas mencerminkan kartu yang akan ditampilkan di KIOSK dan dikirim ke suporter. Jika frame overlay diunggah, bingkai PNG tersebut akan menutupi live feed kamera dan kartu foto secara pas.
                        </p>
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
            const formatCat = document.getElementById('categoryLabelFormatInput').value || '{category} • MATCHDAY COLLECTIBLE';
            const footerText = document.getElementById('footerTextInput').value || 'SATU LIGA, SATU SEMANGAT';

            // Sync texts
            document.getElementById('primaryColorText').value = primaryColor;
            document.getElementById('secondaryColorText').value = secondaryColor;

            // Update Card Border & Badge Styles
            const card = document.getElementById('previewCard');
            card.style.borderColor = secondaryColor;

            document.getElementById('previewHeaderTitle').innerText = headerTitle;
            document.getElementById('previewHeaderSubtitle').innerText = headerSubtitle;
            document.getElementById('previewHeaderSubtitle').style.color = secondaryColor;

            const fanBadge = document.getElementById('previewFanNameBadge');
            if (fanBadge) {
                fanBadge.style.backgroundColor = primaryColor;
                fanBadge.style.borderColor = secondaryColor;
            }

            const badgeTextEl = document.getElementById('previewBadgeText');
            if (badgeTextEl) {
                badgeTextEl.innerText = formatCat.replace('{category}', 'VIP BARAT');
                badgeTextEl.style.color = secondaryColor;
            }

            document.getElementById('previewFooterText').innerText = footerText;

            // Visibility Toggles
            document.getElementById('previewLogosRow').style.display = document.getElementById('toggleTeamLogos').checked ? 'flex' : 'none';
            document.getElementById('previewMatchInfoRow').style.display = document.getElementById('toggleMatchInfo').checked ? 'block' : 'none';
            document.getElementById('previewFanNameWrap').style.display = document.getElementById('toggleFanName').checked ? 'block' : 'none';
            document.getElementById('previewCategoryWrap').style.display = document.getElementById('toggleCategory').checked ? 'block' : 'none';
            document.getElementById('previewQrWrap').style.display = document.getElementById('toggleQr').checked ? 'flex' : 'none';
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

        // Live preview for uploaded custom PNG Frame Overlay
        function handleFrameOverlayChange(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const frameImg = document.getElementById('previewFrameOverlay');
                    frameImg.src = e.target.result;
                    frameImg.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Live preview for uploaded background image
        function handleBackgroundImageChange(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const bgImg = document.getElementById('previewBgImage');
                    bgImg.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-app-layout>