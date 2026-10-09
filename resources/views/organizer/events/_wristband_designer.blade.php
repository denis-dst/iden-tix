@php
    $template = $event?->getEffectiveWristbandTemplate() ?: $event?->wristbandTemplate;
    $currentMode = old('wristband_mode', $template?->mode ?? 'default');
    $currentBg = $template?->getBackgroundImageUrl();
    $currentCols = $template ? $template->getMergedColumnsConfig() : \App\Models\WristbandTemplate::getDefaultColumns();
    $wristbandMeta = $event->meta ?? [];
@endphp

<div class="pt-6 border-t border-slate-100 space-y-6"
    x-data="wristbandDesigner(
        '{{ $currentMode }}',
        @js($currentBg),
        @js($currentCols)
    )">

    <!-- Section Header -->
    <div class="space-y-1">
        <div class="flex items-center gap-2">
            <span class="p-2 rounded-xl bg-purple-100 text-purple-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
            </span>
            <h3 class="text-lg font-black text-slate-800">Model Gelang Tiket (Wristband)</h3>
        </div>
        <p class="text-xs text-slate-500">
            Pilih model desain gelang tiket: gunakan template sistem bawaan IdenTix atau mode kustom dengan unggahan background sendiri.
        </p>
    </div>

    <!-- Mode Selection Cards (High contrast, clearly visible under all circumstances) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <!-- Option 1: Mode Default -->
        <div @click="mode = 'default'"
            class="relative p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 select-none flex items-start justify-between gap-3"
            :class="mode === 'default' 
                ? 'border-purple-600 bg-purple-50/70 ring-2 ring-purple-500/20 shadow-sm' 
                : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60'">
            <div class="flex items-start gap-3">
                <span class="p-2.5 rounded-xl shrink-0 transition-colors"
                    :class="mode === 'default' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black text-slate-800">Mode Default (Sistem)</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Layout standar IdenTix dengan Logo Penyelenggara/Liga, Logo Tim, dan Grid Sponsor.
                    </p>
                </div>
            </div>
            <div class="shrink-0 pt-0.5">
                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                    :class="mode === 'default' ? 'border-purple-600 bg-purple-600' : 'border-slate-300 bg-white'">
                    <svg x-show="mode === 'default'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                </div>
            </div>
        </div>

        <!-- Option 2: Mode Custom -->
        <div @click="mode = 'custom'"
            class="relative p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 select-none flex items-start justify-between gap-3"
            :class="mode === 'custom' 
                ? 'border-purple-600 bg-purple-50/70 ring-2 ring-purple-500/20 shadow-sm' 
                : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60'">
            <div class="flex items-start gap-3">
                <span class="p-2.5 rounded-xl shrink-0 transition-colors"
                    :class="mode === 'custom' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black text-slate-800">Mode Custom (Desain Sendiri)</span>
                        <span class="text-[10px] font-black text-purple-700 bg-purple-100 px-2 py-0.5 rounded-md uppercase">Drag & Drop</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Upload background desain sendiri & geser posisi kolom langsung pada gelang sebelum dicetak ke F4.
                    </p>
                </div>
            </div>
            <div class="shrink-0 pt-0.5">
                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                    :class="mode === 'custom' ? 'border-purple-600 bg-purple-600' : 'border-slate-300 bg-white'">
                    <svg x-show="mode === 'custom'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Fields for Form Submit -->
    <input type="hidden" name="wristband_mode" value="{{ $currentMode }}" :value="mode">
    <input type="hidden" name="wristband_columns_json" value="{{ json_encode($currentCols) }}" :value="columnsJson">
    <input type="hidden" name="wristband_remove_background" :value="removeBackgroundFlag ? '1' : '0'">
    <input type="hidden" name="wristband_custom_background_base64" id="wristband_custom_background_base64_input" value="">

    <!-- ========================================================== -->
    <!-- 1. MODE DEFAULT (Template Bawaan Liga / Klub / Sponsor) -->
    <!-- ========================================================== -->
    <div x-show="mode === 'default'" x-transition class="space-y-4 bg-slate-50/70 p-5 rounded-3xl border border-slate-200/80">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-100">Template Sistem Aktif</span>
                <p class="text-xs text-slate-500 mt-1">Menggunakan tata letak standar IdenTix dengan Logo Liga, Klub Tuan Rumah/Tamu, dan Grid Sponsor.</p>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Liga / Turnamen</label>
            <input type="text" name="wristband_league_name"
                value="{{ old('wristband_league_name', $wristbandMeta['wristband_league_name'] ?? 'BRI Super League 2025-26') }}"
                class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 transition px-4 py-3 text-sm bg-white">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-3 rounded-2xl border border-slate-200">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Logo Liga / Penyelenggara</label>
                @if(!empty($wristbandMeta['wristband_league_logo']))
                    <img src="{{ Storage::url($wristbandMeta['wristband_league_logo']) }}"
                        class="mb-2 h-12 w-full object-contain rounded-xl bg-gray-50 border border-gray-100">
                @endif
                <input type="file" name="wristband_league_logo"
                    class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition cursor-pointer">
            </div>

            <div class="bg-white p-3 rounded-2xl border border-slate-200">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Logo Klub Tuan Rumah (Home)</label>
                @if(!empty($wristbandMeta['wristband_home_club_logo']))
                    <img src="{{ Storage::url($wristbandMeta['wristband_home_club_logo']) }}"
                        class="mb-2 h-12 w-full object-contain rounded-xl bg-gray-50 border border-gray-100">
                @endif
                <input type="file" name="wristband_home_club_logo"
                    class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition cursor-pointer">
            </div>

            <div class="bg-white p-3 rounded-2xl border border-slate-200">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Logo Klub Tamu (Away)</label>
                @if(!empty($wristbandMeta['wristband_away_club_logo']))
                    <img src="{{ Storage::url($wristbandMeta['wristband_away_club_logo']) }}"
                        class="mb-2 h-12 w-full object-contain rounded-xl bg-gray-50 border border-gray-100">
                @endif
                <input type="file" name="wristband_away_club_logo"
                    class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition cursor-pointer">
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Logo Sponsor</label>
            @if(!empty($wristbandMeta['wristband_sponsor_logos']))
                <div class="mb-3 grid grid-cols-4 sm:grid-cols-6 gap-2">
                    @foreach($wristbandMeta['wristband_sponsor_logos'] as $sponsorLogo)
                        <img src="{{ Storage::url($sponsorLogo) }}"
                            class="h-10 w-full object-contain rounded-lg bg-gray-50 border border-gray-100">
                    @endforeach
                </div>
            @endif
            <input type="file" name="wristband_sponsor_logos[]" multiple
                class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition cursor-pointer">
            <p class="mt-2 text-[10px] text-gray-400 italic">Format gambar PNG/JPG/SVG transparan disarankan.</p>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- 2. MODE CUSTOM (Desain Sendiri & Drag-and-Drop Editor)   -->
    <!-- ========================================================== -->
    <div x-show="mode === 'custom'" x-transition class="space-y-6 bg-gradient-to-b from-purple-50/40 via-white to-slate-50/50 p-6 rounded-3xl border-2 border-purple-200 shadow-sm">
        
        <!-- Background Upload & Instructions -->
        <div class="bg-white p-5 rounded-2xl border border-purple-100 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-black text-slate-800 flex items-center gap-2">
                        <span>1. Unggah Desain Background Tiket Gelang</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-purple-100 text-purple-700">Wajib untuk Mode Custom</span>
                    </h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Unggah gambar desain gelang Anda. Rekomendasi ukuran: <strong>215 x 22 mm</strong> (rasio ~9.8:1) atau resolusi <strong>2540 x 260 px</strong> untuk cetak tajam 300 DPI.
                    </p>
                </div>

                <template x-if="backgroundPreview">
                    <button type="button" @click="removeBackground()" class="text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-xl border border-red-200 transition shrink-0">
                        Hapus Background
                    </button>
                </template>
            </div>

            <div class="flex items-center gap-4">
                <input type="file" 
                    id="wristband_custom_background_input"
                    name="wristband_custom_background" 
                    accept="image/*"
                    @change="onFileChange($event)"
                    class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-600 file:text-white hover:file:bg-purple-700 transition cursor-pointer">
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- INTERACTIVE SINGLE-TICKET CANVAS (DRAG & DROP EDITOR)      -->
        <!-- ========================================================== -->
        <div class="space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h4 class="text-sm font-black text-slate-800 flex items-center gap-2">
                        <span>2. Editor Gelang Satuan Interaktif</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800">Bisa Digeser Langsung (Drag & Drop)</span>
                    </h4>
                    <p class="text-xs text-slate-500">
                        Klik dan geser elemen langsung pada kanvas gelang di bawah untuk menentukan posisinya. Hasil tata letak ini akan otomatis digenerate identik ke lembaran cetak F4 (15 tiket).
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="resetDefaultPositions()" class="text-[11px] font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 px-3 py-1.5 rounded-xl shadow-xs transition">
                        ↺ Reset Posisi Standar
                    </button>
                </div>
            </div>

            <!-- Canvas Container -->
            <div class="p-3 sm:p-4 bg-slate-900 rounded-2xl overflow-hidden shadow-inner border border-slate-800">
                <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono mb-2 px-1">
                    <span>KANVAS TIKET GELANG SATUAN (215 mm x 22 mm)</span>
                    <span class="text-purple-300" x-text="'Elemen Terpilih: ' + (columns[selectedColKey]?.label || '-') + ' (X: ' + (columns[selectedColKey]?.x || 0) + ' mm, Y: ' + (columns[selectedColKey]?.y || 0) + ' mm)'"></span>
                </div>

                <!-- Wristband Strip Canvas with strict 215:22 aspect ratio -->
                <div id="wb-interactive-canvas"
                    class="relative w-full overflow-hidden select-none cursor-default shadow-lg"
                    :style="backgroundPreview ? 'aspect-ratio: 215/22; background-image: url(\'' + backgroundPreview + '\'); background-size: 100% 100%; background-repeat: no-repeat; background-position: center; border: 1px solid rgba(255,255,255,0.3); border-radius: 4px;' : 'aspect-ratio: 215/22; background: linear-gradient(90deg, #1e293b 0%, #334155 100%); border: 1px dashed rgba(255,255,255,0.4); border-radius: 4px;'">

                    <!-- Guide watermark if no background -->
                    <template x-if="!backgroundPreview">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none text-slate-400 text-xs font-bold uppercase tracking-wider opacity-60">
                            [ Silakan Upload Background Desain Tiket Gelang ]
                        </div>
                    </template>

                    <!-- Draggable Elements on the Canvas -->
                    <template x-for="(col, key) in columns" :key="key">
                        <div x-show="col.enabled"
                            @mousedown="onColMouseDown($event, key)"
                            @touchstart="onColTouchStart($event, key)"
                            @click="selectedColKey = key"
                            :class="{
                                'ring-2 ring-purple-400 shadow-lg bg-purple-500/25': selectedColKey === key,
                                'hover:ring-1 hover:ring-amber-400 hover:bg-amber-400/10': selectedColKey !== key
                            }"
                            class="absolute cursor-move transition-shadow duration-75 rounded px-0.5 py-0.5 select-none"
                            :style="{
                                left: ((parseFloat(String(col.x).replace(',', '.')) || 0) / 215 * 100) + '%',
                                top: ((parseFloat(String(col.y).replace(',', '.')) || 0) / 22 * 100) + '%',
                                zIndex: selectedColKey === key ? 30 : 20,
                            }">

                            <!-- QR Code Preview Element -->
                            <template x-if="key === 'qr_code'">
                                <div class="bg-white p-0.5 rounded shadow-xs flex items-center justify-center"
                                    :style="{
                                        width: ((parseFloat(String(col.qr_size || 14).replace(',', '.')) || 14) / 215 * 100 * 4) + 'px',
                                        height: ((parseFloat(String(col.qr_size || 14).replace(',', '.')) || 14) / 22 * 100 * 0.4) + 'px',
                                    }">
                                    <svg class="w-full h-full text-slate-900" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14 0h4v4h-4v-4zm-4 4h4v4h-4v-4zm0-4h4v4h-4v-4zm4-4h4v4h-4v-4zm-8 4h4v4h-4v-4z"/>
                                    </svg>
                                </div>
                            </template>

                            <!-- Text Code Element -->
                            <template x-if="key === 'wristband_code'">
                                <span class="font-mono whitespace-nowrap leading-none block font-black"
                                    :style="{
                                        color: col.color || '#111827',
                                        fontSize: 'clamp(7px, 1.1vw, 10px)'
                                    }">WB-C1-0001</span>
                            </template>

                            <!-- Category Name Element -->
                            <template x-if="key === 'category_name'">
                                <span class="font-black uppercase tracking-tight whitespace-nowrap leading-none block"
                                    :style="{
                                        color: col.color || '#111827',
                                        fontSize: 'clamp(9px, 1.5vw, 15px)'
                                    }">VIP SUPPORTER</span>
                            </template>

                            <!-- Event Name Element -->
                            <template x-if="key === 'event_name'">
                                <span class="font-black uppercase tracking-tight whitespace-nowrap leading-none block"
                                    :style="{
                                        color: col.color || '#111827',
                                        fontSize: 'clamp(8px, 1.2vw, 12px)'
                                    }">{{ $event ? $event->name : 'NAMA EVENT BESAR 2026' }}</span>
                            </template>

                            <!-- Event Date Element -->
                            <template x-if="key === 'event_date'">
                                <span class="font-bold uppercase whitespace-nowrap leading-none block"
                                    :style="{
                                        color: col.color || '#374151',
                                        fontSize: 'clamp(6px, 0.8vw, 8.5px)'
                                    }">15 OKT 2026 • 19:00 WIB</span>
                            </template>

                            <!-- Venue Element -->
                            <template x-if="key === 'venue'">
                                <span class="font-semibold uppercase whitespace-nowrap leading-none block"
                                    :style="{
                                        color: col.color || '#4b5563',
                                        fontSize: 'clamp(6px, 0.75vw, 8px)'
                                    }">STADION UTAMA GBK</span>
                            </template>

                            <!-- Seat Number Element -->
                            <template x-if="key === 'seat_number'">
                                <span class="font-mono font-black whitespace-nowrap leading-none block"
                                    :style="{
                                        color: col.color || '#111827',
                                        fontSize: 'clamp(8px, 1.2vw, 12px)'
                                    }">#0001</span>
                            </template>

                            <!-- Price Element -->
                            <template x-if="key === 'price'">
                                <span class="font-black whitespace-nowrap leading-none block"
                                    :style="{
                                        color: col.color || '#111827',
                                        fontSize: 'clamp(7px, 0.9vw, 9px)'
                                    }">Rp 150.000</span>
                            </template>

                            <!-- Custom Text Element -->
                            <template x-if="key === 'custom_text'">
                                <span class="font-bold uppercase whitespace-nowrap leading-none block"
                                    :style="{
                                        color: col.color || '#4b5563',
                                        fontSize: 'clamp(6px, 0.75vw, 8px)'
                                    }"
                                    x-text="col.custom_value || 'NON-REFUNDABLE • WAJIB KTP'"></span>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- 3. SELECTABLE COLUMNS CHECKLIST & FINE-TUNE COORDINATES   -->
        <!-- ========================================================== -->
        <div class="space-y-4">
            <h4 class="text-sm font-black text-slate-800 flex items-center justify-between">
                <span>3. Pilih Kolom & Pengaturan Detail</span>
                <span class="text-xs font-normal text-slate-500">Bisa menggunakan angka bulat atau desimal (misal 17.2 atau 17,2)</span>
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <template x-for="(col, key) in columns" :key="key">
                    <div class="p-3.5 rounded-2xl border transition"
                        :class="col.enabled ? (selectedColKey === key ? 'bg-purple-50/80 border-purple-300 ring-2 ring-purple-400/30' : 'bg-white border-slate-200 hover:border-purple-200') : 'bg-slate-50/60 border-slate-200/60 opacity-60'">
                        
                        <div class="flex items-center justify-between mb-2">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" 
                                    x-model="col.enabled"
                                    @change="if(col.enabled) selectedColKey = key"
                                    class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-gray-300">
                                <span class="text-xs font-bold text-slate-800" x-text="col.label"></span>
                            </label>

                            <template x-if="col.enabled">
                                <button type="button" @click="selectedColKey = key" class="text-[10px] font-bold text-purple-600 hover:underline">
                                    Atur
                                </button>
                            </template>
                        </div>

                        <!-- Mini Fine-Tune Panel if Enabled -->
                        <template x-if="col.enabled && selectedColKey === key">
                            <div class="pt-2.5 border-t border-slate-200/80 space-y-2 text-xs">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500">Posisi X (mm)</label>
                                        <input type="text" inputmode="decimal"
                                            :value="col.x"
                                            @input="col.x = $event.target.value"
                                            placeholder="Contoh: 17.2 atau 17,2"
                                            class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500 font-mono bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500">Posisi Y (mm)</label>
                                        <input type="text" inputmode="decimal"
                                            :value="col.y"
                                            @input="col.y = $event.target.value"
                                            placeholder="Contoh: 5.5 atau 5,5"
                                            class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500 font-mono bg-white">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <template x-if="key !== 'qr_code'">
                                        <div class="flex-1">
                                            <label class="block text-[10px] font-bold text-slate-500">Warna Teks</label>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <input type="color" x-model="col.color" class="w-6 h-6 rounded cursor-pointer border-0 p-0">
                                                <span class="text-[10px] font-mono text-slate-600" x-text="col.color"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="key === 'qr_code'">
                                        <div class="flex-1">
                                            <label class="block text-[10px] font-bold text-slate-500">Ukuran QR (mm)</label>
                                            <input type="text" inputmode="decimal"
                                                :value="col.qr_size"
                                                @input="col.qr_size = $event.target.value"
                                                placeholder="Contoh: 14"
                                                class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500 font-mono mt-0.5 bg-white">
                                        </div>
                                    </template>
                                </div>

                                <template x-if="key === 'custom_text'">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500">Isi Teks Kustom</label>
                                        <input type="text" x-model="col.custom_value"
                                            class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500 mt-0.5 bg-white">
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

    </div>
