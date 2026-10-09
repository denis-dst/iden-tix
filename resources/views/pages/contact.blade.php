@php
    $st = $settings ?? $global_settings ?? \App\Models\Setting::pluck('value', 'key')->all();
    $appName = $st['app_name'] ?? 'IdenTix';
    $email = $st['contact_email'] ?? 'info@iden-tix.com';
    $phone = $st['contact_phone'] ?? '081389890092';
    $address = $st['address'] ?? 'VHEDORA RESIDENCE, PESAWARAN, LAMPUNG.';
    $appLogo = $st['app_logo'] ?? null;
    $metaDescription = $st['meta_description'] ?? 'Layanan Pelanggan & Info Usaha ' . $appName;

    $waPhone = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($waPhone, '0')) {
        $waPhone = '62' . substr($waPhone, 1);
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Usaha - {{ $appName }}</title>
    <meta name="description" content="Layanan kontak dan informasi resmi {{ $appName }}">
    
    <!-- Scripts & Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        .glass-card {
            background: rgba(25, 24, 32, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            background: rgba(35, 33, 44, 0.9);
            border-color: rgba(249, 115, 22, 0.35);
        }
        .bg-gradient-main {
            background: radial-gradient(circle at top right, rgba(249, 115, 22, 0.12), transparent 45%),
                        radial-gradient(circle at bottom left, rgba(251, 146, 60, 0.08), transparent 45%);
        }
        [x-cloak] { display: none !important; }
        ::selection { background: rgba(249, 115, 22, 0.3); color: #ffffff; }
    </style>
    <meta name="wago-verification" content="WAGO-1BE5FB2B">
</head>
<body class="bg-[#111118] text-[#e8e4df] antialiased flex flex-col min-h-screen bg-gradient-main selection:bg-orange-500/30">

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-50 w-full glass border-b border-white/10 shadow-lg shadow-black/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="/" class="flex items-center gap-3 group">
                    @if(!empty($appLogo) && file_exists(public_path('storage/' . $appLogo)))
                        <img src="{{ asset('storage/' . $appLogo) }}" alt="{{ $appName }}" class="h-10 w-auto object-contain">
                    @else
                        <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <span class="text-2xl font-black tracking-tight font-outfit uppercase text-white">Iden<span class="text-orange-400">Tix</span></span>
                    @endif
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

    <!-- Hero Banner -->
    <section class="pt-12 pb-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-orange-500/10 blur-[130px] pointer-events-none"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-500/15 text-orange-400 border border-orange-500/30 inline-flex items-center gap-2 mb-4 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                Layanan Pelanggan & Info Usaha
            </span>
            <h1 class="text-4xl md:text-5xl font-black font-outfit mb-4 text-white tracking-tight">Hubungi Kami</h1>
            <p class="text-stone-300 max-w-2xl mx-auto text-base leading-relaxed font-normal">Kami senang dapat membantu Anda. Silakan hubungi tim {{ $appName }} melalui saluran kontak resmi di bawah ini.</p>
        </div>
    </section>

    <!-- Main Content Grid -->
    <main class="py-6 pb-20 flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Contact Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                
                <!-- Email Card -->
                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group">
                    <div class="w-14 h-14 rounded-2xl bg-orange-500/15 text-orange-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-xs uppercase tracking-wider text-stone-400 font-bold mb-1">Email Support / Bisnis</h3>
                    <p class="text-lg sm:text-xl font-bold text-white mb-2 font-outfit break-all">{{ $email }}</p>
                    <p class="text-stone-300 text-xs mb-4 leading-relaxed">Kirimkan email untuk dukungan tiket, kemitraan event, atau pertanyaan umum.</p>
                    <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 text-xs font-bold text-orange-400 hover:text-orange-300">
                        Kirim Email &rarr;
                    </a>
                </div>

                <!-- Phone / WA Card -->
                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group">
                    <div class="w-14 h-14 rounded-2xl bg-orange-500/15 text-orange-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <h3 class="text-xs uppercase tracking-wider text-stone-400 font-bold mb-1">Telepon / WhatsApp</h3>
                    <p class="text-lg sm:text-xl font-bold text-white mb-2 font-outfit">{{ $phone }}</p>
                    <p class="text-stone-300 text-xs mb-4 leading-relaxed">Layanan respon cepat melalui pesan WhatsApp atau panggilan telepon.</p>
                    <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-orange-400 hover:text-orange-300">
                        Chat WhatsApp &rarr;
                    </a>
                </div>

                <!-- Address Card -->
                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group">
                    <div class="w-14 h-14 rounded-2xl bg-orange-500/15 text-orange-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-xs uppercase tracking-wider text-stone-400 font-bold mb-1">Alamat Bisnis / Kantor</h3>
                    <p class="text-base font-bold text-white mb-2 font-outfit">{{ $appName }}</p>
                    <p class="text-stone-300 text-xs leading-relaxed mb-4">{{ $address }}</p>
                    <a href="https://maps.google.com/?q={{ urlencode($address) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-orange-400 hover:text-orange-300 font-semibold">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></path></svg>
                        Buka di Maps &rarr;
                    </a>
                </div>

            </div>

            <!-- Form & Map Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start" x-data="{ sent: false, name: '', email: '', subject: '', message: '' }">
                
                <!-- Contact Form -->
                <div class="glass bg-[#16151e]/85 backdrop-blur-xl border border-white/10 p-8 md:p-10 rounded-[2.5rem] shadow-2xl shadow-black/40">
                    <h3 class="text-2xl font-bold text-white font-outfit mb-2">Kirim Pesan Langsung</h3>
                    <p class="text-stone-300 text-sm mb-6 leading-relaxed">Isi formulir di bawah ini dan tim {{ $appName }} akan merespons pesan Anda secepatnya.</p>
                    
                    <div x-show="sent" class="p-6 bg-emerald-500/15 border border-emerald-500/30 rounded-2xl mb-6 text-emerald-300 text-sm flex items-center gap-3">
                        <svg class="w-6 h-6 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Terima kasih! Pesan Anda telah terkirim ke tim {{ $appName }}. Kami akan segera menghubungi Anda melalui email.</span>
                    </div>

                    <form @submit.prevent="sent = true" x-show="!sent" class="space-y-4">
                        <div>
                            <label class="block text-xs uppercase font-bold text-stone-300 mb-2">Nama Lengkap</label>
                            <input type="text" x-model="name" required placeholder="Masukkan nama Anda" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-stone-500 focus:outline-none focus:border-orange-500 transition text-sm">
                        </div>

                        <div>
                            <label class="block text-xs uppercase font-bold text-stone-300 mb-2">Alamat Email</label>
                            <input type="email" x-model="email" required placeholder="contoh@domain.com" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-stone-500 focus:outline-none focus:border-orange-500 transition text-sm">
                        </div>

                        <div>
                            <label class="block text-xs uppercase font-bold text-stone-300 mb-2">Subjek / Perihal</label>
                            <input type="text" x-model="subject" required placeholder="misal: Pertanyaan Tiket Event" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-stone-500 focus:outline-none focus:border-orange-500 transition text-sm">
                        </div>

                        <div>
                            <label class="block text-xs uppercase font-bold text-stone-300 mb-2">Pesan Anda</label>
                            <textarea x-model="message" rows="4" required placeholder="Tuliskan pesan atau bantuan yang Anda butuhkan secara jelas..." class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-stone-500 focus:outline-none focus:border-orange-500 transition text-sm"></textarea>
                        </div>

                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-black font-extrabold uppercase tracking-wider rounded-xl transition-all shadow-lg shadow-orange-500/25 text-sm">
                            Kirim Pesan Sekarang
                        </button>
                    </form>
                </div>

                <!-- Business Profile & Map Details -->
                <div class="space-y-6">
                    <div class="glass bg-[#16151e]/85 backdrop-blur-xl border border-white/10 p-8 md:p-10 rounded-[2.5rem] shadow-2xl shadow-black/40">
                        <h3 class="text-xl font-bold text-white font-outfit mb-4">Profil Usaha Resmi</h3>
                        <dl class="space-y-4 text-sm">
                            <div class="border-b border-white/10 pb-3">
                                <dt class="text-xs text-stone-400 uppercase font-semibold">Nama Bisnis / Platform</dt>
                                <dd class="text-white font-bold text-base mt-0.5">{{ $appName }}</dd>
                            </div>
                            <div class="border-b border-white/10 pb-3">
                                <dt class="text-xs text-stone-400 uppercase font-semibold">Alamat Lengkap</dt>
                                <dd class="text-stone-200 leading-relaxed mt-0.5">{{ $address }}</dd>
                            </div>
                            <div class="border-b border-white/10 pb-3">
                                <dt class="text-xs text-stone-400 uppercase font-semibold">Kontak Utama</dt>
                                <dd class="text-stone-200 mt-0.5">
                                    Email: <a href="mailto:{{ $email }}" class="text-orange-400 hover:underline">{{ $email }}</a> 
                                    | WA: <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="text-orange-400 hover:underline">{{ $phone }}</a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-stone-400 uppercase font-semibold">Jam Operasional Layanan Support</dt>
                                <dd class="text-stone-200 mt-0.5">Senin &ndash; Minggu: 08.00 &ndash; 22.00 WIB</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Location Visual Card -->
                    <div class="glass-card p-6 rounded-[2rem] border border-white/10 bg-gradient-to-br from-white/5 to-transparent">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            </div>
                            <h4 class="font-bold text-white">Lokasi Kantor / Usaha</h4>
                        </div>
                        <p class="text-xs text-stone-300 leading-relaxed mb-4">{{ $address }}</p>
                        <a href="https://maps.google.com/?q={{ urlencode($address) }}" target="_blank" class="w-full py-2.5 glass border border-white/10 hover:border-orange-500/40 text-stone-200 hover:text-orange-400 text-xs font-bold rounded-xl flex items-center justify-center gap-2 transition bg-white/5">
                            Buka di Google Maps &rarr;
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer Component -->
    <x-public-footer />

</body>
</html>
