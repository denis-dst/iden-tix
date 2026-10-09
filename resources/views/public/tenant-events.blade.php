<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name }} - Daftar Tiket & Event Resmi | {{ $settings['app_name'] ?? 'IdenTix' }}</title>
    <meta name="description" content="Temukan dan beli tiket resmi untuk berbagai event, pertunjukan, dan pertandingan yang diselenggarakan oleh {{ $tenant->name }} di {{ $settings['app_name'] ?? 'IdenTix' }}.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    </noscript>

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
                            outfit: ['"Outfit"', '"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        },
                        colors: {
                            identix: {
                                50: '#fff7ed',
                                100: '#ffedd5',
                                200: '#fed7aa',
                                300: '#fdba74',
                                400: '#fb923c',
                                500: '#f97316',
                                600: '#ea580c',
                                700: '#c2410c',
                                800: '#9a3412',
                                900: '#7c2d12',
                                950: '#431407',
                            },
                        },
                    }
                }
            }
        </script>
    @endif

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #0b0f19;
            color: #f1f5f9;
            line-height: 1.5;
        }
        .font-outfit {
            font-family: 'Outfit', 'Plus Jakarta Sans', system-ui, sans-serif;
        }
        .glass {
            background: rgba(18, 24, 38, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(18, 24, 38, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.07);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            background: rgba(26, 35, 54, 0.85);
            border-color: rgba(249, 115, 22, 0.35);
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4);
        }
        .text-gradient {
            background: linear-gradient(135deg, #fdba74 0%, #f97316 50%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        [x-cloak] { display: none !important; }
        :focus-visible {
            outline: 2px solid #f97316;
            outline-offset: 2px;
        }

        /* High-Contrast Form Inputs & Selects Overrides for Dark Mode */
        .dark-input,
        input[type="search"].dark-input,
        select.dark-input {
            background-color: #121826 !important;
            color: #f1f5f9 !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            appearance: none;
            -webkit-appearance: none;
        }
        .dark-input::placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }
        .dark-input:focus {
            border-color: #f97316 !important;
            box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.3) !important;
            background-color: #182236 !important;
        }
        select.dark-input option {
            background-color: #121826 !important;
            color: #f1f5f9 !important;
        }
    </style>
