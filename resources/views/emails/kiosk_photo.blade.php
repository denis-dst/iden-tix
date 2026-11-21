<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matchday Collectible Photo</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #0b0f19;
            color: #f1f5f9;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background: #111827;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #f95700 0%, #d84300 100%);
            padding: 28px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header p {
            margin: 6px 0 0 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
        }
        .content {
            padding: 32px 24px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #f8fafc;
        }
        .match-badge {
            display: inline-block;
            background: rgba(249, 87, 0, 0.15);
            color: #f95700;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
            border: 1px solid rgba(249, 87, 0, 0.3);
        }
        .photo-card {
            margin: 20px 0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.15);
            display: inline-block;
            max-width: 100%;
        }
        .photo-card img {
            display: block;
            max-width: 100%;
            height: auto;
            border-radius: 12px;
        }
        .btn-download {
            display: inline-block;
            background: #f95700;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 10px;
            margin-top: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .footer {
            background: #090d16;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Matchday Collectible Photo</h1>
            <p>{{ $event->name ?? 'Pertandingan Resmi' }}</p>
        </div>

        <div class="content">
            <div class="greeting">Halo, {{ $photo->customer_name }}!</div>
            <div class="match-badge">
                {{ $event->home_team_name ?? 'HOME' }} VS {{ $event->away_team_name ?? 'AWAY' }}
            </div>
            <p style="color: #cbd5e1; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">
                Terima kasih telah hadir dan mendukung tim kesayangan di stadion. Berikut adalah kartu foto resmi koleksi matchday kamu yang diambil melalui Kiosk Identix.
            </p>

            <div class="photo-card">
                <img src="{{ asset('storage/' . $photo->card_photo) }}" alt="Matchday Collectible Card">
            </div>

            <div>
                <a href="{{ $downloadUrl }}" class="btn-download" target="_blank">
                    Download Foto Resolusi Penuh
                </a>
            </div>

            <p style="color: #94a3b8; font-size: 12px; margin-top: 18px;">
                Bagikan momen ini ke media sosial dan tag akun resmi klub kamu!
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 4px 0;">&copy; {{ date('Y') }} IdenTix Fan Experience. Hak cipta dilindungi undang-undang.</p>
            <p style="margin: 0;">Email otomatis dari sistem KIOSK Identix.</p>
        </div>
    </div>
</body>
</html>