</div>

<script>
function wristbandDesigner(initialMode, initialBackground, initialColumns) {
    return {
        mode: initialMode || 'default',
        backgroundPreview: initialBackground || null,
        removeBackgroundFlag: false,
        selectedColKey: 'category_name',
        isDragging: false,
        draggedKey: null,
        dragStartX: 0,
        dragStartY: 0,
        dragStartColX: 0,
        dragStartColY: 0,
        columns: initialColumns || {},

        get columnsJson() {
            const cleaned = JSON.parse(JSON.stringify(this.columns));
            for (let k in cleaned) {
                if (cleaned[k].x !== undefined) {
                    cleaned[k].x = parseFloat(String(cleaned[k].x).replace(',', '.')) || 0;
                }
                if (cleaned[k].y !== undefined) {
                    cleaned[k].y = parseFloat(String(cleaned[k].y).replace(',', '.')) || 0;
                }
                if (cleaned[k].qr_size !== undefined) {
                    cleaned[k].qr_size = parseFloat(String(cleaned[k].qr_size).replace(',', '.')) || 14;
                }
            }
            return JSON.stringify(cleaned);
        },

        onFileChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.removeBackgroundFlag = false;
                const reader = new FileReader();
                reader.onload = (event) => {
                    const dataUrl = event.target.result;
                    this.backgroundPreview = dataUrl;

                    // Automatically downscale and compress via HTML5 canvas to guarantee upload succeeds regardless of php.ini limits
                    const img = new Image();
                    img.onload = () => {
                        try {
                            const canvas = document.createElement('canvas');
                            let w = img.naturalWidth || img.width;
                            let h = img.naturalHeight || img.height;
                            const maxW = 2560;
                            if (w > maxW) {
                                h = Math.round(h * (maxW / w));
                                w = maxW;
                            }
                            canvas.width = w;
                            canvas.height = h;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, w, h);
                            
                            let compressed = canvas.toDataURL('image/webp', 0.9);
                            if (!compressed || !compressed.startsWith('data:image/webp')) {
                                compressed = canvas.toDataURL('image/jpeg', 0.9);
                            }
                            const base64Input = document.getElementById('wristband_custom_background_base64_input');
                            if (base64Input) base64Input.value = compressed;
                        } catch (err) {
                            const base64Input = document.getElementById('wristband_custom_background_base64_input');
                            if (base64Input) base64Input.value = dataUrl;
                        }
                    };
                    img.src = dataUrl;
                };
                reader.readAsDataURL(file);
            }
        },

        removeBackground() {
            this.backgroundPreview = null;
            this.removeBackgroundFlag = true;
            const input = document.getElementById('wristband_custom_background_input');
            if (input) input.value = '';
            const base64Input = document.getElementById('wristband_custom_background_base64_input');
            if (base64Input) base64Input.value = '';
        },

        onColMouseDown(e, key) {
            e.preventDefault();
            this.selectedColKey = key;
            this.isDragging = true;
            this.draggedKey = key;
            this.dragStartX = e.clientX;
            this.dragStartY = e.clientY;
            this.dragStartColX = parseFloat(String(this.columns[key].x).replace(',', '.')) || 0;
            this.dragStartColY = parseFloat(String(this.columns[key].y).replace(',', '.')) || 0;

            const onMouseMove = (moveEvent) => {
                if (!this.isDragging || !this.draggedKey) return;
                const canvasEl = document.getElementById('wb-interactive-canvas');
                if (!canvasEl) return;

                const rect = canvasEl.getBoundingClientRect();
                const mmPerPx = 215 / rect.width;

                const deltaX_px = moveEvent.clientX - this.dragStartX;
                const deltaY_px = moveEvent.clientY - this.dragStartY;

                let newX = this.dragStartColX + (deltaX_px * mmPerPx);
                let newY = this.dragStartColY + (deltaY_px * mmPerPx);

                newX = Math.max(0, Math.min(210, Math.round(newX * 10) / 10));
                newY = Math.max(0, Math.min(20, Math.round(newY * 10) / 10));

                this.columns[this.draggedKey].x = newX;
                this.columns[this.draggedKey].y = newY;
            };

            const onMouseUp = () => {
                this.isDragging = false;
                this.draggedKey = null;
                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);
            };

            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
        },

        onColTouchStart(e, key) {
            if (e.touches.length !== 1) return;
            const touch = e.touches[0];
            this.selectedColKey = key;
            this.isDragging = true;
            this.draggedKey = key;
            this.dragStartX = touch.clientX;
            this.dragStartY = touch.clientY;
            this.dragStartColX = parseFloat(String(this.columns[key].x).replace(',', '.')) || 0;
            this.dragStartColY = parseFloat(String(this.columns[key].y).replace(',', '.')) || 0;

            const onTouchMove = (moveEvent) => {
                if (!this.isDragging || !this.draggedKey || moveEvent.touches.length !== 1) return;
                const touchMove = moveEvent.touches[0];
                const canvasEl = document.getElementById('wb-interactive-canvas');
                if (!canvasEl) return;

                const rect = canvasEl.getBoundingClientRect();
                const mmPerPx = 215 / rect.width;

                const deltaX_px = touchMove.clientX - this.dragStartX;
                const deltaY_px = touchMove.clientY - this.dragStartY;

                let newX = this.dragStartColX + (deltaX_px * mmPerPx);
                let newY = this.dragStartColY + (deltaY_px * mmPerPx);

                newX = Math.max(0, Math.min(210, Math.round(newX * 10) / 10));
                newY = Math.max(0, Math.min(20, Math.round(newY * 10) / 10));

                this.columns[this.draggedKey].x = newX;
                this.columns[this.draggedKey].y = newY;
            };

            const onTouchEnd = () => {
                this.isDragging = false;
                this.draggedKey = null;
                window.removeEventListener('touchmove', onTouchMove);
                window.removeEventListener('touchend', onTouchEnd);
            };

            window.addEventListener('touchmove', onTouchMove, { passive: true });
            window.addEventListener('touchend', onTouchEnd);
        },

        resetDefaultPositions() {
            const defaults = {
                'qr_code': { x: 24, y: 2, qr_size: 14, color: '#000000' },
                'wristband_code': { x: 24, y: 17, color: '#111827' },
                'category_name': { x: 45, y: 5, color: '#111827' },
                'event_name': { x: 80, y: 3, color: '#111827' },
                'event_date': { x: 80, y: 11, color: '#374151' },
                'venue': { x: 80, y: 15.5, color: '#4b5563' },
                'seat_number': { x: 150, y: 5, color: '#111827' },
                'price': { x: 150, y: 12, color: '#111827' },
                'custom_text': { x: 140, y: 8, color: '#4b5563', custom_value: 'NON-REFUNDABLE • WAJIB IDENTITAS RESMI' }
            };

            for (let k in defaults) {
                if (this.columns[k]) {
                    Object.assign(this.columns[k], defaults[k]);
                }
            }
        }
    };
}

// Automatically disable empty custom background file input before submit
document.addEventListener('DOMContentLoaded', () => {
    const bgInput = document.getElementById('wristband_custom_background_input');
    if (bgInput) {
        const form = bgInput.closest('form');
        if (form) {
            form.addEventListener('submit', () => {
                if (!bgInput.files || bgInput.files.length === 0) {
                    bgInput.disabled = true;
                }
            });
        }
    }
});
</script>