</head>
<body class="antialiased bg-[#0b0f19] text-[#f1f5f9] min-h-screen flex flex-col selection:bg-orange-500/30"
      x-data="{
          copiedToast: false,
          copyUrl() {
              const url = window.location.href;
              if (navigator.clipboard) {
                  navigator.clipboard.writeText(url).then(() => {
                      this.copiedToast = true;
                      setTimeout(() => { this.copiedToast = false; }, 2500);
                  });
              } else {
                  const input = document.createElement('input');
                  input.value = url;
                  document.body.appendChild(input);
                  input.select();
                  document.execCommand('copy');
                  document.body.removeChild(input);
                  this.copiedToast = true;
                  setTimeout(() => { this.copiedToast = false; }, 2500);
              }
          },
          shareProfile() {
              if (navigator.share) {
                  navigator.share({
                      title: '{{ addslashes($tenant->name) }} - Event & Tiket Resmi',
                      text: 'Daftar tiket resmi yang dijual oleh {{ addslashes($tenant->name) }} di IdenTix',
                      url: window.location.href,
                  }).catch(() => {});
              } else {
                  this.copyUrl();
              }
          }
      }">

    <!-- Toast Notification -->
    <div x-show="copiedToast" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-2xl bg-emerald-600 text-white font-bold text-sm shadow-2xl flex items-center gap-3 border border-emerald-400/30">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>Tautan halaman event berhasil disalin!</span>
    </div>

    <!-- Navigation Bar -->
    <nav class="sticky top-0 w-full z-40 glass border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <!-- App Logo & Home Link -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 rounded-xl p-1">
                    @if(isset($settings['app_logo']) && $settings['app_logo'])
                        @php
                            $logoPath = $settings['app_logo'];
                            $webpLogo = preg_replace('/\.(png|jpe?g)$/i', '.webp', $logoPath);
                            $finalLogo = file_exists(public_path('storage/' . $webpLogo)) ? asset('storage/' . $webpLogo) : asset('storage/' . $logoPath);
                        @endphp
                        <img src="{{ $finalLogo }}" alt="{{ $settings['app_name'] ?? 'IdenTix' }}" width="140" height="36" class="h-9 w-auto object-contain">
                    @else
                        <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/20 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight font-outfit uppercase text-white">
                            {{ $settings['app_name'] ?? 'IdenTix' }}
                        </span>
                    @endif
                </a>

                <!-- Right Actions -->
                <div class="flex items-center gap-3">
                    <button @click="shareProfile()" 
                            type="button"
                            aria-label="Bagikan Halaman Event"
                            class="min-h-[44px] px-4 py-2 rounded-xl glass hover:bg-white/10 text-stone-200 hover:text-white text-xs sm:text-sm font-semibold transition flex items-center gap-2 border border-white/10">
                        <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        <span class="hidden sm:inline">Bagikan</span>
                    </button>

                    @auth
                        <a href="{{ url('/dashboard') }}" class="min-h-[44px] px-4 sm:px-5 py-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-xs sm:text-sm font-bold transition shadow-lg shadow-orange-500/20 flex items-center">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="min-h-[44px] px-4 py-2 rounded-xl text-stone-300 hover:text-white text-xs sm:text-sm font-semibold transition flex items-center">
                            {{ __('Log in') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-10">

        <!-- Tenant / Organizer Header Banner -->
        <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-[#131b2e] to-[#0f172a] border border-white/10 p-6 sm:p-8 md:p-10 shadow-2xl">
            <!-- Ambient Glow Accents -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 md:gap-8">
                <!-- Organizer Brand Details -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 sm:gap-6">
                    <!-- Logo Avatar -->
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-[#1e293b] border-2 border-white/15 p-2 flex items-center justify-center shrink-0 shadow-xl overflow-hidden">
                        @if($tenant->logo)
                            @php
                                $logoSrc = str_starts_with($tenant->logo, 'http') ? $tenant->logo : asset('storage/' . $tenant->logo);
                            @endphp
                            <img src="{{ $logoSrc }}" 
                                 alt="{{ $tenant->name }}" 
                                 width="96" 
                                 height="96" 
                                 class="w-full h-full object-contain rounded-xl"
                                 onerror="this.onerror=null;this.parentElement.innerHTML='<span class=\'font-black text-2xl text-orange-400 font-outfit\'>{{ strtoupper(substr($tenant->name, 0, 2)) }}</span>';">
                        @else
                            <span class="font-black text-2xl sm:text-3xl text-orange-400 font-outfit">
                                {{ strtoupper(substr($tenant->name, 0, 2)) }}
                            </span>
                        @endif
                    </div>

                    <!-- Texts -->
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-500/15 border border-orange-500/30 text-orange-400 text-xs font-bold uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Penyelenggara Resmi
                            </span>
                            @if($tenant->address)
                                <span class="text-xs text-slate-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ Str::limit($tenant->address, 35) }}
                                </span>
                            @endif
                        </div>

                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-white font-outfit tracking-tight">
                            {{ $tenant->name }}
                        </h1>

                        <p class="text-sm text-slate-300 max-w-2xl font-light leading-relaxed">
                            {{ $tenant->meta['description'] ?? "Daftar resmi seluruh tiket event dan pertandingan yang diselenggarakan oleh {$tenant->name}. Pilih event dan dapatkan tiket Anda secara aman." }}
                        </p>
                    </div>
                </div>

                <!-- Stats & Fan Actions -->
                <div class="flex flex-wrap md:flex-col items-center md:items-end gap-3 shrink-0 pt-4 md:pt-0 border-t md:border-t-0 border-white/10 w-full md:w-auto">
                    <div class="px-4 py-2 rounded-2xl bg-white/5 border border-white/10 text-center">
                        <span class="text-xl font-black text-orange-400 font-outfit block">{{ $totalCount }}</span>
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Event</span>
                    </div>

                    @if($hasMembership)
                        <a href="{{ route('fan.register.show', $tenant->slug) }}" 
                           class="min-h-[44px] px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs sm:text-sm font-bold uppercase tracking-wider transition shadow-lg shadow-orange-500/20 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Daftar Member Fans
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <!-- Search, Filter & Controls Toolbar -->
        <section aria-label="Filter dan Pencarian Event" class="space-y-4">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                
                <!-- Filter Tabs -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none" role="tablist">
                    <a href="{{ route('tenant.events', ['slug' => $tenant->slug, 'filter' => 'upcoming', 'q' => $search, 'sort' => $sort]) }}"
                       class="min-h-[44px] px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shrink-0 {{ $filter === 'upcoming' ? 'bg-orange-500 text-black font-extrabold shadow-lg shadow-orange-500/20' : 'glass text-slate-300 hover:text-white hover:bg-white/10' }}">
                        <span>Event Mendatang</span>
                        <span class="px-2 py-0.5 rounded-md text-[11px] {{ $filter === 'upcoming' ? 'bg-black/20 text-black' : 'bg-white/10 text-slate-300' }}">
                            {{ $upcomingCount }}
                        </span>
                    </a>

                    <a href="{{ route('tenant.events', ['slug' => $tenant->slug, 'filter' => 'all', 'q' => $search, 'sort' => $sort]) }}"
                       class="min-h-[44px] px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shrink-0 {{ $filter === 'all' ? 'bg-orange-500 text-black font-extrabold shadow-lg shadow-orange-500/20' : 'glass text-slate-300 hover:text-white hover:bg-white/10' }}">
                        <span>Semua Event</span>
                        <span class="px-2 py-0.5 rounded-md text-[11px] {{ $filter === 'all' ? 'bg-black/20 text-black' : 'bg-white/10 text-slate-300' }}">
                            {{ $totalCount }}
                        </span>
                    </a>

                    <a href="{{ route('tenant.events', ['slug' => $tenant->slug, 'filter' => 'past', 'q' => $search, 'sort' => $sort]) }}"
                       class="min-h-[44px] px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shrink-0 {{ $filter === 'past' ? 'bg-orange-500 text-black font-extrabold shadow-lg shadow-orange-500/20' : 'glass text-slate-300 hover:text-white hover:bg-white/10' }}">
                        <span>Event Selesai</span>
                        <span class="px-2 py-0.5 rounded-md text-[11px] {{ $filter === 'past' ? 'bg-black/20 text-black' : 'bg-white/10 text-slate-300' }}">
                            {{ $pastCount }}
                        </span>
                    </a>
                </div>

                <!-- Search & Sort Controls -->
                <form action="{{ route('tenant.events', $tenant->slug) }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                    <input type="hidden" name="filter" value="{{ $filter }}">

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64 md:w-72">
                        <label for="event-search" class="sr-only">Cari Event</label>
                        <input type="search" 
                               id="event-search"
                               name="q" 
                               value="{{ $search }}" 
                               placeholder="Cari nama event, venue..."
                               class="dark-input w-full min-h-[44px] !bg-[#121826] !text-[#f1f5f9] border border-white/20 rounded-xl pl-10 pr-4 py-2 text-xs sm:text-sm placeholder-slate-400 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition shadow-inner">
                        <svg class="w-4 h-4 text-orange-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Sort Select -->
                    <div class="relative w-full sm:w-auto">
                        <label for="event-sort" class="sr-only">Urutkan Event</label>
                        <select id="event-sort"
                                name="sort" 
                                onchange="this.form.submit()"
                                class="dark-input w-full sm:w-auto min-h-[44px] !bg-[#121826] !text-[#f1f5f9] border border-white/20 rounded-xl pl-4 pr-10 py-2 text-xs sm:text-sm font-bold focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition cursor-pointer shadow-inner">
                            <option value="date_asc" class="bg-[#121826] text-white" {{ $sort === 'date_asc' ? 'selected' : '' }}>Tanggal Terdekat</option>
                            <option value="date_desc" class="bg-[#121826] text-white" {{ $sort === 'date_desc' ? 'selected' : '' }}>Tanggal Terbaru</option>
                            <option value="name_asc" class="bg-[#121826] text-white" {{ $sort === 'name_asc' ? 'selected' : '' }}>Nama Event (A-Z)</option>
                        </select>
                        <svg class="w-4 h-4 text-orange-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    @if(!empty($search))
                        <a href="{{ route('tenant.events', ['slug' => $tenant->slug, 'filter' => $filter]) }}" 
                           class="min-h-[44px] px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white text-xs font-bold transition flex items-center gap-1.5 shrink-0"
                           title="Hapus pencarian">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Reset</span>
                        </a>
                    @endif
                </form>
            </div>
        </section>

        <!-- Events List Grid -->
        <section aria-label="Daftar Tiket Event">
            @if($events->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($events as $event)
                        @php
                            // Determine banner / poster image URL with fallbacks
                            $bgImg = asset('images/concert.webp');
                            if ($event->background_image) {
                                if (str_starts_with($event->background_image, 'http')) {
                                    if (str_contains($event->background_image, 'unsplash.com')) {
                                        $bgImg = preg_replace('/(\?.*)?$/', '?auto=format&fit=crop&w=600&q=75', $event->background_image);
                                    } else {
                                        $bgImg = $event->background_image;
                                    }
                                } else {
                                    $webpCandidate = preg_replace('/\.(png|jpe?g)$/i', '.webp', $event->background_image);
                                    if (file_exists(public_path('storage/' . $webpCandidate))) {
                                        $bgImg = asset('storage/' . $webpCandidate);
                                    } else {
                                        $bgImg = asset('storage/' . $event->background_image);
                                    }
                                }
                            }

                            // Indonesian Day & Month Names for clean localized dates
                            $dayNamesId = [
                                'Sunday' => 'Minggu',
                                'Monday' => 'Senin',
                                'Tuesday' => 'Selasa',
                                'Wednesday' => 'Rabu',
                                'Thursday' => 'Kamis',
                                'Friday' => 'Jumat',
                                'Saturday' => 'Sabtu',
                            ];
                            $monthNamesId = [
                                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
                            ];

                            $startDate = $event->event_start_date;
                            $dayName = $dayNamesId[$startDate->englishDayOfWeek] ?? $startDate->format('D');
                            $monthName = $monthNamesId[(int)$startDate->format('n')] ?? $startDate->format('M');
                            $formattedDate = $dayName . ', ' . $startDate->format('d') . ' ' . $monthName . ' ' . $startDate->format('Y');
                            $formattedTime = $startDate->format('H:i') . ' WIB';

                            $minPrice = $event->ticketCategories->where('is_active', true)->min('price');
                            $isSoldOut = $event->ticketCategories->isNotEmpty() && $event->ticketCategories->every(function($cat) {
                                return $cat->sold_count >= $cat->quota;
                            });
                        @endphp

                        <article class="glass-card rounded-3xl overflow-hidden flex flex-col h-full group focus-within:ring-2 focus-within:ring-orange-500">
                            <!-- Image / Fixture Header Container -->
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-900">
                                <img src="{{ $bgImg }}" 
                                     alt="{{ $event->name }}" 
                                     width="480" 
                                     height="300" 
                                     loading="lazy" 
                                     decoding="async" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-[#0b0f19] via-transparent to-transparent opacity-90"></div>

                                <!-- City Tag -->
                                <div class="absolute top-3 left-3 z-10">
                                    <span class="px-3 py-1 bg-black/60 backdrop-blur-md border border-white/10 rounded-full text-[11px] font-bold tracking-wider text-slate-200">
                                        {{ $event->city ?? 'Event' }}
                                    </span>
                                </div>

                                <!-- Status Tag -->
                                <div class="absolute top-3 right-3 z-10">
                                    @if($isSoldOut)
                                        <span class="px-3 py-1 bg-rose-600/90 text-white rounded-full text-[11px] font-black tracking-wider uppercase shadow-lg">
                                            Sold Out
                                        </span>
                                    @elseif($event->is_free)
                                        <span class="px-3 py-1 bg-emerald-600/90 text-white rounded-full text-[11px] font-black tracking-wider uppercase shadow-lg">
                                            Gratis
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-orange-600/90 text-white rounded-full text-[11px] font-black tracking-wider uppercase shadow-lg">
                                            Tersedia
                                        </span>
                                    @endif
                                </div>

                                <!-- Match Fixture overlay if sports fixture -->
                                @if($event->home_team_name && $event->away_team_name)
                                    <div class="absolute bottom-3 inset-x-3 z-10 p-2.5 rounded-2xl bg-black/75 backdrop-blur-md border border-white/10 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            @if($event->home_team_logo)
                                                <img src="{{ asset('storage/' . $event->home_team_logo) }}" alt="" class="w-6 h-6 object-contain">
                                            @endif
                                            <span class="text-xs font-bold text-white truncate">{{ $event->home_team_name }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded bg-orange-500 text-black text-[10px] font-black shrink-0">VS</span>
                                        <div class="flex items-center gap-2 min-w-0 justify-end">
                                            <span class="text-xs font-bold text-white truncate text-right">{{ $event->away_team_name }}</span>
                                            @if($event->away_team_logo)
                                                <img src="{{ asset('storage/' . $event->away_team_logo) }}" alt="" class="w-6 h-6 object-contain">
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Body -->
                            <div class="p-6 flex flex-col flex-1 justify-between gap-5">
                                <div class="space-y-3">
                                    <!-- Date & Time Row -->
                                    <div class="flex items-center gap-2 text-xs font-semibold text-orange-400">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ $formattedDate }} • {{ $formattedTime }}</span>
                                    </div>

                                    <!-- Event Title -->
                                    <h2 class="text-xl font-bold font-outfit text-white group-hover:text-orange-400 transition-colors line-clamp-2">
                                        <a href="{{ route('events.show', $event->slug) }}" class="focus:outline-none">
                                            {{ $event->name }}
                                        </a>
                                    </h2>

                                    <!-- Venue / Location -->
                                    <div class="flex items-start gap-2 text-xs text-slate-400">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="line-clamp-1">{{ $event->venue ? $event->venue . ', ' . $event->city : ($event->city ?? 'Lokasi diinformasikan segera') }}</span>
                                    </div>

                                    <!-- Excerpt Description -->
                                    @if($event->description)
                                        <p class="text-xs text-slate-400 line-clamp-2 font-light leading-relaxed">
                                            {{ trim(html_entity_decode(strip_tags($event->description), ENT_QUOTES, 'UTF-8')) }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Card Footer: Price & Direct CTA -->
                                <div class="pt-4 border-t border-white/10 flex items-center justify-between gap-3 mt-auto">
                                    <div>
                                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Harga Tiket</span>
                                        <span class="text-base sm:text-lg font-black font-outfit text-orange-400">
                                            @if($event->is_free)
                                                Gratis (Free)
                                            @elseif($minPrice !== null)
                                                Mulai Rp {{ number_format($minPrice, 0, ',', '.') }}
                                            @else
                                                Segera Hadir
                                            @endif
                                        </span>
                                    </div>

                                    <a href="{{ route('events.show', $event->slug) }}" 
                                       class="min-h-[44px] px-5 py-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold text-xs sm:text-sm uppercase tracking-wider transition shadow-lg shadow-orange-500/20 flex items-center gap-1.5 shrink-0">
                                        <span>Beli Tiket</span>
                                        <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($events->hasPages())
                    <div class="pt-8">
                        {{ $events->links() }}
                    </div>
                @endif

            @else
                <!-- UI State: Empty Results (R-27 compliant) -->
                <div class="glass rounded-3xl p-10 md:p-16 text-center max-w-xl mx-auto border border-white/10 space-y-5">
                    <div class="w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center mx-auto text-orange-400 border border-white/10">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>

                    @if(!empty($search))
                        <div class="space-y-2">
                            <h2 class="text-xl font-bold text-white font-outfit">Event Tidak Ditemukan</h2>
                            <p class="text-sm text-slate-400">
                                Tidak ada event dari <strong class="text-white">{{ $tenant->name }}</strong> yang cocok dengan kata kunci &ldquo;<span class="text-orange-400">{{ $search }}</span>&rdquo;.
                            </p>
                        </div>
                        <a href="{{ route('tenant.events', ['slug' => $tenant->slug, 'filter' => $filter]) }}" 
                           class="inline-flex min-h-[44px] items-center px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs uppercase tracking-wider transition">
                            Tampilkan Semua Event
                        </a>
                    @elseif($filter === 'upcoming')
                        <div class="space-y-2">
                            <h2 class="text-xl font-bold text-white font-outfit">Belum Ada Event Mendatang</h2>
                            <p class="text-sm text-slate-400">
                                Saat ini belum ada jadwal event aktif yang dibuka untuk umum oleh {{ $tenant->name }}. Silakan periksa kembali nanti atau lihat arsip event selesai.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                            @if($pastCount > 0)
                                <a href="{{ route('tenant.events', ['slug' => $tenant->slug, 'filter' => 'past']) }}" 
                                   class="min-h-[44px] px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs uppercase tracking-wider transition flex items-center">
                                    Lihat Arsip Event Selesai ({{ $pastCount }})
                                </a>
                            @endif
                            <a href="{{ url('/') }}" 
                               class="min-h-[44px] px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs uppercase tracking-wider transition flex items-center">
                                Jelajahi Event Lainnya di IdenTix
                            </a>
                        </div>
                    @else
                        <div class="space-y-2">
                            <h2 class="text-xl font-bold text-white font-outfit">Belum Ada Event Terdaftar</h2>
                            <p class="text-sm text-slate-400">
                                Penyelenggara ini belum mempublikasikan tiket event.
                            </p>
                        </div>
                        <a href="{{ url('/') }}" 
                           class="inline-flex min-h-[44px] items-center px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs uppercase tracking-wider transition">
                            Kembali ke Beranda
                        </a>
                    @endif
                </div>
            @endif
        </section>

    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-white/5 bg-[#080c14] py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6 text-xs text-slate-400">
            <div class="flex items-center gap-3">
                <span class="font-bold text-white uppercase tracking-wider font-outfit">{{ $settings['app_name'] ?? 'IdenTix' }}</span>
                <span>&bull;</span>
                <span>Halaman Resmi Event {{ $tenant->name }}</span>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <a href="{{ route('terms') }}" class="hover:text-white transition">Syarat & Ketentuan</a>
                <a href="{{ route('refund') }}" class="hover:text-white transition">Kebijakan Pengembalian</a>
                <a href="{{ route('contact') }}" class="hover:text-white transition">Bantuan & Kontak</a>
            </div>

            <div class="text-slate-400">
                &copy; {{ date('Y') }} {{ $settings['app_name'] ?? 'IdenTix' }}. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>
