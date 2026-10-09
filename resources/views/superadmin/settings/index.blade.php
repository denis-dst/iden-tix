<x-app-layout>
    <x-slot name="title">Pengaturan Sistem</x-slot>

    <div class="max-w-6xl mx-auto" x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || 'theme' }">
        @if(session('success'))
            <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold shadow-sm animate-fade-in-down">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 overflow-hidden border border-slate-100 mb-8">
            <div class="flex border-b border-slate-100 overflow-x-auto custom-scrollbar">
                <button @click="activeTab = 'theme'" :class="activeTab === 'theme' ? 'border-blue-600 text-blue-600 bg-blue-50/40' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="flex-1 min-w-[140px] px-6 py-4 text-sm font-bold border-b-2 transition-all duration-200">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                        Tema Frontend
                    </div>
                </button>
                <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'border-orange-500 text-orange-600 bg-orange-50/30' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="flex-1 min-w-[120px] px-6 py-4 text-sm font-bold border-b-2 transition-all duration-200">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Umum & Fitur
                    </div>
                </button>
                <button @click="activeTab = 'appearance'" :class="activeTab === 'appearance' ? 'border-orange-500 text-orange-600 bg-orange-50/30' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="flex-1 min-w-[120px] px-6 py-4 text-sm font-bold border-b-2 transition-all duration-200">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
                        Identitas & Logo
                    </div>
                </button>
                <button @click="activeTab = 'social'" :class="activeTab === 'social' ? 'border-orange-500 text-orange-600 bg-orange-50/30' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="flex-1 min-w-[120px] px-6 py-4 text-sm font-bold border-b-2 transition-all duration-200">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        Media Sosial
                    </div>
                </button>
            </div>

            <!-- TAB 1: THEME SELECTOR & SWITCHER -->
            <div x-show="activeTab === 'theme'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-black text-slate-900 uppercase tracking-wider">Pilih Tema Frontend Aktif</h3>
                            <p class="text-xs text-slate-500 mt-1">Tentukan desain tampilan halaman beranda (landing page) yang aktif untuk seluruh pengunjung website.</p>
                        </div>
                        <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            <span>Buka Frontend</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- THEME 1: DEFAULT / TEMA UTAMA -->
                    <div class="rounded-3xl border-2 {{ ($activeTheme ?? 'default') === 'default' ? 'border-orange-500 bg-orange-50/20 shadow-xl shadow-orange-500/10 ring-4 ring-orange-500/10' : 'border-slate-200 bg-white hover:border-slate-300' }} p-6 flex flex-col justify-between transition-all">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full {{ ($activeTheme ?? 'default') === 'default' ? 'bg-orange-500 animate-pulse' : 'bg-slate-300' }}"></span>
                                    <h4 class="text-base font-black text-slate-900">Tema Utama (Default Classic)</h4>
                                </div>
                                @if(($activeTheme ?? 'default') === 'default')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full border border-emerald-300 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        SEDANG AKTIF
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full">
                                        TIDAK AKTIF
                                    </span>
                                @endif
                            </div>

                            <!-- Visual Mockup Card -->
                            <div class="w-full h-44 rounded-2xl bg-[#111118] border border-white/10 p-4 mb-4 flex flex-col justify-between overflow-hidden relative shadow-inner">
                                <div class="flex items-center justify-between border-b border-white/10 pb-2">
                                    <span class="text-xs font-black text-white font-mono">Iden<span class="text-orange-400">Tix</span></span>
                                    <div class="flex gap-2">
                                        <span class="w-8 h-2 bg-white/20 rounded"></span>
                                        <span class="w-8 h-2 bg-white/20 rounded"></span>
                                    </div>
                                </div>
                                <div class="space-y-1.5 my-auto">
                                    <span class="text-[9px] font-bold text-orange-400 uppercase tracking-widest block">Live Moments</span>
                                    <span class="text-xs font-black text-white block leading-tight">Connecting Generations Through Every Gate</span>
                                    <div class="flex gap-2 pt-1">
                                        <span class="px-2 py-0.5 rounded bg-orange-500 text-[8px] font-bold text-white">Explore Events</span>
                                        <span class="px-2 py-0.5 rounded bg-white/10 text-[8px] font-bold text-slate-300">How it Works</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="bg-white/5 border border-white/5 rounded p-1.5 text-[8px] text-slate-400">Concert Fest</div>
                                    <div class="bg-white/5 border border-white/5 rounded p-1.5 text-[8px] text-slate-400">Match Day</div>
                                    <div class="bg-white/5 border border-white/5 rounded p-1.5 text-[8px] text-slate-400">Seminar</div>
                                </div>
                            </div>

                            <p class="text-xs text-slate-600 leading-relaxed mb-4">
                                Tampilan dark-glassmorphism khas IdenTix dengan aksen oranye cyber, hero background sinematik, diagram alur 4 langkah interaktif, dan kartu event bernuansa modern dark mode.
                            </p>

                            <div class="flex flex-wrap gap-1.5 mb-6">
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[10px] font-bold">Dark Glassmorphism</span>
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[10px] font-bold">Aksen Cyber Orange</span>
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[10px] font-bold">Original IdenTix</span>
                            </div>
                        </div>

                        <div>
                            @if(($activeTheme ?? 'default') === 'default')
                                <button type="button" disabled class="w-full py-3.5 px-4 bg-emerald-600 text-white font-black text-xs uppercase tracking-wider rounded-2xl flex items-center justify-center gap-2 cursor-default shadow-md shadow-emerald-600/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Tema Ini Sedang Digunakan
                                </button>
                            @else
                                <form action="{{ route('superadmin.settings.theme-switch') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="theme" value="default">
                                    <button type="submit" class="w-full py-3.5 px-4 bg-slate-900 hover:bg-black text-white font-black text-xs uppercase tracking-wider rounded-2xl transition shadow-lg hover:-translate-y-0.5">
                                        Aktifkan Tema Utama (Default)
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- THEME 2: NEW-THEMA / TIKETMART STYLE -->
                    <div class="rounded-3xl border-2 {{ ($activeTheme ?? 'default') === 'new-thema' ? 'border-blue-600 bg-blue-50/20 shadow-xl shadow-blue-500/10 ring-4 ring-blue-500/10' : 'border-slate-200 bg-white hover:border-slate-300' }} p-6 flex flex-col justify-between transition-all">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full {{ ($activeTheme ?? 'default') === 'new-thema' ? 'bg-blue-600 animate-pulse' : 'bg-slate-300' }}"></span>
                                    <h4 class="text-base font-black text-slate-900">New Thema (TiketMart Style)</h4>
                                </div>
                                @if(($activeTheme ?? 'default') === 'new-thema')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full border border-emerald-300 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        SEDANG AKTIF
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full">
                                        TIDAK AKTIF
                                    </span>
                                @endif
                            </div>

                            <!-- Visual Mockup Card -->
                            <div class="w-full h-44 rounded-2xl bg-slate-50 border border-slate-200 p-4 mb-4 flex flex-col justify-between overflow-hidden relative shadow-inner">
                                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                    <span class="text-xs font-black text-slate-900">Iden<span class="text-blue-600">Tix</span></span>
                                    <div class="flex gap-1.5 items-center">
                                        <span class="w-16 h-4 bg-slate-200 rounded-full"></span>
                                        <span class="w-10 h-4 bg-blue-600 rounded-lg"></span>
                                    </div>
                                </div>
                                <div class="bg-blue-900 rounded-xl p-2.5 text-white my-auto flex justify-between items-center shadow-xs">
                                    <div>
                                        <span class="text-[8px] bg-orange-500 px-1.5 py-0.5 rounded font-bold uppercase">Unggulan</span>
                                        <span class="text-[10px] font-black block mt-0.5">Konser Musik & Festival Akbar</span>
                                    </div>
                                    <span class="px-2 py-1 bg-white/20 rounded text-[8px] font-bold">Beli Tiket</span>
                                </div>
                                <div class="flex gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full bg-blue-600 text-white text-[8px] font-bold">Semua</span>
                                    <span class="px-2 py-0.5 rounded-full bg-white border border-slate-200 text-slate-600 text-[8px] font-bold">Musik</span>
                                    <span class="px-2 py-0.5 rounded-full bg-white border border-slate-200 text-slate-600 text-[8px] font-bold">Olahraga</span>
                                    <span class="px-2 py-0.5 rounded-full bg-white border border-slate-200 text-slate-600 text-[8px] font-bold">Festival</span>
                                </div>
                            </div>

                            <p class="text-xs text-slate-600 leading-relaxed mb-4">
                                Tampilan modern clean & bright terinspirasi dari portal tiket TiketMart.com. Menampilkan banner slider interaktif, pill filter kategori instan, kartu event informatif, dan bagian keunggulan bisnis.
                            </p>

                            <div class="flex flex-wrap gap-1.5 mb-6">
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">Clean Light Mode</span>
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">TiketMart Style</span>
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">Banner Carousel</span>
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">Pills Filter</span>
                            </div>
                        </div>

                        <div>
                            @if(($activeTheme ?? 'default') === 'new-thema')
                                <button type="button" disabled class="w-full py-3.5 px-4 bg-emerald-600 text-white font-black text-xs uppercase tracking-wider rounded-2xl flex items-center justify-center gap-2 cursor-default shadow-md shadow-emerald-600/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Tema Ini Sedang Digunakan
                                </button>
                            @else
                                <form action="{{ route('superadmin.settings.theme-switch') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="theme" value="new-thema">
                                    <button type="submit" class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-wider rounded-2xl transition shadow-lg shadow-blue-600/20 hover:-translate-y-0.5">
                                        Aktifkan New Thema (TiketMart Style)
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            <form x-show="activeTab !== 'theme'" action="{{ route('superadmin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-8">
                    <!-- General & Feature Settings -->
                    <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                        
                        <!-- Tenant Self-Registration Toggle -->
                        <div class="mb-8 p-6 bg-slate-50 border-2 border-slate-100 rounded-3xl space-y-4">
                            <div>
                                <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Pendaftaran Tenant Mandiri</h3>
                                <p class="text-xs text-slate-400 mt-1">Kontrol akses bagi penyelenggara event (partner baru) untuk mendaftar akun secara mandiri melalui form registrasi.</p>
                            </div>
                            
                            <div class="pt-2">
                                <label class="flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-2xl cursor-pointer hover:border-orange-400 transition"
                                       x-data="{ enabled: {{ $tenantRegistrationEnabled ? 'true' : 'false' }} }">
                                    <div class="relative shrink-0">
                                        <input type="checkbox" name="tenant_registration_enabled" value="1"
                                               x-model="enabled"
                                               {{ $tenantRegistrationEnabled ? 'checked' : '' }}
                                               class="sr-only">
                                        <div :class="enabled ? 'bg-orange-600' : 'bg-slate-300'" class="w-12 h-6 rounded-full transition-colors duration-200">
                                            <div :class="enabled ? 'translate-x-6' : 'translate-x-1'" class="w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-200 mt-1"></div>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-black text-slate-800">Izinkan Pendaftaran Partner Mandiri</p>
                                            <span :class="enabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                                                  class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-md border tracking-wider"
                                                  x-text="enabled ? 'Aktif' : 'Nonaktif'"></span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            Bila dinonaktifkan, tombol registrasi di halaman depan dan halaman login akan disembunyikan, serta form registrasi tidak menerima pendaftaran baru.
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Global Notification Settings -->
                        <div class="mb-8 p-6 bg-slate-50 border-2 border-slate-100 rounded-3xl space-y-4">
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Pengaturan Notifikasi Global E-Voucher</h3>
                            <p class="text-xs text-slate-400">Aktifkan atau matikan pengiriman E-Voucher secara otomatis ke semua tenant (penyedia event).</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                <!-- Email Notification Toggle -->
                                <label class="flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-2xl cursor-pointer hover:border-indigo-400 transition"
                                       x-data="{ enabled: {{ ($globalNotifications['email_notifications_enabled'] == '1' || $globalNotifications['email_notifications_enabled'] === true) ? 'true' : 'false' }} }">
                                    <div class="relative shrink-0">
                                        <input type="checkbox" name="global_email_notifications_enabled" value="1"
                                               x-model="enabled"
                                               {{ ($globalNotifications['email_notifications_enabled'] == '1' || $globalNotifications['email_notifications_enabled'] === true) ? 'checked' : '' }}
                                               class="sr-only">
                                        <div :class="enabled ? 'bg-indigo-600' : 'bg-slate-300'" class="w-12 h-6 rounded-full transition-colors duration-200">
                                            <div :class="enabled ? 'translate-x-6' : 'translate-x-1'" class="w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-200 mt-1"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-800">Kirim via Email</p>
                                        <p class="text-[10px] text-slate-500">Default untuk semua tenant</p>
                                    </div>
                                </label>

                                <!-- WhatsApp Notification Toggle -->
                                <label class="flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-2xl cursor-pointer hover:border-emerald-400 transition"
                                       x-data="{ enabled: {{ ($globalNotifications['wa_notifications_enabled'] == '1' || $globalNotifications['wa_notifications_enabled'] === true) ? 'true' : 'false' }} }">
                                    <div class="relative shrink-0">
                                        <input type="checkbox" name="global_wa_notifications_enabled" value="1"
                                               x-model="enabled"
                                               {{ ($globalNotifications['wa_notifications_enabled'] == '1' || $globalNotifications['wa_notifications_enabled'] === true) ? 'checked' : '' }}
                                               class="sr-only">
                                        <div :class="enabled ? 'bg-emerald-600' : 'bg-slate-300'" class="w-12 h-6 rounded-full transition-colors duration-200">
                                            <div :class="enabled ? 'translate-x-6' : 'translate-x-1'" class="w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-200 mt-1"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-800">Kirim via WhatsApp</p>
                                        <p class="text-[10px] text-slate-500">Default untuk semua tenant</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- General Settings Grid -->
                        <div class="space-y-6">
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Pengaturan Umum</h3>
                            @foreach($settings['general'] ?? [] as $item)
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase text-slate-500 tracking-wider flex items-center gap-2">
                                    {{ str_replace('_', ' ', $item->key) }}
                                    @if($item->key === 'app_name') <span class="text-rose-500">*</span> @endif
                                </label>
                                @if(Str::contains($item->key, 'description') || Str::contains($item->key, 'address') || Str::contains($item->key, 'footer'))
                                    <textarea name="{{ $item->key }}" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all min-h-[100px]">{{ $item->value }}</textarea>
                                @else
                                    <input type="text" name="{{ $item->key }}" value="{{ $item->value }}" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all">
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Appearance Settings -->
                    <div x-show="activeTab === 'appearance'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            @foreach($settings['appearance'] ?? [] as $item)
                                @if(Str::contains($item->key, 'logo') || Str::contains($item->key, 'favicon') || Str::contains($item->key, 'icon'))
                                <div class="space-y-4">
                                    <label class="text-xs font-black uppercase text-slate-500 tracking-wider">{{ str_replace('_', ' ', $item->key) }}</label>
                                    <div class="flex items-center gap-6">
                                        <div class="w-24 h-24 rounded-2xl bg-slate-100 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden shrink-0 group relative">
                                            @if($item->value)
                                                <img src="{{ asset('storage/' . $item->value) }}" class="w-full h-full object-contain">
                                            @else
                                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <input type="file" name="{{ $item->key }}" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 transition-all">
                                            <p class="mt-2 text-[10px] text-slate-400 uppercase font-bold tracking-tight">Format: PNG, JPG, WEBP. Max: 2MB.</p>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="space-y-2">
                                    <label class="text-xs font-black uppercase text-slate-500 tracking-wider">{{ str_replace('_', ' ', $item->key) }}</label>
                                    <textarea name="{{ $item->key }}" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all min-h-[80px]">{{ $item->value }}</textarea>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Social Media Settings -->
                    <div x-show="activeTab === 'social'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            @foreach($settings['social'] ?? [] as $item)
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase text-slate-500 tracking-wider flex items-center gap-2">
                                    @if(Str::contains($item->key, 'facebook')) <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg> @endif
                                    @if(Str::contains($item->key, 'twitter')) <svg class="w-4 h-4 text-sky-500" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.84 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg> @endif
                                    @if(Str::contains($item->key, 'instagram')) <svg class="w-4 h-4 text-pink-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg> @endif
                                    @if(Str::contains($item->key, 'youtube')) <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg> @endif
                                    {{ str_replace('social_', '', $item->key) }}
                                </label>
                                <input type="text" name="{{ $item->key }}" value="{{ $item->value }}" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all" placeholder="https://...">
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-12 flex justify-end">
                        <button type="submit" class="group flex items-center gap-3 px-8 py-4 bg-orange-600 hover:bg-orange-700 text-white font-black rounded-2xl shadow-xl shadow-orange-200 transition-all hover:-translate-y-1 active:translate-y-0">
                            <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                            SIMPAN PERUBAHAN
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100">
                <div class="w-12 h-12 rounded-2xl bg-blue-500 text-white flex items-center justify-center mb-4 shadow-lg shadow-blue-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h4 class="text-sm font-black text-blue-900 uppercase tracking-wider mb-2">Pendaftaran Tenant</h4>
                <p class="text-xs text-blue-700 leading-relaxed font-medium">Bila fitur pendaftaran dimatikan, pihak penyelenggara baru hanya bisa didaftarkan langsung oleh SuperAdmin melalui menu Tenant Management.</p>
            </div>
            <div class="bg-amber-50 p-6 rounded-3xl border border-amber-100">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center mb-4 shadow-lg shadow-amber-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <h4 class="text-sm font-black text-amber-900 uppercase tracking-wider mb-2">Logo & Icon</h4>
                <p class="text-xs text-amber-700 leading-relaxed font-medium">Gunakan gambar dengan latar belakang transparan (PNG) untuk hasil terbaik pada logo dan icon.</p>
            </div>
            <div class="bg-purple-50 p-6 rounded-3xl border border-purple-100">
                <div class="w-12 h-12 rounded-2xl bg-purple-500 text-white flex items-center justify-center mb-4 shadow-lg shadow-purple-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h4 class="text-sm font-black text-purple-900 uppercase tracking-wider mb-2">Pembaruan</h4>
                <p class="text-xs text-purple-700 leading-relaxed font-medium">Setelah menyimpan, perubahan pengaturan sistem langsung aktif dan tersinkronisasi di seluruh aplikasi.</p>
            </div>
        </div>
    </div>

    <style>
        @keyframes fade-in-down {
            0% { opacity: 0; transform: translateY(-10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down { animation: fade-in-down 0.4s ease-out forwards; }
    </style>
</x-app-layout>
