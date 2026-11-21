<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matchday Collectible - {{ $photo->customer_name }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-orange: #f95700;
            --primary-orange-hover: #e04e00;
            --bg-dark: #07090e;
            --card-dark: #111827;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-dark);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .container {
            width: 100%;
            max-width: 440px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .badge-header {
            background: rgba(249, 87, 0, 0.15);
            color: var(--primary-orange);
            font-size: 12px;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid rgba(249, 87, 0, 0.3);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .page-subtitle {
            font-size: 14px;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 24px;
        }

        .card-preview-box {
            width: 100%;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7);
            border: 2px solid rgba(255, 255, 255, 0.15);
            background: var(--card-dark);
            margin-bottom: 24px;
            position: relative;
        }

        .card-preview-box img {
            width: 100%;
            height: auto;
            display: block;
        }

        .btn-group {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-action-primary {
            width: 100%;
            min-height: 52px;
            background: var(--primary-orange);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(249, 87, 0, 0.35);
        }

        .btn-action-primary:hover {
            background: var(--primary-orange-hover);
        }

        .btn-action-secondary {
            width: 100%;
            min-height: 48px;
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-action-secondary:hover {
            background: rgba(255, 255, 255, 0.14);
        }

        .meta-footer {
            margin-top: 24px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="badge-header">OFFICIAL MATCHDAY COLLECTIBLE</div>
    <h1 class="page-title">{{ $photo->customer_name }}</h1>
    <p class="page-subtitle">{{ $photo->event->name ?? 'Matchday Event' }}</p>

    <div class="card-preview-box">
        <img src="{{ asset('storage/' . $photo->card_photo) }}" alt="Matchday Collectible Card">
    </div>

    <div class="btn-group">
        <a href="{{ route('kiosk.photo.download', $photo->token) }}" class="btn-action-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
            Download Foto Resolusi Penuh
        </a>

        @php
            $waShareUrl = "https://api.whatsapp.com/send?text=" . urlencode("Lihat foto Matchday Collectible resmi saya di " . ($photo->event->name ?? 'pertandingan') . "! " . url()->current());
        @endphp
        <a href="{{ $waShareUrl }}" target="_blank" class="btn-action-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#22c55e"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>
            Bagikan ke WhatsApp
        </a>
    </div>

    <div class="meta-footer">
        Dibuat melalui KIOSK Resmi IdenTix &bull; {{ $photo->created_at->translatedFormat('d M Y H:i') }}
    </div>
</div>

</body>
</html>
