<x-app-layout>
    <x-slot name="hideNav">{{ true }}</x-slot>
    <x-slot name="title">Konfigurasi Gate - {{ $event->name }}</x-slot>

    <div class="h-full w-full flex flex-col bg-slate-50 overflow-hidden font-sans"
         x-data="{
             selectedGate: '{{ $currentGateId }}',
             mode: '{{ $currentMode }}',
             autoTimer: {{ $currentAutoTimer ? 'true' : 'false' }}
         }">

        <!-- Header Bar -->
        <header class="h-auto min-h-[5.5rem] pt-10 sm:pt-0 flex items-center justify-between px-4 sm:px-6 bg-white border-b border-slate-200 shrink-0 z-50">
            <div class="flex items-center gap-3 py-4 sm:py-0">
                <a href="{{ route('organizer.gate.verify', $event) }}" 
                   class="flex items-center gap-2 bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-xl transition text-slate-600 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">KEMBALI</span>
                </a>
            </div>

            <div class="text-center">
                <h1 class="text-xs sm:text-sm font-black uppercase tracking-[0.2em] text-slate-800 font-outfit">ACCESS CONTROL</h1>
                <p class="text-[10px] font-bold text-orange-600 tracking-widest uppercase mt-0.5">Konfigurasi Gate System</p>
            </div>

            <div class="flex items-center gap-2 justify-end">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    Online
                </span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl transition text-[10px] font-black uppercase tracking-wider" title="Logout">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4-4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 flex flex-col items-center justify-start sm:justify-center custom-scrollbar">
            <div class="w-full max-w-lg space-y-5 my-auto">

                <!-- 1. Event Info Card -->
                <div class="bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100">
                    <div class="flex items-center gap-2 text-emerald-700 text-xs font-bold tracking-wider uppercase mb-2">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span>Event Terpilih</span>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 font-outfit leading-snug">
                        {{ $event->name }}
                    </h2>

                    <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs text-slate-600 font-medium">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="truncate">{{ $event->venue }}</span>
                        </div>

                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>{{ \Carbon\Carbon::parse($event->event_start_date)->translatedFormat('d F Y, H:i') }} WIB</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Form Card -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xl shadow-slate-200/50 border border-slate-100">
                    <form action="{{ route('organizer.gate.setup.post', $event) }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="gate_id" :value="selectedGate">
                        <input type="hidden" name="gate_mode" :value="mode">
                        <input type="hidden" name="gate_auto_timer" :value="autoTimer ? '1' : '0'">

                        <!-- Pilih Gate -->
                        <div>
                            <label class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
                                Pilih Gate
                            </label>
                            
                            <div class="relative">
                                <select x-model="selectedGate"
                                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-900 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-100 outline-none transition cursor-pointer">
                                    <option value="all" class="text-orange-600 font-bold py-2">
                                        ⚡ All Gate (Semua Kategori Tiket)
                                    </option>
                                    
                                    @if($gates->isNotEmpty())
                                        <optgroup label="── Gate Terdaftar ──" class="text-slate-600 font-semibold">
                                            @foreach($gates as $gate)
                                                <option value="{{ $gate->id }}" class="text-slate-900 font-bold py-2">
                                                    🚪 {{ $gate->name }} ({{ $gate->ticketCategories->count() }} Kategori Diizinkan)
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif

                                    @if($categories->isNotEmpty())
                                        <optgroup label="── Kategori Spesifik (Manual) ──" class="text-slate-600 font-semibold">
                                            @foreach($categories as $category)
                                                <option value="cat_{{ $category->id }}" class="text-slate-900 font-medium py-2">
                                                    🎟️ Tiket {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                            </div>

                            <div class="mt-2 text-[11px] font-medium px-1">
                                <template x-if="selectedGate === 'all'">
                                    <span class="text-orange-600 flex items-center gap-1.5 font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                        Mode All Gate: Memproses semua kategori tiket & wristband tanpa batasan.
                                    </span>
                                </template>
                                <template x-if="selectedGate !== 'all'">
                                    <span class="text-emerald-700 flex items-center gap-1.5 font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Hanya tiket yang diizinkan untuk gate ini yang dapat masuk.
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Mode Akses Gate -->
                        <div>
                            <label class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
                                Mode Akses Gate
                            </label>

                            <div class="grid grid-cols-2 gap-3.5">
                                <!-- Check In Button -->
                                <button type="button" 
                                        @click="mode = 'IN'"
                                        :class="mode === 'IN' 
                                            ? 'border-2 border-emerald-500 bg-emerald-50 text-emerald-900 shadow-md shadow-emerald-100' 
                                            : 'border-2 border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                        class="p-4 rounded-2xl flex items-center gap-3 transition-all duration-200 active:scale-95 text-left cursor-pointer">
                                    <div :class="mode === 'IN' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-200' : 'bg-white text-slate-400 border border-slate-200'"
                                         class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black uppercase tracking-wider" :class="mode === 'IN' ? 'text-emerald-700' : 'text-slate-800'">Check In</div>
                                        <div class="text-[11px] font-medium" :class="mode === 'IN' ? 'text-emerald-600' : 'text-slate-400'">Pintu Masuk</div>
                                    </div>
                                </button>

                                <!-- Check Out Button -->
                                <button type="button" 
                                        @click="mode = 'OUT'"
                                        :class="mode === 'OUT' 
                                            ? 'border-2 border-orange-500 bg-orange-50 text-orange-900 shadow-md shadow-orange-100' 
                                            : 'border-2 border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                        class="p-4 rounded-2xl flex items-center gap-3 transition-all duration-200 active:scale-95 text-left cursor-pointer">
                                    <div :class="mode === 'OUT' ? 'bg-orange-600 text-white shadow-md shadow-orange-200' : 'bg-white text-slate-400 border border-slate-200'"
                                         class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black uppercase tracking-wider" :class="mode === 'OUT' ? 'text-orange-700' : 'text-slate-800'">Check Out</div>
                                        <div class="text-[11px] font-medium" :class="mode === 'OUT' ? 'text-orange-600' : 'text-slate-400'">Pintu Keluar</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Pengaturan Gate -->
                        <div>
                            <div class="mb-1">
                                <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                                    Pengaturan Gate
                                </label>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Cara menutup hasil scan sebelum kembali ke mode siap scan.</p>
                            </div>

                            <div class="grid grid-cols-2 gap-3.5 mt-2.5">
                                <!-- Tombol OK (Manual Confirmation) -->
                                <button type="button"
                                        @click="autoTimer = false"
                                        :class="!autoTimer 
                                            ? 'border-2 border-indigo-600 bg-indigo-50 text-indigo-900 shadow-md shadow-indigo-100' 
                                            : 'border-2 border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                        class="p-4 rounded-2xl flex items-center gap-3 transition-all duration-200 active:scale-95 text-left cursor-pointer">
                                    <div :class="!autoTimer ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-white text-slate-400 border border-slate-200'"
                                         class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs sm:text-sm font-bold truncate" :class="!autoTimer ? 'text-indigo-900' : 'text-slate-800'">Tombol OK</div>
                                        <div class="text-[10px] font-medium truncate" :class="!autoTimer ? 'text-indigo-600' : 'text-slate-400'">Konfirmasi manual</div>
                                    </div>
                                </button>

                                <!-- Timer Otomatis (Auto Reset 3s) -->
                                <button type="button"
                                        @click="autoTimer = true"
                                        :class="autoTimer 
                                            ? 'border-2 border-indigo-600 bg-indigo-50 text-indigo-900 shadow-md shadow-indigo-100' 
                                            : 'border-2 border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                        class="p-4 rounded-2xl flex items-center gap-3 transition-all duration-200 active:scale-95 text-left cursor-pointer">
                                    <div :class="autoTimer ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-white text-slate-400 border border-slate-200'"
                                         class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs sm:text-sm font-bold truncate" :class="autoTimer ? 'text-indigo-900' : 'text-slate-800'">Timer Otomatis</div>
                                        <div class="text-[10px] font-medium truncate" :class="autoTimer ? 'text-indigo-600' : 'text-slate-400'">Kembali 3 detik</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full py-4 sm:py-4.5 bg-orange-600 hover:bg-orange-700 text-white rounded-2xl font-black shadow-xl shadow-orange-500/25 transition transform active:scale-[0.98] flex items-center justify-center gap-3 uppercase tracking-widest text-xs sm:text-sm cursor-pointer">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <span>Scan Wristband / QR</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>
</x-app-layout>
