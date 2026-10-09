<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $global_settings['app_name'] ?? config('app.name', 'IdenTix') }} - {{ $title ?? 'Dashboard' }}</title>
    <!-- Fonts (High Performance Non-Blocking Loading) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    </noscript>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Trix Editor -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
    <style>
        trix-toolbar [data-trix-button-group="file-tools"] { display: none; }
        .trix-content { min-height: 250px !important; }
        trix-editor { background: white; border-radius: 0.75rem; border-color: #e2e8f0; }
        trix-editor:focus-within { border-color: #a855f7; box-shadow: 0 0 0 1px #a855f7; outline: none; }

        /* Trix Formatting Styles to bypass Tailwind CSS resets */
        .trix-content h1, trix-editor h1 {
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            margin-bottom: 0.5rem !important;
            display: block !important;
        }
        .trix-content h2, trix-editor h2 {
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            margin-bottom: 0.5rem !important;
            display: block !important;
        }
        .trix-content strong, .trix-content b, trix-editor strong, trix-editor b {
            font-weight: bold !important;
        }
        .trix-content ul, trix-editor ul {
            list-style-type: disc !important;
            margin-left: 1.5rem !important;
            margin-bottom: 1rem !important;
            padding-left: 0.5rem !important;
            display: block !important;
        }
        .trix-content ol, trix-editor ol {
            list-style-type: decimal !important;
            margin-left: 1.5rem !important;
            margin-bottom: 1rem !important;
            padding-left: 0.5rem !important;
            display: block !important;
        }
        .trix-content li, trix-editor li {
            list-style: inherit !important;
            margin-bottom: 0.25rem !important;
            display: list-item !important;
        }
        .trix-content a, trix-editor a {
            color: #3b82f6 !important;
            text-decoration: underline !important;
        }
    </style>
    <!-- PWA Settings -->
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $global_settings['app_name'] ?? 'IdenTix' }}">
    <link rel="apple-touch-icon" href="{{ isset($global_settings['app_icon']) ? asset('storage/' . $global_settings['app_icon']) : '/icons/icon-192x192.png' }}">
    <link rel="icon" type="image/x-icon" href="{{ isset($global_settings['app_favicon']) ? asset('storage/' . $global_settings['app_favicon']) : '/favicon.ico' }}">
    <meta name="theme-color" content="#f97316">
    <meta name="description" content="{{ $global_settings['meta_description'] ?? '' }}">
    
    <style>
        [x-cloak] { display: none !important; }
        .font-outfit { font-family: 'Outfit', sans-serif; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
    <meta name="wago-verification" content="WAGO-1BE5FB2B">
</head>
@php
    $shouldHideNav = isset($hideNav) && trim($hideNav->toHtml()) === '1';
@endphp
<body class="h-full bg-slate-50 text-slate-900 antialiased font-sans flex flex-col overflow-hidden" x-data="{ sidebarOpen: false }">
    <x-impersonate-banner />
    <div class="flex flex-1 overflow-hidden">
        @if(!$shouldHideNav)
        <!-- Desktop Sidebar -->
        <aside class="hidden lg:flex w-72 bg-[#0f172a] text-slate-300 flex-col shrink-0 border-r border-white/5">
            <div class="h-16 flex items-center px-6 bg-[#0a0f1d] shrink-0">
                <a href="/" class="flex items-center gap-3">
                    @if(isset($global_settings['app_logo']) && $global_settings['app_logo'])
                        <img src="{{ asset('storage/' . $global_settings['app_logo']) }}" alt="Logo" class="h-8 w-auto">
                    @else
                        <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                        </div>
                    @endif
                    <span class="text-xl font-black text-white font-outfit uppercase tracking-tight">
                        @if(isset($global_settings['app_name']))
                            @php
                                $nameParts = explode(' ', $global_settings['app_name']);
                                $firstPart = $nameParts[0] ?? '';
                                $secondPart = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
                            @endphp
                            {{ $firstPart }}<span class="text-orange-400">{{ $secondPart }}</span>
                        @else
                            Gen<span class="text-orange-400">Tix</span>
                        @endif
                    </span>
                </a>
            </div>
            
            @include('layouts.partials.sidebar-content')
        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-50 flex lg:hidden overflow-hidden">
            <div x-show="sidebarOpen" x-transition:opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="sidebarOpen = false"></div>
            <aside x-show="sidebarOpen" 
                   x-transition:enter="transition-transform duration-300"
                   x-transition:enter-start="-translate-x-full"
                   x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition-transform duration-300"
                   x-transition:leave-start="translate-x-0"
                   x-transition:leave-end="-translate-x-full"
                   class="relative w-72 bg-[#0f172a] text-slate-300 flex flex-col h-full shadow-2xl">
                <div class="h-16 flex items-center justify-between px-6 bg-[#0a0f1d] shrink-0 border-b border-white/5">
                    <span class="text-xl font-black text-white font-outfit uppercase tracking-tight">
                        @if(isset($global_settings['app_name']))
                            @php
                                $nameParts = explode(' ', $global_settings['app_name']);
                                $firstPart = $nameParts[0] ?? '';
                                $secondPart = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
                            @endphp
                            {{ $firstPart }}<span class="text-orange-400">{{ $secondPart }}</span>
                        @else
                            Gen<span class="text-orange-400">Tix</span>
                        @endif
                    </span>
                    <button @click="sidebarOpen = false" class="p-2 text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                @include('layouts.partials.sidebar-content')
            </aside>
        </div>
        @endif

        <!-- Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            @if(!$shouldHideNav && !auth()->user()?->hasRole('Petugas Gate'))
                <!-- Desktop Header -->
                <header class="hidden lg:flex h-16 bg-white border-b border-slate-200 items-center justify-between px-8 shrink-0">
                    <h1 class="text-lg font-black text-slate-800 font-outfit">{{ $title ?? 'Dashboard' }}</h1>
                    @include('layouts.partials.header-profile')
                </header>

                <!-- Mobile Header -->
                <header class="lg:hidden h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 shrink-0 z-30">
                    <button @click="sidebarOpen = true" class="p-2 -ml-2 text-slate-500 hover:text-orange-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <span class="text-lg font-black text-slate-800 font-outfit uppercase tracking-tight">
                        @if(isset($global_settings['app_name']))
                            @php
                                $nameParts = explode(' ', $global_settings['app_name']);
                                $firstPart = $nameParts[0] ?? '';
                                $secondPart = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
                            @endphp
                            {{ $firstPart }}<span class="text-orange-500">{{ $secondPart }}</span>
                        @else
                            Iden<span class="text-orange-500">Tix</span>
                        @endif
                    </span>
                    <div class="w-10 h-10 rounded-lg bg-orange-500 text-white flex items-center justify-center font-bold">
                        {{ substr(Auth::user()?->name ?? 'U', 0, 1) }}
                    </div>
                </header>
            @endif
            
            <main class="flex-1 overflow-y-auto bg-slate-50 custom-scrollbar">
                @include('layouts.partials.page-header')
                <div class="{{ $shouldHideNav ? 'h-full w-full' : 'p-4 lg:p-8 max-w-[1400px] mx-auto w-full pb-12' }}">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @if(!$shouldHideNav)
    <x-accessibility-widget />
    @endif

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>

    {{-- Page-level scripts from child views --}}
    @stack('scripts')
</body>
</html>
