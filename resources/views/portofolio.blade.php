<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portofolio Project Website | DnD Tech Solutions</title>
    <meta name="description" content="Katalog Portofolio Project Website & Solusi Digital oleh DnD Tech Solutions - Website, Sistem Informasi, Dashboard, & Platform Digital." />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --green: #16a34a;
            --green-dark: #15803d;
            --green-light: #dcfce7;
            --black: #111827;
            --gray: #6b7280;
            --bg: #ffffff;
            --border: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Inter", sans-serif;
            background: #fff;
            color: var(--black);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .container {
            width: min(1200px, 92%);
            margin: auto;
        }

        /* HEADER */
        header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, .95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 22px;
            color: var(--black);
        }

        .logo-box {
            width: 42px;
            height: 42px;
            background: var(--green);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 22px;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
        }

        .nav-links {
            display: flex;
            gap: 26px;
            color: var(--gray);
            font-weight: 500;
        }

        .nav-links a {
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: var(--green);
        }

        /* HERO */
        .hero {
            padding: 80px 0 60px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 50px;
            align-items: center;
        }

        .badge {
            display: inline-block;
            background: var(--green-light);
            color: var(--green-dark);
            border: 1px solid #86efac;
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .hero h1 {
            font-size: 58px;
            line-height: 1.05;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: -0.02em;
        }

        .hero h1 span {
            color: var(--green);
        }

        .hero p {
            color: var(--gray);
            font-size: 18px;
            margin-bottom: 30px;
            max-width: 650px;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 16px 26px;
            border-radius: 14px;
            font-weight: 700;
            transition: all .25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--green);
            color: #fff;
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.3);
        }

        .btn-primary:hover {
            background: var(--green-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(22, 163, 74, 0.4);
        }

        .btn-outline {
            border: 2px solid var(--green);
            color: var(--green);
            background: transparent;
        }

        .btn-outline:hover {
            background: var(--green);
            color: #fff;
            transform: translateY(-2px);
        }

        .hero-card {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            transition: transform 0.3s ease;
        }

        .hero-card:hover {
            transform: translateY(-4px);
        }

        .hero-card img {
            width: 100%;
            border-radius: 18px;
            display: block;
            object-fit: cover;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 26px;
        }

        .stat {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 20px;
            transition: border-color 0.2s ease;
        }

        .stat:hover {
            border-color: var(--green);
        }

        .stat h3 {
            color: var(--green);
            font-size: 34px;
            margin-bottom: 6px;
            font-weight: 800;
        }

        .stat p {
            color: var(--gray);
            font-size: 14px;
            margin: 0;
            font-weight: 500;
        }

        /* SECTION */
        section {
            padding: 70px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 16px;
        }

        .section-title h2 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.01em;
        }

        .section-title p {
            color: var(--gray);
            max-width: 720px;
            margin: auto;
            font-size: 16px;
        }

        /* GRID */
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 26px;
            margin-top: 40px;
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 22px;
            overflow: hidden;
            transition: all .25s ease;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(0,0,0,.08);
            border-color: rgba(22, 163, 74, 0.3);
        }

        .thumb {
            aspect-ratio: 16/9;
            background: #f1f5f9;
            overflow: hidden;
        }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        .card:hover .thumb img {
            transform: scale(1.05);
        }

        .card-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card-body h3 {
            font-size: 22px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .card-body p {
            color: var(--gray);
            margin-bottom: 16px;
            font-size: 15px;
            flex-grow: 1;
        }

        .tag {
            display: inline-block;
            background: var(--green-light);
            color: var(--green-dark);
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 16px;
            align-self: flex-start;
        }

        .link {
            color: var(--green);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: gap 0.2s ease;
        }

        .link:hover {
            gap: 8px;
            color: var(--green-dark);
        }

        /* FEATURE */
        .feature {
            background: #f8fafc;
        }

        .feature-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .feature-box img {
            width: 100%;
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .feature-box h2 {
            font-size: 42px;
            margin-bottom: 18px;
            font-weight: 800;
        }

        .feature-box p {
            color: var(--gray);
            font-size: 16px;
        }

        .feature-box ul {
            margin-top: 20px;
            display: grid;
            gap: 12px;
        }

        .feature-box li {
            list-style: none;
            position: relative;
            padding-left: 28px;
            color: #374151;
            font-weight: 500;
        }

        .feature-box li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--green);
            font-weight: 800;
        }

        /* CONTACT */
        .contact {
            background: #111827;
            color: #fff;
            border-radius: 28px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(17, 24, 39, 0.15);
        }

        .contact h2 {
            font-size: 40px;
            margin-bottom: 14px;
            font-weight: 800;
        }

        .contact p {
            color: #d1d5db;
            margin-bottom: 28px;
            font-size: 16px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-top: 24px;
        }

        .contact-item {
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 18px;
            padding: 20px;
            transition: border-color 0.2s ease;
        }

        .contact-item:hover {
            border-color: #86efac;
        }

        .contact-item h4 {
            color: #86efac;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .contact-item p {
            color: #fff;
            margin: 0;
            font-size: 15px;
            font-weight: 500;
            word-break: break-all;
        }

        /* FOOTER */
        footer {
            padding: 40px 0;
            text-align: center;
            color: var(--gray);
            font-size: 14px;
            border-top: 1px solid var(--border);
        }

        /* RESPONSIVE */
        @media(max-width: 992px) {
            .hero-grid,
            .feature-box {
                grid-template-columns: 1fr;
            }

            .grid {
                grid-template-columns: 1fr 1fr;
            }

            .contact-grid {
                grid-template-columns: 1fr 1fr;
            }

            .hero h1 {
                font-size: 46px;
            }

            .nav-links {
                display: none;
            }
        }

        @media(max-width: 640px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 38px;
            }

            .section-title h2,
            .feature-box h2,
            .contact h2 {
                font-size: 32px;
            }

            .contact {
                padding: 32px 20px;
            }
        }
    </style>
    <meta name="wago-verification" content="WAGO-1BE5FB2B">
