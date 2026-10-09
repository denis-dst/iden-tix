<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Policy (Kebijakan Pengembalian Dana) - IdenTix</title>
    
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

    <!-- Hero Banner (Properly spaced and styled) -->
    <section class="pt-12 pb-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-orange-500/10 blur-[130px] pointer-events-none"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-500/15 text-orange-400 border border-orange-500/30 inline-flex items-center gap-2 mb-4 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                Jaminan Transaksi Aman
            </span>
            <h1 class="text-4xl md:text-5xl font-black font-outfit mb-4 text-white tracking-tight">Refund Policy</h1>
            <p class="text-stone-300 max-w-2xl mx-auto text-base leading-relaxed font-normal">Panduan dan kebijakan resmi mengenai pengembalian dana (refund) untuk tiket event di IdenTix.</p>
        </div>
    </section>

    <!-- Policy Content -->
    <main class="py-6 pb-20 flex-1">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass bg-[#16151e]/85 backdrop-blur-xl border border-white/10 p-8 md:p-12 rounded-[2.5rem] shadow-2xl shadow-black/50 space-y-8">

                <!-- Introduction -->
                <div class="p-6 rounded-2xl bg-orange-500/10 border border-orange-500/25">
                    <p class="text-stone-200 leading-relaxed text-base">IdenTix selalu berupaya menjamin kepuasan, transparansi, dan keamanan bagi seluruh pengguna platform kami. Kebijakan Pengembalian Dana (<strong class="text-white">Refund Policy</strong>) ini mengatur syarat dan tata cara resmi pengajuan pengembalian dana transaksi tiket.</p>
                </div>

                <!-- Section 1 -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold font-outfit text-white flex items-center gap-3 border-b border-white/10 pb-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-orange-500/20 text-orange-400 font-black text-sm flex items-center justify-center shrink-0 border border-orange-500/30">1</span>
                        Prinsip Utama Penjualan Tiket
                    </h2>
                    <p class="text-stone-200 text-base leading-relaxed pl-1">
                        Setiap transaksi pembelian tiket yang telah berhasil dikonfirmasi secara umum bersifat <strong class="text-white">Final, Terkunci, dan Non-Refundable</strong> (tidak dapat dikembalikan atau ditukar dengan uang tunai), kecuali apabila terjadi kondisi khusus yang secara resmi disetujui oleh Penyelenggara Event atau akibat gangguan teknis sistem pembayaran resmi kami.
                    </p>
                </div>

                <!-- Section 2 -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold font-outfit text-white flex items-center gap-3 border-b border-white/10 pb-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 font-black text-sm flex items-center justify-center shrink-0 border border-emerald-500/30">2</span>
                        Kondisi yang Memenuhi Syarat Refund
                    </h2>
                    <div class="space-y-3 mt-4">
                        <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/10 hover:border-emerald-500/30 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-white mb-1">Pembatalan Resmi Event oleh Penyelenggara (Organizer)</h3>
                                    <p class="text-stone-300 text-sm leading-relaxed">Apabila event dibatalkan sepenuhnya oleh pihak Penyelenggara tanpa ada penetapan tanggal pengganti, seluruh pemegang tiket yang sah berhak mendapatkan pengembalian dana penuh sesuai ketentuan penyelenggara.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/10 hover:border-emerald-500/30 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-white mb-1">Pembayaran Ganda (Overpayment / Double Charge)</h3>
                                    <p class="text-stone-300 text-sm leading-relaxed">Apabila terjadi anomali sistem perbankan/gateway yang mengakibatkan rekening/kartu Anda terpotong lebih dari satu kali untuk satu kode referensi transaksi yang sama, kelebihan dana tersebut akan dikembalikan 100% setelah proses rekonsiliasi selesai.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3 -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold font-outfit text-white flex items-center gap-3 border-b border-white/10 pb-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 font-black text-sm flex items-center justify-center shrink-0 border border-rose-500/30">3</span>
                        Kondisi yang TIDAK Memenuhi Syarat Refund
                    </h2>
                    <ul class="space-y-2.5 text-stone-200 text-sm leading-relaxed pl-1 mt-3">
                        <li class="flex items-start gap-3 p-3.5 rounded-xl bg-white/[0.02] border border-white/5">
                            <span class="text-rose-400 font-bold shrink-0 mt-0.5">&#10005;</span>
                            <span>Pembeli berhalangan hadir, sakit, terjebak macet, atau berubah pikiran setelah transaksi tiket sukses terbit.</span>
                        </li>
                        <li class="flex items-start gap-3 p-3.5 rounded-xl bg-white/[0.02] border border-white/5">
                            <span class="text-rose-400 font-bold shrink-0 mt-0.5">&#10005;</span>
                            <span>Kesalahan pembeli saat memilih jadwal hari, jam sesi acara, ataupun jenis kategori tiket.</span>
                        </li>
                        <li class="flex items-start gap-3 p-3.5 rounded-xl bg-white/[0.02] border border-white/5">
                            <span class="text-rose-400 font-bold shrink-0 mt-0.5">&#10005;</span>
                            <span>Tiket/E-Voucher telah berstatus dipindai (<strong class="text-white">scanned/checked-in</strong>) di pintu masuk gate event.</span>
                        </li>
                        <li class="flex items-start gap-3 p-3.5 rounded-xl bg-white/[0.02] border border-white/5">
                            <span class="text-rose-400 font-bold shrink-0 mt-0.5">&#10005;</span>
                            <span>E-Voucher hilang atau disalahgunakan akibat kelalaian pembeli membagikan tangkapan layar/file QR Code kepada pihak lain.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 4 -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold font-outfit text-white flex items-center gap-3 border-b border-white/10 pb-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-orange-500/20 text-orange-400 font-black text-sm flex items-center justify-center shrink-0 border border-orange-500/30">4</span>
                        Prosedur dan Alur Pengajuan Refund
                    </h2>
                    <div class="space-y-3 mt-4">
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/[0.03] border border-white/10">
                            <div class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 font-bold text-xs flex items-center justify-center shrink-0">1</div>
                            <p class="text-stone-200 text-sm leading-relaxed">Kirimkan permohonan ke email resmi <a href="mailto:virtusunity@gmail.com" class="text-orange-400 font-bold hover:underline ml-1">virtusunity@gmail.com</a> atau melalui WhatsApp Helpdesk di <a href="https://wa.me/6283878537818" target="_blank" class="text-orange-400 font-bold hover:underline ml-1">083878537818</a>.</p>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/[0.03] border border-white/10">
                            <div class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 font-bold text-xs flex items-center justify-center shrink-0">2</div>
                            <p class="text-stone-200 text-sm leading-relaxed">Gunakan format subjek: <strong class="text-white">[Pengajuan Refund] - Kode Transaksi / Nama Pembeli</strong>.</p>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/[0.03] border border-white/10">
                            <div class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 font-bold text-xs flex items-center justify-center shrink-0">3</div>
                            <p class="text-stone-200 text-sm leading-relaxed">Lampirkan kelengkapan data: Bukti Bayar / Screenshot Mutasi, Foto Identitas (KTP/SIM) Pembeli, serta Nomor Rekening Bank Tujuan.</p>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/[0.03] border border-white/10">
                            <div class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 font-bold text-xs flex items-center justify-center shrink-0">4</div>
                            <p class="text-stone-200 text-sm leading-relaxed">Tim verifikasi IdenTix akan memeriksa keabsahan transaksi dalam kurun waktu <strong class="text-white">2 x 24 jam kerja</strong>.</p>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/[0.03] border border-white/10">
                            <div class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 font-bold text-xs flex items-center justify-center shrink-0">5</div>
                            <p class="text-stone-200 text-sm leading-relaxed">Penyaluran dana refund yang disetujui akan diproses dalam waktu <strong class="text-white">7–14 hari kerja</strong> tergantung mekanisme penyedia metode pembayaran asal.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Official Contact Box -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold font-outfit text-white flex items-center gap-3 border-b border-white/10 pb-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-orange-500/20 text-orange-400 font-black text-sm flex items-center justify-center shrink-0 border border-orange-500/30">5</span>
                        Layanan Bantuan & Customer Support
                    </h2>
                    <div class="p-6 md:p-8 rounded-3xl bg-gradient-to-br from-white/5 to-transparent border border-orange-500/30 shadow-lg">
                        <h4 class="text-lg font-black text-white font-outfit mb-3">Customer Support Refund &ndash; IdenTix</h4>
                        <div class="space-y-2.5 text-sm">
                            <p class="text-stone-300"><span class="text-stone-400 font-medium">Email Bantuan:</span> <a href="mailto:virtusunity@gmail.com" class="text-orange-400 font-bold hover:underline ml-1">virtusunity@gmail.com</a></p>
                            <p class="text-stone-300"><span class="text-stone-400 font-medium">WhatsApp Support:</span> <a href="https://wa.me/6283878537818" target="_blank" class="text-orange-400 font-bold hover:underline ml-1">083878537818</a></p>
                            <p class="text-stone-300"><span class="text-stone-400 font-medium">Alamat Usaha:</span> <span class="text-stone-200 ml-1">DUSUN MANDAH INDUK 00/001 MANDAH, NATAR, LAMPUNG SELATAN, LAMPUNG 35362</span></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer Component -->
    <x-public-footer />

</body>
</html>
