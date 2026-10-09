<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan - IdenTix</title>
    
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
                Ketentuan Layanan Resmi
            </span>
            <h1 class="text-4xl md:text-5xl font-black font-outfit mb-4 text-white tracking-tight">Syarat & Ketentuan</h1>
            <p class="text-stone-300 max-w-2xl mx-auto text-base leading-relaxed font-normal">Harap membaca syarat dan ketentuan penggunaan platform IdenTix secara saksama sebelum melakukan transaksi pembelian tiket event.</p>
        </div>
    </section>

    <!-- Document Content -->
    <main class="py-6 pb-20 flex-1">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass bg-[#16151e]/85 backdrop-blur-xl border border-white/10 p-8 md:p-12 rounded-[2.5rem] shadow-2xl shadow-black/50 space-y-8">

                <!-- Metadata notice -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-white/[0.03] border border-white/10">
                    <p class="text-stone-300 text-sm">Selamat datang di platform <strong class="text-white">IdenTix</strong> (&ldquo;Platform&rdquo;).</p>
                    <span class="px-3.5 py-1 rounded-xl bg-orange-500/15 text-orange-400 border border-orange-500/25 text-xs font-bold whitespace-nowrap self-start sm:self-auto">
                        Terakhir Diperbarui: 13 Agustus 2026
                    </span>
                </div>

                <!-- Section 1 -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold font-outfit text-white flex items-center gap-3 border-b border-white/10 pb-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-orange-500/20 text-orange-400 font-black text-sm flex items-center justify-center shrink-0 border border-orange-500/30">1</span>
                        Definisi & Ketentuan Umum
                    </h2>
                    <p class="text-stone-200 text-base leading-relaxed mb-4">Dalam dokumen Syarat & Ketentuan ini, istilah-istilah berikut didefinisikan sebagai berikut:</p>
                    <div class="space-y-3">
                        <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                            <h3 class="text-sm font-bold text-orange-400 mb-1">IdenTix</h3>
                            <p class="text-stone-200 text-sm leading-relaxed">Penyedia infrastruktur teknologi e-ticketing, sistem manajemen gate event, dan platform transaksi tiket online.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                            <h3 class="text-sm font-bold text-orange-400 mb-1">Penyelenggara (Organizer)</h3>
                            <p class="text-stone-200 text-sm leading-relaxed">Pihak ketiga/promotor resmi yang memiliki hak cipta, kepemilikan, dan tanggung jawab penuh atas penyelenggaraan acara/event.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                            <h3 class="text-sm font-bold text-orange-400 mb-1">Pembeli / Pengguna</h3>
                            <p class="text-stone-200 text-sm leading-relaxed">Setiap individu atau entitas yang mengakses, mendaftar, atau melakukan pembelian tiket melalui aplikasi/situs IdenTix.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2 -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold font-outfit text-white flex items-center gap-3 border-b border-white/10 pb-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-orange-500/20 text-orange-400 font-black text-sm flex items-center justify-center shrink-0 border border-orange-500/30">2</span>
                        Pembelian Tiket & Sistem Pembayaran
                    </h2>
                    <ul class="space-y-3 text-stone-200 text-sm leading-relaxed">
                        <li class="flex items-start gap-3 p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                            <span class="text-orange-400 font-black shrink-0">&bull;</span>
                            <span>Pembeli wajib mengisi data identitas yang valid (<strong class="text-white">Nama Lengkap, Email aktif, dan Nomor WhatsApp</strong>) untuk kepentingan verifikasi dan penerbitan tiket elektronik.</span>
                        </li>
                        <li class="flex items-start gap-3 p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                            <span class="text-orange-400 font-black shrink-0">&bull;</span>
                            <span>Transaksi dianggap sah dan sukses setelah gateway pembayaran resmi kami memvalidasi penerimaan dana sesuai total nominal tagihan.</span>
                        </li>
                        <li class="flex items-start gap-3 p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                            <span class="text-orange-400 font-black shrink-0">&bull;</span>
                            <span>IdenTix tidak bertanggung jawab atas kerugian yang ditimbulkan akibat kesalahan input data oleh pembeli atau gangguan jaringan dari penyedia rekening bank pembeli.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 3 -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold font-outfit text-white flex items-center gap-3 border-b border-white/10 pb-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-orange-500/20 text-orange-400 font-black text-sm flex items-center justify-center shrink-0 border border-orange-500/30">3</span>
                        E-Voucher & Keamanan Kode QR
                    </h2>
                    <ul class="space-y-3 text-stone-200 text-sm leading-relaxed">
                        <li class="flex items-start gap-3 p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                            <span class="text-orange-400 font-black shrink-0">&bull;</span>
                            <span>E-Voucher berformat digital dan memiliki kode QR terenkripsi unik per masing-masing tiket.</span>
                        </li>
                        <li class="flex items-start gap-3 p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                            <span class="text-orange-400 font-black shrink-0">&bull;</span>
                            <span>Satu kode QR hanya berlaku untuk 1 (satu) kali scan masuk di pintu/gate event sesuai kategori tiket yang dipesan.</span>
                        </li>
                        <li class="flex items-start gap-3 p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                            <span class="text-orange-400 font-black shrink-0">&bull;</span>
                            <span>Kerahasiaan kode QR E-Voucher merupakan tanggung jawab penuh Pembeli. Dilarang menyebarkan atau mengunggah kode QR ke media sosial guna mencegah duplikasi ilegal.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 4 -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold font-outfit text-white flex items-center gap-3 border-b border-white/10 pb-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-orange-500/20 text-orange-400 font-black text-sm flex items-center justify-center shrink-0 border border-orange-500/30">4</span>
                        Peraturan di Lokasi Event & Gate
                    </h2>
                    <p class="text-stone-200 text-sm leading-relaxed pl-1">
                        Pengunjung wajib mematuhi seluruh tata tertib, standar keamanan, dan protokol yang ditetapkan oleh Penyelenggara Event dan pengelola venue acara. Penyelenggara berhak menolak akses masuk atau mengeluarkan pengunjung dari area event apabila terjadi pelanggaran hukum atau tindakan yang membahayakan kenyamanan umum.
                    </p>
                </div>

                <!-- Section 5 -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold font-outfit text-white flex items-center gap-3 border-b border-white/10 pb-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-orange-500/20 text-orange-400 font-black text-sm flex items-center justify-center shrink-0 border border-orange-500/30">5</span>
                        Perubahan Jadwal & Kebijakan Pembatalan
                    </h2>
                    <p class="text-stone-200 text-sm leading-relaxed pl-1">
                        Segala keputusan mengenai perubahan tanggal acara, penyesuaian susunan line-up pengisi acara, perpindahan venue, atau pembatalan event merupakan wewenang mutlak dari pihak Penyelenggara Event (Organizer). IdenTix akan memfasilitasi pengumuman resmi serta penyaluran pengembalian dana sesuai alur di halaman <a href="{{ route('refund') }}" class="text-orange-400 font-bold hover:underline">Refund Policy</a>.
                    </p>
                </div>

                <!-- Section 6: Official Legal Contact -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold font-outfit text-white flex items-center gap-3 border-b border-white/10 pb-3 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-orange-500/20 text-orange-400 font-black text-sm flex items-center justify-center shrink-0 border border-orange-500/30">6</span>
                        Layanan Legal & Kontak Resmi
                    </h2>
                    <div class="p-6 md:p-8 rounded-3xl bg-gradient-to-br from-white/5 to-transparent border border-orange-500/30 shadow-lg">
                        <h4 class="text-lg font-black text-white font-outfit mb-3">IdenTix &ndash; Legal Department</h4>
                        <div class="space-y-2.5 text-sm">
                            <p class="text-stone-300"><span class="text-stone-400 font-medium">Email Official:</span> <a href="mailto:virtusunity@gmail.com" class="text-orange-400 font-bold hover:underline ml-1">virtusunity@gmail.com</a></p>
                            <p class="text-stone-300"><span class="text-stone-400 font-medium">Telepon / WhatsApp:</span> <a href="https://wa.me/6283878537818" target="_blank" class="text-orange-400 font-bold hover:underline ml-1">083878537818</a></p>
                            <p class="text-stone-300"><span class="text-stone-400 font-medium">Alamat Kantor:</span> <span class="text-stone-200 ml-1">DUSUN MANDAH INDUK 00/001 MANDAH, NATAR, LAMPUNG SELATAN, LAMPUNG 35362</span></p>
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