</head>
<body>

    <!-- HEADER -->
    <header>
        <div class="container nav">
            <a href="#" class="logo">
                <div class="logo-box">D</div>
                <div>DnD Tech Solutions</div>
            </a>

            <nav class="nav-links">
                <a href="#portfolio">Portofolio</a>
                <a href="#featured">Unggulan</a>
                <a href="#contact">Kontak</a>
            </nav>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <div class="badge">Katalog Portofolio Project Website</div>

                <h1>Membangun Solusi Digital <span>Profesional</span> untuk Instansi, UMKM, Pendidikan & Event</h1>

                <p>
                    Kami mengembangkan website, sistem informasi, dashboard, aplikasi SaaS, dan platform digital yang modern,
                    cepat, aman, dan siap digunakan untuk kebutuhan pemerintahan maupun bisnis.
                </p>

                <div class="hero-buttons">
                    <a href="#portfolio" class="btn btn-primary">Lihat Portofolio</a>
                    <a href="#contact" class="btn btn-outline">Hubungi Kami</a>
                </div>

                <div class="stats">
                    <div class="stat">
                        <h3>6+</h3>
                        <p>Project Unggulan</p>
                    </div>

                    <div class="stat">
                        <h3>100%</h3>
                        <p>Custom Development</p>
                    </div>

                    <div class="stat">
                        <h3>24/7</h3>
                        <p>Support & Maintenance</p>
                    </div>
                </div>
            </div>

            <div class="hero-card">
                <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=600&q=75" alt="Portofolio Website" width="600" height="375" loading="lazy" decoding="async">
            </div>
        </div>
    </section>

    <!-- PORTFOLIO SECTION -->
    <section id="portfolio">
        <div class="container">
            <div class="section-title">
                <h2>Portofolio Project Website</h2>
                <p>
                    Beberapa project digital yang telah kami rancang dan implementasikan untuk pemerintahan, pendidikan,
                    UMKM, media online, dan penyelenggara event.
                </p>
            </div>

            <div class="grid">
                <!-- Card 1 -->
                <div class="card">
                    <div class="thumb">
                        <img src="https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=600&q=75" alt="Portal Berita" width="600" height="375" loading="lazy" decoding="async">
                    </div>
                    <div class="card-body">
                        <div class="tag">Portal Berita</div>
                        <h3>Portal Berita Online Terintegrasi</h3>
                        <p>Website berita modern dan responsif dengan dashboard admin, kategori, manajemen konten, dan performa tinggi.</p>
                        <a class="link" href="https://nataragung.id" target="_blank" rel="noopener">nataragung.id &rarr;</a>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="card">
                    <div class="thumb">
                        <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=75" alt="Harga Pasar" width="600" height="375" loading="lazy" decoding="async">
                    </div>
                    <div class="card-body">
                        <div class="tag">Sistem Informasi</div>
                        <h3>Sistem Informasi Harga Pasar</h3>
                        <p>Monitoring harga kebutuhan pokok secara real-time untuk pemerintah daerah dan masyarakat.</p>
                        <a class="link" href="https://disperindag.lampungprov.go.id/pasar/semua" target="_blank" rel="noopener">Lihat Website &rarr;</a>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="card">
                    <div class="thumb">
                        <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=600&q=75" alt="Galeri IKM" width="600" height="375" loading="lazy" decoding="async">
                    </div>
                    <div class="card-body">
                        <div class="tag">UMKM</div>
                        <h3>Sistem Informasi Galeri IKM</h3>
                        <p>Platform katalog digital produk IKM unggulan Lampung untuk promosi dan perluasan pemasaran.</p>
                        <a class="link" href="https://gallery-ikm.disperindag.lampungprov.go.id/" target="_blank" rel="noopener">Lihat Website &rarr;</a>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="card">
                    <div class="thumb">
                        <img src="https://images.unsplash.com/photo-1551650975-87deedd944c3?auto=format&fit=crop&w=600&q=75" alt="LampungIn" width="600" height="375" loading="lazy" decoding="async">
                    </div>
                    <div class="card-body">
                        <div class="tag">Super Apps</div>
                        <h3>SuperApps LampungIn</h3>
                        <p>Aplikasi layanan publik terpadu Pemerintah Provinsi Lampung dalam satu genggaman.</p>
                        <a class="link" href="https://play.google.com/store/search?q=lampung+in&c=apps&hl=id" target="_blank" rel="noopener">Google Play &rarr;</a>
                    </div>
                </div>

                <!-- Card 5 -->
                <div class="card">
                    <div class="thumb">
                        <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=600&q=75" alt="SekolahKu Apps" width="600" height="375" loading="lazy" decoding="async">
                    </div>
                    <div class="card-body">
                        <div class="tag">SaaS Pendidikan</div>
                        <h3>SekolahKu Apps</h3>
                        <p>Platform manajemen sekolah terpadu: E-Rapor, Keuangan BOSP, Presensi, SPP, dan pelaporan.</p>
                        <a class="link" href="https://iden-tix.com/sekolahku" target="_blank" rel="noopener">Lihat Demo &rarr;</a>
                    </div>
                </div>

                <!-- Card 6 -->
                <div class="card">
                    <div class="thumb">
                        <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=600&q=75" alt="IdenTix" width="600" height="375" loading="lazy" decoding="async">
                    </div>
                    <div class="card-body">
                        <div class="tag">Event Ticketing</div>
                        <h3>Portal Ticketing Event</h3>
                        <p>Sistem ticketing event online dengan QR Code, manajemen event, pembayaran, dan laporan penjualan.</p>
                        <a class="link" href="https://iden-tix.com/" target="_blank" rel="noopener">iden-tix.com &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURED SECTION -->
    <section class="feature" id="featured">
        <div class="container feature-box">
            <div>
                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=600&q=75" alt="SekolahKu Apps" width="600" height="375" loading="lazy" decoding="async">
            </div>

            <div>
                <h2>Project Unggulan: <span style="color:#16a34a">SekolahKu Apps</span></h2>
                <p>
                    Platform SaaS manajemen sekolah yang menyatukan berbagai kebutuhan operasional sekolah
                    ke dalam satu sistem yang terintegrasi, cepat, dan mudah digunakan.
                </p>

                <ul>
                    <li>E-Rapor Terintegrasi</li>
                    <li>Keuangan BOSP</li>
                    <li>Pembayaran SPP</li>
                    <li>Presensi Digital</li>
                    <li>Notifikasi WhatsApp</li>
                    <li>Dashboard Kepala Sekolah & Bendahara</li>
                </ul>

                <div style="margin-top:28px">
                    <a href="https://iden-tix.com/sekolahku" target="_blank" rel="noopener" class="btn btn-primary">Lihat Project</a>
                </div>
            </div>
        </div>
    </section>

    <!-- MORE SERVICES SECTION -->
    <section>
        <div class="container">
            <div class="section-title">
                <h2>Dan Masih Banyak Lagi</h2>
                <p>
                    Website Profil Perusahaan, Landing Page, Dashboard Analitik, Sistem Informasi Pemerintahan,
                    Aplikasi Internal, Integrasi API, dan Custom Web Development sesuai kebutuhan klien.
                </p>
            </div>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section id="contact">
        <div class="container">
            <div class="contact">
                <h2>Mari Bangun Project Berikutnya</h2>
                <p>
                    Siap membantu pengembangan website, aplikasi, sistem informasi, dashboard, maupun platform digital
                    untuk instansi pemerintah, perusahaan, sekolah, dan organisasi.
                </p>

                <div class="hero-buttons" style="justify-content:center;margin-bottom:20px">
                    <a href="https://wa.me/6289669651907" class="btn btn-primary" target="_blank" rel="noopener">Hubungi via WhatsApp</a>
                    <a href="mailto:denissetiaji.dst@gmail.com" class="btn btn-outline">Kirim Email</a>
                </div>

                <div class="contact-grid">
                    <div class="contact-item">
                        <h4>WhatsApp</h4>
                        <p>089669651907</p>
                    </div>

                    <div class="contact-item">
                        <h4>Instagram</h4>
                        <p>@denisdst.dnd</p>
                    </div>

                    <div class="contact-item">
                        <h4>Email</h4>
                        <p>denissetiaji.dst@gmail.com</p>
                    </div>

                    <div class="contact-item">
                        <h4>LinkedIn</h4>
                        <p>linkedin.com/in/denissetiaji-dst</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <x-public-footer />

</body>
</html>
