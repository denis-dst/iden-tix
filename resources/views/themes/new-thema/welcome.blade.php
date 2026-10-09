@php
    $registrationOpen = (bool) (\App\Models\Setting::where('key', 'tenant_registration_enabled')->value('value') ?? true);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings['app_name'] ?? 'IdenTix' }} - {{ $settings['app_tagline'] ?? 'Platform Tiket & Event Terpercaya' }}</title>
    <meta name="description" content="{{ $settings['meta_description'] ?? 'Beli tiket event, konser musik, seminar, dan festival dengan mudah, aman, dan instan di IdenTix.' }}">

    <!-- Fonts: Plus Jakarta Sans & Inter for crisp readability -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')))
        @php
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
            $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        @endphp
        @if($cssFile)
            <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
        @else
            @vite(['resources/css/app.css'])
        @endif
        @if($jsFile)
            <script type="module" src="{{ asset('build/' . $jsFile) }}" defer></script>
        @else
            @vite(['resources/js/app.js'])
        @endif
    @elseif(file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        },
                        colors: {
                            brand: {
                                50: '#eff6ff',
                                100: '#dbeafe',
                                200: '#bfdbfe',
                                300: '#93c5fd',
                                400: '#60a5fa',
                                500: '#3b82f6',
                                600: '#2563eb',
                                700: '#1d4ed8',
                                800: '#1e40af',
                                900: '#1e3a8a',
                                950: '#172554',
                            },
                            accent: {
                                500: '#f97316',
                                600: '#ea580c',
                            }
                        }
                    }
                }
            }
        </script>
    @endif

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar for horizontal category list */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px -8px rgba(15, 23, 42, 0.12), 0 4px 12px -2px rgba(15, 23, 42, 0.06);
        }
    </style>
    <meta name="wago-verification" content="WAGO-C2742A2D">
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 flex flex-col antialiased">

    <!-- Top Announcement Bar (TiketMart Style) -->
    <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-blue-950 text-white text-xs py-2 px-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="bg-orange-500 text-white font-bold px-2 py-0.5 rounded text-[10px] tracking-wide uppercase">Info</span>
                <span class="truncate font-medium text-slate-200">Platform Resmi Tiket Online & Manajemen Event IdenTix</span>
            </div>
            <div class="hidden sm:flex items-center gap-4 text-slate-300">
                <a href="{{ route('faq') }}" class="hover:text-white transition">Pusat Bantuan</a>
                <span class="text-slate-600">&bull;</span>
                <a href="{{ route('flow') }}" class="hover:text-white transition">Alur Bisnis & Verifikasi</a>
                <span class="text-slate-600">&bull;</span>
                <a href="{{ route('contact') }}" class="hover:text-white transition">Hubungi Kami</a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar (Clean White TiketMart Style) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center gap-4">
                
                <!-- Logo -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                        @if(isset($settings['app_logo']) && $settings['app_logo'])
                            @php
                                $logoPath = $settings['app_logo'];
                                $webpLogo = preg_replace('/\.(png|jpe?g)$/i', '.webp', $logoPath);
                                $finalLogo = file_exists(public_path('storage/' . $webpLogo)) ? asset('storage/' . $webpLogo) : asset('storage/' . $logoPath);
                            @endphp
                            <img src="{{ $finalLogo }}" alt="{{ $settings['app_name'] ?? 'IdenTix' }}" width="160" height="40" class="h-10 w-auto object-contain">
                        @else
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center shadow-md shadow-blue-500/20 text-white font-black text-xl">
                                <span>I</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-2xl font-black tracking-tight text-slate-900 leading-none">
                                    Iden<span class="text-blue-600">Tix</span>
                                </span>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-600 mt-0.5">Ticket Portal</span>
                            </div>
                        @endif
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex items-center gap-6 lg:gap-8">
                    <a href="#events-section" class="text-sm font-bold text-slate-800 hover:text-blue-600 transition">Semua Event</a>
                    <a href="#categories-section" class="text-sm font-bold text-slate-800 hover:text-blue-600 transition">Kategori</a>
                    <a href="#why-us" class="text-sm font-bold text-slate-800 hover:text-blue-600 transition">Keunggulan</a>
                    <a href="{{ route('portofolio') }}" class="text-sm font-bold text-slate-800 hover:text-blue-600 transition">Portofolio</a>
                    <a href="{{ route('flow') }}" class="text-sm font-bold text-slate-800 hover:text-blue-600 transition">Alur Bisnis</a>
                </nav>

                <!-- Action Controls & Auth -->
                <div class="flex items-center gap-3">
                    <!-- Language Switcher -->
                    <div class="relative hidden sm:block" x-data="{ openLang: false }">
                        <button @click="openLang = !openLang" class="px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:text-blue-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition flex items-center gap-1">
                            <span>{{ strtoupper(app()->getLocale()) }}</span>
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="openLang" x-cloak @click.away="openLang = false" class="absolute top-full mt-2 right-0 bg-white border border-slate-200 rounded-xl shadow-lg py-1 w-28 z-50">
                            <a href="{{ route('lang.switch', 'id') }}" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-bold {{ app()->getLocale() == 'id' ? 'bg-blue-50 text-blue-600' : '' }}">Indonesia (ID)</a>
                            <a href="{{ route('lang.switch', 'en') }}" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-blue-50 hover:text-blue-600 font-bold {{ app()->getLocale() == 'en' ? 'bg-blue-50 text-blue-600' : '' }}">English (EN)</a>
                        </div>
                    </div>

                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold transition shadow-md shadow-blue-600/20 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Dashboard</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-bold text-slate-700 hover:text-blue-600 transition">
                            Masuk
                        </a>
                        @php
                            $registrationOpen = (bool) (\App\Models\Setting::where('key', 'tenant_registration_enabled')->value('value') ?? true);
                        @endphp
                        @if (Route::has('register') && $registrationOpen)
                            <a href="{{ route('register') }}" class="hidden sm:inline-flex px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold transition shadow-md shadow-orange-600/20">
                                Buat Event
                            </a>
                        @endif
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 transition" aria-label="Toggle menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 space-y-3 shadow-lg">
            <a @click="mobileMenuOpen = false" href="#events-section" class="block px-3 py-2 rounded-lg text-base font-bold text-slate-800 hover:bg-blue-50 hover:text-blue-600">Semua Event</a>
            <a @click="mobileMenuOpen = false" href="#categories-section" class="block px-3 py-2 rounded-lg text-base font-bold text-slate-800 hover:bg-blue-50 hover:text-blue-600">Kategori</a>
            <a @click="mobileMenuOpen = false" href="#why-us" class="block px-3 py-2 rounded-lg text-base font-bold text-slate-800 hover:bg-blue-50 hover:text-blue-600">Keunggulan</a>
            <a @click="mobileMenuOpen = false" href="{{ route('portofolio') }}" class="block px-3 py-2 rounded-lg text-base font-bold text-slate-800 hover:bg-blue-50 hover:text-blue-600">Portofolio</a>
            <a @click="mobileMenuOpen = false" href="{{ route('flow') }}" class="block px-3 py-2 rounded-lg text-base font-bold text-slate-800 hover:bg-blue-50 hover:text-blue-600">Alur Bisnis</a>
            
            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                @guest
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-xl border border-slate-300 font-bold text-slate-700">Masuk Akun</a>
                    @if (Route::has('register') && $registrationOpen)
                        <a href="{{ route('register') }}" class="w-full text-center py-2.5 rounded-xl bg-orange-600 text-white font-bold shadow">Daftar Jadi Partner</a>
                    @endif
                @endguest
            </div>
        </div>
    </header>

    <!-- Main Container with Reactive Alpine State for Search & Filters -->
    <main class="flex-1" x-data="{
        searchQuery: '',
        selectedCategory: 'all',
        selectedCity: 'all',
        eventTab: 'all',
        activeSlide: 0,
        slidesCount: {{ max($events->count(), 1) }},
        init() {
            setInterval(() => {
                this.activeSlide = (this.activeSlide + 1) % this.slidesCount;
            }, 5000);
        }
    }">

        <!-- HERO BANNER CAROUSEL (TiketMart Style) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-4">
            <div class="relative rounded-3xl overflow-hidden shadow-xl bg-slate-900 aspect-[21/9] sm:aspect-[24/9] lg:aspect-[28/9] min-h-[220px] max-h-[440px] group">
                
                @if($events->count() > 0)
                    @foreach($events->take(4) as $idx => $slideEvent)
                        @php
                            $slideImg = asset('images/hero.webp');
                            if ($slideEvent->background_image) {
                                if (str_starts_with($slideEvent->background_image, 'http')) {
                                    $slideImg = $slideEvent->background_image;
                                } else {
                                    $slideImg = asset('storage/' . $slideEvent->background_image);
                                }
                            }
                        @endphp
                        <div x-show="activeSlide === {{ $idx }}"
                             x-transition:enter="transition ease-out duration-700"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-500"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-105"
                             class="absolute inset-0 w-full h-full">
                            <img src="{{ $slideImg }}" alt="{{ $slideEvent->name }}" class="w-full h-full object-cover">
                            <!-- Soft gradient vignette -->
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/60 to-transparent flex items-center">
                                <div class="px-6 sm:px-12 lg:px-16 max-w-2xl text-white space-y-3">
                                    <div class="inline-flex items-center gap-2 bg-blue-600/90 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider text-white">
                                        <span>★ Event Unggulan</span>
                                        <span class="text-white/60">&bull;</span>
                                        <span>{{ $slideEvent->city ?? 'Indonesia' }}</span>
                                    </div>
                                    <h2 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight line-clamp-2">
                                        {{ $slideEvent->name }}
                                    </h2>
                                    <p class="text-xs sm:text-sm text-slate-300 font-medium flex items-center gap-2">
                                        <svg class="w-4 h-4 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ $slideEvent->event_start_date->isoFormat('dddd, D MMMM Y • HH:mm') }} WIB</span>
                                    </p>
                                    <div class="pt-2 flex items-center gap-3">
                                        <a href="{{ route('events.show', $slideEvent->slug) }}" class="px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs sm:text-sm font-bold shadow-lg shadow-orange-600/30 transition transform hover:-translate-y-0.5">
                                            Beli Tiket Sekarang
                                        </a>
                                        <a href="{{ route('events.show', $slideEvent->slug) }}" class="px-5 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs sm:text-sm font-bold transition">
                                            Detail Event
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Fallback Slide Banner -->
                    <div class="absolute inset-0 w-full h-full">
                        <img src="{{ asset('images/hero.webp') }}" alt="IdenTix Banner" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-950/50 to-transparent flex items-center">
                            <div class="px-8 sm:px-16 max-w-2xl text-white space-y-4">
                                <span class="bg-blue-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Tiket Resmi</span>
                                <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                                    {{ $settings['hero_title'] ?? 'Connecting Generations Through Every Gate' }}
                                </h2>
                                <p class="text-sm text-slate-300 font-medium">
                                    {{ $settings['hero_subtitle'] ?? 'Platform sistem tiket dan manajemen event modern untuk pengalaman pembelian tiket yang cepat dan terpercaya.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Navigation Arrows (Desktop) -->
                @if($events->count() > 1)
                    <button @click="activeSlide = (activeSlide - 1 + slidesCount) % slidesCount" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-sm transition opacity-0 group-hover:opacity-100 z-20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click="activeSlide = (activeSlide + 1) % slidesCount" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-sm transition opacity-0 group-hover:opacity-100 z-20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <!-- Indicators Dots -->
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
                        @foreach($events->take(4) as $idx => $e)
                            <button @click="activeSlide = {{ $idx }}" :class="activeSlide === {{ $idx }} ? 'w-8 bg-orange-500' : 'w-2 bg-white/60 hover:bg-white'" class="h-2 rounded-full transition-all duration-300"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <!-- FLOATING QUICK SEARCH & FILTER BAR (TiketMart Style) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-2 sm:-mt-4 relative z-30">
            <div class="bg-white rounded-2xl p-3 sm:p-4 shadow-lg border border-slate-200">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    
                    <!-- Search Input -->
                    <div class="md:col-span-8 relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text"
                               x-model="searchQuery"
                               placeholder="Cari konser musik, seminar, festival, atau nama event..."
                               class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>

                    <!-- City Filter Dropdown -->
                    @php
                        $cities = $events->pluck('city')->filter()->unique()->values();
                    @endphp
                    <div class="md:col-span-4 flex items-center gap-2">
                        <div class="relative w-full">
                            <select x-model="selectedCity" class="w-full py-3 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition appearance-none pr-9">
                                <option value="all">📍 Semua Kota / Lokasi</option>
                                @foreach($cities as $c)
                                    <option value="{{ strtolower($c) }}">{{ $c }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CATEGORY PILLS BAR (TiketMart Style) -->
        <section id="categories-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-700">Kategori Pilihan</h3>
                <button @click="searchQuery = ''; selectedCategory = 'all'; selectedCity = 'all'" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition">
                    Reset Filter
                </button>
            </div>

            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2">
                <!-- All -->
                <button @click="selectedCategory = 'all'"
                        :class="selectedCategory === 'all' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-150 flex items-center gap-2">
                    <span>Semua Kategori</span>
                    <span :class="selectedCategory === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $events->count() }}</span>
                </button>

                <!-- Music & Concert -->
                <button @click="selectedCategory = 'musik'"
                        :class="selectedCategory === 'musik' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-150 flex items-center gap-2">
                    <span>🎵 Musik & Konser</span>
                </button>

                <!-- Sports & Fan Pass -->
                <button @click="selectedCategory = 'olahraga'"
                        :class="selectedCategory === 'olahraga' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-150 flex items-center gap-2">
                    <span>⚽ Olahraga & Suporter</span>
                </button>

                <!-- Festival & Expo -->
                <button @click="selectedCategory = 'festival'"
                        :class="selectedCategory === 'festival' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-150 flex items-center gap-2">
                    <span>🎪 Festival & Expo</span>
                </button>

                <!-- Seminar & Workshop -->
                <button @click="selectedCategory = 'seminar'"
                        :class="selectedCategory === 'seminar' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-150 flex items-center gap-2">
                    <span>🎓 Seminar & Workshop</span>
                </button>
            </div>
        </section>

        <!-- MAIN EVENT GRID SECTION (TiketMart Style) -->
        <section id="events-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Section Header & Tabs -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Event Mendatang</h2>
                    <p class="text-sm text-slate-500 mt-1">Temukan berbagai event menarik dan amankan tiket Anda sekarang juga.</p>
                </div>
                
                <!-- Quick Filter Tabs -->
                <div class="flex items-center bg-slate-200/70 p-1 rounded-xl self-start sm:self-auto">
                    <button @click="eventTab = 'all'" :class="eventTab === 'all' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-semibold'" class="px-3.5 py-1.5 rounded-lg text-xs transition">Semua</button>
                    <button @click="eventTab = 'upcoming'" :class="eventTab === 'upcoming' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-semibold'" class="px-3.5 py-1.5 rounded-lg text-xs transition">Mendatang</button>
                </div>
            </div>

            <!-- EVENT CARDS GRID -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pt-8">
                @forelse($events as $event)
                    @php
                        $eventImg = asset('images/concert.webp');
                        if ($event->background_image) {
                            if (str_starts_with($event->background_image, 'http')) {
                                $eventImg = $event->background_image;
                            } else {
                                $eventImg = asset('storage/' . $event->background_image);
                            }
                        }
                        
                        $isUpcoming = $event->event_start_date->isFuture() || $event->event_start_date->isToday();
                        $categoryTag = 'festival';
                        $lowerName = strtolower($event->name . ' ' . $event->description);
                        if (str_contains($lowerName, 'musik') || str_contains($lowerName, 'konser') || str_contains($lowerName, 'sound') || str_contains($lowerName, 'band')) {
                            $categoryTag = 'musik';
                        } elseif (str_contains($lowerName, 'olahraga') || str_contains($lowerName, 'bola') || str_contains($lowerName, 'bhayangkara') || str_contains($lowerName, 'match') || str_contains($lowerName, 'fc')) {
                            $categoryTag = 'olahraga';
                        } elseif (str_contains($lowerName, 'seminar') || str_contains($lowerName, 'workshop') || str_contains($lowerName, 'conference')) {
                            $categoryTag = 'seminar';
                        }

                        $minPrice = $event->ticketCategories->count() > 0 ? $event->ticketCategories->min('price') : 0;
                        $hasTickets = $event->ticketCategories->count() > 0;
                    @endphp

                    <div x-show="(searchQuery === '' || '{{ strtolower(addslashes($event->name . ' ' . $event->city . ' ' . ($event->tenant->name ?? ''))) }}'.includes(searchQuery.toLowerCase())) &&
                                 (selectedCategory === 'all' || '{{ $categoryTag }}' === selectedCategory) &&
                                 (selectedCity === 'all' || '{{ strtolower($event->city ?? '') }}' === selectedCity) &&
                                 (eventTab === 'all' || (eventTab === 'upcoming' && {{ $isUpcoming ? 'true' : 'false' }}))"
                         class="event-card bg-white rounded-2xl overflow-hidden border border-slate-200 flex flex-col h-full group">
                        
                        <!-- Thumbnail 16:9 -->
                        <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                            <img src="{{ $eventImg }}" alt="{{ $event->name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            
                            <!-- City Pill Overlay -->
                            <div class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-bold px-2.5 py-1 rounded-lg">
                                📍 {{ $event->city ?? 'Indonesia' }}
                            </div>

                            @if(!$isUpcoming)
                                <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-[1px] flex items-center justify-center">
                                    <span class="bg-rose-600 text-white text-xs font-black uppercase tracking-wider px-3 py-1 rounded-lg shadow">
                                        Event Selesai
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Card Content -->
                        <div class="p-5 flex flex-col flex-1 justify-between">
                            <div>
                                <!-- Date & Time Badge -->
                                <div class="flex items-center gap-1.5 text-xs font-bold text-blue-600 mb-2.5">
                                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ $event->event_start_date->isoFormat('ddd, D MMM Y') }}</span>
                                </div>

                                <!-- Event Title -->
                                <h3 class="text-base font-extrabold text-slate-900 group-hover:text-blue-600 transition leading-snug line-clamp-2 mb-2">
                                    <a href="{{ route('events.show', $event->slug) }}">
                                        {{ $event->name }}
                                    </a>
                                </h3>

                                <!-- Organizer / Tenant -->
                                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mb-3">
                                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span class="truncate">{{ $event->tenant->name ?? ($settings['app_name'] ?? 'IdenTix Organizer') }}</span>
                                </div>

                                <!-- Venue Location -->
                                <p class="text-xs text-slate-600 line-clamp-1 mb-4 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>{{ $event->venue ?? ($event->city ?? 'Venue Belum Ditentukan') }}</span>
                                </p>
                            </div>

                            <!-- Price & CTA Button (TiketMart Style) -->
                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-2 mt-auto">
                                <div>
                                    <span class="text-[10px] text-slate-600 block uppercase font-bold tracking-wider">Mulai dari</span>
                                    <span class="text-sm sm:text-base font-black text-slate-900">
                                        @if($hasTickets)
                                            @if($minPrice == 0)
                                                <span class="text-emerald-600 font-bold">GRATIS</span>
                                            @else
                                                <span class="text-blue-700">Rp {{ number_format($minPrice, 0, ',', '.') }}</span>
                                            @endif
                                        @else
                                            <span class="text-slate-400 text-xs">Segera Hadir</span>
                                        @endif
                                    </span>
                                </div>

                                <a href="{{ route('events.show', $event->slug) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm hover:shadow flex items-center gap-1 shrink-0">
                                    <span>Beli Tiket</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-16 bg-white rounded-3xl border border-slate-200 p-8">
                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Belum Ada Event Tersedia</h3>
                        <p class="text-sm text-slate-500 mt-1">Event baru akan segera dipublikasikan di platform IdenTix.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- WHY CHOOSE US / KEUNGGULAN (TiketMart Style) -->
        <section id="why-us" class="bg-white border-y border-slate-200 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Keunggulan Layanan</span>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight mt-3">Kenapa Memilih IdenTix?</h2>
                    <p class="text-sm text-slate-500 mt-2">Solusi pembelian tiket dan manajemen pengunjung yang dirancang untuk kenyamanan penonton dan efisiensi penyelenggara.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- Feature 1 -->
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/40 transition group">
                        <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-600/20 mb-4 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 mb-1.5">E-Ticket Instan & Aman</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">E-Voucher ber-QR Code unik langsung terkirim ke WhatsApp & Email Anda dalam hitungan detik setelah pembayaran sukses.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/40 transition group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-600/20 mb-4 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 mb-1.5">Metode Pembayaran Lengkap</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">Mendukung pembayaran instan melalui QRIS, Virtual Account Bank (BCA, Mandiri, BRI, BNI), dan E-Wallet resmi terintegrasi.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/40 transition group">
                        <div class="w-12 h-12 rounded-xl bg-orange-600 text-white flex items-center justify-center shadow-md shadow-orange-600/20 mb-4 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 mb-1.5">Gate Scanner Kilat</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">Teknologi verifikasi gate scan super cepat & offline-ready untuk meminimalisir antrean di pintu masuk venue event.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/40 transition group">
                        <div class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-600/20 mb-4 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 mb-1.5">Bantuan 24/7 Ramah</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">Tim customer care kami siap membantu kendala tiket, invoice, dan informasi event Anda melalui WhatsApp & Email.</p>
                    </div>

                </div>
            </div>
        </section>

        <!-- ORGANIZER PARTNER CTA BANNER (TiketMart Style) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="bg-gradient-to-br from-blue-900 via-indigo-900 to-slate-950 rounded-3xl p-8 sm:p-12 lg:p-16 text-white relative overflow-hidden shadow-2xl">
                <!-- Background ambient circle -->
                <div class="absolute -right-16 -top-16 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-16 -bottom-16 w-80 h-80 bg-orange-500/15 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-8 space-y-4">
                        <span class="inline-block bg-orange-500 text-white font-extrabold text-[11px] uppercase tracking-wider px-3 py-1 rounded-full">
                            Untuk Penyelenggara Event
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight">
                            Punya Konser, Pertandingan, atau Seminar? Jual Tiket Anda di IdenTix!
                        </h2>
                        <p class="text-sm sm:text-base text-slate-300 font-normal leading-relaxed max-w-2xl">
                            Kelola penjualan tiket, kuota korwil, validasi gate scan, ticketing offline (POS), hingga rekapitulasi keuangan transparan dalam satu sistem terpadu.
                        </p>
                    </div>

                    <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3 justify-end">
                        @if (Route::has('register') && $registrationOpen)
                            <a href="{{ route('register') }}" class="px-6 py-3.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-black text-center text-sm transition shadow-lg shadow-orange-600/30 transform hover:-translate-y-0.5">
                                Daftar Jadi Partner Event
                            </a>
                        @endif
                        <a href="{{ route('contact') }}" class="px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-center text-sm transition backdrop-blur-md">
                            Konsultasi dengan Tim Kami
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- PAYMENT PARTNER BADGES (TiketMart Style) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <div class="bg-white rounded-2xl p-6 border border-slate-200 text-center">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-600 mb-4">Didukung Metode Pembayaran Terlengkap & Aman</p>
                <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-8 text-slate-600 text-xs font-bold">
                    <span class="px-3 py-1.5 bg-slate-100 rounded-lg">QRIS (Semua E-Wallet)</span>
                    <span class="px-3 py-1.5 bg-slate-100 rounded-lg">BCA Virtual Account</span>
                    <span class="px-3 py-1.5 bg-slate-100 rounded-lg">Mandiri Livin'</span>
                    <span class="px-3 py-1.5 bg-slate-100 rounded-lg">BNI Virtual Account</span>
                    <span class="px-3 py-1.5 bg-slate-100 rounded-lg">BRI BRIVA</span>
                    <span class="px-3 py-1.5 bg-slate-100 rounded-lg">ShopeePay & GoPay</span>
                    <span class="px-3 py-1.5 bg-slate-100 rounded-lg">OVO & DANA</span>
                </div>
            </div>
        </section>

    </main>

    <!-- CLEAN TIKETMART-STYLE FOOTER -->
    <footer class="bg-slate-900 text-slate-300 border-t border-slate-800 pt-14 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 pb-12 border-b border-slate-800">
                
                <!-- Brand Info -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        @if(isset($settings['app_logo']) && $settings['app_logo'])
                            <img src="{{ asset('storage/' . $settings['app_logo']) }}" alt="{{ $settings['app_name'] ?? 'IdenTix' }}" class="h-8 w-auto">
                        @else
                            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-black text-sm">I</div>
                            <span class="text-xl font-black text-white tracking-tight">Iden<span class="text-blue-500">Tix</span></span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ $settings['meta_description'] ?? 'Platform sistem tiket dan manajemen event modern untuk pengalaman pembelian tiket yang cepat, aman, dan terpercaya.' }}
                    </p>
                </div>

                <!-- Navigation Links -->
                <div>
                    <h4 class="text-xs font-black uppercase tracking-widest text-white mb-4">Navigasi Utama</h4>
                    <ul class="space-y-2.5 text-xs text-slate-400">
                        <li><a href="{{ url('/') }}" class="hover:text-white transition">Beranda</a></li>
                        <li><a href="#events-section" class="hover:text-white transition">Daftar Event</a></li>
                        <li><a href="{{ route('portofolio') }}" class="hover:text-white transition">Portofolio & Klien</a></li>
                        <li><a href="{{ route('flow') }}" class="hover:text-white transition">Alur Bisnis</a></li>
                    </ul>
                </div>

                <!-- Help & Legal -->
                <div>
                    <h4 class="text-xs font-black uppercase tracking-widest text-white mb-4">Informasi & Bantuan</h4>
                    <ul class="space-y-2.5 text-xs text-slate-400">
                        <li><a href="{{ route('faq') }}" class="hover:text-white transition">Pertanyaan Umum (FAQ)</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-white transition">Syarat & Ketentuan</a></li>
                        <li><a href="{{ route('refund') }}" class="hover:text-white transition">Kebijakan Pengembalian Dana</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Layanan Pelanggan</a></li>
                    </ul>
                </div>

                <!-- Contact & Address -->
                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase tracking-widest text-white mb-4">Kontak Usaha</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ $settings['address'] ?? 'DUSUN MANDAH INDUK 00/001 MANDAH, NATAR, LAMPUNG SELATAN, LAMPUNG 35362' }}
                    </p>
                    <p class="text-xs text-slate-300 font-bold">
                        Email: <a href="mailto:{{ $settings['contact_email'] ?? 'virtusunity@gmail.com' }}" class="text-blue-400 hover:underline">{{ $settings['contact_email'] ?? 'virtusunity@gmail.com' }}</a>
                    </p>
                    <p class="text-xs text-slate-300 font-bold">
                        WhatsApp: <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['contact_phone'] ?? '083878537818') }}" class="text-emerald-400 hover:underline">{{ $settings['contact_phone'] ?? '083878537818' }}</a>
                    </p>
                </div>

            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500">
                <p>{!! $settings['footer_text'] ?? '&copy; ' . date('Y') . ' IdenTix. All rights reserved.' !!}</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('terms') }}" class="hover:text-slate-300 transition">Syarat</a>
                    <span>&bull;</span>
                    <a href="{{ route('refund') }}" class="hover:text-slate-300 transition">Refund</a>
                    <span>&bull;</span>
                    <a href="{{ route('contact') }}" class="hover:text-slate-300 transition">Kontak</a>
                </div>
            </div>
        </div>
    </footer>

    <x-accessibility-widget />
</body>
</html>
