@php
    $registrationOpen = (bool) (\App\Models\Setting::where('key', 'tenant_registration_enabled')->value('value') ?? true);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings['app_name'] ?? 'IdenTix' }} - {{ $settings['app_tagline'] ?? 'Connecting Generations' }}</title>
    <meta name="description" content="{{ $settings['meta_description'] ?? '' }}">

    <!-- Fonts (High Performance Non-Render-Blocking Loading) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style"
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    </noscript>

    <!-- Styles / Scripts (Production Optimized Delivery) -->
    @if (file_exists(public_path('build/manifest.json')))
        @php
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
            $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        @endphp
        @if($cssFile)
            <link rel="preload" as="style" href="{{ asset('build/' . $cssFile) }}">
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
            background-color: #111118;
            color: #e8e4df;
            line-height: 1.5;
        }

        .font-outfit {
            font-family: 'Outfit', 'Plus Jakarta Sans', system-ui, sans-serif;
        }

        .glass {
            background: rgba(30, 28, 35, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .glass-card {
            background: rgba(30, 28, 35, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            background: rgba(40, 38, 48, 0.8);
            border: 1px solid rgba(249, 115, 22, 0.2);
            transform: translateY(-6px);
            box-shadow: 0 20px 60px rgba(249, 115, 22, 0.08);
        }

        .text-gradient {
            background: linear-gradient(135deg, #fdba74 0%, #f97316 50%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .bg-gradient-main {
            background: radial-gradient(circle at top right, rgba(249, 115, 22, 0.08), transparent),
                radial-gradient(circle at bottom left, rgba(251, 146, 60, 0.06), transparent);
        }

        .hero-overlay {
            background: linear-gradient(to bottom, rgba(17, 17, 24, 0.88) 0%, rgba(17, 17, 24, 0.55) 50%, rgba(17, 17, 24, 0.95) 100%);
        }

        [x-cloak] {
            display: none !important;
        }

        ::selection {
            background: rgba(249, 115, 22, 0.3);
        }

        button[type="submit"] {
            background-color: #f97316 !important;
            color: #000000 !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            transition: all 0.2s !important;
        }

        button[type="submit"]:hover {
            background-color: #ea580c !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3) !important;
        }

        /* Global Orange Theme Overrides */
        .bg-orange-600,
        .bg-orange-500,
        .bg-orange-400 {
            background-color: #f97316 !important;
            color: #000000 !important;
        }

        .bg-orange-600 *,
        .bg-orange-500 *,
        .bg-orange-400 * {
            color: #000000 !important;
        }

        .bg-orange-600:hover,
        .bg-orange-500:hover,
        .bg-orange-400:hover {
            background-color: #ea580c !important;
        }
    </style>
    <meta name="wago-verification" content="WAGO-C2742A2D">
</head>

<body class="antialiased bg-[#111118] text-[#e8e4df] min-h-screen">

    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 glass border-b border-black/5 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-2">
                    @if(isset($settings['app_logo']) && $settings['app_logo'])
                        @php
                            $logoPath = $settings['app_logo'];
                            $webpLogo = preg_replace('/\.(png|jpe?g)$/i', '.webp', $logoPath);
                            $finalLogo = file_exists(public_path('storage/' . $webpLogo)) ? asset('storage/' . $webpLogo) : asset('storage/' . $logoPath);
                        @endphp
                        <img src="{{ $finalLogo }}" alt="Logo" width="160" height="40" fetchpriority="high" decoding="async"
                            class="h-10 w-auto object-contain">
                    @else
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/30">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold tracking-tight font-outfit uppercase text-white">
                            @php
                                $appName = $settings['app_name'] ?? 'IdenTix';
                                if (str_contains($appName, ' ')) {
                                    $parts = explode(' ', $appName, 2);
                                    $first = $parts[0];
                                    $second = $parts[1];
                                } elseif (str_starts_with(strtolower($appName), 'iden') && strlen($appName) > 4) {
                                    $first = substr($appName, 0, 4);
                                    $second = substr($appName, 4);
                                } elseif (str_starts_with(strtolower($appName), 'gen') && strlen($appName) > 3) {
                                    $first = substr($appName, 0, 3);
                                    $second = substr($appName, 3);
                                } else {
                                    $first = $appName;
                                    $second = '';
                                }
                            @endphp
                            {{ $first }}<span class="text-orange-400">{{ $second }}</span>
                        </span>
                    @endif
                </div>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="#events"
                        class="text-sm font-medium text-white/80 hover:text-white transition">{{ __('Events') }}</a>
                    <a href="#how-it-works"
                        class="text-sm font-medium text-white/80 hover:text-white transition">{{ __('How it Works') }}</a>
                    <a href="{{ route('flow') }}"
                        class="text-sm font-medium text-white/80 hover:text-white transition">Alur Bisnis</a>
                    <a href="#about"
                        class="text-sm font-medium text-white/80 hover:text-white transition">{{ __('About') }}</a>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="text-sm font-bold text-white/80 hover:text-white transition uppercase flex items-center gap-1">
                            {{ app()->getLocale() }}
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false" x-transition
                            class="absolute top-full mt-4 right-0 glass rounded-xl py-2 w-24">
                            <a href="{{ route('lang.switch', 'id') }}"
                                class="block px-4 py-2 text-sm text-stone-300 hover:text-white hover:bg-white/5 {{ app()->getLocale() == 'id' ? 'font-bold text-white' : '' }}">ID</a>
                            <a href="{{ route('lang.switch', 'en') }}"
                                class="block px-4 py-2 text-sm text-stone-300 hover:text-white hover:bg-white/5 {{ app()->getLocale() == 'en' ? 'font-bold text-white' : '' }}">EN</a>
                        </div>
                    </div>
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="px-5 py-2.5 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-sm font-semibold transition shadow-lg shadow-orange-500/20">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-white/80 hover:text-white transition">{{ __('Log in') }}</a>
                        @php
                            $registrationOpen = (bool) (\App\Models\Setting::where('key', 'tenant_registration_enabled')->value('value') ?? true);
                        @endphp
                        @if (Route::has('register') && $registrationOpen)
                            <a href="{{ route('register') }}"
                                class="px-5 py-2.5 rounded-full bg-orange-600 text-white hover:bg-orange-700 text-sm font-semibold transition shadow-lg shadow-orange-500/20">{{ __('Partner with Us') }}</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute inset-0 z-0">
            <picture>
                <source srcset="/images/hero.webp" type="image/webp">
                <img src="/images/hero.png" alt="Hero Background" width="1200" height="675" fetchpriority="high"
                    decoding="async" class="w-full h-full object-cover opacity-30">
            </picture>
            <div class="absolute inset-0 hero-overlay"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full glass mb-8 border border-white/10 shadow-sm">
                <span class="w-2 h-2 bg-orange-400 rounded-full mr-2"></span>
                <span
                    class="text-xs font-semibold tracking-wider uppercase text-orange-300/80">{{ __('Live your best moments') }}</span>
            </div>
            <h3 class="text-5xl lg:text-8xl font-extrabold font-outfit mb-8 leading-tight text-white">
                {{ __($settings['hero_title'] ?? 'IdenTix: Connecting Generations Through Every Gate.') }}
            </h3>
            <p class="text-xl lg:text-2xl text-stone-300 max-w-2xl mx-auto mb-10 leading-relaxed font-light">
                {{ __($settings['hero_subtitle'] ?? 'Bridging the gap between Identity and Tickets.') }}
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#events"
                    class="w-full sm:w-auto px-10 py-4 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold text-lg transition-all shadow-xl shadow-orange-500/25">
                    {{ __('Explore Events') }}
                </a>
                <a href="#how-it-works"
                    class="w-full sm:w-auto px-10 py-4 rounded-full glass hover:bg-white/10 text-white font-bold text-lg transition-all border border-white/10">
                    {{ __('How it Works') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Events -->
    <section id="events" class="py-24 bg-[#13131b]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                <div>
                    <h2 class="text-4xl font-bold font-outfit mb-4 text-white">{{ __('Featured Events') }}</h2>
                    <p class="text-stone-400 font-light text-lg">
                        {{ __('Handpicked experiences you shouldn\'t miss this month.') }}</p>
                </div>
                <a href="#"
                    class="inline-flex items-center text-orange-400 font-semibold hover:text-orange-300 transition group">
                    {{ __('View all events') }}
                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($events as $event)
                    @php
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
                    @endphp
                    <div class="glass-card rounded-3xl overflow-hidden group transition-all flex flex-col h-full">
                        <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-[#181824] to-[#12121c] flex items-center justify-center"
                            style="aspect-ratio: 16/10;">
                            <!-- Main contained poster image (100% visible, fully responsive without cropping) -->
                            <img src="{{ $bgImg }}" alt="{{ $event->name }}" width="473" height="236" loading="lazy"
                                decoding="async"
                                class="relative z-10 w-full h-full object-cover transition duration-500 group-hover:scale-105">

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-[#13131b]/80 via-transparent to-transparent z-10 pointer-events-none">
                            </div>
                            <div
                                class="absolute top-4 left-4 z-20 px-3 py-1 bg-gradient-to-r from-orange-500 to-amber-500 rounded-full text-xs font-bold uppercase tracking-widest text-white shadow-lg">
                                {{ $event->city ?? 'Event' }}
                            </div>
                        </div>
                        <div class="p-8 flex flex-col flex-1 justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-4 h-4 text-orange-400 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-xs text-stone-400 font-medium tracking-wide">
                                        {{ $event->event_start_date->format('M d, Y • H:i A') }}
                                    </span>
                                </div>
                                <h3
                                    class="text-2xl font-bold mb-4 font-outfit text-white group-hover:text-orange-400 transition">
                                    {{ $event->name }}</h3>
                                <p class="text-stone-400 font-light text-sm mb-6 line-clamp-2">
                                    {{ trim(html_entity_decode(strip_tags($event->description), ENT_QUOTES, 'UTF-8')) }}</p>
                            </div>
                            <div class="flex items-center justify-between pt-6 border-t border-white/5 mt-auto">
                                <span class="text-xl font-bold text-orange-400">
                                    @if($event->ticketCategories->count() > 0)
                                        Mulai Rp. {{ number_format($event->ticketCategories->min('price'), 0, ',', '.') }}
                                    @else
                                        Coming Soon
                                    @endif
                                </span>
                                <a href="{{ route('events.show', $event->slug) }}"
                                    class="px-6 py-2 rounded-xl bg-white/5 hover:bg-orange-500 hover:text-white text-stone-300 transition-all text-sm font-bold">Details</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section id="how-it-works" class="py-24 relative overflow-hidden bg-[#111118]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-bold font-outfit mb-6 text-white">{{ __('Simple Steps to Attend') }}</h2>
                <p class="text-stone-400 font-light text-lg max-w-xl mx-auto">
                    {{ __('Getting your tickets has never been easier. Follow our seamless process to join the action.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 relative">
                <!-- Connective line (Desktop) -->
                <div class="hidden md:block absolute top-1/4 left-0 w-full h-px bg-white/10 z-0"></div>

                <!-- Step 1 -->
                <div class="relative z-10 flex flex-col items-center text-center group">
                    <div
                        class="w-16 h-16 rounded-2xl glass flex items-center justify-center mb-6 group-hover:bg-orange-500 group-hover:text-white transition-all shadow-xl text-white">
                        <span class="text-2xl font-bold font-outfit">01</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">{{ __('Browse Events') }}</h3>
                    <p class="text-stone-400 text-sm font-light">
                        {{ __('Explore our curated list of events across various categories.') }}</p>
                </div>

                <!-- Step 2 -->
                <div class="relative z-10 flex flex-col items-center text-center group">
                    <div
                        class="w-16 h-16 rounded-2xl glass flex items-center justify-center mb-6 group-hover:bg-orange-500 group-hover:text-white transition-colors shadow-xl text-white">
                        <span class="text-2xl font-bold font-outfit">02</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">{{ __('Choose Seats') }}</h3>
                    <p class="text-stone-400 text-sm font-light">
                        {{ __('Select your preferred viewing area and number of tickets.') }}</p>
                </div>

                <!-- Step 3 -->
                <div class="relative z-10 flex flex-col items-center text-center group">
                    <div
                        class="w-16 h-16 rounded-2xl glass flex items-center justify-center mb-6 group-hover:bg-orange-500 group-hover:text-white transition-colors shadow-xl text-white">
                        <span class="text-2xl font-bold font-outfit">03</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">{{ __('Secure Payment') }}</h3>
                    <p class="text-stone-400 text-sm font-light">
                        {{ __('Pay safely using our encrypted payment gateway.') }}</p>
                </div>

                <!-- Step 4 -->
                <div class="relative z-10 flex flex-col items-center text-center group">
                    <div
                        class="w-16 h-16 rounded-2xl glass flex items-center justify-center mb-6 group-hover:bg-orange-500 group-hover:text-white transition-colors shadow-xl text-white">
                        <span class="text-2xl font-bold font-outfit">04</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">{{ __('Get E-Ticket') }}</h3>
                    <p class="text-stone-400 text-sm font-light">
                        {{ __('Your ticket will be sent to your email and IdenTix wallet.') }}</p>
                </div>
            </div>

            <div class="mt-14 text-center">
                <a href="{{ route('flow') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white/5 hover:bg-orange-500/20 text-stone-200 hover:text-orange-400 border border-white/10 hover:border-orange-500/30 text-sm font-semibold transition shadow-lg shadow-black/20">
                    <span>Lihat Dokumentasi Lengkap Alur Bisnis & Integrasi WAGO Payment</span>
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Philosophy Section -->
    <section class="py-24 relative overflow-hidden bg-[#13131b]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
                <div>
                    <div class="inline-flex items-center px-4 py-1.5 rounded-full glass mb-6 border border-white/5">
                        <span
                            class="text-xs font-bold uppercase tracking-widest text-orange-400">{{ __('Our Philosophy') }}</span>
                    </div>
                    <h2 class="text-3xl lg:text-5xl font-bold font-outfit mb-6 leading-tight text-white">
                        End-to-End <span class="text-gradient">Event Experience</span>
                    </h2>

                    <p class="text-stone-300 text-base lg:text-lg font-light leading-relaxed mb-6">
                        IDEnTix hadir untuk menghadirkan pengalaman penonton yang lebih mudah, nyaman, dan terintegrasi,
                        mulai dari proses pembelian tiket hingga penonton memasuki venue dan menikmati event.
                    </p>

                    <p class="text-stone-400 text-sm leading-relaxed mb-8">
                        Melalui pendekatan <span class="text-white font-medium">end-to-end event experience</span>,
                        IDEnTIX mengintegrasikan teknologi, ticketing, customer service, access control, crowd
                        management, dan operasional lapangan untuk menciptakan perjalanan penonton yang lebih seamless.
                    </p>

                    <!-- Our Journey Flow Badge -->
                    <div class="mb-6 p-4 rounded-2xl glass border border-white/10">
                        <div class="text-[11px] font-bold uppercase tracking-widest text-orange-400 mb-2.5">Our Journey
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold text-white">
                            <span
                                class="px-2.5 py-1 rounded-lg bg-orange-500/20 text-orange-300 border border-orange-500/30">DISCOVER</span>
                            <span class="text-orange-400">&rarr;</span>
                            <span
                                class="px-2.5 py-1 rounded-lg bg-orange-500/20 text-orange-300 border border-orange-500/30">PURCHASE</span>
                            <span class="text-orange-400">&rarr;</span>
                            <span
                                class="px-2.5 py-1 rounded-lg bg-orange-500/20 text-orange-300 border border-orange-500/30">PREPARE</span>
                            <span class="text-orange-400">&rarr;</span>
                            <span
                                class="px-2.5 py-1 rounded-lg bg-orange-500/20 text-orange-300 border border-orange-500/30">ARRIVE</span>
                            <span class="text-orange-400">&rarr;</span>
                            <span
                                class="px-2.5 py-1 rounded-lg bg-orange-500/20 text-orange-300 border border-orange-500/30">ENTER</span>
                            <span class="text-orange-400">&rarr;</span>
                            <span
                                class="px-2.5 py-1 rounded-lg bg-orange-500/20 text-orange-300 border border-orange-500/30">EXPERIENCE</span>
                        </div>
                    </div>

                    <!-- 6 Journey Pillars Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-8">
                        <div class="p-3.5 rounded-xl glass border border-white/5 space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded-full bg-orange-500/20 text-orange-400 text-[10px] font-black flex items-center justify-center">1</span>
                                <span class="text-sm font-bold text-white">Discover</span>
                            </div>
                            <p class="text-xs text-stone-400 leading-snug">Informasi event yang jelas dan mudah diakses.
                            </p>
                        </div>
                        <div class="p-3.5 rounded-xl glass border border-white/5 space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded-full bg-orange-500/20 text-orange-400 text-[10px] font-black flex items-center justify-center">2</span>
                                <span class="text-sm font-bold text-white">Purchase</span>
                            </div>
                            <p class="text-xs text-stone-400 leading-snug">Proses pembelian tiket yang sederhana, aman,
                                dan nyaman.</p>
                        </div>
                        <div class="p-3.5 rounded-xl glass border border-white/5 space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded-full bg-orange-500/20 text-orange-400 text-[10px] font-black flex items-center justify-center">3</span>
                                <span class="text-sm font-bold text-white">Prepare</span>
                            </div>
                            <p class="text-xs text-stone-400 leading-snug">Informasi sebelum event seperti jadwal, gate,
                                venue, dan ketentuan masuk.</p>
                        </div>
                        <div class="p-3.5 rounded-xl glass border border-white/5 space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded-full bg-orange-500/20 text-orange-400 text-[10px] font-black flex items-center justify-center">4</span>
                                <span class="text-sm font-bold text-white">Arrive</span>
                            </div>
                            <p class="text-xs text-stone-400 leading-snug">Pengalaman kedatangan dan alur menuju venue
                                yang terarah.</p>
                        </div>
                        <div class="p-3.5 rounded-xl glass border border-white/5 space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded-full bg-orange-500/20 text-orange-400 text-[10px] font-black flex items-center justify-center">5</span>
                                <span class="text-sm font-bold text-white">Enter</span>
                            </div>
                            <p class="text-xs text-stone-400 leading-snug">Proses ticket scanning dan access control
                                yang cepat serta efisien.</p>
                        </div>
                        <div class="p-3.5 rounded-xl glass border border-white/5 space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded-full bg-orange-500/20 text-orange-400 text-[10px] font-black flex items-center justify-center">6</span>
                                <span class="text-sm font-bold text-white">Experience</span>
                            </div>
                            <p class="text-xs text-stone-400 leading-snug">Memastikan penonton dapat fokus menikmati
                                event tanpa terganggu oleh proses operasional.</p>
                        </div>
                    </div>

                    <!-- Closing Insight -->
                    <div
                        class="p-4 rounded-2xl bg-orange-500/10 border border-orange-500/20 text-stone-300 text-xs leading-relaxed space-y-2">
                        <p class="font-medium text-white">
                            Bagi IDEnTIX, tiket bukanlah akhir dari sebuah transaksi. Tiket adalah awal dari sebuah
                            pengalaman.
                        </p>
                        <p class="text-stone-400">
                            Karena itu, kami tidak hanya mengelola tiket. Kami membangun seamless journey yang
                            menghubungkan penonton, event organizer, venue, dan seluruh stakeholder dalam satu
                            pengalaman yang terintegrasi.
                        </p>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute inset-0 bg-orange-500/10 blur-[100px] rounded-full"></div>
                    <div class="glass p-2 rounded-[2.5rem] relative overflow-hidden border border-white/10">
                        <picture>
                            <source srcset="/images/hero.webp" type="image/webp">
                            <img src="/images/hero.png" alt="IdenTix Vision" loading="lazy" decoding="async" width="600"
                                height="400"
                                class="rounded-[2.2rem] w-full h-full object-cover opacity-80 mix-blend-lighten">
                        </picture>
                        <div class="absolute inset-0 bg-gradient-to-t from-[#13131b] via-transparent to-transparent">
                        </div>
                        <div class="absolute bottom-10 left-10 right-10">
                            <div class="text-4xl font-bold font-outfit mb-2">IdenTix</div>
                            <div class="text-orange-400 font-medium italic">"Connecting Generations Through Every Gate"
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-24 bg-[#111118]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="relative">
                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-orange-500/20 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-amber-500/20 rounded-full blur-3xl"></div>
                    <div class="glass p-8 rounded-[40px] relative overflow-hidden">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-4">
                                <div class="h-48 bg-[#1e1e2a] rounded-3xl flex items-center justify-center">
                                    <span class="text-4xl">🎟️</span>
                                </div>
                                <div
                                    class="h-32 bg-gradient-to-br from-orange-500 to-amber-600 rounded-3xl flex items-center justify-center">
                                    <span class="text-4xl">🚀</span>
                                </div>
                            </div>
                            <div class="space-y-4 pt-8">
                                <div class="h-32 bg-[#252530] rounded-3xl flex items-center justify-center">
                                    <span class="text-4xl">💎</span>
                                </div>
                                <div class="h-48 bg-[#1e1e2a] rounded-3xl flex items-center justify-center">
                                    <span class="text-4xl">✨</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-4xl font-bold font-outfit mb-8 leading-tight text-white">About <span
                            class="text-orange-400">IdenTix</span></h2>
                    <p class="text-lg text-stone-300 font-light mb-8 leading-relaxed">
                        IdenTix is more than just a ticketing platform. We are a bridge between passionate event-goers
                        and the most extraordinary experiences. Founded in 2024, our mission is to make event access
                        seamless, secure, and purely delightful.
                    </p>
                    <ul class="space-y-6 mb-10">
                        <li class="flex items-start gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-orange-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-base">Verified Organizers</h3>
                                <p class="text-sm text-stone-300">Every event on our platform is vetted for security.
                                </p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-orange-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-base">Fast-Pass Entry</h3>
                                <p class="text-sm text-stone-300">Scan your QR code and get in within seconds.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-orange-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-base">24/7 Priority Support</h3>
                                <p class="text-sm text-stone-300">Our team is always here to help with your bookings.
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <x-public-footer :settings="$settings" />

    <x-accessibility-widget />
</body>

</html>