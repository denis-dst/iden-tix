<x-app-layout>
    <x-slot name="hideNav">{{ true }}</x-slot>
    <x-slot name="title">SCANNER - {{ $event->name }}</x-slot>

    <div class="h-full w-full flex flex-col bg-[#010409] text-white overflow-hidden font-sans" x-data="gateScanner()">
        <!-- Header Bar -->
        <header
            class="h-auto min-h-[5.5rem] pt-10 sm:pt-0 flex items-center justify-between px-4 sm:px-6 bg-[#0d1117] border-b border-white/5 shrink-0 z-50">
            <div class="flex items-center gap-3 sm:gap-4 py-4 sm:py-0">
                <a href="{{ route('organizer.gate.setup', $event) }}"
                    class="flex items-center gap-2 bg-white/5 hover:bg-white/10 px-3 py-2 rounded-xl transition text-slate-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">BACK</span>
                </a>
                <div class="leading-tight">
                    <h2
                        class="text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] text-white/90 truncate max-w-[120px] sm:max-w-none font-outfit">
                        {{ $event->name }}</h2>
                    <p class="text-[8px] sm:text-[10px] font-bold text-orange-500 uppercase tracking-widest mt-0.5">
                        {{ session('gate_name') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <!-- Mode Switcher -->
                <div class="flex bg-black p-1 rounded-xl border border-white/5">
                    <button @click="setMode('IN')"
                        :class="mode === 'IN' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-500 hover:text-slate-300'"
                        class="px-3 sm:px-4 py-1 sm:py-1.5 rounded-lg text-[8px] sm:text-[10px] font-black transition-all uppercase tracking-widest">IN</button>
                    <button @click="setMode('OUT')"
                        :class="mode === 'OUT' ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-500 hover:text-slate-300'"
                        class="px-3 sm:px-4 py-1 sm:py-1.5 rounded-lg text-[8px] sm:text-[10px] font-black transition-all uppercase tracking-widest">OUT</button>
                </div>

                <!-- Input Type Switcher -->
                <div class="flex bg-black p-1 rounded-xl border border-white/5">
                    <button @click="setInputType('auto')"
                        :class="inputType === 'auto' ? 'bg-indigo-600 text-white' : 'text-slate-500'"
                        class="p-1.5 sm:p-2 rounded-lg transition-all" title="Auto Scan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </button>
                    <button @click="setInputType('camera')"
                        :class="inputType === 'camera' ? 'bg-indigo-600 text-white' : 'text-slate-500'"
                        class="p-1.5 sm:p-2 rounded-lg transition-all" title="Camera Scan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                    <button @click="setInputType('manual')"
                        :class="inputType === 'manual' ? 'bg-indigo-600 text-white' : 'text-slate-500'"
                        class="p-1.5 sm:p-2 rounded-lg transition-all" title="Manual Input">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                </div>

                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin keluar?')" class="w-10 h-10 flex items-center justify-center bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 rounded-xl transition-all border border-rose-500/30" title="Keluar / Logout" aria-label="Keluar / Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Content Body -->
        <main class="flex-1 flex flex-col lg:flex-row overflow-hidden relative">
            <!-- Scan Area -->
            <div class="flex-1 flex flex-col min-h-0 relative bg-black overflow-hidden">

                <!-- Main Status & Scanner Interface -->
                <div class="absolute inset-0 flex flex-col items-center justify-center transition-all duration-300">
                    
                    <!-- Background Flash -->
                    <div class="absolute inset-0 opacity-20 transition-all duration-500"
                         :class="{
                            'bg-emerald-500': status === 'success',
                            'bg-rose-500': status === 'error',
                            'bg-slate-900': status === 'idle'
                         }">
                    </div>

                    <!-- IR Hidden Input (Better compatibility for mobile) -->
                    <input type="text" x-ref="ticketInput" @keydown.enter="processScan()"
                        class="fixed opacity-0 pointer-events-none -left-[9999px]" 
                        x-model="scannedCode" autocomplete="off"
                        x-show="inputType === 'auto'">

                    <!-- Camera Container & Tap-to-Refocus Wrapper -->
                    <div x-show="inputType === 'camera'" 
                        class="absolute inset-0 w-full h-full bg-black overflow-hidden select-none cursor-pointer"
                        @click="triggerRefocus($event)">
                        
                        <div id="reader" class="w-full h-full relative"></div>

                        <!-- Tap to Focus Reticle / Ring Indicator -->
                        <div x-show="focusRing.visible" x-cloak
                            class="absolute pointer-events-none transition-all duration-200 transform -translate-x-1/2 -translate-y-1/2 z-30"
                            :style="`left: ${focusRing.x}px; top: ${focusRing.y}px;`">
                            <div class="w-16 h-16 border-2 border-amber-400 rounded-2xl animate-ping duration-500 opacity-60"></div>
                            <div class="w-16 h-16 border-2 border-amber-400 rounded-2xl -mt-16 shadow-[0_0_15px_rgba(251,191,36,0.9)] flex items-center justify-center">
                                <div class="w-1.5 h-1.5 bg-amber-400 rounded-full"></div>
                            </div>
                        </div>

                        <!-- Floating Camera Toolbar (Top) -->
                        <div x-show="cameraStarted && status === 'idle'" 
                            class="absolute top-4 left-4 right-4 z-40 flex items-center justify-between pointer-events-auto"
                            @click.stop>
                            
                            <!-- Switch Camera Button -->
                            <div class="flex items-center gap-2">
                                <button type="button" @click="switchCamera()" 
                                    x-show="cameraDevices && cameraDevices.length > 1"
                                    class="flex items-center gap-2 bg-black/70 hover:bg-black/90 backdrop-blur-md px-3 py-2 rounded-xl border border-white/15 text-white text-xs font-bold transition shadow-lg active:scale-95"
                                    title="Ganti Lensa Kamera">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span class="text-[10px] font-bold text-slate-200" x-text="getCameraShortName()"></span>
                                </button>
                                
                                <div x-show="!cameraDevices || cameraDevices.length <= 1"
                                    class="bg-black/70 backdrop-blur-md px-3 py-2 rounded-xl border border-white/10 text-[10px] font-bold text-slate-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                                    <span x-text="getCameraShortName()"></span>
                                </div>
                            </div>

                            <!-- Right Controls: Zoom & Torch -->
                            <div class="flex items-center gap-2">
                                <!-- Zoom Toggle (if supported by device) -->
                                <template x-if="hasZoom && maxZoom > 1">
                                    <div class="flex bg-black/70 backdrop-blur-md p-1 rounded-xl border border-white/15">
                                        <button type="button" @click="setZoom(1)" 
                                            :class="currentZoom <= 1.2 ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'"
                                            class="px-2.5 py-1 rounded-lg text-[9px] font-black transition">1x</button>
                                        <button type="button" @click="setZoom(2)" 
                                            :class="currentZoom > 1.2 ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'"
                                            class="px-2.5 py-1 rounded-lg text-[9px] font-black transition">2x</button>
                                    </div>
                                </template>

                                <!-- Torch / Flashlight Toggle (if supported by device) -->
                                <button type="button" @click="toggleTorch()" 
                                    x-show="hasTorch"
                                    :class="torchOn ? 'bg-amber-500 text-black border-amber-400 shadow-amber-500/40' : 'bg-black/70 text-slate-300 border-white/15 hover:bg-black/90'"
                                    class="flex items-center gap-1.5 px-3 py-2 rounded-xl border backdrop-blur-md text-xs font-bold transition shadow-lg active:scale-95"
                                    title="Nyalakan/Matikan Flashlight">
                                    <svg class="w-4 h-4" :class="torchOn ? 'text-black' : 'text-amber-400'" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                                    </svg>
                                    <span class="text-[10px] font-black" x-text="torchOn ? 'FLASH ON' : 'FLASH'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Camera Error / Retry Modal -->
                    <div x-show="cameraError" x-cloak class="absolute inset-0 flex items-center justify-center z-40 pointer-events-auto p-4">
                        <div class="bg-black/95 p-6 sm:p-8 rounded-2xl border border-white/10 text-center max-w-md w-full shadow-2xl backdrop-blur-xl">
                            <div class="w-12 h-12 rounded-full bg-rose-500/10 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-500/20">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="text-base font-black text-white uppercase tracking-wider">Kamera Bermasalah</div>
                            <p class="text-xs text-slate-300 mt-2 leading-relaxed" x-text="cameraErrorMessage || 'Periksa izin kamera pada browser atau pilih lensa kamera lain.'"></p>

                            <template x-if="cameraDevices && cameraDevices.length">
                                <div class="mt-5 text-left">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pilih Lensa Kamera</label>
                                    <select x-model="selectedCameraId" class="w-full mt-1.5 p-2.5 rounded-xl bg-white/5 border border-white/10 text-xs text-white focus:ring-indigo-500 focus:border-indigo-500">
                                        <template x-for="dev in cameraDevices" :key="dev.id">
                                            <option :value="dev.id" x-text="dev.label || dev.id" class="bg-slate-900 text-white"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>

                            <div class="mt-6 flex flex-col sm:flex-row gap-2.5 justify-center">
                                <button @click="startCamera(true)" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">Coba Kamera Utama</button>
                                <button @click="switchCamera(selectedCameraId)" x-show="selectedCameraId" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition">Pakai Lensa Terpilih</button>
                                <button @click="setInputType('manual')" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition">Gunakan Manual</button>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Entry Card -->
                    <div x-show="inputType === 'manual'"
                        class="z-20 w-full max-w-sm p-6 animate-in zoom-in duration-300">
                        <div
                            class="bg-slate-900/90 backdrop-blur-xl border border-white/10 p-8 rounded-[2.5rem] shadow-2xl">
                            <h3
                                class="text-[10px] font-black text-center uppercase tracking-[0.4em] text-indigo-400 mb-2">
                                Input Manual Kode Tiket</h3>
                            <p class="text-[11px] text-slate-400 text-center mb-6">Ketik kode angka yang tertera di bawah QR Code</p>
                            <input type="text" 
                                inputmode="numeric" 
                                pattern="[0-9]*" 
                                x-model="manualCode" 
                                placeholder="Contoh: 8492019384"
                                @keydown.enter="processManualScan()"
                                class="w-full bg-white/5 border-2 border-white/10 rounded-2xl px-6 py-5 text-2xl font-black text-center tracking-[0.2em] font-mono text-white focus:border-indigo-500 focus:ring-0 mb-6 placeholder:text-white/20">
                            <button @click="processManualScan()"
                                class="w-full py-5 bg-indigo-600 rounded-2xl font-black uppercase tracking-widest text-sm hover:bg-indigo-700 transition shadow-lg shadow-indigo-600/20 active:scale-[0.98]">Validasi
                                Tiket</button>
                        </div>
                    </div>

                    <!-- Feedback Overlays -->
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-30">
                        <!-- Idle Overlay (Auto Scan Mode) -->
                        <div x-show="status === 'idle' && inputType === 'auto'"
                            class="flex flex-col items-center justify-center w-full h-full">
                            <div class="relative group">
                                <div class="absolute inset-0 rounded-full bg-indigo-500/20 animate-ping duration-1000"></div>
                                <div class="absolute -inset-8 rounded-full bg-indigo-500/5 animate-pulse duration-700"></div>
                                <div class="w-56 h-56 sm:w-80 sm:h-80 border-2 border-white/10 rounded-[3rem] relative bg-white/5 backdrop-blur-sm shadow-2xl shadow-indigo-500/10">
                                    <div class="absolute top-0 left-0 w-16 h-16 border-t-4 border-l-4 border-indigo-500 rounded-tl-[2.5rem]"></div>
                                    <div class="absolute top-0 right-0 w-16 h-16 border-t-4 border-r-4 border-indigo-500 rounded-tr-[2.5rem]"></div>
                                    <div class="absolute bottom-0 left-0 w-16 h-16 border-b-4 border-l-4 border-indigo-500 rounded-bl-[2.5rem]"></div>
                                    <div class="absolute bottom-0 right-0 w-16 h-16 border-b-4 border-r-4 border-indigo-500 rounded-br-[2.5rem]"></div>
                                    <div class="absolute top-1/2 left-6 right-6 h-[2px] bg-red-500 shadow-[0_0_15px_rgba(239,68,68,0.8)] blur-[0.5px] animate-scan-line"></div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-20">
                                        <svg class="w-24 h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-12 text-center">
                                <p class="text-xs sm:text-sm font-black uppercase tracking-[0.6em] text-white/60 animate-pulse">Ready to Scan</p>
                                <p class="mt-2 text-[8px] font-bold text-slate-500 uppercase tracking-widest opacity-50">Connect Infrared Scanner or Type Code</p>
                            </div>
                        </div>

                        <!-- Idle Overlay (Camera Mode) -->
                        <div x-show="status === 'idle' && inputType === 'camera'"
                            class="flex flex-col items-center justify-center w-full h-full pointer-events-none z-20">
                            <div class="w-64 h-64 sm:w-80 sm:h-80 border border-white/20 rounded-[2.5rem] relative">
                                <!-- Corner target accents -->
                                <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-indigo-500 rounded-tl-2xl"></div>
                                <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-indigo-500 rounded-tr-2xl"></div>
                                <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-indigo-500 rounded-bl-2xl"></div>
                                <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-indigo-500 rounded-br-2xl"></div>
                                
                                <div class="absolute inset-0 bg-indigo-500/5 rounded-[2.5rem]"></div>
                                <div class="absolute top-1/2 left-4 right-4 h-0.5 bg-gradient-to-r from-transparent via-red-500 to-transparent shadow-[0_0_12px_rgba(239,68,68,0.9)] animate-scan-line"></div>
                            </div>
                            <div class="mt-8 flex flex-col items-center gap-1">
                                <p class="text-[11px] font-black uppercase tracking-[0.3em] text-white/90">Arahkan QR ke Tengah Kotak</p>
                                <p class="text-[9px] font-semibold text-slate-400 tracking-wider">Ketuk layar jika kamera buram untuk fokus ulang</p>
                            </div>
                        </div>

                        <!-- Success Overlay -->
                        <div x-show="status === 'success'"
                            class="bg-[#065f46] fixed inset-0 flex flex-col items-center justify-center p-6 animate-in fade-in duration-200 z-50 pointer-events-auto cursor-pointer select-none"
                            @click="dismissResult()">
                            <div class="w-28 h-28 sm:w-32 sm:h-32 bg-white rounded-full flex items-center justify-center text-emerald-600 mb-6 shadow-2xl animate-bounce">
                                <svg class="w-16 h-16 sm:w-20 sm:h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <h3 class="text-3xl sm:text-5xl font-black text-white uppercase tracking-tight text-center mb-3 font-outfit px-4" x-text="result.customer"></h3>
                            <div class="px-6 py-2 bg-white text-emerald-900 rounded-full font-black text-xs sm:text-sm uppercase tracking-[0.2em] mb-3" x-text="result.category"></div>
                            <p class="text-lg sm:text-2xl font-black uppercase tracking-[0.4em] text-emerald-200/90 mb-6">BERHASIL</p>

                            <!-- Confirm OK Button (especially useful in manual OK mode, or to dismiss immediately) -->
                            <button type="button" @click.stop="dismissResult()"
                                class="px-8 py-3.5 bg-white text-emerald-900 hover:bg-emerald-50 rounded-2xl font-black text-xs sm:text-sm uppercase tracking-widest transition shadow-2xl active:scale-95 flex items-center gap-2">
                                <span>OK / Scan Berikutnya</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>

                        <!-- Error Overlay -->
                        <div x-show="status === 'error'"
                            class="bg-[#9f1239] fixed inset-0 flex flex-col items-center justify-center p-6 animate-in shake duration-300 z-50 pointer-events-auto cursor-pointer select-none"
                            @click="dismissResult()">
                            <div class="w-28 h-28 sm:w-32 sm:h-32 bg-white rounded-full flex items-center justify-center text-rose-600 mb-6 shadow-2xl">
                                <svg class="w-16 h-16 sm:w-20 sm:h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12" /></svg>
                            </div>
                            <h3 class="text-2xl sm:text-4xl font-black text-white uppercase tracking-tight text-center mb-4 font-outfit">AKSES DITOLAK</h3>
                            <div class="bg-black/40 px-6 py-4 rounded-2xl border border-white/20 text-base sm:text-xl font-black text-center max-w-lg leading-relaxed mb-3" x-text="errorMessage"></div>
                            <p class="text-white/40 font-bold uppercase tracking-[0.3em] text-xs mb-6" x-text="'CODE: ' + lastScannedCode"></p>

                            <button type="button" @click.stop="dismissResult()"
                                class="px-8 py-3.5 bg-white text-rose-900 hover:bg-rose-50 rounded-2xl font-black text-xs sm:text-sm uppercase tracking-widest transition shadow-2xl active:scale-95 flex items-center gap-2">
                                <span>Tutup / Coba Lagi (OK)</span>
                            </button>
                        </div>

                        <!-- Processing Overlay -->
                        <div x-show="status === 'processing'"
                            class="bg-black/90 fixed inset-0 flex flex-col items-center justify-center backdrop-blur-xl z-50">
                            <div class="w-20 h-20 border-4 border-white/5 border-t-indigo-500 rounded-full animate-spin mb-6"></div>
                            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-indigo-400">Validating...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Group Check-in Modal Overlay -->
            <div x-show="isGroupScan" x-cloak 
                 class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 sm:p-6 z-[60] overflow-y-auto">
                <div x-show="isGroupScan" 
                     @click.away="closeGroupModal()"
                     class="bg-[#0d1117] border border-white/10 w-full max-w-lg rounded-[2rem] shadow-2xl flex flex-col overflow-hidden my-8 transform transition-all animate-in zoom-in duration-300">
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-5 bg-[#161b22] border-b border-white/5 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-black font-outfit text-white">Daftar Anggota Transaksi</h3>
                            <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mt-0.5" x-text="'Invoice: ' + (groupData ? groupData.customer : '')"></p>
                        </div>
                        <button @click="closeGroupModal()" class="text-slate-400 hover:text-white p-1 hover:bg-white/5 rounded-lg transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body (Attendees List) -->
                    <div class="p-6 max-h-[60vh] overflow-y-auto space-y-3 custom-scrollbar">
                        <div class="flex justify-between items-center text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                            <span>Daftar Peserta</span>
                            <button type="button" @click="toggleSelectAllGroup()" class="text-indigo-400 hover:text-indigo-300 text-[10px]">
                                <span x-text="selectedTicketIds.length === groupData?.attendees.length ? 'Batal Pilih Semua' : 'Pilih Semua'"></span>
                            </button>
                        </div>

                        <template x-for="attendee in (groupData ? groupData.attendees : [])" :key="attendee.ticket_id">
                            <label :class="selectedTicketIds.includes(attendee.ticket_id) ? 'border-indigo-500/50 bg-indigo-500/5' : 'border-white/5 bg-[#161b22]/50 hover:bg-[#161b22]'"
                                   class="flex items-center justify-between p-4 border rounded-xl cursor-pointer transition">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" :value="attendee.ticket_id" x-model="selectedTicketIds" 
                                           class="rounded border-white/10 bg-white/5 text-indigo-600 focus:ring-0 focus:ring-offset-0 w-4 h-4">
                                    <div>
                                        <div class="text-sm font-black text-white" x-text="attendee.name"></div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider" x-text="attendee.ticket_code"></span>
                                            <span class="text-[8px] bg-white/5 text-slate-400 px-1.5 py-0.5 rounded uppercase" x-text="attendee.gender || 'n/a'"></span>
                                        </div>
                                        <template x-if="attendee.is_checked_in">
                                            <div class="text-[10px] text-emerald-400 font-bold mt-1" x-text="'In: ' + (attendee.checked_in_at || '-') + ' oleh ' + (attendee.checked_in_by || '-')"></div>
                                        </template>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <template x-if="attendee.is_checked_in">
                                        <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[8px] font-black uppercase rounded-lg tracking-widest">DI DALAM</span>
                                    </template>
                                    <template x-if="!attendee.is_checked_in">
                                        <span class="px-2.5 py-1 bg-slate-500/10 text-slate-400 border border-white/5 text-[8px] font-black uppercase rounded-lg tracking-widest">DI LUAR</span>
                                    </template>
                                </div>
                            </label>
                        </template>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-[#161b22] border-t border-white/5 flex gap-3">
                        <button @click="closeGroupModal()" 
                                class="flex-1 py-3 border border-white/10 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-white/5 transition">
                            Batal
                        </button>
                        <button @click="submitBulkCheckin()" 
                                :disabled="selectedTicketIds.length === 0"
                                :class="mode === 'IN' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : 'bg-orange-600 hover:bg-orange-700 shadow-orange-600/20'"
                                class="flex-1 py-3 text-white disabled:opacity-40 disabled:pointer-events-none rounded-xl text-xs font-black uppercase tracking-widest transition shadow-lg">
                            Konfirmasi <span x-text="mode === 'IN' ? 'Check-in' : 'Check-out'"></span> (<span x-text="selectedTicketIds.length"></span>)
                        </button>
                    </div>
                </div>
            </div>

            <!-- History Sidebar -->
            <aside class="hidden lg:flex w-96 bg-[#010409] border-l border-white/5 flex-col shrink-0 h-full overflow-hidden z-40">
                <div class="h-16 px-6 bg-[#0d1117] border-b border-white/5 flex items-center justify-between shrink-0">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Activity Log</h4>
                    <span class="bg-indigo-500/10 text-indigo-400 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider" x-text="history.length + ' Entri'"></span>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                    <template x-for="(log, index) in history" :key="log.time + index">
                        <div class="flex items-center justify-between p-4 rounded-2xl border transition-all animate-in slide-in-from-right-4 duration-300"
                             :class="log.success ? 'bg-white/5 border-white/5 hover:border-indigo-500/30' : 'bg-rose-500/5 border-rose-500/20'">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-[10px] font-black shadow-inner"
                                     :class="log.type === 'IN' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-orange-500/10 text-orange-400'">
                                    <span x-text="log.type"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-black text-white/90 truncate font-outfit" x-text="log.name || 'Access Denied'"></div>
                                    <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-0.5 truncate" x-text="log.category || log.code"></div>
                                </div>
                            </div>
                            <div class="text-[9px] font-black text-slate-600 whitespace-nowrap bg-black/40 px-2 py-1 rounded-lg" x-text="log.time"></div>
                        </div>
                    </template>
                    <template x-if="history.length === 0">
                        <div class="flex flex-col items-center justify-center py-20 opacity-10">
                            <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <p class="text-[10px] font-black uppercase tracking-[0.4em]">Waiting for data</p>
                        </div>
                    </template>
                </div>
            </aside>
        </main>

        <!-- Bottom Stats Bar (Global) -->
        <div class="bg-black border-t border-white/5 p-4 sm:p-6 grid grid-cols-3 gap-4 sm:gap-8 shrink-0 z-50">
            <div class="text-center group">
                <div class="text-xl sm:text-3xl font-black font-outfit text-emerald-500 leading-none mb-1 sm:mb-2" x-text="inCount">0</div>
                <div class="text-[8px] sm:text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] group-hover:text-emerald-400 transition-colors">Total In</div>
            </div>
            <div class="text-center group border-x border-white/5">
                <div class="text-xl sm:text-3xl font-black font-outfit text-orange-500 leading-none mb-1 sm:mb-2" x-text="outCount">0</div>
                <div class="text-[8px] sm:text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] group-hover:text-orange-400 transition-colors">Total Out</div>
            </div>
            <div class="text-center group">
                <div class="text-xl sm:text-3xl font-black font-outfit text-white leading-none mb-1 sm:mb-2" x-text="inCount - outCount">0</div>
                <div class="text-[8px] sm:text-[9px] font-black text-indigo-400 uppercase tracking-[0.2em] group-hover:text-indigo-300 transition-colors">Inside</div>
            </div>
        </div>
    </div>

    <!-- Audio Effects -->
    <audio id="sound-success" src="{{ asset('sounds/success.mp3') }}"></audio>
    <audio id="sound-error" src="{{ asset('sounds/invalid.mp3') }}"></audio>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        function gateScanner() {
            return {
                mode: '{{ session('gate_mode', 'IN') }}',
                inputType: 'camera',
                status: 'idle',
                gateAutoTimer: {{ session('gate_auto_timer', true) ? 'true' : 'false' }},
                autoResetTimer: null,
                cameraError: false,
                cameraErrorMessage: '',
                cameraDevices: [],
                selectedCameraId: null,
                cameraStarted: false,
                isStartingCamera: false,
                lastScanTime: 0,
                hasTorch: false,
                torchOn: false,
                hasZoom: false,
                currentZoom: 1,
                minZoom: 1,
                maxZoom: 1,
                focusRing: { visible: false, x: 0, y: 0, timer: null },
                scannedCode: '',
                manualCode: '',
                lastScannedCode: '',
                errorMessage: '',
                result: { customer: '', category: '' },
                inCount: {{ $inCount }},
                outCount: {{ $outCount }},
                history: [],
                html5QrCode: null,

                // Group Scan State
                isGroupScan: false,
                groupData: null,
                selectedTicketIds: [],

                init() {
                    this.$nextTick(() => {
                        if (this.inputType === 'camera') {
                            setTimeout(() => this.startCamera(true), 300);
                        } else {
                            this.focusInput();
                        }
                    });
                    document.addEventListener('click', () => { if (this.inputType === 'auto' && !this.isGroupScan) this.focusInput(); });
                    setInterval(() => { if (this.inputType === 'auto' && this.status === 'idle' && !this.isGroupScan) this.focusInput(); }, 1000);
                    
                    // Allow dismiss by Space, Enter, or Escape key
                    window.addEventListener('keydown', (e) => {
                        if ((e.key === ' ' || e.key === 'Enter' || e.key === 'Escape') && (this.status === 'success' || this.status === 'error')) {
                            e.preventDefault();
                            this.dismissResult();
                        }
                    });
                },

                dismissResult() {
                    if (this.autoResetTimer) {
                        clearTimeout(this.autoResetTimer);
                        this.autoResetTimer = null;
                    }
                    if (this.status === 'success' || this.status === 'error') {
                        this.status = 'idle';
                    }
                },

                focusInput() { if (this.$refs.ticketInput) this.$refs.ticketInput.focus(); },

                setMode(val) { this.mode = val; this.status = 'idle'; },

                setInputType(val) {
                    if (this.inputType === 'camera' && val !== 'camera') {
                        this.stopCamera();
                    }
                    this.inputType = val;
                    if (val === 'camera') {
                        this.status = 'idle';
                        setTimeout(() => this.startCamera(true), 300);
                    }
                    if (val === 'auto') {
                        this.$nextTick(() => this.focusInput());
                    }
                },

                playSound(id) {
                    try {
                        const audio = document.getElementById(id);
                        if (audio) {
                            audio.currentTime = 0;
                            audio.play().catch(() => {});
                        }
                    } catch (e) {}
                },

                async startCamera(preferFacingMode = true) {
                    if (this.isStartingCamera) return;
                    this.isStartingCamera = true;
                    this.cameraError = false;
                    this.cameraErrorMessage = '';

                    if (this.html5QrCode) {
                        await this.stopCamera();
                    }

                    this.html5QrCode = new Html5Qrcode("reader");

                    const config = {
                        fps: 15,
                        qrbox: (viewfinderWidth, viewfinderHeight) => {
                            const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                            const size = Math.max(220, Math.floor(minEdge * 0.75));
                            return { width: Math.min(size, 380), height: Math.min(size, 380) };
                        },
                        experimentalFeatures: {
                            useBarCodeDetectorIfSupported: true
                        }
                    };

                    // Enumerate available cameras in background
                    Html5Qrcode.getCameras().then(devices => {
                        this.cameraDevices = devices || [];
                        if (!this.selectedCameraId && devices && devices.length > 0) {
                            const mainBack = devices.find(d => /back|rear|environment/i.test(d.label) && !/wide|ultra|0\.5|tele|macro/i.test(d.label));
                            const anyBack = devices.find(d => /back|rear|environment/i.test(d.label));
                            this.selectedCameraId = (mainBack || anyBack || devices[0]).id;
                        }
                    }).catch(e => {
                        console.warn('getCameras warning:', e);
                    });

                    const onScanSuccess = (text) => {
                        // CRITICAL: Only scan when idle to prevent infinite error/shake loop
                        if (this.status === 'idle' && !this.isGroupScan) {
                            const now = Date.now();
                            // Debounce exact duplicate scans within 2.5 seconds
                            if (text === this.lastScannedCode && (now - this.lastScanTime) < 2500) {
                                return;
                            }
                            this.lastScanTime = now;
                            this.scannedCode = text;
                            this.processScan();
                        }
                    };

                    const onScanFailure = (err) => {
                        // per-frame decode failure ignored
                    };

                    // Prefer standard { facingMode: "environment" } by default so browser picks primary 1x rear camera with autofocus
                    let cameraConstraint = { facingMode: "environment" };
                    if (!preferFacingMode && this.selectedCameraId) {
                        cameraConstraint = { deviceId: { exact: this.selectedCameraId } };
                    }

                    try {
                        await this.html5QrCode.start(cameraConstraint, config, onScanSuccess, onScanFailure);
                        this.cameraStarted = true;
                        this.cameraError = false;
                        this.status = 'idle';
                        this.isStartingCamera = false;
                        setTimeout(() => this.updateCameraCapabilities(), 500);
                    } catch (err) {
                        console.warn("Camera start with facingMode failed, falling back to deviceId:", err);
                        try {
                            const devices = await Html5Qrcode.getCameras();
                            this.cameraDevices = devices || [];
                            if (!devices || devices.length === 0) {
                                throw new Error("Tidak ada perangkat kamera ditemukan.");
                            }

                            const mainBack = devices.find(d => /back|rear|environment/i.test(d.label) && !/wide|ultra|0\.5|tele|macro/i.test(d.label));
                            const anyBack = devices.find(d => /back|rear|environment/i.test(d.label));
                            const target = mainBack || anyBack || devices[0];
                            this.selectedCameraId = target.id;

                            await this.html5QrCode.start(
                                { deviceId: { exact: target.id } },
                                config,
                                onScanSuccess,
                                onScanFailure
                            );
                            this.cameraStarted = true;
                            this.cameraError = false;
                            this.status = 'idle';
                            this.isStartingCamera = false;
                            setTimeout(() => this.updateCameraCapabilities(), 500);
                        } catch (err2) {
                            this.cameraError = true;
                            this.cameraErrorMessage = (err2 && err2.message) ? err2.message : String(err2);
                            this.isStartingCamera = false;
                            console.error("All camera start attempts failed:", err2);
                        }
                    }
                },

                async switchCamera(deviceId = null) {
                    if (this.isStartingCamera) return;

                    if (deviceId) {
                        this.selectedCameraId = deviceId;
                    } else if (this.cameraDevices && this.cameraDevices.length > 1) {
                        const currentIndex = this.cameraDevices.findIndex(d => d.id === this.selectedCameraId);
                        const nextIndex = (currentIndex + 1) % this.cameraDevices.length;
                        this.selectedCameraId = this.cameraDevices[nextIndex].id;
                    }

                    await this.startCamera(false);
                },

                async stopCamera() {
                    this.torchOn = false;
                    if (this.html5QrCode) {
                        try {
                            if (this.html5QrCode.isScanning) {
                                await this.html5QrCode.stop();
                            }
                            this.html5QrCode.clear();
                        } catch (e) {
                            console.warn("stopCamera warning:", e);
                        }
                    }
                    this.cameraStarted = false;
                },

                updateCameraCapabilities() {
                    try {
                        const video = document.querySelector('#reader video');
                        if (!video || !video.srcObject) return;
                        const track = video.srcObject.getVideoTracks()[0];
                        if (!track || !track.getCapabilities) return;

                        const caps = track.getCapabilities();
                        const settings = track.getSettings ? track.getSettings() : {};
                        if (settings.deviceId) {
                            this.selectedCameraId = settings.deviceId;
                        }

                        this.hasTorch = !!caps.torch;
                        this.hasZoom = !!caps.zoom;
                        if (caps.zoom) {
                            this.minZoom = caps.zoom.min || 1;
                            this.maxZoom = caps.zoom.max || 1;
                            this.currentZoom = settings.zoom || 1;
                        }

                        // Ensure continuous autofocus is enabled on track
                        if (caps.focusMode && caps.focusMode.includes('continuous')) {
                            track.applyConstraints({
                                advanced: [{ focusMode: 'continuous' }]
                            }).catch(() => {});
                        }
                    } catch (e) {
                        console.warn('updateCameraCapabilities error:', e);
                    }
                },

                triggerRefocus(e) {
                    if (this.inputType !== 'camera' || this.status !== 'idle') return;

                    // Animate tap-to-focus indicator
                    const rect = e.currentTarget.getBoundingClientRect();
                    this.focusRing.x = e.clientX - rect.left;
                    this.focusRing.y = e.clientY - rect.top;
                    this.focusRing.visible = true;
                    clearTimeout(this.focusRing.timer);
                    this.focusRing.timer = setTimeout(() => {
                        this.focusRing.visible = false;
                    }, 900);

                    // Re-trigger autofocus constraint on video track
                    try {
                        const video = document.querySelector('#reader video');
                        if (video && video.srcObject) {
                            const track = video.srcObject.getVideoTracks()[0];
                            if (track && track.getCapabilities) {
                                const caps = track.getCapabilities();
                                if (caps.focusMode && caps.focusMode.includes('continuous')) {
                                    track.applyConstraints({
                                        advanced: [{ focusMode: 'continuous' }]
                                    }).catch(() => {});
                                }
                            }
                        }
                    } catch (err) {}
                },

                async toggleTorch() {
                    try {
                        const video = document.querySelector('#reader video');
                        if (!video || !video.srcObject) return;
                        const track = video.srcObject.getVideoTracks()[0];
                        if (!track) return;

                        this.torchOn = !this.torchOn;
                        await track.applyConstraints({
                            advanced: [{ torch: this.torchOn }]
                        });
                    } catch (e) {
                        console.warn('Torch toggle failed:', e);
                        this.torchOn = false;
                    }
                },

                async setZoom(level) {
                    try {
                        const video = document.querySelector('#reader video');
                        if (!video || !video.srcObject) return;
                        const track = video.srcObject.getVideoTracks()[0];
                        if (!track || !track.applyConstraints) return;

                        const target = Math.min(Math.max(level, this.minZoom), this.maxZoom);
                        this.currentZoom = target;
                        await track.applyConstraints({
                            advanced: [{ zoom: target }]
                        });
                    } catch (e) {
                        console.warn('Set zoom failed:', e);
                    }
                },

                getCameraShortName() {
                    const active = this.cameraDevices.find(d => d.id === this.selectedCameraId);
                    const label = active?.label || (this.cameraStarted ? 'Kamera Belakang' : 'Kamera');
                    if (/front|user/i.test(label)) return 'Kamera Depan';
                    if (/ultra|wide.*0\.5/i.test(label)) return 'Kamera Wide';
                    if (/tele/i.test(label)) return 'Kamera Tele';
                    if (/back|rear|environment/i.test(label)) return 'Kamera Belakang';
                    return label.length > 18 ? label.substring(0, 16) + '...' : label;
                },

                processManualScan() { if (!this.manualCode) return; this.scannedCode = this.manualCode; this.manualCode = ''; this.processScan(); },

                processScan() {
                    if (!this.scannedCode || this.status === 'processing') return;
                    const code = this.scannedCode.trim();
                    this.scannedCode = '';
                    this.lastScannedCode = code;
                    this.status = 'processing';

                    fetch('{{ route('organizer.gate.process') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ 
                            ticket_code: code, 
                            event_id: {{ $event->id }}, 
                            mode: this.mode, 
                            gate_name: '{{ session('gate_name') }}' 
                        })
                    })
                    .then(r => r.ok ? r.json() : r.json().then(e => { throw e }))
                    .then(d => this.handleSuccess(d))
                    .catch(e => this.handleError(e.message || 'Gagal'))
                    .finally(() => { if (this.inputType === 'auto' && !this.isGroupScan) this.focusInput(); });
                },

                handleSuccess(d) {
                    if (d.is_group) {
                        this.status = 'idle';
                        this.groupData = d;
                        this.isGroupScan = true;
                        
                        this.selectedTicketIds = d.attendees
                            .filter(a => {
                                if (this.mode === 'IN') {
                                    return !a.is_checked_in || a.ticket_id === d.scanned_ticket_id;
                                } else {
                                    return a.is_checked_in || a.ticket_id === d.scanned_ticket_id;
                                }
                            })
                            .map(a => a.ticket_id);
                        
                        this.playSound('sound-success');
                        return;
                    }

                    this.status = 'success';
                    this.result = { customer: d.customer, category: d.category };
                    this.inCount = d.in_count; this.outCount = d.out_count;
                    this.history.unshift({ success: true, type: this.mode, name: d.customer, category: d.category, time: new Date().toLocaleTimeString() });
                    this.playSound('sound-success');

                    if (this.autoResetTimer) clearTimeout(this.autoResetTimer);
                    if (this.gateAutoTimer) {
                        this.autoResetTimer = setTimeout(() => { if (this.status === 'success') this.status = 'idle'; }, 3000);
                    }
                },

                handleError(msg) {
                    this.status = 'error';
                    this.errorMessage = msg;
                    this.history.unshift({ success: false, type: this.mode, code: this.lastScannedCode, time: new Date().toLocaleTimeString() });
                    this.playSound('sound-error');

                    if (this.autoResetTimer) clearTimeout(this.autoResetTimer);
                    if (this.gateAutoTimer) {
                        this.autoResetTimer = setTimeout(() => { if (this.status === 'error') this.status = 'idle'; }, 4500);
                    }
                },

                closeGroupModal() {
                    this.isGroupScan = false;
                    this.groupData = null;
                    this.selectedTicketIds = [];
                    if (this.inputType === 'auto') this.$nextTick(() => this.focusInput());
                },

                toggleSelectAllGroup() {
                    if (!this.groupData) return;
                    if (this.selectedTicketIds.length === this.groupData.attendees.length) {
                        this.selectedTicketIds = [];
                    } else {
                        this.selectedTicketIds = this.groupData.attendees.map(a => a.ticket_id);
                    }
                },

                submitBulkCheckin() {
                    if (this.selectedTicketIds.length === 0) return;
                    this.status = 'processing';
                    
                    fetch('{{ route('organizer.gate.bulk-checkin', $event) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            ticket_ids: this.selectedTicketIds,
                            mode: this.mode,
                            gate_name: '{{ session('gate_name') }}'
                        })
                    })
                    .then(r => r.ok ? r.json() : r.json().then(e => { throw e }))
                    .then(d => {
                        this.inCount = d.in_count;
                        this.outCount = d.out_count;
                        
                        // Add elements to log history
                        this.selectedTicketIds.forEach(id => {
                            const att = this.groupData.attendees.find(a => a.ticket_id === id);
                            if (att) {
                                this.history.unshift({
                                    success: true,
                                    type: this.mode,
                                    name: att.name,
                                    category: att.category,
                                    time: new Date().toLocaleTimeString()
                                });
                            }
                        });

                        // Show quick temporary success state
                        this.isGroupScan = false;
                        this.groupData = null;
                        this.selectedTicketIds = [];
                        
                        this.status = 'success';
                        this.result = { customer: 'Check-in Grup', category: 'Berhasil memproses ' + this.mode };
                        setTimeout(() => { this.status = 'idle'; }, 1500);
                    })
                    .catch(e => {
                        this.isGroupScan = false;
                        this.handleError(e.message || 'Gagal memproses bulk check-in');
                    })
                    .finally(() => { if (this.inputType === 'auto') this.focusInput(); });
                }
            }
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 3px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 10px; }
        .animate-scan-line { animation: scan-line 2s ease-in-out infinite; }
        @keyframes scan-line { 0%, 100% { top: 8%; } 50% { top: 92%; } }

        /* Ensure camera container fills screen cleanly */
        #reader {
            background: #000 !important;
            overflow: hidden !important;
            position: absolute !important;
            inset: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }

        /* Camera video stream */
        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            z-index: 1 !important;
            display: block !important;
            visibility: visible !important;
            transform: translateZ(0) !important;
            backface-visibility: hidden !important;
        }

        /* CRITICAL: Hide internal decoding canvas from viewport so it NEVER flickers/shakes over video */
        #reader canvas {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        /* Hide built-in shaded region in favor of custom modern UI reticle */
        #reader #qr-shaded-region {
            display: none !important;
        }

        /* Ensure overlays (scan decorative UI) sit above video */
        .shake { animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both; }
        @keyframes shake { 10%, 90% { transform: translate3d(-1px, 0, 0); } 20%, 80% { transform: translate3d(2px, 0, 0); } 30%, 50%, 70% { transform: translate3d(-4px, 0, 0); } 40%, 60% { transform: translate3d(4px, 0, 0); } }
    </style>
</x-app-layout>