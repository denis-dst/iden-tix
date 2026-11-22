<x-app-layout>
    <x-slot name="title">Master Template Fotobooth Klub - {{ $tenant->name }}</x-slot>

    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Breadcrumb & Top Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-black text-orange-500 uppercase tracking-widest mb-1">
                    <a href="{{ route('organizer.kiosk.launcher') }}" class="hover:underline">KIOSK & Photobooth</a>
                    <span>/</span>
                    <span>Pengaturan Master Template</span>
                </div>
                <h2 class="text-3xl font-black text-slate-800 font-outfit">Master Template Fotobooth Klub</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Desain kartu foto suporter (Matchday Collectible Card) default klub yang otomatis berlaku untuk semua pertandingan.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('organizer.kiosk.launcher') }}" 
                   class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl font-bold shadow-sm transition flex items-center gap-2 text-sm min-h-[44px]">
                    <svg class="w-4 h-4 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Launcher
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
            <div class="lg:col-span-7 bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100">
                <form action="{{ route('organizer.photobooth.master.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Status Toggle -->
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div>
                            <span class="block text-sm font-black text-slate-800">Aktifkan Master Template Klub</span>
                            <span class="text-xs text-slate-500 font-medium">Bila pertandingan belum memiliki template khusus, sistem otomatis menggunakan master template ini.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ ($template->is_active ?? true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                        </label>
                    </div>

                    <!-- Template Name -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Nama Master Template</label>
                        <input type="text" name="name" value="{{ old('name', $template->name ?? ('Master Template ' . $tenant->name)) }}" required
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
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Contoh: #5A2D82 (Ungu Persita), #002244 (Biru Navy)</span>
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
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Contoh: #F95700 (Oranye Api), #FFD700 (Emas)</span>
                        </div>
                    </div>

                    <!-- Typography & Match Slogans -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Header Liga / Turnamen</label>
                            <input type="text" id="headerTitleInput" name="header_title" value="{{ old('header_title', $template->header_title) }}"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                   placeholder="Kosongkan jika tidak ingin ditampilkan" oninput="updateLivePreview()">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Sub-Slogan Atas</label>
                            <input type="text" id="headerSubtitleInput" name="header_subtitle" value="{{ old('header_subtitle', $template->header_subtitle) }}"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                   placeholder="Kosongkan jika tidak ingin ditampilkan" oninput="updateLivePreview()">
                        </div>
                    </div>

                    <!-- Dynamic Category Label Format -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600">Format Label Kategori Tiket (Dinamis)</label>
                            <span class="text-[11px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md">Dinamis per Suporter</span>
                        </div>
                        <input type="text" id="categoryFormatInput" name="category_label_format" 
                               value="{{ old('category_label_format', $template->category_label_format) }}"
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                               placeholder="Kosongkan jika tidak ingin ditampilkan" oninput="updateLivePreview()">
                        
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-[11px] text-slate-500 font-medium">Tag variabel:</span>
                            <button type="button" onclick="insertTag('{category}')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-mono font-bold transition">
                                + {category}
                            </button>
                            <span class="text-[11px] text-slate-400">Otomatis diganti nama kategori tiket pembeli (misal: "VIP Barat", "Tribun Timur").</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Badge Text Cadangan</label>
                            <input type="text" id="badgeTextInput" name="badge_text" value="{{ old('badge_text', $template->badge_text) }}"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                   placeholder="Kosongkan jika tidak ingin ditampilkan" oninput="updateLivePreview()">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">Footer Slogan</label>
                            <input type="text" id="footerTextInput" name="footer_text" value="{{ old('footer_text', $template->footer_text) }}"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                   placeholder="Kosongkan jika tidak ingin ditampilkan" oninput="updateLivePreview()">
                        </div>
                    </div>

                    <!-- Custom Graphic Assets Upload -->
                    <div class="border-t border-slate-100 pt-6 space-y-4">
                        <input type="hidden" name="remove_frame_overlay" id="master_remove_frame_input" value="0">
                        <input type="hidden" name="remove_background_image" id="master_remove_bg_input" value="0">

                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Aset Grafis Custom Master Template (Opsional)</h4>
                            <button type="button" onclick="resetMasterAllImages()" 
                                    class="text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-xl border border-red-200 transition flex items-center gap-1.5 shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                <span>Reset Semua Gambar</span>
                            </button>
                        </div>
                        
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Custom Frame Overlay (PNG Transparan)</label>
                                <div class="flex items-center gap-2">
                                    <span id="masterFrameBadge" class="text-[10px] font-bold {{ !empty($template->frame_overlay) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }} px-2 py-0.5 rounded-full">
                                        {{ !empty($template->frame_overlay) ? 'Frame Terpasang' : 'Belum Diupload' }}
                                    </span>
                                    <button type="button" id="btnMasterResetFrame" onclick="resetMasterFrame()" 
                                            class="text-[11px] font-bold text-red-600 hover:text-red-700 bg-white hover:bg-red-50 px-2.5 py-1 rounded-lg border border-red-200 transition flex items-center gap-1 shadow-xs {{ empty($template->frame_overlay) ? 'hidden' : '' }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        <span>Reset Frame</span>
                                    </button>
                                </div>
                            </div>
                            <input type="file" name="frame_overlay" id="masterFrameInput" accept="image/png"
                                   class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 cursor-pointer">
                            <span class="text-[11px] text-slate-500 font-medium block">Format PNG rasio 2:3 (disarankan 1024 x 1536 px dengan cutout transparan di tengah).</span>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Background Stadion / Lapangan</label>
                                <div class="flex items-center gap-2">
                                    <span id="masterBgBadge" class="text-[10px] font-bold {{ !empty($template->background_image) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }} px-2 py-0.5 rounded-full">
                                        {{ !empty($template->background_image) ? 'Background Terpasang' : 'Belum Diupload' }}
                                    </span>
                                    <button type="button" id="btnMasterResetBg" onclick="resetMasterBg()" 
                                            class="text-[11px] font-bold text-red-600 hover:text-red-700 bg-white hover:bg-red-50 px-2.5 py-1 rounded-lg border border-red-200 transition flex items-center gap-1 shadow-xs {{ empty($template->background_image) ? 'hidden' : '' }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        <span>Reset Background</span>
                                    </button>
                                </div>
                            </div>
                            <input type="file" name="background_image" id="masterBgInput" accept="image/jpeg,image/png,image/webp"
                                   class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            <span class="text-[11px] text-slate-500 font-medium block">Latar belakang atmosfer stadion / tribun supporter untuk kartu foto.</span>
                        </div>
                    </div>

                    <!-- Toggles -->
                    <div class="border-t border-slate-100 pt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-slate-100/70 transition">
                            <input type="checkbox" id="toggleShowCategory" name="show_category" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ ($template->show_category ?? true) ? 'checked' : '' }} onchange="updateLivePreview()">
                            <span class="text-xs font-bold text-slate-700">Tampilkan Kategori Tiket</span>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-slate-100/70 transition">
                            <input type="checkbox" id="toggleShowFanName" name="show_fan_name" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ ($template->show_fan_name ?? true) ? 'checked' : '' }} onchange="updateLivePreview()">
                            <span class="text-xs font-bold text-slate-700">Tampilkan Nama Suporter</span>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-slate-100/70 transition">
                            <input type="checkbox" id="toggleShowTeamLogos" name="show_team_logos" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ ($template->show_team_logos ?? true) ? 'checked' : '' }} onchange="updateLivePreview()">
                            <span class="text-xs font-bold text-slate-700">Tampilkan Logo & Tim Matchup</span>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-slate-100/70 transition">
                            <input type="checkbox" id="toggleShowMatchInfo" name="show_match_info" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ ($template->show_match_info ?? true) ? 'checked' : '' }} onchange="updateLivePreview()">
                            <span class="text-xs font-bold text-slate-700">Tampilkan Info Bar (Tanggal & Venue)</span>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-slate-100/70 transition sm:col-span-2">
                            <input type="checkbox" id="toggleShowQr" name="show_qr" value="1" class="w-4 h-4 text-orange-500 rounded border-slate-300" {{ ($template->show_qr ?? true) ? 'checked' : '' }} onchange="updateLivePreview()">
                            <span class="text-xs font-bold text-slate-700">Tampilkan QR Code Scan to Download</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="submit" class="px-8 py-3.5 bg-orange-600 hover:bg-orange-700 text-white font-black text-sm rounded-xl shadow-lg shadow-orange-600/25 transition min-h-[44px]">
                            Simpan Master Template Klub
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Interactive Live Preview (5 cols) -->
            <div class="lg:col-span-5 flex flex-col items-center">
                <div class="sticky top-6 w-full max-w-sm">
                    <div class="flex items-center justify-between mb-3 px-2">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-500">Live Card Preview (Matchday Edition)</span>
                        <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full border border-emerald-200">REALTIME</span>
                    </div>

                    <!-- Test Dynamic Category Simulator Selector -->
                    <div class="mb-3 p-3 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
                        <label class="block text-[10px] font-black uppercase text-slate-500 tracking-wider mb-1">Simulasikan Kategori Tiket Pembeli:</label>
                        <select id="previewCategorySelect" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-extrabold text-slate-800" onchange="updateLivePreview()">
                            <option value="VIP BARAT" selected>VIP Barat (Rp 250.000)</option>
                            <option value="TRIBUN TIMUR">Tribun Timur (Rp 100.000)</option>
                            <option value="TRIBUN SELATAN">Tribun Selatan (Rp 75.000)</option>
                            <option value="TRIBUN UTARA">Tribun Utara (Rp 75.000)</option>
                            <option value="VVIP LOUNGE">VVIP Lounge (Rp 500.000)</option>
                        </select>
                    </div>

                    <!-- Visual Card Preview matching uploaded reference -->
                    <div id="previewCard" class="w-full rounded-3xl overflow-hidden shadow-2xl border-4 transition-all duration-300 relative select-none" 
                         style="background-color: {{ $template->primary_color ?? '#5A2D82' }}; border-color: {{ $template->secondary_color ?? '#F95700' }}; aspect-ratio: 1024 / 1536; color: #ffffff;">
                        
                        <!-- Top League Branding Banner -->
                        <div class="pt-4 px-4 pb-2 flex items-center justify-between relative z-10">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-orange-500 flex items-center justify-center text-white text-xs font-black shadow-md">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                                </div>
                                <div class="text-left">
                                    <div id="previewHeaderTitle" class="text-xs font-black tracking-wider uppercase drop-shadow-sm leading-tight">{{ $template->header_title ?? 'I LEAGUE 2026/2027' }}</div>
                                    <div class="text-[8px] font-extrabold text-slate-200 tracking-wider">OFFICIAL MATCHDAY</div>
                                </div>
                            </div>
                            <div id="previewHeaderSubtitle" class="text-[10px] font-black tracking-widest uppercase italic drop-shadow-sm" style="color: {{ $template->secondary_color ?? '#F95700' }};">
                                {{ $template->header_subtitle ?? 'FOOTBALL UNITES US' }}
                            </div>
                        </div>

                        <!-- Fan Photo Container with Stadium Background & Scarf Fan -->
                        <div class="mx-3 my-1 rounded-2xl overflow-hidden bg-slate-950 border border-white/20 relative shadow-inner" style="height: 52%;">
                            <img src="{{ asset('images/kiosk/master_template_sample.jpg') }}" alt="Card Photo" class="w-full h-full object-cover">
                            
                            <!-- Dynamic Frame Graphic Overlay Slices -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/10 to-transparent"></div>
                            
                            <!-- Badge Preview indicator -->
                            <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-sm px-2 py-0.5 rounded text-[8px] font-bold text-white/90 uppercase tracking-widest">
                                Live Camera Frame
                            </div>
                        </div>

                        <!-- Matchup Section (Home vs Away) -->
                        <div id="previewMatchupRow" class="px-4 py-1 flex items-center justify-between text-center relative z-10">
                            <!-- Home Club -->
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-9 h-9 rounded-full bg-white/90 p-1 flex items-center justify-center shadow-md">
                                    <span class="text-[9px] font-black text-purple-900 leading-none">PERSITA</span>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-white mt-1">{{ $sampleEvent->home_team_name ?? 'PERSITA' }}</span>
                            </div>

                            <!-- VS Badge -->
                            <div class="px-2 text-center">
                                <span class="text-xl font-black italic tracking-tighter text-white drop-shadow-lg">VS</span>
                            </div>

                            <!-- Away Club -->
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-9 h-9 rounded-full bg-white/90 p-1 flex items-center justify-center shadow-md">
                                    <span class="text-[9px] font-black text-red-600 leading-none">PERSIJA</span>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-white mt-1">{{ $sampleEvent->away_team_name ?? 'PERSIJA' }}</span>
                            </div>
                        </div>

                        <!-- White Info Strip Bar (Date, Stadium Venue, Tournament) -->
                        <div id="previewInfoStrip" class="mx-3 mt-1 py-1.5 px-3 rounded-lg bg-white text-slate-800 text-[8px] font-extrabold flex items-center justify-between shadow-md">
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span id="previewDateText">{{ $sampleEvent->event_start_date ? $sampleEvent->event_start_date->format('d M Y') : '20 OKT 2026' }}</span>
                            </div>
                            <span class="text-slate-300">|</span>
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span id="previewVenueText" class="truncate max-w-[90px]">{{ $sampleEvent->venue ?? 'INDOMILK ARENA' }}</span>
                            </div>
                            <span class="text-slate-300">|</span>
                            <div class="flex items-center gap-1">
                                <span id="previewLeagueMini" class="truncate max-w-[80px] text-orange-600 font-black">I LEAGUE</span>
                            </div>
                        </div>

                        <!-- Chevron Ribbon Banner with Fan Name and Dynamic Category -->
                        <div class="mx-2 mt-2 relative">
                            <div id="previewChevronRibbon" class="py-1 px-4 text-center rounded-xl relative shadow-lg" 
                                 style="background-color: {{ $template->primary_color ?? '#5A2D82' }}; border: 2px solid {{ $template->secondary_color ?? '#F95700' }};">
                                
                                <!-- Fan Name -->
                                <div id="previewFanName" class="text-sm font-black uppercase text-white tracking-wider drop-shadow-sm">
                                    BRIAN ARIBOWO
                                </div>
                                
                                <!-- Dynamic Category Sub-line -->
                                <div id="previewCategoryBadge" class="text-[9px] font-black uppercase tracking-widest mt-0.5" 
                                     style="color: {{ $template->secondary_color ?? '#F95700' }};">
                                    VIP BARAT • MATCHDAY COLLECTIBLE
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Footer Strip with Slogan & QR Code -->
                        <div class="px-4 py-2 mt-1 flex items-center justify-between text-left">
                            <div id="previewFooterText" class="text-[8px] font-bold text-slate-300 uppercase tracking-wider flex-1 mr-2">
                                {{ $template->footer_text ?? 'SATU LIGA, SATU SEMANGAT' }}
                            </div>

                            <div id="previewQrBlock" class="flex flex-col items-center">
                                <div class="w-8 h-8 bg-white p-0.5 rounded shadow-sm flex items-center justify-center">
                                    <!-- QR icon simulation -->
                                    <svg class="w-full h-full text-black" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v2h-3v-2zm-5 0h2v2h-2v-2zm2 3h2v2h-2v-2zm3 0h3v5h-2v-3h-1v-2zm-3 3h2v2h-2v-2zm-5-3h3v2h-3v-2z"/>
                                    </svg>
                                </div>
                                <span class="text-[6px] font-black uppercase text-white tracking-tight mt-0.5">SCAN TO DOWNLOAD</span>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 p-4 rounded-2xl bg-slate-100 border border-slate-200/80 text-slate-600 text-xs font-medium">
                        <div class="flex items-center gap-2 text-slate-800 font-bold mb-1">
                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Penjelasan Master Template
                        </div>
                        Template ini akan diterapkan otomatis untuk setiap pertandingan klub Anda di KIOSK self-service stadion, dengan nama fan, kategori tiket yang dibeli, dan data lawan tanding yang dinamis.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateLivePreview() {
            const primaryColor = document.getElementById('primaryColorInput').value;
            const secondaryColor = document.getElementById('secondaryColorInput').value;
            const headerTitle = (document.getElementById('headerTitleInput')?.value || '').trim();
            const headerSubtitle = (document.getElementById('headerSubtitleInput')?.value || '').trim();
            const categoryFormat = (document.getElementById('categoryFormatInput')?.value || '').trim();
            const badgeText = (document.getElementById('badgeTextInput')?.value || '').trim();
            const footerText = (document.getElementById('footerTextInput')?.value || '').trim();
            const selectedSimCat = document.getElementById('previewCategorySelect').value;

            // Sync color input texts
            document.getElementById('primaryColorText').value = primaryColor;
            document.getElementById('secondaryColorText').value = secondaryColor;

            // Card background & borders
            const card = document.getElementById('previewCard');
            card.style.backgroundColor = primaryColor;
            card.style.borderColor = secondaryColor;

            // Header Texts
            const hTitle = document.getElementById('previewHeaderTitle');
            if (hTitle) {
                hTitle.innerText = headerTitle;
                hTitle.style.display = headerTitle ? 'block' : 'none';
            }
            const hSub = document.getElementById('previewHeaderSubtitle');
            if (hSub) {
                hSub.innerText = headerSubtitle;
                hSub.style.display = headerSubtitle ? 'block' : 'none';
                hSub.style.color = secondaryColor;
            }
            const leagueMini = document.getElementById('previewLeagueMini');
            if (leagueMini) {
                leagueMini.innerText = headerTitle ? (headerTitle.split(' ')[0] || 'LEAGUE') : '';
                leagueMini.style.display = headerTitle ? 'inline' : 'none';
            }

            // Chevron Ribbon & Fan Name
            const ribbon = document.getElementById('previewChevronRibbon');
            ribbon.style.backgroundColor = primaryColor;
            ribbon.style.borderColor = secondaryColor;

            // Dynamic Category formatting
            const catBadge = document.getElementById('previewCategoryBadge');
            if (catBadge) {
                if (categoryFormat) {
                    let formattedCat = categoryFormat.replace('{category}', selectedSimCat);
                    catBadge.innerText = formattedCat;
                    catBadge.style.color = secondaryColor;
                    catBadge.style.display = document.getElementById('toggleShowCategory').checked ? 'block' : 'none';
                } else {
                    catBadge.style.display = 'none';
                }
            }

            // Footer Slogan
            const fText = document.getElementById('previewFooterText');
            if (fText) {
                fText.innerText = footerText;
                fText.style.display = footerText ? 'block' : 'none';
            }

            // Toggles
            const showFan = document.getElementById('toggleShowFanName').checked;
            const showMatchup = document.getElementById('toggleShowTeamLogos').checked;
            const showInfo = document.getElementById('toggleShowMatchInfo').checked;
            const showQr = document.getElementById('toggleShowQr').checked;

            document.getElementById('previewFanName').style.display = showFan ? 'block' : 'none';
            document.getElementById('previewMatchupRow').style.display = showMatchup ? 'flex' : 'none';
            document.getElementById('previewInfoStrip').style.display = showInfo ? 'flex' : 'none';
            document.getElementById('previewQrBlock').style.display = showQr ? 'flex' : 'none';
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

        function insertTag(tag) {
            const input = document.getElementById('categoryFormatInput');
            input.value += ' ' + tag;
            updateLivePreview();
        }

        async function resetMasterFrame() {
            document.getElementById('master_remove_frame_input').value = '1';
            const input = document.getElementById('masterFrameInput');
            if (input) input.value = '';
            const badge = document.getElementById('masterFrameBadge');
            if (badge) {
                badge.innerText = 'Belum Diupload';
                badge.className = 'text-[10px] font-bold bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full';
            }
            const btn = document.getElementById('btnMasterResetFrame');
            if (btn) btn.style.display = 'none';

            try {
                await fetch("{{ route('organizer.photobooth.master.reset-images') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ target: 'frame' })
                });
            } catch (e) {
                console.warn(e);
            }
        }

        async function resetMasterBg() {
            document.getElementById('master_remove_bg_input').value = '1';
            const input = document.getElementById('masterBgInput');
            if (input) input.value = '';
            const badge = document.getElementById('masterBgBadge');
            if (badge) {
                badge.innerText = 'Belum Diupload';
                badge.className = 'text-[10px] font-bold bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full';
            }
            const btn = document.getElementById('btnMasterResetBg');
            if (btn) btn.style.display = 'none';

            try {
                await fetch("{{ route('organizer.photobooth.master.reset-images') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ target: 'background' })
                });
            } catch (e) {
                console.warn(e);
            }
        }

        async function resetMasterAllImages() {
            await resetMasterFrame();
            await resetMasterBg();
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            updateLivePreview();
        });
    </script>
</x-app-layout>
