<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alur Proses Bisnis & Integrasi Pembayaran WAGO Payment ID - IdenTix</title>
    <meta name="description" content="Dokumentasi resmi alur proses bisnis platform ticketing IdenTix dan penjelasan integrasi gerbang pembayaran WAGO Payment ID." />

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
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #111118;
            color: #e8e4df;
        }
        .font-outfit { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(30, 28, 35, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(26, 24, 33, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            border-color: rgba(249, 115, 22, 0.35);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px -10px rgba(249, 115, 22, 0.15);
        }
        .bg-gradient-main {
            background: radial-gradient(circle at 85% 15%, rgba(249, 115, 22, 0.12), transparent 45%),
                        radial-gradient(circle at 15% 85%, rgba(251, 146, 60, 0.08), transparent 45%);
        }
        ::selection { background: rgba(249, 115, 22, 0.3); color: #ffffff; }
        
        .step-connector::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 2rem;
            width: 2px;
            height: 2rem;
            background: linear-gradient(to bottom, rgba(249, 115, 22, 0.5), rgba(249, 115, 22, 0.1));
        }
        @media (min-width: 768px) {
            .step-connector::after {
                left: 2.25rem;
            }
        }
    </style>
</head>
<body class="bg-[#111118] text-[#e8e4df] antialiased flex flex-col min-h-screen bg-gradient-main selection:bg-orange-500/30">

    <!-- Header / Navbar -->
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
                <div class="flex items-center gap-3 sm:gap-4">
                    <a href="/#events" class="hidden sm:flex text-sm font-medium text-stone-300 hover:text-white transition items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 hover:border-orange-500/30">
                        Event Katalog
                    </a>
                    <a href="/" class="text-sm font-medium text-stone-200 hover:text-orange-400 transition flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 hover:border-orange-500/30 hover:bg-white/10">
                        <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Beranda
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Banner -->
    <section class="pt-14 pb-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-orange-500/10 blur-[140px] pointer-events-none"></div>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-500/15 text-orange-400 border border-orange-500/30 mb-5 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                Dokumentasi Resmi Alur Bisnis & Pembayaran
            </div>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black font-outfit mb-5 text-white tracking-tight leading-tight">
                Alur Transaksi & Integrasi <span class="text-orange-400">WAGO Payment ID</span>
            </h1>
            <p class="text-stone-300 max-w-3xl mx-auto text-base sm:text-lg leading-relaxed font-normal">
                Transparansi alur proses pemesanan tiket digital (E-Voucher) pada platform <strong>IdenTix</strong>, peran gerbang pembayaran <strong>WAGO Payment ID (Pay-Engine)</strong>, serta jaminan pengiriman produk digital secara instan dan aman.
            </p>

            <!-- Quick Badges -->
            <div class="flex flex-wrap justify-center gap-3 sm:gap-4 mt-8">
                <div class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs font-semibold text-stone-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Produk Digital: E-Voucher & QR Tiket
                </div>
                <div class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs font-semibold text-stone-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Gerbang Pembayaran Resmi: WAGO Payment ID
                </div>
                <div class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs font-semibold text-stone-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Pengiriman Instan (< 1 Menit)
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="py-6 pb-24 flex-1">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Section: Profil Model Bisnis IdenTix -->
            <div class="glass bg-[#16151e]/90 backdrop-blur-xl border border-white/10 p-6 sm:p-10 rounded-[2.5rem] shadow-2xl shadow-black/40">
                <div class="flex items-center gap-3 border-b border-white/10 pb-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center font-black border border-orange-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold font-outfit text-white">1. Profil Model Bisnis & Layanan Platform</h2>
                        <p class="text-xs sm:text-sm text-stone-400">Deskripsi kegiatan operasional dan produk yang ditransaksikan</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="p-5 rounded-2xl bg-white/[0.02] border border-white/5">
                        <h3 class="text-base font-bold text-orange-400 mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                            Tentang IdenTix
                        </h3>
                        <p class="text-stone-300 text-sm leading-relaxed">
                            <strong>IdenTix</strong> (beroperasi di bawah manajemen DnD Tech Solutions / Virtus Unity) adalah platform digital penyelenggaraan dan penjualan tiket online resmi (<em>e-ticketing</em>) serta penyedia sistem manajemen akses gerbang (<em>event gate management</em>) untuk berbagai acara seperti konser musik, turnamen sepak bola (termasuk Bhayangkara FC), seminar, festival budaya, dan pameran.
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white/[0.02] border border-white/5">
                        <h3 class="text-base font-bold text-orange-400 mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                            Bentuk Produk yang Dijual
                        </h3>
                        <p class="text-stone-300 text-sm leading-relaxed">
                            Produk yang diperjualbelikan adalah <strong>100% Produk Digital</strong> berupa <strong>E-Voucher Tiket Elektronik</strong> yang dilengkapi barcode/QR Code berenkripsi unik. <strong>Tidak ada barang fisik</strong> yang dikirimkan melalui jasa kurir ekspedisi logistik. E-Voucher langsung diterbitkan oleh sistem secara otomatis segera setelah transaksi pembayaran sukses diverifikasi.
                        </p>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-orange-500/10 border border-orange-500/20 text-stone-300 text-sm leading-relaxed flex items-start gap-3">
                    <svg class="w-5 h-5 text-orange-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <strong class="text-white">Transparansi Penjualan:</strong> Seluruh harga tiket yang tertera di website sudah mencakup biaya layanan resmi, pajak berlaku, dan biaya pemrosesan gateway. Pembeli mendapatkan rincian tagihan secara rinci sebelum diarahkan ke portal pembayaran WAGO.
                    </div>
                </div>
            </div>

            <!-- Section: HIGHLIGHT PENJELASAN WAGO (KHUSUS VERIFIKASI) -->
            <div class="glass bg-gradient-to-br from-orange-950/40 via-[#1a1824] to-[#16151e] border-2 border-orange-500/40 p-6 sm:p-10 rounded-[2.5rem] shadow-2xl shadow-orange-950/30 relative overflow-hidden">
                <div class="absolute -top-24 -right-24 w-60 h-60 bg-orange-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-orange-500 text-black text-xs font-black uppercase tracking-wider mb-4 shadow-md shadow-orange-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Bagian Krusial Verifikasi WAGO Payment ID
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-black font-outfit text-white mb-3 tracking-tight">
                        Di Bagian Flow Bisnis Mana <span class="text-orange-400 underline decoration-orange-400/50">WAGO Digunakan?</span>
                    </h2>
                    <p class="text-stone-300 text-sm sm:text-base leading-relaxed mb-6">
                        IdenTix menggunakan <strong>WAGO Payment ID (Pay-Engine)</strong> sebagai <strong>gerbang pemrosesan pembayaran online resmi</strong> untuk memfasilitasi transaksi aman antara Pembeli dan Penyelenggara Event. WAGO aktif bekerja pada <strong>Tahap 3, 4, dan 5</strong> dalam siklus transaksi:
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <!-- Box 1 -->
                        <div class="p-4 rounded-2xl bg-black/40 border border-white/10 hover:border-orange-500/40 transition">
                            <div class="text-xs font-bold text-orange-400 uppercase tracking-wider mb-1 flex items-center justify-between">
                                <span>Tahap 3 &ndash; Checkout</span>
                                <span class="px-2 py-0.5 rounded bg-orange-500/20 text-orange-300 text-[10px]">API Inisiasi</span>
                            </div>
                            <h4 class="text-sm font-bold text-white mb-2">Pembuatan Sesi Bayar</h4>
                            <p class="text-xs text-stone-300 leading-relaxed">
                                Saat pembeli klik <em>"Bayar Sekarang"</em>, backend IdenTix memanggil API WAGO (<code class="text-orange-300">/api/order</code>) menggunakan APP ID & API Key resmi untuk menghasilkan Order Token dan link Checkout aman.
                            </p>
                        </div>

                        <!-- Box 2 -->
                        <div class="p-4 rounded-2xl bg-black/40 border border-white/10 hover:border-orange-500/40 transition">
                            <div class="text-xs font-bold text-orange-400 uppercase tracking-wider mb-1 flex items-center justify-between">
                                <span>Tahap 4 &ndash; Pembayaran</span>
                                <span class="px-2 py-0.5 rounded bg-orange-500/20 text-orange-300 text-[10px]">Multi-Kanal</span>
                            </div>
                            <h4 class="text-sm font-bold text-white mb-2">Layanan Bayar Pelanggan</h4>
                            <p class="text-xs text-stone-300 leading-relaxed">
                                Pembeli membayar melalui portal pembayaran WAGO dengan opsi: <strong>QRIS Real-Time (Scan via semua Mobile Banking & E-Wallet)</strong>, <strong>Virtual Account</strong>, serta <strong>E-Wallet Direct</strong>.
                            </p>
                        </div>

                        <!-- Box 3 -->
                        <div class="p-4 rounded-2xl bg-black/40 border border-white/10 hover:border-orange-500/40 transition">
                            <div class="text-xs font-bold text-orange-400 uppercase tracking-wider mb-1 flex items-center justify-between">
                                <span>Tahap 5 &ndash; Callback</span>
                                <span class="px-2 py-0.5 rounded bg-orange-500/20 text-orange-300 text-[10px]">Webhook Otomatis</span>
                            </div>
                            <h4 class="text-sm font-bold text-white mb-2">Verifikasi Instan 24/7</h4>
                            <p class="text-xs text-stone-300 leading-relaxed">
                                Sesaat setelah dana diterima, server WAGO mengirim HTTP POST Webhook ke <code class="text-orange-300">/wago/notification</code> dengan signature HMAC-SHA256. Sistem IdenTix otomatis mengubah status transaksi menjadi <strong>PAID</strong> tanpa konfirmasi manual.
                            </p>
                        </div>
                    </div>

                    <!-- Technical Security Callout -->
                    <div class="p-4 rounded-2xl bg-white/[0.04] border border-white/10 text-xs text-stone-300 leading-relaxed flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shrink-0"></span>
                            <span><strong>Kepatuhan Transaksi:</strong> IdenTix tidak menyimpan data kartu atau kredensial perbankan pelanggan. Semua pemrosesan data keuangan ditangani di lingkungan terenkripsi dan aman milik WAGO Payment ID.</span>
                        </div>
                        <span class="text-[11px] font-mono px-2.5 py-1 rounded-lg bg-orange-500/15 text-orange-400 border border-orange-500/30 whitespace-nowrap self-start sm:self-auto">
                            Endpoint: /wago/notification
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section: ALUR BISNIS END-TO-END STEP BY STEP -->
            <div class="glass bg-[#16151e]/90 backdrop-blur-xl border border-white/10 p-6 sm:p-10 rounded-[2.5rem] shadow-2xl shadow-black/40">
                <div class="flex items-center gap-3 border-b border-white/10 pb-4 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center font-black border border-orange-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold font-outfit text-white">2. Diagram Alur Transaksi Lengkap (End-to-End Flow)</h2>
                        <p class="text-xs sm:text-sm text-stone-400">Tahapan proses dari pemilihan tiket sampai kedatangan di lokasi event</p>
                    </div>
                </div>

                <!-- Step Timeline -->
                <div class="space-y-6">

                    <!-- Step 1 -->
                    <div class="glass-card p-5 sm:p-6 rounded-2xl flex flex-col sm:flex-row gap-4 sm:gap-6 items-start">
                        <div class="w-12 h-12 rounded-2xl bg-orange-500/20 text-orange-400 border border-orange-500/30 flex items-center justify-center font-outfit font-black text-lg shrink-0">
                            01
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <h3 class="text-base sm:text-lg font-bold text-white">Pemilihan Acara & Kategori Tiket</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-stone-300 text-xs">Di Sisi Pengguna</span>
                            </div>
                            <p class="text-stone-300 text-sm leading-relaxed">
                                Calon pembeli menjelajahi katalog acara pada website <strong>iden-tix.com</strong>. Pengguna memilih event yang diinginkan, melihat deskripsi, jadwal, syarat & ketentuan khusus event, serta memilih kategori tiket (misal: VIP, Regular, Festival, Presale) beserta kuantitas jumlah tiket yang akan dibeli.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="glass-card p-5 sm:p-6 rounded-2xl flex flex-col sm:flex-row gap-4 sm:gap-6 items-start">
                        <div class="w-12 h-12 rounded-2xl bg-orange-500/20 text-orange-400 border border-orange-500/30 flex items-center justify-center font-outfit font-black text-lg shrink-0">
                            02
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <h3 class="text-base sm:text-lg font-bold text-white">Pengisian Identitas & Validasi Pesanan</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-stone-300 text-xs">Di Sisi Pengguna & Platform</span>
                            </div>
                            <p class="text-stone-300 text-sm leading-relaxed">
                                Pembeli mengisi formulir pemesanan yang meliputi: <strong>Nama Lengkap (sesuai KTP/identitas)</strong>, <strong>Alamat Email Aktif</strong> (tujuan pengiriman E-Voucher), <strong>Nomor WhatsApp/Telepon</strong>, serta nomor identitas (NIK/KTP/Paspor). Jika memiliki voucher diskon/kode promo, pengguna dapat memasukkan kode untuk divalidasi oleh sistem.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 (WAGO API Step) -->
                    <div class="glass-card p-5 sm:p-6 rounded-2xl flex flex-col sm:flex-row gap-4 sm:gap-6 items-start border-l-4 border-l-orange-500 bg-orange-500/[0.03]">
                        <div class="w-12 h-12 rounded-2xl bg-orange-500 text-black font-outfit font-black text-lg shrink-0 flex items-center justify-center shadow-md shadow-orange-500/25">
                            03
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <h3 class="text-base sm:text-lg font-bold text-orange-400 flex items-center gap-2">
                                    Checkout & Inisiasi Pembayaran ke WAGO
                                    <span class="px-2 py-0.5 rounded bg-orange-500/20 text-orange-400 text-[11px] font-mono font-bold">WAGO API</span>
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-orange-500/20 border border-orange-500/40 text-orange-300 text-xs font-bold">Integrasi WAGO</span>
                            </div>
                            <p class="text-stone-300 text-sm leading-relaxed mb-3">
                                Saat tombol <strong>"Lanjutkan ke Pembayaran"</strong> diklik:
                            </p>
                            <ul class="space-y-1.5 text-xs text-stone-300 list-disc list-inside">
                                <li>Sistem IdenTix membuat invoice transaksi unik dengan format referensi <code class="text-orange-300">TX-XXXXXX</code> dengan status awal <span class="text-amber-400 font-bold">UNPAID / PENDING</span>.</li>
                                <li>Backend IdenTix mengirim payload API ke endpoint <code class="text-orange-300">https://api.wago-id.web.id/api/order</code> berisi order_id, nominal, data pembeli, serta parameter callback_url (<code class="text-orange-300">notifyUrl</code>).</li>
                                <li>WAGO merespons dengan Order Token resmi, dan browser pengguna langsung dialihkan ke portal pembayaran checkout WAGO (<code class="text-orange-300">https://pay.wago-id.web.id/checkout/...</code>).</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Step 4 (Payment Processing) -->
                    <div class="glass-card p-5 sm:p-6 rounded-2xl flex flex-col sm:flex-row gap-4 sm:gap-6 items-start border-l-4 border-l-orange-500 bg-orange-500/[0.03]">
                        <div class="w-12 h-12 rounded-2xl bg-orange-500 text-black font-outfit font-black text-lg shrink-0 flex items-center justify-center shadow-md shadow-orange-500/25">
                            04
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <h3 class="text-base sm:text-lg font-bold text-orange-400 flex items-center gap-2">
                                    Penyelesaian Pembayaran oleh Pembeli via WAGO
                                    <span class="px-2 py-0.5 rounded bg-orange-500/20 text-orange-400 text-[11px] font-mono font-bold">Pay-Engine</span>
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-orange-500/20 border border-orange-500/40 text-orange-300 text-xs font-bold">Integrasi WAGO</span>
                            </div>
                            <p class="text-stone-300 text-sm leading-relaxed mb-3">
                                Pembeli memilih metode pembayaran yang didukung penuh oleh jaringan perbankan & mitra WAGO Payment ID:
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs text-stone-200">
                                <div class="p-2.5 rounded-xl bg-white/[0.04] border border-white/5 text-center">
                                    <span class="block text-orange-400 font-bold mb-0.5">QRIS Real-Time</span>
                                    Scan via BCA, Mandiri, BRI, BNI, GoPay, OVO, Dana
                                </div>
                                <div class="p-2.5 rounded-xl bg-white/[0.04] border border-white/5 text-center">
                                    <span class="block text-orange-400 font-bold mb-0.5">Virtual Account</span>
                                    Transfer otomatis antar bank terverifikasi
                                </div>
                                <div class="p-2.5 rounded-xl bg-white/[0.04] border border-white/5 text-center">
                                    <span class="block text-orange-400 font-bold mb-0.5">E-Wallet Direct</span>
                                    Pembayaran instan tanpa verifikasi manual
                                </div>
                            </div>
                            <p class="text-[11px] text-stone-400 mt-2.5">
                                *Setiap transaksi memiliki batas waktu pembayaran (expired time) guna menjaga kuota ketersediaan tiket.
                            </p>
                        </div>
                    </div>

                    <!-- Step 5 (Callback & Webhook) -->
                    <div class="glass-card p-5 sm:p-6 rounded-2xl flex flex-col sm:flex-row gap-4 sm:gap-6 items-start border-l-4 border-l-orange-500 bg-orange-500/[0.03]">
                        <div class="w-12 h-12 rounded-2xl bg-orange-500 text-black font-outfit font-black text-lg shrink-0 flex items-center justify-center shadow-md shadow-orange-500/25">
                            05
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <h3 class="text-base sm:text-lg font-bold text-orange-400 flex items-center gap-2">
                                    Verifikasi Pembayaran Otomatis (Webhook Callback)
                                    <span class="px-2 py-0.5 rounded bg-orange-500/20 text-orange-400 text-[11px] font-mono font-bold">Instant IPN</span>
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-orange-500/20 border border-orange-500/40 text-orange-300 text-xs font-bold">Integrasi WAGO</span>
                            </div>
                            <p class="text-stone-300 text-sm leading-relaxed mb-2">
                                Begitu pembeli berhasil menyelesaikan transfer/pembayaran:
                            </p>
                            <ul class="space-y-1.5 text-xs text-stone-300 list-disc list-inside">
                                <li>Server WAGO mengirimkan HTTP POST Callback ke webhook endpoint IdenTix: <code class="text-orange-300">https://iden-tix.com/wago/notification</code>.</li>
                                <li>Sistem IdenTix memvalidasi signature HMAC-SHA256 data transaksi dan status pembayaran (<code class="text-orange-300">status: SUCCESS / PAID</code>).</li>
                                <li>Status transaksi dalam database IdenTix seketika diperbarui menjadi <span class="text-emerald-400 font-bold">PAID</span> secara otomatis tanpa perlu intervensi atau upload bukti transfer manual.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Step 6 (Product Delivery) -->
                    <div class="glass-card p-5 sm:p-6 rounded-2xl flex flex-col sm:flex-row gap-4 sm:gap-6 items-start">
                        <div class="w-12 h-12 rounded-2xl bg-orange-500/20 text-orange-400 border border-orange-500/30 flex items-center justify-center font-outfit font-black text-lg shrink-0">
                            06
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <h3 class="text-base sm:text-lg font-bold text-white">Penerbitan & Pengiriman E-Voucher Tiket (Delivery)</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold">Instant Fulfillment</span>
                            </div>
                            <p class="text-stone-300 text-sm leading-relaxed mb-3">
                                Detik itu juga setelah status diverifikasi PAID:
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-stone-300">
                                <div class="p-3 rounded-xl bg-white/[0.03] border border-white/5 flex items-start gap-2.5">
                                    <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <div>
                                        <strong class="text-white block mb-0.5">Email Pengiriman Otomatis:</strong>
                                        Sistem mengirimkan file E-Voucher resmi berformat digital (beserta Barcode & QR Code resolusi tinggi) langsung ke email terdaftar pembeli.
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-white/[0.03] border border-white/5 flex items-start gap-2.5">
                                    <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <div>
                                        <strong class="text-white block mb-0.5">Akses Web Langsung:</strong>
                                        Pembeli juga langsung diarahkan ke halaman sukses (<code class="text-orange-300">/checkout/success/{reference}</code>) dan tautan e-voucher publik (<code class="text-orange-300">/evoucher/{reference}</code>) untuk disimpan di smartphone.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 7 (Event Gate Check-in) -->
                    <div class="glass-card p-5 sm:p-6 rounded-2xl flex flex-col sm:flex-row gap-4 sm:gap-6 items-start">
                        <div class="w-12 h-12 rounded-2xl bg-orange-500/20 text-orange-400 border border-orange-500/30 flex items-center justify-center font-outfit font-black text-lg shrink-0">
                            07
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <h3 class="text-base sm:text-lg font-bold text-white">Validasi Tiket di Lokasi Event (Gate Check-In)</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-stone-300 text-xs">Di Lokasi Acara</span>
                            </div>
                            <p class="text-stone-300 text-sm leading-relaxed">
                                Pada hari H pelaksanaan acara, pengunjung cukup menunjukkan QR Code tiket pada smartphone mereka kepada petugas tiket (<em>Ranger IdenTix</em>) di pintu masuk gate. Petugas memindai QR Code menggunakan sistem scanner IdenTix. Tiket langsung tervalidasi secara real-time untuk mencegah duplikasi (1 tiket hanya dapat dipindai 1 kali).
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Section: Kebijakan Pengiriman Produk Digital, Pembatalan & Refund -->
            <div class="glass bg-[#16151e]/90 backdrop-blur-xl border border-white/10 p-6 sm:p-10 rounded-[2.5rem] shadow-2xl shadow-black/40">
                <div class="flex items-center gap-3 border-b border-white/10 pb-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center font-black border border-orange-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold font-outfit text-white">3. Kebijakan Pengiriman & Komitmen Layanan Konsumen</h2>
                        <p class="text-xs sm:text-sm text-stone-400">Standar operasional perlindungan konsumen dan penanganan kendala transaksi</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="p-5 rounded-2xl bg-white/[0.02] border border-white/5">
                        <h4 class="text-base font-bold text-white mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Waktu Pengiriman
                        </h4>
                        <p class="text-stone-300 text-xs leading-relaxed">
                            Pengiriman produk digital (E-Voucher) diproses secara instan seketika dalam kurun waktu <strong>kurang dari 1 sampai 3 menit</strong> setelah notifikasi pembayaran diterima dari sistem WAGO Payment ID.
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white/[0.02] border border-white/5">
                        <h4 class="text-base font-bold text-white mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                            Kebijakan Refund
                        </h4>
                        <p class="text-stone-300 text-xs leading-relaxed">
                            Pengembalian dana (refund) difasilitasi jika acara dibatalkan secara resmi oleh Penyelenggara/Promotor atau terjadi kelebihan bayar. Prosedur lengkap dapat dibaca pada halaman <a href="{{ route('refund') }}" class="text-orange-400 font-bold hover:underline">Refund Policy</a>.
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white/[0.02] border border-white/5">
                        <h4 class="text-base font-bold text-white mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                            Bantuan Transaksi
                        </h4>
                        <p class="text-stone-300 text-xs leading-relaxed">
                            Jika pembeli mengalami kendala seperti email tidak masuk atau salah ketik nomor WhatsApp, tim helpdesk IdenTix siap melakukan verifikasi data dan pengiriman ulang E-Voucher dalam 1x24 jam.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section: Informasi Legalitas & Kontak Resmi -->
            <div class="glass bg-[#16151e]/90 backdrop-blur-xl border border-white/10 p-6 sm:p-10 rounded-[2.5rem] shadow-2xl shadow-black/40">
                <div class="flex items-center gap-3 border-b border-white/10 pb-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center font-black border border-orange-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold font-outfit text-white">4. Informasi Legalitas Merchant & Layanan Kontak Resmi</h2>
                        <p class="text-xs sm:text-sm text-stone-400">Data entitas pemilik platform IdenTix untuk verifikasi merchant WAGO Payment ID</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3.5 text-sm">
                        <div class="flex items-start gap-3">
                            <span class="text-stone-400 font-medium w-36 shrink-0">Nama Platform:</span>
                            <span class="text-white font-bold">IdenTix</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-stone-400 font-medium w-36 shrink-0">Pengelola / Developer:</span>
                            <span class="text-stone-200">DnD Tech Solutions / Virtus Unity</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-stone-400 font-medium w-36 shrink-0">Domain Resmi:</span>
                            <span class="text-orange-400 font-mono font-bold">iden-tix.com</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-stone-400 font-medium w-36 shrink-0">Mitra Gateway:</span>
                            <span class="text-stone-200">WAGO Payment ID (PT WAGO Digital Multi)</span>
                        </div>
                    </div>

                    <div class="space-y-3.5 text-sm">
                        <div class="flex items-start gap-3">
                            <span class="text-stone-400 font-medium w-36 shrink-0">Email Resmi:</span>
                            <a href="mailto:virtusunity@gmail.com" class="text-orange-400 font-bold hover:underline">virtusunity@gmail.com</a>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-stone-400 font-medium w-36 shrink-0">WhatsApp / Telepon:</span>
                            <a href="https://wa.me/6283878537818" target="_blank" class="text-orange-400 font-bold hover:underline">083878537818</a>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-stone-400 font-medium w-36 shrink-0">Alamat Usaha:</span>
                            <span class="text-stone-300 leading-snug">DUSUN MANDAH INDUK 00/001 MANDAH, NATAR, LAMPUNG SELATAN, LAMPUNG 35362</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-stone-400 font-medium w-36 shrink-0">Jam Operasional:</span>
                            <span class="text-stone-300">Senin &ndash; Minggu (08.00 &ndash; 22.00 WIB)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-white/10 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('terms') }}" class="text-xs text-stone-400 hover:text-white transition underline">Syarat & Ketentuan Layanan</a>
                        <span class="text-stone-600">&bull;</span>
                        <a href="{{ route('refund') }}" class="text-xs text-stone-400 hover:text-white transition underline">Kebijakan Pengembalian Dana</a>
                        <span class="text-stone-600">&bull;</span>
                        <a href="{{ route('contact') }}" class="text-xs text-stone-400 hover:text-white transition underline">Kontak Bantuan Pengguna</a>
                    </div>
                    <a href="/#events" class="px-5 py-2.5 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold text-xs transition shadow-lg shadow-orange-500/20 flex items-center gap-1.5">
                        Mulai Pesan Tiket
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer Component -->
    <x-public-footer />

</body>
</html>
