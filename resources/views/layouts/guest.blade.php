<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'IdenTix') }} - Authentication</title>

        <!-- Fonts (High Performance Non-Blocking Loading) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'">
        <noscript>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
        </noscript>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
            --identix-50: #fff7ed;
                --identix-600: #ea580c;
                --identix-700: #c2410c;
            }
            [x-cloak] { display: none !important; }
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #111118;
            }
            .font-outfit { font-family: 'Outfit', sans-serif; }
            .glass {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.1);
            }
            .auth-bg {
                background-image: url('/images/hero.webp');
                background-size: cover;
                background-position: center;
            }
            .auth-overlay {
                background: radial-gradient(circle at center, rgba(15, 23, 42, 0.7) 0%, rgba(15, 23, 42, 0.95) 100%);
            }
            button[type="submit"] {
                background-color: #f97316 !important;
                color: #000000 !important;
                font-weight: 800 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.05em !important;
                transition: all 0.2s !important;
                border-radius: 0.75rem !important;
            }
            button[type="submit"]:hover {
                background-color: #ea580c !important;
                transform: translateY(-1px) !important;
                box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3) !important;
            }

            /* Global Orange Theme Overrides */
            .bg-orange-600, .bg-orange-500, .bg-orange-400 {
                background-color: #f97316 !important;
                color: #000000 !important;
            }
            
            .bg-orange-600 *, .bg-orange-500 *, .bg-orange-400 * {
                color: #000000 !important;
            }

            .bg-orange-600:hover, .bg-orange-500:hover, .bg-orange-400:hover {
                background-color: #ea580c !important;
            }
        </style>
        <meta name="wago-verification" content="WAGO-1BE5FB2B">
    </head>
    <body class="antialiased text-white">
        <div class="min-h-screen relative flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 auth-bg">
            <div class="absolute inset-0 auth-overlay z-0"></div>
            
            <div class="relative z-10 w-full sm:max-w-md">
                <div class="text-center mb-10">
                    <a href="/" class="inline-flex items-center gap-3 group">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl flex items-center justify-center shadow-2xl shadow-orange-500/40 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <span class="text-3xl font-bold tracking-tight font-outfit uppercase">Iden<span class="text-orange-400">Tix</span></span>
                    </a>
                </div>

                <div class="glass p-8 sm:p-10 rounded-[2.5rem] shadow-2xl">
                    {{ $slot }}
                </div>
                
                <p class="mt-8 text-center text-slate-400 text-sm">
                    &copy; {{ date('Y') }} IdenTix. All rights reserved.
                </p>
            </div>
        </div>
        <x-accessibility-widget />
</body>
</html>
