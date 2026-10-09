<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - IdenTix</title>
    
    <!-- Scripts & Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
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

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #111118; color: #e8e4df; }
        .font-outfit { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(30, 28, 35, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .bg-gradient-main {
            background: radial-gradient(circle at top right, rgba(249, 115, 22, 0.12), transparent 45%),
                        radial-gradient(circle at bottom left, rgba(251, 146, 60, 0.08), transparent 45%);
        }
        .page-content ul { list-style-type: disc; margin-left: 1.5rem; margin-bottom: 1rem; color: #e2e8f0; }
        .page-content ol { list-style-type: decimal; margin-left: 1.5rem; margin-bottom: 1rem; color: #e2e8f0; }
        .page-content li { margin-bottom: 0.5rem; }
        .page-content b, .page-content strong { font-weight: 700; color: #ffffff; }
        .page-content p { margin-bottom: 1rem; line-height: 1.7; color: #e2e8f0; }
        .page-content h2 { font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; color: #ffffff; margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.5rem; }
        .page-content h3 { font-size: 1.2rem; font-weight: 600; color: #f97316; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        ::selection { background: rgba(249, 115, 22, 0.3); color: #ffffff; }
    </style>
</head>
<body class="bg-[#111118] text-[#e8e4df] antialiased flex flex-col min-h-screen bg-gradient-main selection:bg-orange-500/30">

    <!-- Header / Navbar (Sticky so it NEVER overlaps header content) -->
    <header class="sticky top-0 z-50 w-full glass border-b border-white/10 shadow-lg shadow-black/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-black tracking-tight font-outfit uppercase text-white">Iden<span class="text-orange-400">Tix</span></span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="/" class="text-sm font-medium text-stone-200 hover:text-orange-400 transition flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 hover:border-orange-500/30 hover:bg-white/10">
                        <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Header Section (Properly spaced and styled) -->
    <section class="pt-12 pb-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-orange-500/10 blur-[130px] pointer-events-none"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-500/15 text-orange-400 border border-orange-500/30 inline-flex items-center gap-2 mb-4 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                Informasi Resmi
            </span>
            <h1 class="text-4xl md:text-5xl font-black font-outfit mb-4 text-white tracking-tight">{{ $page->title }}</h1>
        </div>
    </section>

    <!-- Content Section -->
    <main class="py-6 pb-20 flex-1">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass bg-[#16151e]/85 backdrop-blur-xl border border-white/10 p-8 md:p-12 rounded-[2.5rem] shadow-2xl shadow-black/50 page-content">
                {!! $page->content !!}
            </div>
        </div>
    </main>

    <!-- Footer -->
    <x-public-footer />
</body>
</html>
