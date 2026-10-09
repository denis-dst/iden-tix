@php
    $st = $settings ?? $global_settings ?? \App\Models\Setting::pluck('value', 'key')->all();
    $appName = $st['app_name'] ?? 'IdenTix';
    $email = $st['contact_email'] ?? 'info@iden-tix.com';
    $phone = $st['contact_phone'] ?? '081389890092';
    $address = $st['address'] ?? 'VHEDORA RESIDENCE, PESAWARAN, LAMPUNG.';
    $appLogo = $st['app_logo'] ?? null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pertanyaan Umum (FAQ) - IdenTix</title>
    
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

    <!-- Hero Banner (Properly spaced and styled) -->
    <section class="pt-12 pb-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-orange-500/10 blur-[130px] pointer-events-none"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-500/15 text-orange-400 border border-orange-500/30 inline-flex items-center gap-2 mb-4 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                Pusat Bantuan & Layanan
            </span>
            <h1 class="text-4xl md:text-5xl font-black font-outfit mb-4 text-white tracking-tight">Pertanyaan Umum (FAQ)</h1>
            <p class="text-stone-300 max-w-2xl mx-auto text-base leading-relaxed font-normal">Temukan jawaban lengkap dan cepat seputar pemesanan tiket, sistem e-voucher, metode pembayaran, hingga panduan masuk gate event di IdenTix.</p>
        </div>
    </section>

    <!-- Content & Interactive Accordion -->
    <main class="py-6 pb-20 flex-1" x-data="{ activeTab: 'all', openFaq: null }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Category Tabs -->
            <div class="flex flex-wrap gap-2.5 justify-center mb-10 p-2 bg-stone-900/60 glass rounded-2xl border border-white/10">
                <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-black font-extrabold shadow-lg shadow-orange-500/30' : 'text-stone-300 hover:text-white hover:bg-white/10'" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200">Semua FAQ</button>
                <button @click="activeTab = 'pemesanan'" :class="activeTab === 'pemesanan' ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-black font-extrabold shadow-lg shadow-orange-500/30' : 'text-stone-300 hover:text-white hover:bg-white/10'" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200">Pembelian & Pembayaran</button>
                <button @click="activeTab = 'evoucher'" :class="activeTab === 'evoucher' ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-black font-extrabold shadow-lg shadow-orange-500/30' : 'text-stone-300 hover:text-white hover:bg-white/10'" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200">E-Voucher & Check-in</button>
                <button @click="activeTab = 'refund'" :class="activeTab === 'refund' ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-black font-extrabold shadow-lg shadow-orange-500/30' : 'text-stone-300 hover:text-white hover:bg-white/10'" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200">Refund & Pembatalan</button>
            </div>

            <!-- FAQ List -->
            <div class="space-y-4">
                
                <!-- Item 1 -->
                <div x-show="activeTab === 'all' || activeTab === 'pemesanan'" :class="openFaq === 1 ? 'border-orange-500/50 bg-[#1d1c25]' : 'border-white/10 bg-[#16151e]'" class="glass-card rounded-2xl overflow-hidden transition-all duration-200">
                    <button @click="openFaq = (openFaq === 1 ? null : 1)" class="w-full p-6 text-left flex justify-between items-center gap-4 hover:bg-white/[0.04] transition">
                        <span class="font-bold text-white text-lg font-outfit">Bagaimana cara membeli tiket di IdenTix?</span>
                        <div :class="openFaq === 1 ? 'bg-orange-500 text-black' : 'bg-orange-500/15 text-orange-400'" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                            <svg x-show="openFaq !== 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <svg x-show="openFaq === 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                        </div>
                    </button>
                    <div x-show="openFaq === 1" x-collapse class="px-6 pb-6 text-stone-200 text-sm leading-relaxed border-t border-white/10 pt-4">
                        <p class="mb-3 text-stone-300">Untuk membeli tiket event di platform IdenTix, ikuti langkah-langkah mudah berikut:</p>
                        <ol class="list-decimal list-inside space-y-2 text-stone-200">
                            <li>Pilih event yang ingin Anda hadiri dari daftar event di beranda IdenTix.</li>
                            <li>Tentukan jenis kategori tiket dan jumlah tiket yang Anda inginkan.</li>
                            <li>Isi data pemesan (<strong class="text-white">Nama Lengkap, Email, dan No. WhatsApp</strong>).</li>
                            <li>Pilih metode pembayaran yang tersedia (<strong class="text-white">Virtual Account, QRIS, E-Wallet, atau Kartu Kredit</strong>).</li>
                            <li>Lakukan pembayaran sesuai instruksi sebelum batas waktu (expired timer) berakhir.</li>
                        </ol>
                    </div>
                </div>

                <!-- Item 2 -->
                <div x-show="activeTab === 'all' || activeTab === 'pemesanan'" :class="openFaq === 2 ? 'border-orange-500/50 bg-[#1d1c25]' : 'border-white/10 bg-[#16151e]'" class="glass-card rounded-2xl overflow-hidden transition-all duration-200">
                    <button @click="openFaq = (openFaq === 2 ? null : 2)" class="w-full p-6 text-left flex justify-between items-center gap-4 hover:bg-white/[0.04] transition">
                        <span class="font-bold text-white text-lg font-outfit">Metode pembayaran apa saja yang tersedia?</span>
                        <div :class="openFaq === 2 ? 'bg-orange-500 text-black' : 'bg-orange-500/15 text-orange-400'" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                            <svg x-show="openFaq !== 2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <svg x-show="openFaq === 2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                        </div>
                    </button>
                    <div x-show="openFaq === 2" x-collapse class="px-6 pb-6 text-stone-200 text-sm leading-relaxed border-t border-white/10 pt-4">
                        <p class="mb-3 text-stone-300">IdenTix bekerjasama dengan payment gateway resmi untuk menyediakan pilihan metode pembayaran yang instan, aman, dan terverifikasi otomatis:</p>
                        <ul class="list-disc list-inside space-y-2 text-stone-200">
                            <li><strong class="text-white">QRIS:</strong> Scan real-time menggunakan GoPay, OVO, DANA, ShopeePay, LinkAja, BCA Mobile, Livin Mandiri, BRImo, atau semua aplikasi perbankan berstandar QRIS.</li>
                            <li><strong class="text-white">Virtual Account Bank:</strong> BCA, Bank Mandiri, BNI, BRI, Permata Bank, dan jaringan transfer antarbank.</li>
                            <li><strong class="text-white">Kartu Kredit / Debit Online:</strong> Visa, Mastercard, dan JCB dengan proteksi 3D Secure OTP.</li>
                        </ul>
                    </div>
                </div>

                <!-- Item 3 -->
                <div x-show="activeTab === 'all' || activeTab === 'evoucher'" :class="openFaq === 3 ? 'border-orange-500/50 bg-[#1d1c25]' : 'border-white/10 bg-[#16151e]'" class="glass-card rounded-2xl overflow-hidden transition-all duration-200">
                    <button @click="openFaq = (openFaq === 3 ? null : 3)" class="w-full p-6 text-left flex justify-between items-center gap-4 hover:bg-white/[0.04] transition">
                        <span class="font-bold text-white text-lg font-outfit">Di mana saya bisa melihat & mengunduh E-Voucher tiket?</span>
                        <div :class="openFaq === 3 ? 'bg-orange-500 text-black' : 'bg-orange-500/15 text-orange-400'" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                            <svg x-show="openFaq !== 3" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <svg x-show="openFaq === 3" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                        </div>
                    </button>
                    <div x-show="openFaq === 3" x-collapse class="px-6 pb-6 text-stone-200 text-sm leading-relaxed border-t border-white/10 pt-4">
                        <p>Setelah status pembayaran Anda berhasil terverifikasi, E-Voucher tiket resmi beserta QR Code unik akan <strong class="text-white">otomatis dikirimkan langsung ke email terdaftar</strong>. Anda juga dapat mengunduh format PDF E-Voucher langsung dari halaman sukses transaksi setelah pembayaran selesai.</p>
                    </div>
                </div>

                <!-- Item 4 -->
                <div x-show="activeTab === 'all' || activeTab === 'evoucher'" :class="openFaq === 4 ? 'border-orange-500/50 bg-[#1d1c25]' : 'border-white/10 bg-[#16151e]'" class="glass-card rounded-2xl overflow-hidden transition-all duration-200">
                    <button @click="openFaq = (openFaq === 4 ? null : 4)" class="w-full p-6 text-left flex justify-between items-center gap-4 hover:bg-white/[0.04] transition">
                        <span class="font-bold text-white text-lg font-outfit">Apakah E-Voucher perlu dicetak (print out)?</span>
                        <div :class="openFaq === 4 ? 'bg-orange-500 text-black' : 'bg-orange-500/15 text-orange-400'" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                            <svg x-show="openFaq !== 4" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <svg x-show="openFaq === 4" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                        </div>
                    </button>
                    <div x-show="openFaq === 4" x-collapse class="px-6 pb-6 text-stone-200 text-sm leading-relaxed border-t border-white/10 pt-4">
                        <p><strong class="text-orange-400">Tidak perlu dicetak!</strong> IdenTix sepenuhnya mendukung sistem paperless yang ramah lingkungan. Cukup tunjukkan layar QR Code pada smartphone Anda kepada kru gate/petugas check-in di venue event untuk dipindai.</p>
                    </div>
                </div>

                <!-- Item 5 -->
                <div x-show="activeTab === 'all' || activeTab === 'refund'" :class="openFaq === 5 ? 'border-orange-500/50 bg-[#1d1c25]' : 'border-white/10 bg-[#16151e]'" class="glass-card rounded-2xl overflow-hidden transition-all duration-200">
                    <button @click="openFaq = (openFaq === 5 ? null : 5)" class="w-full p-6 text-left flex justify-between items-center gap-4 hover:bg-white/[0.04] transition">
                        <span class="font-bold text-white text-lg font-outfit">Bagaimana kebijakan pengembalian dana (Refund)?</span>
                        <div :class="openFaq === 5 ? 'bg-orange-500 text-black' : 'bg-orange-500/15 text-orange-400'" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                            <svg x-show="openFaq !== 5" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <svg x-show="openFaq === 5" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                        </div>
                    </button>
                    <div x-show="openFaq === 5" x-collapse class="px-6 pb-6 text-stone-200 text-sm leading-relaxed border-t border-white/10 pt-4">
                        <p>Tiket yang telah berhasil dibeli dan terkonfirmasi secara umum bersifat <strong class="text-white">Non-Refundable</strong> (tidak dapat dikembalikan/ditukar). Namun, apabila terjadi kondisi pembatalan resmi event oleh pihak Penyelenggara (Organizer), dana akan dikembalikan sesuai alur dan ketentuan lengkap pada halaman <a href="{{ route('refund') }}" class="text-orange-400 font-semibold hover:text-orange-300 underline">Refund Policy</a>.</p>
                    </div>
                </div>

            </div>

            <!-- Still have questions card -->
            <div class="mt-14 p-8 md:p-10 glass rounded-3xl border border-orange-500/30 bg-gradient-to-r from-orange-500/15 via-[#1a1924] to-transparent flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl shadow-black/40">
                <div>
                    <span class="px-3 py-1 rounded-lg text-[11px] font-extrabold uppercase tracking-wider bg-orange-500/20 text-orange-300 border border-orange-500/30 inline-block mb-2">Pusat Layanan</span>
                    <h3 class="text-2xl font-extrabold text-white font-outfit mb-2">Masih memiliki pertanyaan lain?</h3>
                    <p class="text-stone-300 text-sm max-w-lg leading-relaxed">Tim dukungan IdenTix siap membantu Anda menjawab segala kendala dan pertanyaan melalui saluran resmi kami.</p>
                </div>
                <a href="{{ route('contact') }}" class="px-7 py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-black font-black uppercase tracking-wider rounded-xl transition-all shadow-lg shadow-orange-500/25 whitespace-nowrap hover:scale-105">Hubungi Kami &rarr;</a>
            </div>

        </div>
    </main>

    <!-- Footer Component -->
    <x-public-footer />

</body>
</html>
