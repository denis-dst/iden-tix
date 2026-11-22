<x-app-layout>
    <x-slot name="title">Kustomisasi Template Photobooth - {{ $event->name }}</x-slot>

    @php
        $layoutConfig = $template->getEffectiveLayoutConfig();
        $hasFrame = !empty($template->frame_overlay) && file_exists(public_path('storage/' . $template->frame_overlay));
        $hasBg = !empty($template->background_image) && file_exists(public_path('storage/' . $template->background_image));
    @endphp

    <style>
        .drag-item {
            position: absolute;
            transform: translate(-50%, -50%);
            cursor: grab;
            user-select: none;
            touch-action: none;
            transition: box-shadow 0.15s ease, outline 0.15s ease;
            z-index: 30;
        }
        .drag-item:active, .drag-item.is-dragging {
            cursor: grabbing !important;
            z-index: 50 !important;
        }
        .drag-item.is-selected {
            outline: 2px dashed #f97316 !important;
            outline-offset: 4px;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.25);
        }
        .drag-item:hover {
            outline: 1.5px dashed rgba(255, 255, 255, 0.7);
            outline-offset: 2px;
        }
        #centerGuideLine {
            display: none;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 1px;
            border-left: 1.5px dashed #f97316;
            z-index: 25;
            pointer-events: none;
        }
        #centerGuideLine.active {
            display: block;
        }
    </style>

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
                <p class="text-sm text-slate-500 font-medium mt-1">Sesuaikan tema, logo klub, bingkai Matchday Collectible, dan atur posisi setiap kolom dengan drag and drop.</p>
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

        @if(isset($errors) && $errors->any())
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

                <form id="photoboothForm" action="{{ route('organizer.photobooth.update', $event) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Hidden Layout Config JSON -->
                    <input type="hidden" name="layout_config" id="layoutConfigInput" value="{{ json_encode($layoutConfig) }}">
                    <!-- Hidden flags for permanent deletion on save -->
                    <input type="hidden" name="remove_frame_overlay" id="removeFrameOverlayInput" value="0">
                    <input type="hidden" name="remove_background_image" id="removeBackgroundImageInput" value="0">

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
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Warna dasar pita nama & background</span>
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
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Warna highlight border, VS badge & teks</span>
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
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Aset Grafis Custom Pertandingan</h4>
                            <button type="button" onclick="resetAllImagesToPlain()" 
                                    class="text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-xl border border-red-200 transition flex items-center gap-1.5 shadow-xs"
                                    title="Hilangkan semua frame dan gambar agar preview kartu bersih polos">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                <span>Reset Semua Gambar (Polos)</span>
                            </button>
                        </div>

                        <!-- Custom Frame Overlay -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Custom Frame Overlay (PNG Transparan)</label>
                                <div class="flex items-center gap-2">
                                    <span id="badgeFrameStatus" class="text-[10px] font-bold {{ $hasFrame ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }} px-2.5 py-0.5 rounded-full">
                                        {{ $hasFrame ? 'Frame Terpasang' : 'Belum Diupload' }}
                                    </span>
                                    <button type="button" id="btnResetFrame" onclick="resetFrameOverlay(true)" 
                                            class="text-[11px] font-bold text-red-600 hover:text-red-700 bg-white hover:bg-red-50 px-2.5 py-1 rounded-lg border border-red-200 transition flex items-center gap-1 shadow-xs {{ $hasFrame ? '' : 'hidden' }}"
                                            title="Hilangkan frame overlay">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        <span>Reset Frame</span>
                                    </button>
                                </div>
                            </div>
                            
                            <input type="file" name="frame_overlay" id="frameOverlayInput" accept="image/png"
                                   onchange="handleFrameOverlayChange(this)"
                                   class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-500 file:text-white hover:file:bg-orange-600 cursor-pointer">
                            <div class="text-[11px] text-slate-500 font-medium leading-relaxed">
                                Format PNG rasio 2:3 transparan di tengah (seperti <em>Bingkai Matchday Football Unites Us</em>). Frame ini otomatis dipasang di kamera standby kiosk dan hasil foto suporter.
                            </div>
                        </div>

                        <!-- Background Stadion -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Background Stadion / Lapangan</label>
                                <div class="flex items-center gap-2">
                                    <span id="badgeBgStatus" class="text-[10px] font-bold {{ $hasBg ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }} px-2.5 py-0.5 rounded-full">
                                        {{ $hasBg ? 'Background Terpasang' : 'Belum Diupload' }}
                                    </span>
                                    <button type="button" id="btnResetBg" onclick="resetBackgroundImage(true)" 
                                            class="text-[11px] font-bold text-red-600 hover:text-red-700 bg-white hover:bg-red-50 px-2.5 py-1 rounded-lg border border-red-200 transition flex items-center gap-1 shadow-xs {{ $hasBg ? '' : 'hidden' }}"
                                            title="Hilangkan background stadion">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        <span>Reset Background</span>
                                    </button>
                                </div>
                            </div>
                            <input type="file" name="background_image" id="backgroundImageInput" accept="image/jpeg,image/png,image/webp"
                                   onchange="handleBackgroundImageChange(this)"
                                   class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer">
                            <span class="text-[11px] text-slate-500 font-medium block">Latar belakang atmosfer stadion untuk kartu foto suporter.</span>
                        </div>

                        <!-- Quick Logo Uploads for Matchday -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Logo Pertandingan & Tim (Home, Away & Penyelenggara)</label>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- League / Organizer Logo -->
                                <div class="p-3 rounded-xl bg-white border border-slate-200 space-y-2">
                                    <div class="text-[11px] font-black uppercase text-slate-700 flex items-center justify-between">
                                        <span>Logo Liga / Turnamen</span>
                                        @if($event->effective_league_logo_url)
                                            <span class="text-[9px] text-emerald-600 font-bold">Ada</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center p-1 shrink-0 overflow-hidden">
                                            <img id="formPreviewLeagueLogo" src="{{ $event->effective_league_logo_url ?: asset('images/kiosk/default_league_logo.png') }}" class="w-full h-full object-contain">
                                        </div>
                                        <input type="file" name="league_logo" accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer" onchange="previewUploadImage(this, 'formPreviewLeagueLogo', 'dragImg_league_logo')">
                                    </div>
                                </div>

                                <!-- Home Team Logo -->
                                <div class="p-3 rounded-xl bg-white border border-slate-200 space-y-2">
                                    <div class="text-[11px] font-black uppercase text-slate-700 flex items-center justify-between">
                                        <span>Logo Home ({{ $event->effective_home_team_name }})</span>
                                        @if($event->effective_home_team_logo_url)
                                            <span class="text-[9px] text-emerald-600 font-bold">Ada</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center p-1 shrink-0 overflow-hidden">
                                            <img id="formPreviewHomeLogo" src="{{ $event->effective_home_team_logo_url ?: '' }}" class="w-full h-full object-contain" style="{{ $event->effective_home_team_logo_url ? '' : 'display:none;' }}">
                                            <span id="formPlaceholderHomeLogo" class="text-[10px] font-black text-slate-400" style="{{ $event->effective_home_team_logo_url ? 'display:none;' : '' }}">HOME</span>
                                        </div>
                                        <input type="file" name="home_team_logo" accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer" onchange="previewUploadImage(this, 'formPreviewHomeLogo', 'dragImg_home_logo', 'formPlaceholderHomeLogo')">
                                    </div>
                                </div>

                                <!-- Away Team Logo -->
                                <div class="p-3 rounded-xl bg-white border border-slate-200 space-y-2">
                                    <div class="text-[11px] font-black uppercase text-slate-700 flex items-center justify-between">
                                        <span>Logo Away ({{ $event->effective_away_team_name }})</span>
                                        @if($event->effective_away_team_logo_url)
                                            <span class="text-[9px] text-emerald-600 font-bold">Ada</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center p-1 shrink-0 overflow-hidden">
                                            <img id="formPreviewAwayLogo" src="{{ $event->effective_away_team_logo_url ?: '' }}" class="w-full h-full object-contain" style="{{ $event->effective_away_team_logo_url ? '' : 'display:none;' }}">
                                            <span id="formPlaceholderAwayLogo" class="text-[10px] font-black text-slate-400" style="{{ $event->effective_away_team_logo_url ? 'display:none;' : '' }}">AWAY</span>
                                        </div>
                                        <input type="file" name="away_team_logo" accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer" onchange="previewUploadImage(this, 'formPreviewAwayLogo', 'dragImg_away_logo', 'formPlaceholderAwayLogo')">
                                    </div>
                                </div>
                            </div>
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

            <!-- Right: Interactive Live Card Preview & Drag-and-Drop Editor (5 cols) -->
            <div class="lg:col-span-5 flex flex-col items-center">
                <div class="sticky top-6 w-full max-w-sm space-y-4">
                    <div class="flex items-center justify-between px-2">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-slate-700">Live Card & Layout Editor</span>
                            <div class="text-[11px] text-slate-500">Geser (drag) setiap elemen untuk mengatur posisi</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" id="btnToggleCleanCard" onclick="toggleCleanPreview()" 
                                    class="text-[11px] font-bold px-2.5 py-1 rounded-full border transition flex items-center gap-1 bg-white hover:bg-slate-100 text-slate-700 border-slate-300 shadow-xs"
                                    title="Tampilkan contoh foto suporter atau biarkan bersih polos">
                                <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span id="lblCleanPreview">Contoh Foto</span>
                            </button>
                            <span class="text-[10px] font-black bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-full border border-emerald-200">
                                REALTIME
                            </span>
                        </div>
                    </div>

                    <!-- Reset Toast Notification -->
                    <div id="resetToastNotice" class="hidden w-full p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold text-center animate-in fade-in transition">
                        Semua gambar telah direset. Live Card Preview sekarang bersih polos.
                    </div>

                    <!-- Visual Portrait Matchday Card Preview (Strictly 2:3 Aspect Ratio) -->
                    <div id="previewCard" 
                         class="w-full rounded-3xl overflow-hidden shadow-2xl transition-all duration-300 border-4 relative"
                         style="aspect-ratio: 2/3; background: radial-gradient(circle at 50% 30%, #1e1338 0%, #0d0818 60%, #000000 100%); border-color: {{ $template->secondary_color ?? '#F95700' }}; --card-w: 380px;">
                        
                        <!-- Layer 1: Background Stadion / Gradient -->
                        <img id="previewBgImage" 
                             src="{{ $hasBg ? asset('storage/' . $template->background_image) : '' }}" 
                             alt="Background" 
                             class="absolute inset-0 w-full h-full object-cover pointer-events-none"
                             style="{{ $hasBg ? '' : 'display: none;' }}">
                        <div id="previewBgGradient" class="absolute inset-0 bg-gradient-to-b from-black/70 via-transparent to-black/95 pointer-events-none" style="{{ $hasBg ? '' : 'display: none;' }}"></div>

                        <!-- Layer 2: Clean Fan Face Photo (Sample Supporter - ONLY shown when toggled on) -->
                        <div id="previewFanPhotoWrap" class="absolute inset-0 flex items-center justify-center p-6 z-0 pointer-events-none" style="display: none;">
                            <img id="previewFanPhoto" src="{{ asset('images/kiosk/clean_fan_photo.jpg') }}" alt="Fan Photo" class="w-full h-full object-cover rounded-2xl shadow-inner">
                        </div>

                        <!-- Layer 2B: Clean Empty Wireframe Placeholder (Default Clean / Polos) -->
                        <div id="previewCleanPlaceholder" class="absolute inset-x-4 top-20 bottom-36 rounded-2xl border-2 border-dashed border-white/20 bg-white/5 flex flex-col items-center justify-center p-6 text-center pointer-events-none z-0">
                            <div class="w-12 h-12 rounded-xl border border-white/20 bg-white/10 flex items-center justify-center mb-2 shadow-inner">
                                <svg class="w-6 h-6 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div class="text-[11px] font-black text-white/70 uppercase tracking-widest">AREA LIVE FEED KAMERA KIOSK</div>
                            <div class="text-[9px] text-white/40 mt-1">Foto suporter & bingkai custom akan ditampilkan di sini</div>
                        </div>

                        <!-- Layer 3: Custom Frame Overlay (Rendered directly on top of photo) -->
                        <img id="previewFrameOverlay" 
                             src="{{ $hasFrame ? asset('storage/' . $template->frame_overlay) : '' }}" 
                             alt="Custom Frame Overlay" 
                             class="absolute inset-0 w-full h-full object-fill pointer-events-none z-10"
                             style="{{ $hasFrame ? '' : 'display: none;' }}">

                        <!-- Center Guideline (Snaps at X: 50%) -->
                        <div id="centerGuideLine"></div>

                        <!-- ========================================================
                             LAYER 4: DRAGGABLE MATCHDAY CARD COLUMNS & COMPONENTS
                             Each element uses percentage position (x, y) with center anchor
                             ======================================================== -->

                        <!-- 1. League / Organizer Logo -->
                        <div id="dragItem_league_logo" 
                             class="drag-item"
                             data-key="league_logo"
                             data-title="Logo Turnamen / Liga"
                             data-type="size"
                             title="Geser posisi Logo Turnamen/Liga"
                             style="left: {{ $layoutConfig['league_logo']['x'] ?? 15 }}%; top: {{ $layoutConfig['league_logo']['y'] ?? 9 }}%; width: calc(var(--card-w) * ({{ $layoutConfig['league_logo']['size'] ?? 65 }} / 400)); height: calc(var(--card-w) * ({{ $layoutConfig['league_logo']['size'] ?? 65 }} / 400)); display: {{ ($layoutConfig['league_logo']['visible'] ?? true) ? 'block' : 'none' }};">
                            <img id="dragImg_league_logo" 
                                 src="{{ $event->effective_league_logo_url ?: asset('images/kiosk/default_league_logo.png') }}" 
                                 alt="League Logo" 
                                 class="w-full h-full object-contain filter drop-shadow">
                        </div>

                        <!-- 2. Header Slogan (Liga & Football Unites Us) -->
                        @php
                            $hasHeaderTitle = !empty(trim($template->header_title ?? ''));
                            $hasHeaderSubtitle = !empty(trim($template->header_subtitle ?? ''));
                            $showHeaderSlogan = ($hasHeaderTitle || $hasHeaderSubtitle) && ($layoutConfig['header_slogan']['visible'] ?? true);
                        @endphp
                        <div id="dragItem_header_slogan" 
                             class="drag-item text-center pointer-events-auto"
                             data-key="header_slogan"
                             data-title="Header Liga / Turnamen"
                             data-type="font_size"
                             title="Geser posisi Header Liga"
                             style="left: {{ $layoutConfig['header_slogan']['x'] ?? 50 }}%; top: {{ $layoutConfig['header_slogan']['y'] ?? 5 }}%; font-size: calc(var(--card-w) * ({{ $layoutConfig['header_slogan']['font_size'] ?? 14 }} / 400)); display: {{ $showHeaderSlogan ? 'block' : 'none' }};">
                            <div id="previewHeaderTitle" class="font-black uppercase tracking-wider text-white drop-shadow whitespace-nowrap leading-tight" style="{{ $hasHeaderTitle ? '' : 'display:none;' }}">
                                {{ $template->header_title }}
                            </div>
                            <div id="previewHeaderSubtitle" class="font-black tracking-widest uppercase drop-shadow text-[0.7em] leading-tight" style="color: {{ $template->secondary_color ?? '#F95700' }}; {{ $hasHeaderSubtitle ? '' : 'display:none;' }}">
                                {{ $template->header_subtitle }}
                            </div>
                        </div>

                        <!-- 3. Home Team Logo -->
                        <div id="dragItem_home_logo" 
                             class="drag-item"
                             data-key="home_logo"
                             data-title="Logo Tim Tuan Rumah (Home)"
                             data-type="size"
                             title="Geser posisi Logo Klub Tuan Rumah"
                             style="left: {{ $layoutConfig['home_logo']['x'] ?? 24 }}%; top: {{ $layoutConfig['home_logo']['y'] ?? 69 }}%; width: calc(var(--card-w) * ({{ $layoutConfig['home_logo']['size'] ?? 85 }} / 400)); height: calc(var(--card-w) * ({{ $layoutConfig['home_logo']['size'] ?? 85 }} / 400)); display: {{ ($template->show_team_logos && ($layoutConfig['home_logo']['visible'] ?? true)) ? 'block' : 'none' }};">
                            <img id="dragImg_home_logo" 
                                 src="{{ $event->effective_home_team_logo_url ?: '' }}" 
                                 alt="Home Logo" 
                                 class="w-full h-full object-contain filter drop-shadow-lg"
                                 style="{{ $event->effective_home_team_logo_url ? '' : 'display:none;' }}">
                            <div id="dragPlaceholder_home_logo" class="w-full h-full rounded-full bg-purple-900 border-2 border-orange-500 text-white font-black flex items-center justify-center text-[10px] shadow-lg" style="{{ $event->effective_home_team_logo_url ? 'display:none;' : '' }}">
                                HOME
                            </div>
                        </div>

                        <!-- 4. Home Team Name -->
                        <div id="dragItem_home_name" 
                             class="drag-item text-center pointer-events-auto"
                             data-key="home_name"
                             data-title="Nama Klub Home"
                             data-type="font_size"
                             title="Geser posisi Nama Klub Home"
                             style="left: {{ $layoutConfig['home_name']['x'] ?? 24 }}%; top: {{ $layoutConfig['home_name']['y'] ?? 77 }}%; font-size: calc(var(--card-w) * ({{ $layoutConfig['home_name']['font_size'] ?? 14 }} / 400)); display: {{ ($template->show_team_logos && ($layoutConfig['home_name']['visible'] ?? true)) ? 'block' : 'none' }};">
                            <div class="font-black uppercase tracking-wider text-white drop-shadow-lg whitespace-nowrap leading-none">
                                {{ $event->effective_home_team_name }}
                            </div>
                        </div>

                        <!-- 5. VS Badge -->
                        <div id="dragItem_vs_badge" 
                             class="drag-item text-center pointer-events-auto"
                             data-key="vs_badge"
                             data-title="Badge VS"
                             data-type="font_size"
                             title="Geser posisi Badge VS"
                             style="left: {{ $layoutConfig['vs_badge']['x'] ?? 50 }}%; top: {{ $layoutConfig['vs_badge']['y'] ?? 74 }}%; font-size: calc(var(--card-w) * ({{ $layoutConfig['vs_badge']['font_size'] ?? 22 }} / 400)); display: {{ ($template->show_team_logos && ($layoutConfig['vs_badge']['visible'] ?? true)) ? 'block' : 'none' }};">
                            <div class="font-black font-mono text-amber-400 drop-shadow-lg tracking-wider leading-none">
                                VS
                            </div>
                        </div>

                        <!-- 6. Away Team Logo -->
                        <div id="dragItem_away_logo" 
                             class="drag-item"
                             data-key="away_logo"
                             data-title="Logo Tim Tamu (Away)"
                             data-type="size"
                             title="Geser posisi Logo Klub Tamu"
                             style="left: {{ $layoutConfig['away_logo']['x'] ?? 76 }}%; top: {{ $layoutConfig['away_logo']['y'] ?? 69 }}%; width: calc(var(--card-w) * ({{ $layoutConfig['away_logo']['size'] ?? 85 }} / 400)); height: calc(var(--card-w) * ({{ $layoutConfig['away_logo']['size'] ?? 85 }} / 400)); display: {{ ($template->show_team_logos && ($layoutConfig['away_logo']['visible'] ?? true)) ? 'block' : 'none' }};">
                            <img id="dragImg_away_logo" 
                                 src="{{ $event->effective_away_team_logo_url ?: '' }}" 
                                 alt="Away Logo" 
                                 class="w-full h-full object-contain filter drop-shadow-lg"
                                 style="{{ $event->effective_away_team_logo_url ? '' : 'display:none;' }}">
                            <div id="dragPlaceholder_away_logo" class="w-full h-full rounded-full bg-blue-900 border-2 border-orange-500 text-white font-black flex items-center justify-center text-[10px] shadow-lg" style="{{ $event->effective_away_team_logo_url ? 'display:none;' : '' }}">
                                AWAY
                            </div>
                        </div>

                        <!-- 7. Away Team Name -->
                        <div id="dragItem_away_name" 
                             class="drag-item text-center pointer-events-auto"
                             data-key="away_name"
                             data-title="Nama Klub Away"
                             data-type="font_size"
                             title="Geser posisi Nama Klub Away"
                             style="left: {{ $layoutConfig['away_name']['x'] ?? 76 }}%; top: {{ $layoutConfig['away_name']['y'] ?? 77 }}%; font-size: calc(var(--card-w) * ({{ $layoutConfig['away_name']['font_size'] ?? 14 }} / 400)); display: {{ ($template->show_team_logos && ($layoutConfig['away_name']['visible'] ?? true)) ? 'block' : 'none' }};">
                            <div class="font-black uppercase tracking-wider text-white drop-shadow-lg whitespace-nowrap leading-none">
                                {{ $event->effective_away_team_name }}
                            </div>
                        </div>

                        <!-- 8. Category Badge -->
                        @php
                            $hasCatFormat = !empty(trim($template->category_label_format ?? ''));
                            $showCategoryBadge = $template->show_category && $hasCatFormat && ($layoutConfig['category_badge']['visible'] ?? true);
                        @endphp
                        <div id="dragItem_category_badge" 
                             class="drag-item text-center pointer-events-auto"
                             data-key="category_badge"
                             data-title="Badge Kategori Tiket"
                             data-type="font_size"
                             title="Geser posisi Badge Kategori"
                             style="left: {{ $layoutConfig['category_badge']['x'] ?? 50 }}%; top: {{ $layoutConfig['category_badge']['y'] ?? 82 }}%; font-size: calc(var(--card-w) * ({{ $layoutConfig['category_badge']['font_size'] ?? 10 }} / 400)); display: {{ $showCategoryBadge ? 'block' : 'none' }};">
                            <div id="previewBadgeText" class="font-black uppercase tracking-wider drop-shadow-md whitespace-nowrap leading-none" style="color: {{ $template->secondary_color ?? '#F95700' }};">
                                {{ str_replace('{category}', 'VIP BARAT', $template->category_label_format ?? '') }}
                            </div>
                        </div>

                        <!-- 9. Fan Name Ribbon -->
                        <div id="dragItem_fan_name" 
                             class="drag-item text-center pointer-events-auto"
                             data-key="fan_name"
                             data-title="Pita Nama Suporter"
                             data-type="font_size"
                             title="Geser posisi Pita Nama Suporter"
                             style="left: {{ $layoutConfig['fan_name']['x'] ?? 50 }}%; top: {{ $layoutConfig['fan_name']['y'] ?? 86 }}%; font-size: calc(var(--card-w) * ({{ $layoutConfig['fan_name']['font_size'] ?? 18 }} / 400)); display: {{ ($template->show_fan_name && ($layoutConfig['fan_name']['visible'] ?? true)) ? 'block' : 'none' }};">
                            <div id="previewFanNameBadge" class="py-1 px-4 rounded-xl font-black uppercase text-white shadow-2xl drop-shadow-lg whitespace-nowrap leading-none border" style="background-color: {{ $template->primary_color ?? '#5A2D82' }}; border-color: {{ $template->secondary_color ?? '#F95700' }};">
                                BRIAN ARIBOWO
                            </div>
                        </div>

                        <!-- 10. Match Details (Date & Venue) -->
                        <div id="dragItem_match_info" 
                             class="drag-item text-center pointer-events-auto"
                             data-key="match_info"
                             data-title="Detail Laga (Tanggal & Venue)"
                             data-type="font_size"
                             title="Geser posisi Jadwal & Venue"
                             style="left: {{ $layoutConfig['match_info']['x'] ?? 50 }}%; top: {{ $layoutConfig['match_info']['y'] ?? 92 }}%; font-size: calc(var(--card-w) * ({{ $layoutConfig['match_info']['font_size'] ?? 11 }} / 400)); display: {{ ($template->show_match_info && ($layoutConfig['match_info']['visible'] ?? true)) ? 'block' : 'none' }};">
                            <div class="font-black uppercase tracking-wider text-slate-100 drop-shadow-lg whitespace-nowrap leading-none">
                                {{ $event->event_start_date ? $event->event_start_date->translatedFormat('d M Y') : '29 NOV 2026' }} &bull; {{ $event->venue ?? 'INDOMILK ARENA' }}
                            </div>
                        </div>

                        <!-- 11. QR Code Area -->
                        <div id="dragItem_qr_code" 
                             class="drag-item"
                             data-key="qr_code"
                             data-title="QR Code Download"
                             data-type="size"
                             title="Geser posisi QR Code"
                             style="left: {{ $layoutConfig['qr_code']['x'] ?? 88 }}%; top: {{ $layoutConfig['qr_code']['y'] ?? 92 }}%; width: calc(var(--card-w) * ({{ $layoutConfig['qr_code']['size'] ?? 48 }} / 400)); height: calc(var(--card-w) * ({{ $layoutConfig['qr_code']['size'] ?? 48 }} / 400)); display: {{ ($template->show_qr && ($layoutConfig['qr_code']['visible'] ?? true)) ? 'block' : 'none' }};">
                            <div class="w-full h-full bg-white rounded-lg p-1 shadow-2xl flex flex-col items-center justify-center">
                                <svg class="w-full h-full" viewBox="0 0 100 100" fill="black"><rect width="100" height="100" fill="white"/><rect x="10" y="10" width="30" height="30" fill="black"/><rect x="15" y="15" width="20" height="20" fill="white"/><rect x="60" y="10" width="30" height="30" fill="black"/><rect x="10" y="60" width="30" height="30" fill="black"/><rect x="60" y="60" width="30" height="30" fill="black"/></svg>
                            </div>
                        </div>

                        <!-- 12. Footer Slogan -->
                        @php
                            $hasFooterText = !empty(trim($template->footer_text ?? ''));
                            $showFooterText = $hasFooterText && ($layoutConfig['footer_text']['visible'] ?? true);
                        @endphp
                        <div id="dragItem_footer_text" 
                             class="drag-item text-center pointer-events-auto"
                             data-key="footer_text"
                             data-title="Footer Slogan Bawah"
                             data-type="font_size"
                             title="Geser posisi Slogan Bawah"
                             style="left: {{ $layoutConfig['footer_text']['x'] ?? 50 }}%; top: {{ $layoutConfig['footer_text']['y'] ?? 97 }}%; font-size: calc(var(--card-w) * ({{ $layoutConfig['footer_text']['font_size'] ?? 9 }} / 400)); display: {{ $showFooterText ? 'block' : 'none' }};">
                            <div id="previewFooterText" class="font-bold uppercase tracking-wider text-slate-300 drop-shadow whitespace-nowrap leading-none">
                                {{ $template->footer_text }}
                            </div>
                        </div>

                    </div>

                    <!-- ========================================================
                         DRAG & DROP TOOLBAR & ACTIVE ELEMENT INSPECTOR
                         ======================================================== -->
                    <div class="w-full bg-white rounded-2xl p-4 shadow-sm border border-slate-200 space-y-3.5">
                        <!-- Toolbar Quick Actions -->
                        <div class="flex items-center justify-between gap-2">
                            <button type="button" id="btnSaveLayout" onclick="saveLayoutConfig()"
                                    class="flex-1 py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 min-h-[44px]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                <span>Simpan Posisi Kolom</span>
                            </button>

                            <button type="button" id="btnResetLayout" onclick="resetLayoutConfig()"
                                    class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 min-h-[44px]"
                                    title="Kembalikan semua posisi ke default template matchday">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Reset Default</span>
                            </button>
                        </div>

                        <!-- Active Element Inspector -->
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-black uppercase text-slate-700">Elemen Terpilih:</label>
                                <span id="inspectorCoordBadge" class="text-[11px] font-mono font-black text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md border border-orange-200">
                                    X: 50.0% | Y: 86.0%
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <select id="inspectorElementSelect" class="flex-1 text-xs font-bold text-slate-800 bg-white border border-slate-300 rounded-xl px-3 py-2" onchange="selectElementByKey(this.value)">
                                    <option value="fan_name">Pita Nama Suporter</option>
                                    <option value="home_logo">Logo Klub Home</option>
                                    <option value="away_logo">Logo Klub Away</option>
                                    <option value="vs_badge">Badge VS</option>
                                    <option value="home_name">Nama Klub Home</option>
                                    <option value="away_name">Nama Klub Away</option>
                                    <option value="match_info">Detail Jadwal & Venue</option>
                                    <option value="category_badge">Badge Kategori Tiket</option>
                                    <option value="league_logo">Logo Turnamen / Liga</option>
                                    <option value="header_slogan">Header Liga & Slogan</option>
                                    <option value="qr_code">QR Code Download</option>
                                    <option value="footer_text">Slogan Footer Bawah</option>
                                </select>

                                <button type="button" onclick="snapActiveElementToCenterX()" class="p-2 bg-white hover:bg-orange-50 text-slate-700 hover:text-orange-600 border border-slate-300 rounded-xl text-xs font-bold transition shrink-0" title="Pusatkan di Tengah Secara Horizontal (X: 50%)">
                                    X: 50%
                                </button>
                            </div>

                            <!-- Size / Font Size Slider -->
                            <div class="space-y-1 pt-1">
                                <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                                    <span id="lblSizeProperty">Ukuran / Font Size:</span>
                                    <span id="valSizeProperty" class="font-mono text-slate-800 font-black">18</span>
                                </div>
                                <input type="range" id="inspectorSizeSlider" min="8" max="180" step="1" value="18"
                                       class="w-full accent-orange-500 cursor-pointer" oninput="handleInspectorSizeChange(this.value)">
                            </div>

                            <!-- Nudge D-Pad for Pixel-Perfect Alignment -->
                            <div class="pt-1.5 flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-500">Geser Halus (1%):</span>
                                <div class="flex items-center gap-1">
                                    <button type="button" onclick="nudgeActiveElement(0, -1)" class="w-7 h-7 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-bold text-xs flex items-center justify-center transition shadow-2xs" title="Geser ke Atas">&uarr;</button>
                                    <button type="button" onclick="nudgeActiveElement(0, 1)" class="w-7 h-7 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-bold text-xs flex items-center justify-center transition shadow-2xs" title="Geser ke Bawah">&darr;</button>
                                    <button type="button" onclick="nudgeActiveElement(-1, 0)" class="w-7 h-7 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-bold text-xs flex items-center justify-center transition shadow-2xs" title="Geser ke Kiri">&larr;</button>
                                    <button type="button" onclick="nudgeActiveElement(1, 0)" class="w-7 h-7 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-bold text-xs flex items-center justify-center transition shadow-2xs" title="Geser ke Kanan">&rarr;</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guidance Info -->
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-950 text-xs space-y-1.5">
                        <div class="font-black flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            <span>Panduan Drag & Drop Kolom</span>
                        </div>
                        <p class="text-slate-600 leading-relaxed">
                            Klik atau sentuh setiap elemen kartu untuk menggesernya secara bebas agar pas dengan lekukan bingkai frame overlay. Garis pandu oranye otomatis muncul saat elemen berada tepat di tengah (50%). Klik tombol <strong>Simpan Posisi Kolom</strong> setelah selesai mengatur.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Store layout config state
        let currentLayout = @json($layoutConfig);
        let activeElementKey = 'fan_name';

        // Responsive Card Width Observer for WYSIWYG scaling
        function updateCardScaleVariable() {
            const card = document.getElementById('previewCard');
            if (card) {
                const w = card.offsetWidth;
                card.style.setProperty('--card-w', `${w}px`);
            }
        }
        window.addEventListener('resize', updateCardScaleVariable);
        updateCardScaleVariable();

        // -------------------------------------------------------------
        // POINTER DRAG & DROP ENGINE (Touch & Mouse)
        // -------------------------------------------------------------
        let isDragging = false;
        let dragTarget = null;
        let dragKey = null;

        function initDraggableElements() {
            const dragItems = document.querySelectorAll('.drag-item');
            const card = document.getElementById('previewCard');
            const guideLine = document.getElementById('centerGuideLine');

            dragItems.forEach(item => {
                item.addEventListener('pointerdown', (e) => {
                    // Prevent right click or non-primary buttons
                    if (e.button !== 0 && e.pointerType === 'mouse') return;

                    isDragging = true;
                    dragTarget = item;
                    dragKey = item.dataset.key;
                    selectElementByKey(dragKey);

                    item.classList.add('is-dragging');
                    guideLine.classList.add('active');

                    item.setPointerCapture(e.pointerId);
                    e.preventDefault();
                });

                item.addEventListener('pointermove', (e) => {
                    if (!isDragging || dragTarget !== item) return;

                    const cardRect = card.getBoundingClientRect();
                    let xPercent = ((e.clientX - cardRect.left) / cardRect.width) * 100;
                    let yPercent = ((e.clientY - cardRect.top) / cardRect.height) * 100;

                    // Clamping
                    xPercent = Math.max(2, Math.min(98, xPercent));
                    yPercent = Math.max(2, Math.min(98, yPercent));

                    // Magnet Snap to 50%
                    if (Math.abs(xPercent - 50) < 1.6) {
                        xPercent = 50;
                    }

                    // Apply to DOM
                    item.style.left = `${xPercent.toFixed(1)}%`;
                    item.style.top = `${yPercent.toFixed(1)}%`;

                    // Update memory state
                    if (!currentLayout[dragKey]) currentLayout[dragKey] = {};
                    currentLayout[dragKey].x = parseFloat(xPercent.toFixed(1));
                    currentLayout[dragKey].y = parseFloat(yPercent.toFixed(1));

                    // Update UI Readout
                    updateInspectorBadge(currentLayout[dragKey].x, currentLayout[dragKey].y);
                    syncLayoutConfigInput();
                });

                const endDrag = (e) => {
                    if (isDragging && dragTarget === item) {
                        isDragging = false;
                        item.classList.remove('is-dragging');
                        guideLine.classList.remove('active');
                        try {
                            item.releasePointerCapture(e.pointerId);
                        } catch (err) {}
                        dragTarget = null;
                    }
                };

                item.addEventListener('pointerup', endDrag);
                item.addEventListener('pointercancel', endDrag);
            });
        }
        initDraggableElements();

        // -------------------------------------------------------------
        // INSPECTOR & SELECTION
        // -------------------------------------------------------------
        function selectElementByKey(key) {
            activeElementKey = key;
            document.querySelectorAll('.drag-item').forEach(el => {
                if (el.dataset.key === key) {
                    el.classList.add('is-selected');
                } else {
                    el.classList.remove('is-selected');
                }
            });

            const sel = document.getElementById('inspectorElementSelect');
            if (sel && sel.value !== key) {
                sel.value = key;
            }

            const item = document.getElementById(`dragItem_${key}`);
            if (!item || !currentLayout[key]) return;

            const cfg = currentLayout[key];
            updateInspectorBadge(cfg.x || 50, cfg.y || 50);

            // Configure size slider
            const type = item.dataset.type || 'font_size';
            const slider = document.getElementById('inspectorSizeSlider');
            const lbl = document.getElementById('lblSizeProperty');
            const valLbl = document.getElementById('valSizeProperty');

            if (type === 'size') {
                lbl.innerText = 'Diameter / Lebar (px):';
                slider.min = 20;
                slider.max = 160;
                const curVal = cfg.size || 80;
                slider.value = curVal;
                valLbl.innerText = curVal;
            } else {
                lbl.innerText = 'Ukuran Font (pt):';
                slider.min = 8;
                slider.max = 38;
                const curVal = cfg.font_size || 14;
                slider.value = curVal;
                valLbl.innerText = curVal;
            }
        }

        function updateInspectorBadge(x, y) {
            const badge = document.getElementById('inspectorCoordBadge');
            if (badge) {
                badge.innerText = `X: ${parseFloat(x).toFixed(1)}% | Y: ${parseFloat(y).toFixed(1)}%`;
            }
        }

        function handleInspectorSizeChange(val) {
            val = parseInt(val, 10);
            document.getElementById('valSizeProperty').innerText = val;

            const item = document.getElementById(`dragItem_${activeElementKey}`);
            if (!item || !currentLayout[activeElementKey]) return;

            const type = item.dataset.type || 'font_size';
            if (type === 'size') {
                currentLayout[activeElementKey].size = val;
                item.style.width = `calc(var(--card-w) * (${val} / 400))`;
                item.style.height = `calc(var(--card-w) * (${val} / 400))`;
            } else {
                currentLayout[activeElementKey].font_size = val;
                item.style.fontSize = `calc(var(--card-w) * (${val} / 400))`;
            }
            syncLayoutConfigInput();
        }

        function nudgeActiveElement(dx, dy) {
            if (!currentLayout[activeElementKey]) return;
            const item = document.getElementById(`dragItem_${activeElementKey}`);
            if (!item) return;

            let curX = currentLayout[activeElementKey].x || 50;
            let curY = currentLayout[activeElementKey].y || 50;

            curX = Math.max(2, Math.min(98, parseFloat((curX + dx).toFixed(1))));
            curY = Math.max(2, Math.min(98, parseFloat((curY + dy).toFixed(1))));

            currentLayout[activeElementKey].x = curX;
            currentLayout[activeElementKey].y = curY;

            item.style.left = `${curX}%`;
            item.style.top = `${curY}%`;

            updateInspectorBadge(curX, curY);
            syncLayoutConfigInput();
        }

        function snapActiveElementToCenterX() {
            if (!currentLayout[activeElementKey]) return;
            const item = document.getElementById(`dragItem_${activeElementKey}`);
            if (!item) return;

            currentLayout[activeElementKey].x = 50;
            item.style.left = `50%`;
            updateInspectorBadge(50, currentLayout[activeElementKey].y || 50);
            syncLayoutConfigInput();
        }

        function syncLayoutConfigInput() {
            const input = document.getElementById('layoutConfigInput');
            if (input) {
                input.value = JSON.stringify(currentLayout);
            }
        }

        // Select default element on load
        selectElementByKey('fan_name');

        // -------------------------------------------------------------
        // AJAX SAVE & RESET LAYOUT
        // -------------------------------------------------------------
        async function saveLayoutConfig() {
            const btn = document.getElementById('btnSaveLayout');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Menyimpan...</span>`;

            try {
                const response = await fetch("{{ route('organizer.photobooth.save-layout', $event) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ layout_config: currentLayout })
                });

                const data = await response.json();
                if (data.success) {
                    showResetToast('Posisi semua kolom berhasil disimpan!');
                    if (data.layout_config) {
                        currentLayout = data.layout_config;
                        applyLayoutStateToDom(currentLayout);
                    }
                } else {
                    alert('Gagal menyimpan posisi layout.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }

        async function resetLayoutConfig() {
            if (!confirm('Kembalikan posisi semua kolom ke posisi default pertandingan?')) return;

            const btn = document.getElementById('btnResetLayout');
            btn.disabled = true;

            try {
                const response = await fetch("{{ route('organizer.photobooth.reset-layout', $event) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success && data.layout_config) {
                    currentLayout = data.layout_config;
                    applyLayoutStateToDom(currentLayout);
                    syncLayoutConfigInput();
                    selectElementByKey(activeElementKey);
                    showResetToast('Posisi semua kolom dikembalikan ke default matchday.');
                }
            } catch (err) {
                console.error(err);
            } finally {
                btn.disabled = false;
            }
        }

        function applyLayoutStateToDom(layout) {
            for (const [key, cfg] of Object.entries(layout)) {
                const el = document.getElementById(`dragItem_${key}`);
                if (!el) continue;

                if (cfg.x !== undefined) el.style.left = `${cfg.x}%`;
                if (cfg.y !== undefined) el.style.top = `${cfg.y}%`;

                if (cfg.size !== undefined) {
                    el.style.width = `calc(var(--card-w) * (${cfg.size} / 400))`;
                    el.style.height = `calc(var(--card-w) * (${cfg.size} / 400))`;
                }
                if (cfg.font_size !== undefined) {
                    el.style.fontSize = `calc(var(--card-w) * (${cfg.font_size} / 400))`;
                }
            }
        }

        // -------------------------------------------------------------
        // LIVE FORM PREVIEWS (Colors, Text & Uploads)
        // -------------------------------------------------------------
        function updateLivePreview() {
            const primaryColor = document.getElementById('primaryColorInput').value;
            const secondaryColor = document.getElementById('secondaryColorInput').value;
            const headerTitle = (document.getElementById('headerTitleInput')?.value || '').trim();
            const headerSubtitle = (document.getElementById('headerSubtitleInput')?.value || '').trim();
            const badgeText = (document.getElementById('badgeTextInput')?.value || '').trim();
            const formatCat = (document.getElementById('categoryLabelFormatInput')?.value || '').trim();
            const footerText = (document.getElementById('footerTextInput')?.value || '').trim();

            // Sync texts
            document.getElementById('primaryColorText').value = primaryColor;
            document.getElementById('secondaryColorText').value = secondaryColor;

            // Update Card Border
            const card = document.getElementById('previewCard');
            if (card) card.style.borderColor = secondaryColor;

            // 1. Header & Subtitle: if both are empty/null, hide element completely!
            const showHeader = Boolean(headerTitle || headerSubtitle);
            toggleElementVisibility('header_slogan', showHeader);
            const hTitle = document.getElementById('previewHeaderTitle');
            if (hTitle) {
                if (headerTitle) {
                    hTitle.innerText = headerTitle;
                    hTitle.style.display = 'block';
                } else {
                    hTitle.innerText = '';
                    hTitle.style.display = 'none';
                }
            }

            const hSub = document.getElementById('previewHeaderSubtitle');
            if (hSub) {
                if (headerSubtitle) {
                    hSub.innerText = headerSubtitle;
                    hSub.style.display = 'block';
                    hSub.style.color = secondaryColor;
                } else {
                    hSub.innerText = '';
                    hSub.style.display = 'none';
                }
            }

            const fanBadge = document.getElementById('previewFanNameBadge');
            if (fanBadge) {
                fanBadge.style.backgroundColor = primaryColor;
                fanBadge.style.borderColor = secondaryColor;
            }

            // 2. Category Badge: if formatCat is empty/null or toggleCategory unchecked, hide element!
            const showCategory = document.getElementById('toggleCategory').checked && Boolean(formatCat);
            toggleElementVisibility('category_badge', showCategory);
            const badgeTextEl = document.getElementById('previewBadgeText');
            if (badgeTextEl && formatCat) {
                let renderedCat = formatCat.replace('{category}', 'VIP BARAT');
                if (badgeText) {
                    renderedCat = renderedCat.replace('{badge}', badgeText);
                }
                badgeTextEl.innerText = renderedCat;
                badgeTextEl.style.color = secondaryColor;
            }

            // 3. Footer Text: if empty/null, hide element completely!
            const showFooter = Boolean(footerText);
            toggleElementVisibility('footer_text', showFooter);
            const fText = document.getElementById('previewFooterText');
            if (fText) {
                fText.innerText = footerText;
            }

            // Visibility Toggles
            const showTeamLogos = document.getElementById('toggleTeamLogos').checked;
            toggleElementVisibility('home_logo', showTeamLogos);
            toggleElementVisibility('away_logo', showTeamLogos);
            toggleElementVisibility('home_name', showTeamLogos);
            toggleElementVisibility('away_name', showTeamLogos);
            toggleElementVisibility('vs_badge', showTeamLogos);

            toggleElementVisibility('match_info', document.getElementById('toggleMatchInfo').checked);
            toggleElementVisibility('fan_name', document.getElementById('toggleFanName').checked);
            toggleElementVisibility('qr_code', document.getElementById('toggleQr').checked);
        }

        function toggleElementVisibility(key, isVisible) {
            const el = document.getElementById(`dragItem_${key}`);
            if (el) {
                el.style.display = isVisible ? 'block' : 'none';
            }
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

        // Quick Image Upload Previews
        function previewUploadImage(input, formImgId, cardImgId, placeholderId = null) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const formImg = document.getElementById(formImgId);
                    if (formImg) {
                        formImg.src = e.target.result;
                        formImg.style.display = 'block';
                    }
                    const cardImg = document.getElementById(cardImgId);
                    if (cardImg) {
                        cardImg.src = e.target.result;
                        cardImg.style.display = 'block';
                    }
                    if (placeholderId) {
                        const ph = document.getElementById(placeholderId);
                        if (ph) ph.style.display = 'none';
                        const cardPh = document.getElementById(`dragPlaceholder_${activeElementKey}`);
                        if (cardPh) cardPh.style.display = 'none';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        let isCleanMode = true; // Default clean plain mode

        function toggleCleanPreview() {
            isCleanMode = !isCleanMode;
            applyCleanModeState();
        }

        function applyCleanModeState() {
            const fanPhotoWrap = document.getElementById('previewFanPhotoWrap');
            const cleanPlaceholder = document.getElementById('previewCleanPlaceholder');
            const lbl = document.getElementById('lblCleanPreview');
            const btn = document.getElementById('btnToggleCleanCard');

            if (isCleanMode) {
                if (fanPhotoWrap) fanPhotoWrap.style.display = 'none';
                if (cleanPlaceholder) cleanPlaceholder.style.display = 'flex';
                if (lbl) lbl.innerText = 'Contoh Foto';
                if (btn) {
                    btn.className = 'text-[11px] font-bold px-2.5 py-1 rounded-full border transition flex items-center gap-1 bg-white hover:bg-slate-100 text-slate-700 border-slate-300 shadow-xs';
                }
            } else {
                if (fanPhotoWrap) fanPhotoWrap.style.display = 'flex';
                if (cleanPlaceholder) cleanPlaceholder.style.display = 'none';
                if (lbl) lbl.innerText = 'Mode Polos';
                if (btn) {
                    btn.className = 'text-[11px] font-bold px-2.5 py-1 rounded-full border transition flex items-center gap-1 bg-orange-50 text-orange-700 border-orange-300 shadow-xs';
                }
            }
        }

        function showResetToast(msg) {
            const toast = document.getElementById('resetToastNotice');
            if (toast) {
                toast.innerText = msg;
                toast.classList.remove('hidden');
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 4000);
            }
        }

        async function resetFrameOverlay(persist = true) {
            document.getElementById('removeFrameOverlayInput').value = '1';
            const input = document.getElementById('frameOverlayInput');
            if (input) input.value = '';

            const frameImg = document.getElementById('previewFrameOverlay');
            if (frameImg) {
                frameImg.src = '';
                frameImg.style.display = 'none';
            }

            const badge = document.getElementById('badgeFrameStatus');
            if (badge) {
                badge.innerText = 'Belum Diupload';
                badge.className = 'text-[10px] font-bold bg-slate-200 text-slate-600 px-2.5 py-0.5 rounded-full';
            }

            const btn = document.getElementById('btnResetFrame');
            if (btn) btn.style.display = 'none';

            if (persist) {
                try {
                    await fetch("{{ route('organizer.photobooth.reset-images', $event) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ target: 'frame' })
                    });
                    showResetToast('Frame overlay berhasil direset (polos).');
                } catch (e) {
                    console.warn(e);
                }
            }
        }

        async function resetBackgroundImage(persist = true) {
            document.getElementById('removeBackgroundImageInput').value = '1';
            const input = document.getElementById('backgroundImageInput');
            if (input) input.value = '';

            const bgImg = document.getElementById('previewBgImage');
            if (bgImg) {
                bgImg.src = '';
                bgImg.style.display = 'none';
            }

            const bgGrad = document.getElementById('previewBgGradient');
            if (bgGrad) bgGrad.style.display = 'none';

            const badge = document.getElementById('badgeBgStatus');
            if (badge) {
                badge.innerText = 'Belum Diupload';
                badge.className = 'text-[10px] font-bold bg-slate-200 text-slate-600 px-2.5 py-0.5 rounded-full';
            }

            const btn = document.getElementById('btnResetBg');
            if (btn) btn.style.display = 'none';

            if (persist) {
                try {
                    await fetch("{{ route('organizer.photobooth.reset-images', $event) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ target: 'background' })
                    });
                    showResetToast('Background stadion berhasil direset (polos).');
                } catch (e) {
                    console.warn(e);
                }
            }
        }

        async function resetAllImagesToPlain() {
            await resetFrameOverlay(false);
            await resetBackgroundImage(false);

            // Hide fan photo and switch to clean wireframe placeholder
            isCleanMode = true;
            applyCleanModeState();

            // Persist reset in DB
            try {
                const response = await fetch("{{ route('organizer.photobooth.reset-images', $event) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ target: 'all' })
                });
                const res = await response.json();
                if (res.success) {
                    showResetToast('Semua gambar berhasil direset! Live Card Preview sekarang bersih polos.');
                }
            } catch (err) {
                console.warn('Persist reset error:', err);
                showResetToast('Semua gambar dihilangkan. Live Card Preview sekarang bersih polos.');
            }
        }

        // Live preview for uploaded custom PNG Frame Overlay
        function handleFrameOverlayChange(input) {
            if (input.files && input.files[0]) {
                document.getElementById('removeFrameOverlayInput').value = '0';
                const badge = document.getElementById('badgeFrameStatus');
                if (badge) {
                    badge.innerText = 'Frame Baru Dipilih';
                    badge.className = 'text-[10px] font-bold bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full';
                }
                const btn = document.getElementById('btnResetFrame');
                if (btn) btn.style.display = 'inline-flex';

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
                document.getElementById('removeBackgroundImageInput').value = '0';
                const badge = document.getElementById('badgeBgStatus');
                if (badge) {
                    badge.innerText = 'Background Baru Dipilih';
                    badge.className = 'text-[10px] font-bold bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full';
                }
                const btn = document.getElementById('btnResetBg');
                if (btn) btn.style.display = 'inline-flex';

                const reader = new FileReader();
                reader.onload = function(e) {
                    const bgImg = document.getElementById('previewBgImage');
                    bgImg.src = e.target.result;
                    bgImg.style.display = 'block';
                    const bgGrad = document.getElementById('previewBgGradient');
                    if (bgGrad) bgGrad.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-app-layout>