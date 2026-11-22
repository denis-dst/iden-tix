<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->name ?? 'Kiosk Penjualan' }} - IdenTix Club Kiosk</title>
    
    {{-- Google Font Outfit & Inter for high readability and sporty matchday vibe --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-orange: #f95700;
            --primary-orange-hover: #e04e00;
            --club-purple: {{ $photoboothTemplate->primary_color ?? '#5A2D82' }};
            --club-accent: {{ $photoboothTemplate->secondary_color ?? '#f95700' }};
            --bg-dark: #07090e;
            --card-dark: #000000;
            --surface-card: rgba(18, 20, 29, 0.95);
            --text-main: #ffffff;
            --text-muted: #94a3b8;
            --border-light: rgba(255, 255, 255, 0.12);
            --input-bg: #ffffff;
            --input-text: #0f172a;
            --input-placeholder: #64748b;
            --success-green: #22c55e;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }

        body {
            font-family: 'Outfit', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            width: 100vw;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Stadium Atmosphere Ambient Background */
        .kiosk-viewport {
            width: 100vw;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-image: linear-gradient(180deg, rgba(7, 9, 14, 0.72) 0%, rgba(7, 9, 14, 0.88) 100%), url('{{ asset('images/kiosk/stadium_bg.jpg') }}');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        /* Top Kiosk Hardware Header Bar */
        .kiosk-top-bar {
            position: absolute;
            top: 20px;
            left: 32px;
            right: 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 50;
        }

        .kiosk-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .kiosk-brand-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .kiosk-brand-badge {
            background: rgba(249, 87, 0, 0.2);
            color: var(--primary-orange);
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid rgba(249, 87, 0, 0.4);
            letter-spacing: 1px;
        }

        .kiosk-step-indicator {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 1px;
            background: rgba(0, 0, 0, 0.5);
            padding: 6px 16px;
            border-radius: 20px;
            border: 1px solid var(--border-light);
        }

        /* The Main Kiosk Central Terminal Card (Matching Figma Screen) */
        .kiosk-bezel {
            width: 100%;
            max-width: 920px;
            min-height: 600px;
            background: #000000;
            border: 14px solid #000000;
            border-radius: 46px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 32px;
            transition: all 0.3s ease;
        }

        /* Screen Wrapper & Fade Transitions */
        .kiosk-screen {
            display: none;
            width: 100%;
            max-width: 720px;
            flex-direction: column;
            align-items: center;
            text-align: center;
            animation: fadeIn 0.25s ease-out forwards;
        }

        .kiosk-screen.active {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Welcome Screen Elements */
        .welcome-title {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 1.5px;
            color: #ffffff;
            margin-bottom: 24px;
            text-transform: uppercase;
        }

        .welcome-logo-wrap {
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .welcome-logo-svg {
            width: 76px;
            height: 76px;
            fill: var(--primary-orange);
            filter: drop-shadow(0 4px 12px rgba(249, 87, 0, 0.4));
        }

        .welcome-subtitle {
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1.2px;
            color: #cbd5e1;
            text-transform: uppercase;
            margin-bottom: 32px;
        }

        .btn-kiosk-menu-group {
            width: 100%;
            max-width: 440px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .btn-kiosk-menu {
            width: 100%;
            min-height: 64px;
            background: var(--primary-orange);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            padding: 14px 24px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(249, 87, 0, 0.35);
            transition: transform 0.15s ease, background 0.15s ease;
        }

        .btn-kiosk-menu:hover, .btn-kiosk-menu:active {
            background: var(--primary-orange-hover);
            transform: scale(0.99);
        }

        .btn-kiosk-menu .btn-content {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-kiosk-menu svg {
            width: 26px;
            height: 26px;
            fill: currentColor;
        }

        .btn-kiosk-menu .chevron-icon {
            width: 20px;
            height: 20px;
            fill: currentColor;
            opacity: 0.9;
        }

        /* Match Details Banner (Screen 02) */
        .match-card-header {
            width: 100%;
            margin-bottom: 24px;
            text-align: center;
        }

        .match-title {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .match-info-meta {
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .section-label {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #ffffff;
            margin-bottom: 18px;
        }

        /* Ticket Category Cards */
        .category-list {
            width: 100%;
            max-width: 520px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }

        .category-item {
            width: 100%;
            min-height: 58px;
            background: #fed7aa;
            color: #0f172a;
            border-radius: 12px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.2s ease;
        }

        .category-item.selected {
            background: #ffedd5;
            border-color: var(--primary-orange);
            box-shadow: 0 4px 16px rgba(249, 87, 0, 0.3);
        }

        .category-item .radio-dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #cbd5e1;
            margin-right: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .category-item.selected .radio-dot {
            background: var(--primary-orange);
            border-color: var(--primary-orange);
        }

        .category-item.selected .radio-dot::after {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ffffff;
        }

        .category-details {
            flex-grow: 1;
            text-align: left;
        }

        .category-name {
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .category-price {
            font-size: 15px;
            font-weight: 700;
            color: #334155;
        }

        /* Ticket Quantity Stepper */
        .quantity-stepper-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        .quantity-stepper-label {
            font-size: 15px;
            font-weight: 700;
            color: #ffffff;
        }

        .quantity-stepper {
            display: flex;
            align-items: center;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .stepper-btn {
            width: 44px;
            height: 44px;
            background: transparent;
            border: none;
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stepper-btn:active {
            background: #e2e8f0;
        }

        .stepper-value {
            width: 40px;
            text-align: center;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        /* Action Buttons (Kembali & Lanjutkan) */
        .action-buttons-wrap {
            width: 100%;
            max-width: 520px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .btn-kiosk-secondary {
            flex: 1;
            min-height: 52px;
            background: #ffffff;
            color: #0f172a;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-kiosk-secondary:hover, .btn-kiosk-secondary:active {
            background: #f1f5f9;
        }

        .btn-kiosk-primary {
            flex: 1;
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
            box-shadow: 0 4px 12px rgba(249, 87, 0, 0.35);
            transition: all 0.15s ease;
        }

        .btn-kiosk-primary:hover, .btn-kiosk-primary:active {
            background: var(--primary-orange-hover);
            transform: scale(0.99);
        }

        .btn-kiosk-primary:disabled {
            background: #475569;
            color: #94a3b8;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* Form Inputs (Screen 03) */
        .form-grid {
            width: 100%;
            max-width: 540px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
        }

        .form-input {
            width: 100%;
            height: 48px;
            background: var(--input-bg);
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 600;
            color: var(--input-text);
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        .form-input::placeholder {
            color: var(--input-placeholder);
            font-weight: 500;
        }

        .form-input:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(249, 87, 0, 0.2);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 20px;
        }

        .terms-checkbox-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            max-width: 540px;
            margin-bottom: 24px;
            text-align: left;
            font-size: 13px;
            color: #cbd5e1;
            cursor: pointer;
        }

        .terms-checkbox-wrap input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--primary-orange);
            cursor: pointer;
            flex-shrink: 0;
        }

        .terms-link-text {
            color: var(--primary-orange);
            font-weight: 800;
            text-decoration: underline;
            cursor: pointer;
            transition: opacity 0.15s, color 0.15s;
        }

        .terms-link-text:hover {
            color: #fb923c;
            opacity: 0.85;
        }

        /* Payment Screen Elements (Screen 04 & 05) */
        .payment-method-box {
            width: 100%;
            max-width: 440px;
            background: #fed7aa;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 28px;
            text-align: center;
            border: 3px solid var(--primary-orange);
        }

        .payment-method-title {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 2px;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .payment-method-desc {
            font-size: 14px;
            font-weight: 600;
            color: #475569;
        }

        .total-payment-bar {
            width: 100%;
            max-width: 440px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .total-label {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #cbd5e1;
        }

        .total-amount {
            font-size: 24px;
            font-weight: 900;
            color: #ffffff;
        }

        /* QRIS Screen (Screen 05) */
        .qris-display-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            max-width: 280px;
        }

        .qris-display-card svg, .qris-display-card img {
            width: 100%;
            height: auto;
            max-width: 240px;
        }

        .timer-badge {
            font-size: 14px;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 12px;
        }

        .timer-badge span {
            color: #f87171;
            font-weight: 800;
        }

        .waiting-status-box {
            background: #ffedd5;
            color: #9a3412;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .pulsing-dot {
            width: 10px;
            height: 10px;
            background: #ea580c;
            border-radius: 50%;
            animation: pulse 1.2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.9); opacity: 0.6; }
            50% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.6; }
        }

        .btn-test-pay {
            background: #334155;
            color: #f1f5f9;
            border: 1px dashed #64748b;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
        }

        /* Photo Confirmation (Screen 06) */
        .check-icon-large {
            width: 80px;
            height: 80px;
            margin-bottom: 18px;
        }

        .email-sent-note {
            font-size: 14px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 24px;
        }

        .free-experience-banner {
            width: 100%;
            max-width: 480px;
            background: #ffedd5;
            color: #0f172a;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 28px;
            text-align: center;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
        }

        .free-experience-banner h4 {
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .free-experience-banner p {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        /* E-Voucher Ticket Cards (Screen 06 & Screen 09) */
        .evouchers-wrapper {
            width: 100%;
            max-width: 820px;
            margin: 0 auto 20px;
        }

        .evouchers-grid {
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            justify-content: center;
            gap: 16px;
            max-height: 310px;
            overflow-y: auto;
            padding: 4px;
        }

        .kiosk-ticket-card {
            flex: 1 1 200px;
            max-width: 250px;
            min-width: 190px;
            background: #0f172a;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 18px;
            padding: 14px;
            text-align: center;
            color: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }

        .ticket-card-badge {
            background: rgba(249, 87, 0, 0.2);
            color: #ff8533;
            border: 1px solid rgba(249, 87, 0, 0.4);
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 8px;
            border-radius: 6px;
            margin-bottom: 6px;
        }

        .ticket-qr-wrap {
            background: #ffffff;
            padding: 8px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            margin: 6px 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ticket-qr-wrap svg {
            width: 130px;
            height: 130px;
            display: block;
        }

        .ticket-code-text {
            font-family: monospace;
            font-size: 13px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        .ticket-category-pill {
            font-size: 11px;
            font-weight: 800;
            color: #38bdf8;
            margin-top: 2px;
        }

        /* Camera Screen (Screen 07) - STRICTLY PORTRAIT ON LANDSCAPE KIOSK */
        .camera-container {
            width: 310px;
            height: 450px;
            max-height: 52vh;
            aspect-ratio: 2/3;
            background: #090d16;
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.8), 0 0 0 2px rgba(255, 255, 255, 0.15);
        }

        .camera-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Mirror camera */
        }

        /* Portrait Live Camera Overlay */
        .camera-frame-overlay-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: fill;
            pointer-events: none;
            z-index: 4;
        }

        .camera-live-logos {
            position: absolute;
            top: 76%;
            left: 0;
            right: 0;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 0 45px;
            pointer-events: none;
            z-index: 6;
        }

        .camera-live-logos .live-team-logo {
            width: 52px;
            height: 52px;
            object-fit: contain;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,0.85));
        }

        .camera-portrait-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 5;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-radius: 24px;
        }

        .camera-portrait-header {
            padding: 12px 14px;
            background: linear-gradient(180deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #ffffff;
        }

        .camera-portrait-footer {
            padding: 12px 14px;
            background: linear-gradient(0deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%);
            text-align: center;
        }

        .photo-session-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 9999px;
            background: var(--primary-orange);
            color: #ffffff;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(249, 87, 0, 0.4);
        }

        /* Viewfinder Corners */
        .viewfinder-corner {
            position: absolute;
            width: 32px;
            height: 32px;
            border-color: #ffffff;
            border-style: solid;
            pointer-events: none;
            z-index: 10;
        }

        .vf-top-left { top: 20px; left: 20px; border-width: 4px 0 0 4px; border-top-left-radius: 6px; }
        .vf-top-right { top: 20px; right: 20px; border-width: 4px 4px 0 0; border-top-right-radius: 6px; }
        .vf-bottom-left { bottom: 20px; left: 20px; border-width: 0 0 4px 4px; border-bottom-left-radius: 6px; }
        .vf-bottom-right { bottom: 20px; right: 20px; border-width: 0 4px 4px 0; border-bottom-right-radius: 6px; }

        /* Countdown Overlay */
        .countdown-overlay {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.45);
            z-index: 20;
        }

        .countdown-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--primary-orange);
            color: #ffffff;
            font-size: 48px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 30px rgba(249, 87, 0, 0.8);
            animation: bounceIn 0.8s ease infinite;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.8); }
            50% { transform: scale(1.1); }
            100% { transform: scale(0.8); }
        }

        /* Photo Preview (Screen 08) - PORTRAIT MATCHDAY CARD PREVIEW */
        .photo-preview-grid {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 32px;
            margin-bottom: 20px;
        }

        .collectible-card-container {
            width: 270px;
            max-height: 48vh;
            aspect-ratio: 2/3;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7);
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: #111827;
            position: relative;
        }

        .collectible-card-container img, .collectible-card-container canvas {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .photo-status-sidebar {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            max-width: 280px;
        }

        .photo-status-sidebar svg {
            width: 48px;
            height: 48px;
            fill: var(--success-green);
            margin-bottom: 10px;
        }

        .photo-status-sidebar h5 {
            font-size: 17px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .photo-status-sidebar p {
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
        }

        /* Screen 09 Selesai */
        .finish-card {
            background: #000000;
            border-radius: 24px;
            padding: 32px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 100%;
            max-width: 780px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-height: 82vh;
            overflow-y: auto;
        }

        .finish-title {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 1.5px;
            color: #ffffff;
            margin: 12px 0 6px 0;
            text-transform: uppercase;
        }

        .finish-desc {
            font-size: 14px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        /* Modal Redeem / Merchandise Overlay */
        .kiosk-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 20px;
        }

        .kiosk-modal-backdrop.active {
            display: flex;
        }

        .kiosk-modal-card {
            width: 100%;
            max-width: 560px;
            background: #0f172a;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 32px;
            color: #ffffff;
            position: relative;
        }

        .kiosk-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .kiosk-modal-title {
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .btn-modal-close {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #ffffff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 18px;
            font-weight: 800;
            cursor: pointer;
        }

        /* Flash snapshot animation */
        #flashOverlay {
            position: fixed;
            inset: 0;
            background: #ffffff;
            opacity: 0;
            pointer-events: none;
            z-index: 999;
            transition: opacity 0.15s ease-out;
        }

        #flashOverlay.flash {
            opacity: 1;
        }

        /* Hidden Canvas for Card Generation */
        #cardCanvas {
            display: none;
        }
    </style>
</head>
<body>

<div id="flashOverlay"></div>
<canvas id="cardCanvas" width="1024" height="1536"></canvas>

<div class="kiosk-viewport">
    <!-- Top Header Bar -->
    <div class="kiosk-top-bar">
        <div class="kiosk-brand">
            <span class="kiosk-brand-title">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="#f95700">
                    <path d="M15.58 2.83c-.39-.39-1.02-.39-1.41 0l-1.42 1.41c-.39.39-.39 1.02 0 1.41.78.78.78 2.05 0 2.83-.78.78-2.05.78-2.83 0-.39-.39-1.02-.39-1.41 0L7.09 9.9c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0 .78-.78 2.05-.78 2.83 0 .78.78.78 2.05 0 2.83-.39.39-.39 1.02 0 1.41l1.42 1.42c.39.39 1.02.39 1.41 0l7.07-7.07c.39-.39.39-1.02 0-1.41l-5.65-5.66z"/>
                    <path d="M4.22 11.31c-.39.39-.39 1.02 0 1.41l1.42 1.42c.39.39 1.02.39 1.41 0 .78-.78 2.05-.78 2.83 0 .78.78.78 2.05 0 2.83-.39.39-.39 1.02 0 1.41l1.42 1.42c.39.39 1.02.39 1.41 0l2.83-2.83-7.07-7.07-2.83 2.83z"/>
                </svg>
                IDENTIX KIOSK
            </span>
            <span class="kiosk-brand-badge">{{ $tenant->name ?? 'OFFICIAL CLUB' }}</span>
        </div>
        <div class="kiosk-step-indicator" id="stepIndicator">SCREEN 01/09</div>
    </div>

    <!-- Main Kiosk Bezel Terminal -->
    <div class="kiosk-bezel">

        <!-- ==========================================
             SCREEN 01: WELCOME SCREEN
             ========================================== -->
        <div class="kiosk-screen active" id="screen01">
            <h1 class="welcome-title">SELAMAT DATANG</h1>
            <div class="welcome-logo-wrap">
                <svg class="welcome-logo-svg" viewBox="0 0 100 100">
                    <path d="M30 15 L70 15 C75 15, 80 20, 80 25 L80 35 C70 40, 70 55, 80 60 L80 75 C80 80, 75 85, 70 85 L30 85 C25 85, 20 80, 20 75 L20 60 C30 55, 30 40, 20 35 L20 25 C20 20, 25 15, 30 15 Z" fill="#f95700"/>
                    <path d="M42 32 L58 48 L42 64" stroke="#000000" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </svg>
            </div>
            <p class="welcome-subtitle">PENGALAMAN ACARA KAMU DIMULAI DARI SINI</p>

            <div class="btn-kiosk-menu-group">
                <button type="button" class="btn-kiosk-menu" onclick="goToScreen(2)">
                    <div class="btn-content">
                        <svg viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                        <span>BELI TIKET</span>
                    </div>
                    <svg class="chevron-icon" viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                </button>

                <button type="button" class="btn-kiosk-menu" onclick="openRedeemModal()">
                    <div class="btn-content">
                        <svg viewBox="0 0 24 24"><path d="M3 5v4h2V5h4V3H5c-1.1 0-2 .9-2 2zm2 10H3v4c0 1.1.9 2 2 2h4v-2H5v-4zm14 4h-4v2h4c1.1 0 2-.9 2-2v-4h-2v4zm0-16h-4v2h4v4h2V5c0-1.1-.9-2-2-2zm-9 4h6v2h-6zm0 4h6v2h-6zm0 4h6v2h-6z"/></svg>
                        <span>PENUKARAN TIKET</span>
                    </div>
                    <svg class="chevron-icon" viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                </button>

                <button type="button" class="btn-kiosk-menu" onclick="openMerchModal()">
                    <div class="btn-content">
                        <svg viewBox="0 0 24 24"><path d="M21.99 8c0-.72-.37-1.35-.94-1.7L16 4.13V3c0-.55-.45-1-1-1h-6c-.55 0-1 .45-1 1v1.13L2.95 6.3c-.57.35-.95.98-.95 1.7 0 .5.19.96.5 1.31L4 11v9c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-9l1.49-1.69c.31-.35.5-.81.5-1.31zM10 4h4v2h-4V4z"/></svg>
                        <span>MERCHANDISE</span>
                    </div>
                    <svg class="chevron-icon" viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                </button>
            </div>
        </div>

        <!-- ==========================================
             SCREEN 02: PILIH KATEGORI TIKET
             ========================================== -->
        <div class="kiosk-screen" id="screen02">
            <div class="match-card-header">
                <h2 class="match-title">{{ $event->effective_home_team_name }} VS {{ $event->effective_away_team_name }}</h2>
                <div class="match-info-meta">
                    {{ $event->event_start_date ? $event->event_start_date->translatedFormat('d F Y | H:i') . ' WIB' : '29 NOVEMBER 2026 | 19.00 WIB' }}
                    | {{ $event->venue ?? 'STADION INDOMILK ARENA' }}{{ $event->city ? ', ' . $event->city : '' }}
                </div>
            </div>

            <div class="section-label">PILIH KATEGORI TIKET</div>

            <div class="category-list" id="categoryList">
                @forelse($categories as $index => $cat)
                    <div class="category-item {{ $index === 0 ? 'selected' : '' }}" 
                         data-id="{{ $cat->id }}" 
                         data-name="{{ $cat->name }}" 
                         data-price="{{ $cat->price }}" 
                         data-fee="{{ $cat->service_fee ?? 0 }}"
                         onclick="selectCategory(this)">
                        <div class="radio-dot"></div>
                        <div class="category-details">
                            <div class="category-name">{{ $cat->name }}</div>
                            <div class="category-price">Rp {{ number_format($cat->price, 0, ',', '.') }}</div>
                        </div>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#64748b"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                    </div>
                @empty
                    <div class="category-item selected" data-id="1" data-name="VIP BARAT" data-price="150000" data-fee="0">
                        <div class="radio-dot"></div>
                        <div class="category-details">
                            <div class="category-name">VIP BARAT</div>
                            <div class="category-price">Rp 150.000</div>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="quantity-stepper-wrap">
                <span class="quantity-stepper-label">Jumlah tiket</span>
                <div class="quantity-stepper">
                    <button type="button" class="stepper-btn" onclick="updateQty(-1)">−</button>
                    <div class="stepper-value" id="qtyDisplay">1</div>
                    <button type="button" class="stepper-btn" onclick="updateQty(1)">+</button>
                </div>
            </div>

            <div class="action-buttons-wrap">
                <button type="button" class="btn-kiosk-secondary" onclick="goToScreen(1)">KEMBALI</button>
                <button type="button" class="btn-kiosk-primary" onclick="goToScreen(3)">LANJUTKAN</button>
            </div>
        </div>

        <!-- ==========================================
             SCREEN 03: ISI DATA DIRI
             ========================================== -->
        <div class="kiosk-screen" id="screen03">
            <h2 class="welcome-title" style="font-size: 24px; margin-bottom: 24px;">LENGKAPI DATA DIRI ANDA</h2>

            <form id="userDataForm" onsubmit="event.preventDefault(); goToScreen(4);">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" id="custName" class="form-input" placeholder="Masukkan nama sesuai identitas" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" id="custEmail" class="form-input" placeholder="nama@email.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="tel" id="custPhone" class="form-input" placeholder="08xxxxxxxxxx" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIK (Opsional)</label>
                        <input type="text" id="custNik" class="form-input" placeholder="16 digit NIK" maxlength="16">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select id="custGender" class="form-input form-select" required>
                            <option value="" disabled selected>Pilih: Pria / Wanita</option>
                            <option value="Pria">Pria</option>
                            <option value="Wanita">Wanita</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="text" id="custBirthdate" class="form-input" placeholder="DD / MM / YYYY">
                    </div>
                </div>

                <div class="terms-checkbox-wrap">
                    <input type="checkbox" id="termsCheck" required>
                    <label for="termsCheck" style="cursor: pointer; margin: 0; font-size: inherit; color: inherit; user-select: none;">
                        Saya menyetujui 
                        <span class="terms-link-text" onclick="event.preventDefault(); event.stopPropagation(); openModal('termsModal');">Syarat & Ketentuan</span> 
                        dan 
                        <span class="terms-link-text" onclick="event.preventDefault(); event.stopPropagation(); openModal('privacyModal');">Kebijakan Privasi</span>.
                    </label>
                </div>

                <div class="action-buttons-wrap">
                    <button type="button" class="btn-kiosk-secondary" onclick="goToScreen(2)">KEMBALI</button>
                    <button type="submit" class="btn-kiosk-primary">LANJUTKAN</button>
                </div>
            </form>
        </div>

        <!-- ==========================================
             SCREEN 04: PILIH METODE PEMBAYARAN
             ========================================== -->
        <div class="kiosk-screen" id="screen04">
            <h2 class="welcome-title" style="font-size: 24px; margin-bottom: 28px;">METODE PEMBAYARAN</h2>

            <div class="payment-method-box">
                <div class="payment-method-title">QRIS</div>
                <div class="payment-method-desc">Pembayaran melalui QRIS (Semua E-Wallet & Mobile Banking)</div>
            </div>

            <div class="total-payment-bar">
                <span class="total-label">TOTAL PEMBAYARAN</span>
                <span class="total-amount" id="totalPaymentDisplay">Rp 150.000</span>
            </div>

            <div class="action-buttons-wrap">
                <button type="button" class="btn-kiosk-secondary" onclick="goToScreen(3)">KEMBALI</button>
                <button type="button" class="btn-kiosk-primary" id="btnPayNow" onclick="startQrisCheckout()">BAYAR SEKARANG</button>
            </div>
        </div>

        <!-- ==========================================
             SCREEN 05: KONFIRMASI PEMBAYARAN (QRIS)
             ========================================== -->
        <div class="kiosk-screen" id="screen05">
            <h2 class="welcome-title" style="font-size: 22px; margin-bottom: 12px;">SCAN QRIS UNTUK MEMBAYAR</h2>
            <div style="font-size: 15px; font-weight: 700; color: #ffffff; margin-bottom: 16px;">
                TOTAL PEMBAYARAN &bull; <span id="qrisTotalAmount">Rp 150.000</span>
            </div>

            <div class="qris-display-card" id="qrisContainer">
                <div style="padding: 40px; text-align: center; color: #64748b;">Membuat QRIS...</div>
            </div>

            <div class="timer-badge">
                Sisa waktu pembayaran: <span id="paymentCountdown">05:00</span>
            </div>

            <div class="waiting-status-box">
                <div class="pulsing-dot"></div>
                <span id="waitingStatusText">Menunggu konfirmasi pembayaran...</span>
            </div>

            <!-- Testing helper for demo kiosk -->
            <button type="button" class="btn-test-pay" id="btnSimulatePay" onclick="simulatePaymentSuccess()">
                &check; Simulasi Bayar Instan (Test Mode)
            </button>
        </div>

        <!-- ==========================================
             SCREEN 06: E-VOUCHER & KONFIRMASI PHOTO EXPERIENCE
             ========================================== -->
        <div class="kiosk-screen" id="screen06">
            <svg class="check-icon-large" viewBox="0 0 24 24" fill="#22c55e" style="margin-bottom: 6px;">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            <h2 class="welcome-title" style="font-size: 22px; margin-bottom: 4px;">PEMBAYARAN BERHASIL!</h2>
            <p class="email-sent-note" id="screen06EmailNote" style="margin-bottom: 16px;">E-ticket telah dikirim ke email Anda.</p>

            <!-- Dynamic E-Vouchers Grid (1 tiket = 1 QR, >1 tiket = multiple QR) -->
            <div class="evouchers-wrapper">
                <div style="font-size: 11px; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;" id="screen06EvoucherHeader">
                    E-VOUCHER TIKET ANDA:
                </div>
                <div class="evouchers-grid" id="screen06EvouchersWrap">
                    <!-- Populated dynamically by renderScreen06EVouchers() -->
                </div>
            </div>

            <!-- Free Photo Experience Offer (Adjusts chances to quantity) -->
            <div class="free-experience-banner" id="screen06PhotoBanner">
                <h4 id="screen06PhotoBannerTitle">Nikmati FREE PHOTO EXPERIENCE!</h4>
                <p id="screen06PhotoBannerDesc">Abadikan momen spesial Anda dan dapatkan Matchday Collectible Card resmi.</p>
                <div id="photoAutoNotice" style="display: none; font-size: 12px; font-weight: 800; color: #ea580c; margin-top: 8px;">
                    Standby kamera otomatis dalam <span id="photoAutoSec">3</span>s...
                </div>
            </div>

            <div class="action-buttons-wrap">
                <button type="button" class="btn-kiosk-primary" id="btnStartPhotoSession" onclick="startPhotoSession(event)" style="background: var(--primary-orange); min-height: 52px; cursor: pointer;">
                    YA, AMBIL FOTO
                </button>
                <button type="button" class="btn-kiosk-secondary" onclick="goToScreen(9)" style="min-height: 52px;">
                    TIDAK, SELESAI
                </button>
            </div>
        </div>

        <!-- ==========================================
             SCREEN 07: TAKE PHOTO – CAMERA & COUNTDOWN (PORTRAIT VIEWFINDER)
             ========================================== -->
        <div class="kiosk-screen" id="screen07">
            <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 6px;">
                <h2 class="welcome-title" style="font-size: 22px; margin-bottom: 0;">SIAP UNTUK FOTO?</h2>
                <div class="photo-session-pill" id="screen07PhotoPill">FOTO 1 DARI 1</div>
            </div>
            <p style="font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 10px;" id="screen07Subtitle">
                Berdiri di depan kamera dan posisikan diri Anda di dalam bingkai portrait.
            </p>

            <!-- Strictly Portrait Viewfinder on Landscape Kiosk Screen -->
            <div class="camera-container">
                <video id="cameraVideo" class="camera-video" autoplay playsinline muted></video>

                <!-- Custom PNG Frame Overlay on live camera feed -->
                @if(!empty($photoboothTemplate->frame_overlay))
                    <img id="cameraFrameOverlay" 
                         src="{{ asset('storage/' . $photoboothTemplate->frame_overlay) }}" 
                         class="camera-frame-overlay-img" 
                         alt="Matchday Frame">
                @endif

                <!-- Live Team Logos on Viewfinder (Aligned with Frame VS mark) -->
                <div id="cameraLiveLogos" class="camera-live-logos" style="{{ empty($photoboothTemplate->frame_overlay) ? 'display: none;' : '' }}">
                    @if($event->effective_home_team_logo_url)
                        <img src="{{ $event->effective_home_team_logo_url }}" class="live-team-logo" alt="Home">
                    @endif
                    @if($event->effective_away_team_logo_url)
                        <img src="{{ $event->effective_away_team_logo_url }}" class="live-team-logo" alt="Away">
                    @endif
                </div>

                <!-- Fallback Portrait Live Overlays (if no custom frame uploaded) -->
                <div id="cameraFrameFallback" class="camera-portrait-overlay" style="{{ !empty($photoboothTemplate->frame_overlay) ? 'display: none;' : '' }}">
                    <div class="camera-portrait-header">
                        <span style="font-size: 10px; font-weight: 900; letter-spacing: 1px; color: #f95700;" id="cameraLeagueTag">{{ $photoboothTemplate->header_title ?? ($event->season_name ?: 'I LEAGUE 2026/2027') }}</span>
                        <span style="font-size: 9px; font-weight: 800; background: rgba(0,0,0,0.6); padding: 2px 6px; border-radius: 4px;">PORTRAIT</span>
                    </div>
                    <div class="camera-portrait-footer">
                        <span style="font-size: 9px; font-weight: 800; color: rgba(255,255,255,0.9); text-transform: uppercase;" id="cameraLiveFanName">BRIAN ARIBOWO</span>
                    </div>
                </div>

                <!-- Viewfinder Corners -->
                <div class="viewfinder-corner vf-top-left"></div>
                <div class="viewfinder-corner vf-top-right"></div>
                <div class="viewfinder-corner vf-bottom-left"></div>
                <div class="viewfinder-corner vf-bottom-right"></div>

                <!-- Animated Countdown Circle -->
                <div class="countdown-overlay" id="countdownOverlay">
                    <div class="countdown-circle" id="countdownNumber">3</div>
                </div>
            </div>

            <!-- Name personalization for current photo session -->
            <div style="margin-bottom: 8px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <label style="font-size: 12px; font-weight: 700; color: #cbd5e1;">Nama Suporter di Kartu:</label>
                <input type="text" id="screen07FanNameInput" 
                       style="background: #1e293b; border: 1px solid #475569; border-radius: 8px; padding: 6px 12px; font-size: 13px; font-weight: 800; color: #ffffff; text-transform: uppercase; width: 220px;"
                       oninput="onFanNameInputChange(this.value)">
            </div>

            <div style="font-size: 12px; color: #94a3b8; margin-bottom: 12px;" id="screen07ChanceNotice">
                Anda memiliki 1x kesempatan untuk mengambil gambar
            </div>

            <div class="action-buttons-wrap">
                <button type="button" class="btn-kiosk-primary" id="btnCapturePhoto" onclick="startPhotoCountdown()" style="max-width: 320px; min-height: 52px;">
                    AMBIL FOTO (1/1)
                </button>
            </div>
        </div>

        <!-- ==========================================
             SCREEN 08: PHOTO PREVIEW & MULTI-PHOTO STEPPER
             ========================================== -->
        <div class="kiosk-screen" id="screen08">
            <h2 class="welcome-title" style="font-size: 22px; margin-bottom: 16px;">FOTO KAMU SUDAH SIAP!</h2>

            <div class="photo-preview-grid">
                <!-- Portrait Matchday Collectible Card Preview -->
                <div class="collectible-card-container">
                    <img id="finalCardPreview" src="{{ asset('images/kiosk/matchday_card_sample.jpg') }}" alt="Matchday Collectible Preview">
                </div>

                <!-- Multi-photo Status & Navigation Sidebar -->
                <div class="photo-status-sidebar" style="max-width: 300px;">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <h5 id="screen08StatusTitle">Foto berhasil disimpan!</h5>
                    <p id="screen08StatusDesc">Foto telah dikirim ke email Anda.</p>

                    <!-- Status Pills for multiple photos -->
                    <div id="screen08PhotoPills" style="display: flex; gap: 6px; margin: 12px 0; justify-content: center; flex-wrap: wrap;"></div>

                    <!-- Dynamic Action Buttons -->
                    <div id="screen08Actions" style="width: 100%; display: flex; flex-direction: column; gap: 10px; margin-top: 6px;"></div>
                </div>
            </div>

            <div style="font-size: 12px; font-weight: 800; letter-spacing: 1px; color: #64748b; margin-top: 4px;">
                MATCHDAY COLLECTIBLE CARD &bull; PORTRAIT EDITION
            </div>
        </div>

        <!-- ==========================================
             SCREEN 09: SELESAI – SEMUA TIKET & FOTO
             ========================================== -->
        <div class="kiosk-screen" id="screen09">
            <div class="finish-card">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="#22c55e" style="margin-bottom: 4px;">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <h2 class="finish-title">TERIMA KASIH!</h2>
                <p class="finish-desc" id="screen09SummaryText" style="margin-bottom: 16px;">
                    Semua E-ticket dan foto Matchday Collectible telah dikirim ke email Anda.<br>
                    Selamat menikmati pertandingan!
                </p>

                <!-- Grid of E-Vouchers on Screen 09 -->
                <div style="width: 100%; margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                        E-VOUCHER TIKET ANDA:
                    </div>
                    <div class="evouchers-grid" id="screen09EvouchersGrid"></div>
                </div>

                <!-- Gallery of Captured Photos on Screen 09 -->
                <div id="screen09PhotosSection" style="width: 100%; margin-bottom: 20px; display: none;">
                    <div style="font-size: 11px; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                        KARTU MATCHDAY COLLECTIBLE ANDA:
                    </div>
                    <div id="screen09PhotosGrid" style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;"></div>
                </div>

                <button type="button" class="btn-kiosk-primary" onclick="resetToWelcome()" style="width: 100%; max-width: 340px; min-height: 52px;">
                    KEMBALI KE MENU AWAL... (<span id="autoResetTimer">15</span>s)
                </button>
            </div>
        </div>

    </div>
</div>

<!-- ==========================================
     MODAL: PENUKARAN TIKET (REDEEM / EXCHANGE)
     ========================================== -->
<div class="kiosk-modal-backdrop" id="redeemModal">
    <div class="kiosk-modal-card">
        <div class="kiosk-modal-header">
            <div class="kiosk-modal-title">PENUKARAN TIKET</div>
            <button type="button" class="btn-modal-close" onclick="closeModal('redeemModal')">&times;</button>
        </div>

        <div style="margin-bottom: 20px;">
            <label class="form-label">Masukkan Kode Tiket / No. Invoice</label>
            <div style="display: flex; gap: 10px; margin-top: 8px;">
                <input type="text" id="redeemInputCode" class="form-input" placeholder="Contoh: 8192839182 / KIOSK-..." style="text-transform: uppercase;">
                <button type="button" class="btn-kiosk-primary" style="flex: 0 0 120px;" onclick="lookupTicketRedeem()">CARI</button>
            </div>
        </div>

        <div id="redeemResultBox" style="display: none; background: #1e293b; border-radius: 12px; padding: 18px; margin-top: 16px;">
            <div id="redeemStatusBadge" style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 800; margin-bottom: 10px;"></div>
            <div style="font-size: 16px; font-weight: 800; color: #ffffff;" id="redeemEventName"></div>
            <div style="font-size: 14px; color: #94a3b8; margin: 4px 0;" id="redeemVisitorName"></div>
            <div style="font-size: 14px; font-weight: 700; color: var(--primary-orange);" id="redeemCategory"></div>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: MERCHANDISE SHOWCASE
     ========================================== -->
<div class="kiosk-modal-backdrop" id="merchModal">
    <div class="kiosk-modal-card" style="max-width: 680px;">
        <div class="kiosk-modal-header">
            <div class="kiosk-modal-title">MERCHANDISE RESMI KLUB</div>
            <button type="button" class="btn-modal-close" onclick="closeModal('merchModal')">&times;</button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
            <div style="background: #1e293b; border-radius: 14px; padding: 16px; text-align: center;">
                <div style="font-size: 32px; margin-bottom: 8px;">👕</div>
                <div style="font-weight: 800; color: #ffffff;">Jersey Home 2026/2027</div>
                <div style="font-size: 14px; color: var(--primary-orange); font-weight: 700; margin-top: 4px;">Rp 399.000</div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Tersedia di Store Utama</div>
            </div>
            <div style="background: #1e293b; border-radius: 14px; padding: 16px; text-align: center;">
                <div style="font-size: 32px; margin-bottom: 8px;">🧣</div>
                <div style="font-weight: 800; color: #ffffff;">Syal Matchday Jacquard</div>
                <div style="font-size: 14px; color: var(--primary-orange); font-weight: 700; margin-top: 4px;">Rp 125.000</div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Official Matchday Edition</div>
            </div>
        </div>

        <button type="button" class="btn-kiosk-primary" style="width: 100%;" onclick="closeModal('merchModal')">TUTUP</button>
    </div>
</div>

<!-- ==========================================
     MODAL: SYARAT & KETENTUAN
     ========================================== -->
<div class="kiosk-modal-backdrop" id="termsModal">
    <div class="kiosk-modal-card" style="max-width: 720px; max-height: 85vh; display: flex; flex-direction: column; background: #0f172a; border: 1px solid rgba(255,255,255,0.15);">
        <div class="kiosk-modal-header" style="flex-shrink: 0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 16px;">
            <div class="kiosk-modal-title" style="display: flex; align-items: center; gap: 8px;">
                <svg style="width: 20px; height: 20px; color: var(--primary-orange);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>SYARAT & KETENTUAN PEMBELIAN TIKET</span>
            </div>
            <button type="button" class="btn-modal-close" onclick="closeModal('termsModal')">&times;</button>
        </div>

        <div style="flex: 1; overflow-y: auto; padding: 22px 24px; color: #cbd5e1; font-size: 13px; line-height: 1.7; text-align: left;">
            @if(!empty($event->terms_conditions))
                <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 16px; margin-bottom: 20px; color: #ffffff;">
                    <div style="font-weight: 800; font-size: 13px; margin-bottom: 6px; color: var(--primary-orange); text-transform: uppercase;">Ketentuan Khusus Pertandingan:</div>
                    {!! $event->terms_conditions !!}
                </div>
            @endif

            <div style="font-weight: 800; color: #ffffff; margin-bottom: 6px; font-size: 14px;">1. Ketentuan E-Voucher & Tiket Resmi</div>
            <p style="margin-bottom: 14px;">
                E-Ticket / E-Voucher diterbitkan secara otomatis setelah pembayaran terverifikasi. QR Code pada E-Voucher berlaku untuk 1 (satu) kali penukaran gelang masuk (wristband) atau akses gate resmi di {{ $event->venue ?? 'Stadion Indomilk Arena' }}.
            </p>

            <div style="font-weight: 800; color: #ffffff; margin-bottom: 6px; font-size: 14px;">2. Akses Masuk & Identitas</div>
            <p style="margin-bottom: 14px;">
                Pengunjung wajib menunjukkan e-voucher dengan QR Code yang jelas dan membawa kartu identitas (KTP/SIM/Kartu Pelajar) yang sah sesuai nama pemesan saat penukaran gelang atau pemeriksaan petugas gate.
            </p>

            <div style="font-weight: 800; color: #ffffff; margin-bottom: 6px; font-size: 14px;">3. Fasilitas Free Photobooth Experience</div>
            <p style="margin-bottom: 14px;">
                Setiap pembelian tiket berhak menikmati sesi foto suporter berbingkai resmi matchday (Matchday Collectible Card). Jumlah kesempatan foto diberikan tepat sejumlah tiket yang dibeli dalam transaksi ini.
            </p>

            <div style="font-weight: 800; color: #ffffff; margin-bottom: 6px; font-size: 14px;">4. Larangan & Tata Tertib Pertandingan</div>
            <p style="margin-bottom: 14px;">
                Dilarang membawa senjata tajam, kembang api/flare, minuman keras/alkohol, flare, laser pointer, benda tumpul berbahaya, maupun zat terlarang lainnya ke dalam stadion. Pihak keamanan berhak menolak masuk atau mengeluarkan pengunjung yang melanggar ketertiban.
            </p>

            <div style="font-weight: 800; color: #ffffff; margin-bottom: 6px; font-size: 14px;">5. Kebijakan Non-Refundable</div>
            <p style="margin-bottom: 0;">
                Tiket yang sudah berhasil dibayar tidak dapat dibatalkan, ditukar, dipindahtangankan tanpa persetujuan, atau diuangkan kembali, kecuali terdapat pembatalan resmi pertandingan dari pihak penyelenggara.
            </p>
        </div>

        <div style="padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.1); background: #0b1120; flex-shrink: 0; display: flex; gap: 12px;">
            <button type="button" class="btn-kiosk-primary" style="width: 100%;" onclick="closeModal('termsModal')">SAYA MENGERTI & TUTUP</button>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: KEBIJAKAN PRIVASI
     ========================================== -->
<div class="kiosk-modal-backdrop" id="privacyModal">
    <div class="kiosk-modal-card" style="max-width: 720px; max-height: 85vh; display: flex; flex-direction: column; background: #0f172a; border: 1px solid rgba(255,255,255,0.15);">
        <div class="kiosk-modal-header" style="flex-shrink: 0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 16px;">
            <div class="kiosk-modal-title" style="display: flex; align-items: center; gap: 8px;">
                <svg style="width: 20px; height: 20px; color: var(--primary-orange);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>KEBIJAKAN PRIVASI DATA PENGUNJUNG</span>
            </div>
            <button type="button" class="btn-modal-close" onclick="closeModal('privacyModal')">&times;</button>
        </div>

        <div style="flex: 1; overflow-y: auto; padding: 22px 24px; color: #cbd5e1; font-size: 13px; line-height: 1.7; text-align: left;">
            <div style="font-weight: 800; color: #ffffff; margin-bottom: 6px; font-size: 14px;">1. Pengumpulan Data Pribadi</div>
            <p style="margin-bottom: 14px;">
                Terminal Kiosk mencatat data nama lengkap, nomor telepon/WhatsApp, alamat email, jenis kelamin, dan tanggal lahir semata-mata untuk penerbitan E-Voucher tiket resmi, verifikasi pengunjung di gate stadion, serta pengiriman e-ticket dan collectible digital.
            </p>

            <div style="font-weight: 800; color: #ffffff; margin-bottom: 6px; font-size: 14px;">2. Penggunaan Data Kontak</div>
            <p style="margin-bottom: 14px;">
                Alamat email Anda digunakan untuk mengirimkan salinan E-Ticket barcode dan file kartu foto digital Matchday Collectible. Nomor telepon digunakan untuk konfirmasi status pesanan atau notifikasi penting darurat terkait pertandingan. Data Anda tidak akan pernah dijual kepada pihak ketiga.
            </p>

            <div style="font-weight: 800; color: #ffffff; margin-bottom: 6px; font-size: 14px;">3. Perlindungan & Kerahasiaan Foto Suporter</div>
            <p style="margin-bottom: 14px;">
                Foto yang diambil melalui kamera kiosk diproses secara aman untuk mencetak kartu foto suporter berbingkai resmi matchday. Foto hanya dapat diakses melalui tautan token terenkripsi yang dikirimkan ke email Anda dan kode QR unik pada kartu suporter Anda.
            </p>

            <div style="font-weight: 800; color: #ffffff; margin-bottom: 6px; font-size: 14px;">4. Keamanan Transaksi QRIS</div>
            <p style="margin-bottom: 0;">
                Seluruh pemrosesan pembayaran QRIS menggunakan gateway pembayaran berlisensi Bank Indonesia dengan standar keamanan enkripsi SSL tingkat tinggi. Terminal Kiosk tidak pernah menyimpan data rahasia perbankan atau PIN dompet digital Anda.
            </p>
        </div>

        <div style="padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.1); background: #0b1120; flex-shrink: 0; display: flex; gap: 12px;">
            <button type="button" class="btn-kiosk-primary" style="width: 100%;" onclick="closeModal('privacyModal')">SAYA MENGERTI & TUTUP</button>
        </div>
    </div>
</div>

<!-- ==========================================
     KIOSK JAVASCRIPT ENGINE
     ========================================== -->
<script>
    // Configuration & State
    const KIOSK_CONFIG = {
        tenantSlug: '{{ $tenant->slug }}',
        eventId: {{ $event->id }},
        eventName: @json($event->name),
        matchDate: @json($event->event_start_date ? $event->event_start_date->translatedFormat('d F Y | H:i') . ' WIB' : '29 NOVEMBER 2026 | 19.00 WIB'),
        venue: @json($event->venue ?? 'STADION INDOMILK ARENA'),
        homeTeam: @json($event->effective_home_team_name),
        awayTeam: @json($event->effective_away_team_name),
        homeTeamLogo: @json($event->effective_home_team_logo_url),
        awayTeamLogo: @json($event->effective_away_team_logo_url),
        leagueLogo: @json($event->effective_league_logo_url),
        homeTeamLogoDataUri: @json($event->effective_home_team_logo_data_uri),
        awayTeamLogoDataUri: @json($event->effective_away_team_logo_data_uri),
        leagueLogoDataUri: @json($event->effective_league_logo_data_uri),
        layoutConfig: @json($photoboothTemplate ? $photoboothTemplate->getEffectiveLayoutConfig() : []),
        frameOverlayUrl: @json(!empty($photoboothTemplate?->frame_overlay) ? asset('storage/' . $photoboothTemplate->frame_overlay) : null),
        backgroundUrl: @json(!empty($photoboothTemplate?->background_image) ? asset('storage/' . $photoboothTemplate->background_image) : null),
        primaryColor: @json($photoboothTemplate?->primary_color ?? '#5A2D82'),
        secondaryColor: @json($photoboothTemplate?->secondary_color ?? '#F95700'),
        headerTitle: @json($photoboothTemplate?->header_title),
        headerSubtitle: @json($photoboothTemplate?->header_subtitle),
        badgeText: @json($photoboothTemplate?->badge_text),
        categoryLabelFormat: @json($photoboothTemplate?->category_label_format),
        footerText: @json($photoboothTemplate?->footer_text),
        showCategory: {{ ($photoboothTemplate?->show_category ?? true) ? 'true' : 'false' }},
        showFanName: {{ ($photoboothTemplate?->show_fan_name ?? true) ? 'true' : 'false' }},
        showQr: {{ ($photoboothTemplate?->show_qr ?? true) ? 'true' : 'false' }},
        showMatchInfo: {{ ($photoboothTemplate?->show_match_info ?? true) ? 'true' : 'false' }},
        showTeamLogos: {{ ($photoboothTemplate?->show_team_logos ?? true) ? 'true' : 'false' }},
        sampleCardUrl: '{{ asset('images/kiosk/matchday_card_sample.jpg') }}',
        routes: {
            checkout: '{{ route('kiosk.checkout', $tenant->slug) }}',
            status: '{{ url('kiosk/' . $tenant->slug . '/status') }}',
            simulatePay: '{{ url('kiosk/' . $tenant->slug . '/simulate-pay') }}',
            savePhoto: '{{ route('kiosk.photo.save', $tenant->slug) }}',
            redeemLookup: '{{ route('kiosk.redeem.lookup', $tenant->slug) }}'
        }
    };

    // Preload image assets for instant canvas rendering
    let preloadedFrameImg = null;
    let preloadedHomeLogo = null;
    let preloadedAwayLogo = null;
    let preloadedLeagueLogo = null;
    let preloadedBgImg = null;

    function initPreloadAssets() {
        if (KIOSK_CONFIG.frameOverlayUrl) {
            preloadedFrameImg = new Image();
            preloadedFrameImg.src = KIOSK_CONFIG.frameOverlayUrl;
        }
        const hSrc = KIOSK_CONFIG.homeTeamLogoDataUri || KIOSK_CONFIG.homeTeamLogo;
        if (hSrc) {
            preloadedHomeLogo = new Image();
            preloadedHomeLogo.src = hSrc;
        }
        const aSrc = KIOSK_CONFIG.awayTeamLogoDataUri || KIOSK_CONFIG.awayTeamLogo;
        if (aSrc) {
            preloadedAwayLogo = new Image();
            preloadedAwayLogo.src = aSrc;
        }
        const lSrc = KIOSK_CONFIG.leagueLogoDataUri || KIOSK_CONFIG.leagueLogo;
        if (lSrc) {
            preloadedLeagueLogo = new Image();
            preloadedLeagueLogo.src = lSrc;
        }
        if (KIOSK_CONFIG.backgroundUrl) {
            preloadedBgImg = new Image();
            preloadedBgImg.src = KIOSK_CONFIG.backgroundUrl;
        }
    }
    initPreloadAssets();

    let kioskState = {
        currentScreen: 1,
        selectedCategory: null,
        quantity: 1,
        totalPhotos: 1,
        currentPhotoIndex: 1,
        currentPhotoFanName: '',
        capturedPhotos: [],
        tickets: [],
        customer: {
            name: '',
            email: '',
            phone: '',
            nik: '',
            gender: '',
            birthdate: ''
        },
        transaction: null,
        capturedRawPhoto: null,
        generatedCardData: null,
        cameraStream: null,
        pollInterval: null,
        autoResetInterval: null,
        autoPhotoTimer: null,
        isPaid: false,
        isProcessingPayment: false
    };

    // Initialize Default Category
    document.addEventListener('DOMContentLoaded', () => {
        const firstCat = document.querySelector('.category-item.selected');
        if (firstCat) {
            selectCategory(firstCat);
        }
    });

    // Screen Navigation
    function goToScreen(screenNumber) {
        clearInterval(kioskState.pollInterval);
        clearInterval(kioskState.autoResetInterval);

        // Hide all screens
        document.querySelectorAll('.kiosk-screen').forEach(s => s.classList.remove('active'));

        const targetScreen = document.getElementById('screen0' + screenNumber);
        if (targetScreen) {
            targetScreen.classList.add('active');
            kioskState.currentScreen = screenNumber;
            const stepInd = document.getElementById('stepIndicator');
            if (stepInd) stepInd.innerText = `SCREEN 0${screenNumber}/09`;
        }

        // Screen specific hooks
        if (screenNumber === 4) {
            calculateTotal();
        } else if (screenNumber === 7) {
            startCamera();
            updateScreen07UI();
        } else if (screenNumber !== 7) {
            stopCamera();
        }

        if (screenNumber === 9) {
            renderScreen09Summary();
            startAutoResetTimer();
        }
    }

    // Category Selection
    function selectCategory(el) {
        document.querySelectorAll('.category-item').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');

        kioskState.selectedCategory = {
            id: el.dataset.id,
            name: el.dataset.name,
            price: parseInt(el.dataset.price || 0),
            fee: parseFloat(el.dataset.fee || 0)
        };
        calculateTotal();
    }

    // Quantity Stepper (1 to 10)
    function updateQty(delta) {
        let newQty = kioskState.quantity + delta;
        if (newQty >= 1 && newQty <= 10) {
            kioskState.quantity = newQty;
            kioskState.totalPhotos = newQty;
            document.getElementById('qtyDisplay').innerText = newQty;
            calculateTotal();
        }
    }

    // Calculate Total Amount
    function calculateTotal() {
        if (!kioskState.selectedCategory) return;
        const total = (kioskState.selectedCategory.price + kioskState.selectedCategory.fee) * kioskState.quantity;
        const formatted = 'Rp ' + total.toLocaleString('id-ID');
        const displayEl = document.getElementById('totalPaymentDisplay');
        if (displayEl) displayEl.innerText = formatted;
        const qrisEl = document.getElementById('qrisTotalAmount');
        if (qrisEl) qrisEl.innerText = formatted;
    }

    // QRIS Checkout Process
    async function startQrisCheckout() {
        const btn = document.getElementById('btnPayNow');
        btn.disabled = true;
        btn.innerText = 'MEMPROSES...';

        // Gather form data
        kioskState.customer.name = document.getElementById('custName').value.trim();
        kioskState.customer.email = document.getElementById('custEmail').value.trim();
        kioskState.customer.phone = document.getElementById('custPhone').value.trim();
        kioskState.customer.nik = document.getElementById('custNik').value.trim();
        kioskState.customer.gender = document.getElementById('custGender').value;
        kioskState.customer.birthdate = document.getElementById('custBirthdate').value;

        try {
            const response = await fetch(KIOSK_CONFIG.routes.checkout, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    event_id: KIOSK_CONFIG.eventId,
                    ticket_category_id: kioskState.selectedCategory.id,
                    quantity: kioskState.quantity,
                    customer_name: kioskState.customer.name,
                    customer_email: kioskState.customer.email,
                    customer_phone: kioskState.customer.phone,
                    customer_nik: kioskState.customer.nik,
                    customer_gender: kioskState.customer.gender,
                    customer_birthdate: kioskState.customer.birthdate
                })
            });

            const data = await response.json();

            if (data.success) {
                kioskState.transaction = data;
                kioskState.isPaid = false;
                kioskState.isProcessingPayment = false;
                const btnSimulate = document.getElementById('btnSimulatePay');
                if (btnSimulate) {
                    btnSimulate.disabled = false;
                    btnSimulate.style.opacity = '1';
                    btnSimulate.style.cursor = 'pointer';
                    btnSimulate.innerHTML = '&check; Simulasi Bayar Instan (Test Mode)';
                }
                const waitEl = document.getElementById('waitingStatusText');
                if (waitEl) waitEl.innerText = 'Menunggu konfirmasi pembayaran...';

                // Render QR code SVG
                document.getElementById('qrisContainer').innerHTML = data.qr_svg;
                document.getElementById('qrisTotalAmount').innerText = data.formatted_total;
                
                // Go to screen 05
                goToScreen(5);
                startQrisTimer();
                startPaymentPolling(data.reference_no);
            } else {
                alert(data.message || 'Gagal memproses pemesanan.');
            }
        } catch (err) {
            console.error('Checkout error:', err);
            alert('Terjadi kesalahan jaringan saat memproses pesanan.');
        } finally {
            btn.disabled = false;
            btn.innerText = 'BAYAR SEKARANG';
        }
    }

    // Polling Payment Status
    function startPaymentPolling(referenceNo) {
        clearInterval(kioskState.pollInterval);
        kioskState.pollInterval = setInterval(async () => {
            try {
                const res = await fetch(`${KIOSK_CONFIG.routes.status}/${referenceNo}`);
                const data = await res.json();
                if (data.paid) {
                    clearInterval(kioskState.pollInterval);
                    onPaymentSuccess(data);
                }
            } catch (e) {
                console.warn('Status poll warning:', e);
            }
        }, 2500);
    }

    // Simulate Payment Success (Test Mode Button)
    async function simulatePaymentSuccess() {
        if (!kioskState.transaction || !kioskState.transaction.reference_no) {
            alert('Belum ada transaksi aktif.');
            return;
        }

        // Prevent double click or concurrent execution
        if (kioskState.isProcessingPayment || kioskState.isPaid) {
            return;
        }
        kioskState.isProcessingPayment = true;

        const btn = document.getElementById('btnSimulatePay');
        const originalText = btn ? btn.innerHTML : '&check; Simulasi Bayar Instan (Test Mode)';
        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.65';
            btn.style.cursor = 'not-allowed';
            btn.innerHTML = '<span style="display:inline-block; animation: spin 1s linear infinite; margin-right: 6px;">&#9696;</span> Memproses Pembayaran...';
        }

        // Stop polling immediately while processing simulation
        if (kioskState.pollInterval) {
            clearInterval(kioskState.pollInterval);
            kioskState.pollInterval = null;
        }

        try {
            const res = await fetch(`${KIOSK_CONFIG.routes.simulatePay}/${kioskState.transaction.reference_no}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await res.json();
            if (data.success) {
                onPaymentSuccess(data);
            } else {
                alert(data.message || 'Pembayaran gagal diproses.');
                if (!kioskState.isPaid) {
                    kioskState.isProcessingPayment = false;
                    if (btn) {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.style.cursor = 'pointer';
                        btn.innerHTML = originalText;
                    }
                    startPaymentPolling(kioskState.transaction.reference_no);
                }
            }
        } catch (e) {
            console.error('Simulate payment error:', e);
            if (!kioskState.isPaid) {
                kioskState.isProcessingPayment = false;
                if (btn) {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                    btn.innerHTML = originalText;
                }
                startPaymentPolling(kioskState.transaction.reference_no);
            }
        }
    }

    // Payment Success Transition: generates exact number of E-Voucher QR codes and photo chances
    function onPaymentSuccess(data) {
        // Strict idempotency: if already paid or processed, ignore duplicate triggers
        if (kioskState.isPaid) return;
        kioskState.isPaid = true;
        kioskState.isProcessingPayment = false;

        // Clear poll interval
        if (kioskState.pollInterval) {
            clearInterval(kioskState.pollInterval);
            kioskState.pollInterval = null;
        }

        // Update button status
        const btn = document.getElementById('btnSimulatePay');
        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
            btn.innerHTML = '&check; Pembayaran Berhasil Diproses';
        }

        document.getElementById('waitingStatusText').innerText = 'PEMBAYARAN BERHASIL!';

        // Determine exact quantity purchased
        const targetQuantity = (data && data.quantity) ? parseInt(data.quantity) : (kioskState.quantity || 1);
        kioskState.quantity = targetQuantity;

        if (data && data.tickets && data.tickets.length > 0) {
            // Strictly bound tickets to quantity purchased (prevents duplicate vouchers)
            kioskState.tickets = data.tickets.slice(0, targetQuantity);
        } else {
            // Fallback: create simulated tickets matching quantity
            kioskState.tickets = [];
            const ref = (kioskState.transaction && kioskState.transaction.reference_no) ? kioskState.transaction.reference_no : ('KIOSK-' + Date.now());
            for (let i = 1; i <= targetQuantity; i++) {
                const code = ref + '-' + String(i).padStart(2, '0');
                kioskState.tickets.push({
                    ticket_number: i,
                    total_tickets: targetQuantity,
                    ticket_code: code,
                    category_name: kioskState.selectedCategory ? kioskState.selectedCategory.name : 'VIP BARAT',
                    visitor_name: kioskState.customer.name || 'SUPORTER',
                    qr_svg: generateSimulatedQrSvg(code)
                });
            }
        }

        // Set photobooth chances strictly according to quantity purchased
        kioskState.totalPhotos = targetQuantity;
        kioskState.currentPhotoIndex = 1;
        kioskState.capturedPhotos = [];

        renderScreen06EVouchers();

        setTimeout(() => {
            goToScreen(6);
            startAutoPhotoStandbyTimer();
        }, 1200);
    }

    // Auto-advance timer from screen 06 to screen 07 (standby camera)
    function startAutoPhotoStandbyTimer() {
        if (kioskState.autoPhotoTimer) clearInterval(kioskState.autoPhotoTimer);
        let sec = 3;
        const secDisplay = document.getElementById('photoAutoSec');
        if (secDisplay) secDisplay.innerText = sec;
        const noticeEl = document.getElementById('photoAutoNotice');
        if (noticeEl) noticeEl.style.display = 'block';

        kioskState.autoPhotoTimer = setInterval(() => {
            sec--;
            if (secDisplay) secDisplay.innerText = sec;
            if (sec <= 0) {
                clearInterval(kioskState.autoPhotoTimer);
                kioskState.autoPhotoTimer = null;
                startPhotoSession();
            }
        }, 1000);
    }

    // Vector QR SVG generator fallback
    function generateSimulatedQrSvg(text) {
        return `<svg viewBox="0 0 100 100" fill="black"><rect width="100" height="100" fill="white"/><rect x="8" y="8" width="28" height="28" fill="black"/><rect x="12" y="12" width="20" height="20" fill="white"/><rect x="16" y="16" width="12" height="12" fill="black"/><rect x="64" y="8" width="28" height="28" fill="black"/><rect x="68" y="12" width="20" height="20" fill="white"/><rect x="72" y="16" width="12" height="12" fill="black"/><rect x="8" y="64" width="28" height="28" fill="black"/><rect x="12" y="68" width="20" height="20" fill="white"/><rect x="16" y="72" width="12" height="12" fill="black"/><rect x="44" y="12" width="8" height="8" fill="black"/><rect x="44" y="28" width="8" height="8" fill="black"/><rect x="44" y="44" width="8" height="8" fill="black"/><rect x="44" y="60" width="8" height="8" fill="black"/><rect x="44" y="76" width="8" height="8" fill="black"/><rect x="60" y="44" width="12" height="12" fill="black"/><rect x="76" y="60" width="16" height="16" fill="black"/><rect x="60" y="76" width="12" height="12" fill="black"/></svg>`;
    }

    // Render E-Voucher tickets dynamically on Screen 06
    function renderScreen06EVouchers() {
        const wrap = document.getElementById('screen06EvouchersWrap');
        if (!wrap) return;

        const emailNote = document.getElementById('screen06EmailNote');
        if (emailNote) {
            emailNote.innerText = `E-ticket telah dikirim ke email: ${kioskState.customer.email || 'email Anda'}`;
        }

        const header = document.getElementById('screen06EvoucherHeader');
        if (header) {
            if (kioskState.tickets.length === 1) {
                header.innerText = 'E-VOUCHER TIKET ANDA (1 TIKET):';
            } else {
                header.innerText = `E-VOUCHER TIKET ANDA (${kioskState.tickets.length} TIKET):`;
            }
        }

        let html = '';
        kioskState.tickets.forEach((t, idx) => {
            const badgeLabel = (kioskState.tickets.length === 1) ? 'E-VOUCHER TIKET RESMI' : `TIKET ${idx + 1} DARI ${kioskState.tickets.length}`;
            html += `
                <div class="kiosk-ticket-card">
                    <div class="ticket-card-badge">${badgeLabel}</div>
                    <div class="ticket-qr-wrap">${t.qr_svg}</div>
                    <div class="ticket-code-text">${t.ticket_code}</div>
                    <div class="ticket-category-pill">${t.category_name}</div>
                    <div style="font-size: 11px; color: #cbd5e1; margin-top: 2px;">${t.visitor_name}</div>
                </div>
            `;
        });
        wrap.innerHTML = html;

        // Configure Photo Experience banner
        const titleEl = document.getElementById('screen06PhotoBannerTitle');
        const descEl = document.getElementById('screen06PhotoBannerDesc');
        const btnStart = document.getElementById('btnStartPhotoSession');

        if (kioskState.totalPhotos > 1) {
            if (titleEl) titleEl.innerText = `Nikmati FREE PHOTO EXPERIENCE! (${kioskState.totalPhotos}x Sesi Foto Suporter)`;
            if (descEl) descEl.innerText = `Anda membeli ${kioskState.quantity} tiket dan berhak atas ${kioskState.totalPhotos}x foto Matchday Collectible resmi!`;
            if (btnStart) btnStart.innerText = `YA, AMBIL FOTO (FOTO 1 DARI ${kioskState.totalPhotos})`;
        } else {
            if (titleEl) titleEl.innerText = 'Nikmati FREE PHOTO EXPERIENCE! (1x Sesi Foto)';
            if (descEl) descEl.innerText = 'Abadikan momen spesial Anda dan dapatkan Matchday Collectible Card resmi.';
            if (btnStart) btnStart.innerText = 'YA, AMBIL FOTO';
        }
    }

    // Start Photo Session from Screen 06
    function startPhotoSession(e) {
        if (e && e.preventDefault) e.preventDefault();
        if (kioskState.autoPhotoTimer) {
            clearInterval(kioskState.autoPhotoTimer);
            kioskState.autoPhotoTimer = null;
        }
        kioskState.currentPhotoIndex = 1;
        try {
            goToScreen(7);
        } catch (err) {
            console.error('goToScreen(7) error:', err);
            document.querySelectorAll('.kiosk-screen').forEach(s => s.classList.remove('active'));
            const s7 = document.getElementById('screen07');
            if (s7) s7.classList.add('active');
            kioskState.currentScreen = 7;
            startCamera();
            updateScreen07UI();
        }
    }

    // QRIS 5-minute Countdown Timer
    function startQrisTimer() {
        let timeLeft = 300;
        const display = document.getElementById('paymentCountdown');
        const timer = setInterval(() => {
            timeLeft--;
            const mins = Math.floor(timeLeft / 60).toString().padStart(2, '0');
            const secs = (timeLeft % 60).toString().padStart(2, '0');
            display.innerText = `${mins}:${secs}`;

            if (timeLeft <= 0 || kioskState.currentScreen !== 5) {
                clearInterval(timer);
            }
        }, 1000);
    }

    // ============================================================
    // CAMERA & PHOTOBOOTH ENGINE (PORTRAIT OUTPUT & PORTRAIT VIEWFINDER)
    // ============================================================

    async function startCamera() {
        const video = document.getElementById('cameraVideo');
        if (!video) return;
        try {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                if (!kioskState.cameraStream) {
                    kioskState.cameraStream = await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' },
                        audio: false
                    });
                }
                video.srcObject = kioskState.cameraStream;
                video.play().catch(e => console.warn('Video play interrupted:', e));
            }
        } catch (err) {
            console.warn('Webcam not accessible:', err);
        }
    }

    function stopCamera() {
        if (kioskState.cameraStream) {
            kioskState.cameraStream.getTracks().forEach(track => track.stop());
            kioskState.cameraStream = null;
        }
    }

    // Update Screen 07 UI for Current Photo Opportunity
    function updateScreen07UI() {
        const pill = document.getElementById('screen07PhotoPill');
        if (pill) {
            pill.innerText = `FOTO ${kioskState.currentPhotoIndex} DARI ${kioskState.totalPhotos}`;
        }

        const subtitle = document.getElementById('screen07Subtitle');
        if (subtitle) {
            subtitle.innerText = (kioskState.totalPhotos > 1) 
                ? `Sesi foto suporter ke-${kioskState.currentPhotoIndex} dari total ${kioskState.totalPhotos}x foto.`
                : 'Berdiri di depan kamera dan posisikan diri Anda di dalam bingkai portrait.';
        }

        const notice = document.getElementById('screen07ChanceNotice');
        if (notice) {
            if (kioskState.totalPhotos === 1) {
                notice.innerText = 'Anda memiliki 1x kesempatan untuk mengambil gambar.';
            } else {
                const sisa = kioskState.totalPhotos - kioskState.currentPhotoIndex;
                notice.innerText = `Kesempatan foto ke-${kioskState.currentPhotoIndex} dari ${kioskState.totalPhotos} (Sisa ${sisa}x lagi).`;
            }
        }

        // Set default name for current photo
        let defaultName = kioskState.customer.name || 'SUPORTER';
        if (kioskState.totalPhotos > 1 && kioskState.currentPhotoIndex > 1) {
            defaultName = kioskState.customer.name ? `${kioskState.customer.name} #${kioskState.currentPhotoIndex}` : `SUPORTER ${kioskState.currentPhotoIndex}`;
        }
        kioskState.currentPhotoFanName = defaultName;

        const nameInput = document.getElementById('screen07FanNameInput');
        if (nameInput) {
            nameInput.value = defaultName;
        }

        const liveName = document.getElementById('cameraLiveFanName');
        if (liveName) {
            liveName.innerText = defaultName.toUpperCase();
        }

        const btn = document.getElementById('btnCapturePhoto');
        if (btn) {
            btn.disabled = false;
            btn.innerText = `AMBIL FOTO (${kioskState.currentPhotoIndex}/${kioskState.totalPhotos})`;
        }
    }

    function onFanNameInputChange(val) {
        kioskState.currentPhotoFanName = val.trim() || 'SUPORTER';
        const liveName = document.getElementById('cameraLiveFanName');
        if (liveName) {
            liveName.innerText = kioskState.currentPhotoFanName.toUpperCase();
        }
    }

    // 3 - 2 - 1 Smile Countdown
    function startPhotoCountdown() {
        const btn = document.getElementById('btnCapturePhoto');
        btn.disabled = true;

        const overlay = document.getElementById('countdownOverlay');
        const numDisplay = document.getElementById('countdownNumber');
        overlay.style.display = 'flex';

        let count = 3;
        numDisplay.innerText = count;

        const timer = setInterval(() => {
            count--;
            if (count > 0) {
                numDisplay.innerText = count;
            } else if (count === 0) {
                numDisplay.innerText = 'SMILE!';
            } else {
                clearInterval(timer);
                overlay.style.display = 'none';
                capturePhotoNow();
                btn.disabled = false;
            }
        }, 1000);
    }

    // Helper to draw centered and scaled logo on canvas
    function drawCenteredLogo(ctx, img, cx, cy, maxDiameter) {
        if (!img || !img.complete || img.naturalWidth === 0) return;
        const aspect = img.naturalWidth / img.naturalHeight;
        let w = maxDiameter;
        let h = maxDiameter;
        if (aspect > 1) {
            h = maxDiameter / aspect;
        } else {
            w = maxDiameter * aspect;
        }
        ctx.drawImage(img, cx - (w / 2), cy - (h / 2), w, h);
    }

    // Capture & Composite PORTRAIT Matchday Collectible Card
    function capturePhotoNow() {
        const flash = document.getElementById('flashOverlay');
        if (flash) {
            flash.classList.add('flash');
            setTimeout(() => flash.classList.remove('flash'), 200);
        }

        const video = document.getElementById('cameraVideo');
        const canvas = document.getElementById('cardCanvas');
        const ctx = canvas.getContext('2d');

        // STRICTLY PORTRAIT CARD (1024 x 1536 px - 2:3 aspect ratio)
        const cardW = 1024;
        const cardH = 1536;
        canvas.width = cardW;
        canvas.height = cardH;

        const primaryColor = KIOSK_CONFIG.primaryColor || '#5A2D82';
        const accentColor = KIOSK_CONFIG.secondaryColor || '#F95700';

        // 1. Draw Background
        if (preloadedBgImg && preloadedBgImg.complete && preloadedBgImg.naturalWidth > 0) {
            ctx.drawImage(preloadedBgImg, 0, 0, cardW, cardH);
        } else {
            const grad = ctx.createLinearGradient(0, 0, 0, cardH);
            grad.addColorStop(0, '#0c0714');
            grad.addColorStop(0.35, primaryColor);
            grad.addColorStop(0.7, '#0f0817');
            grad.addColorStop(1, '#000000');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, cardW, cardH);
        }

        const hasFrame = preloadedFrameImg && preloadedFrameImg.complete && preloadedFrameImg.naturalWidth > 0;

        // 2. Draw Fan Photo from Webcam Feed
        ctx.save();
        if (hasFrame) {
            // Cutout area behind PNG frame
            const photoX = 35;
            const photoY = 45;
            const photoW = cardW - 70; // 954 px
            const photoH = 1180;
            roundedRect(ctx, photoX, photoY, photoW, photoH, 20);
            ctx.clip();

            if (video && video.videoWidth > 0) {
                const vW = video.videoWidth;
                const vH = video.videoHeight;
                const targetRatio = photoW / photoH;
                let srcW = vW, srcH = vH, srcX = 0, srcY = 0;
                if (vW / vH > targetRatio) {
                    srcW = vH * targetRatio;
                    srcX = (vW - srcW) / 2;
                } else {
                    srcH = vW / targetRatio;
                    srcY = (vH - srcH) / 2;
                }
                // Mirrored draw
                ctx.translate(photoX + photoW, photoY);
                ctx.scale(-1, 1);
                ctx.drawImage(video, srcX, srcY, srcW, srcH, 0, 0, photoW, photoH);
            } else {
                ctx.fillStyle = '#1e1b4b';
                ctx.fillRect(photoX, photoY, photoW, photoH);
            }
        } else {
            // Fallback photo box
            const photoY = 160;
            const photoW = cardW - 120;
            const photoH = 920;
            const photoX = 60;
            roundedRect(ctx, photoX, photoY, photoW, photoH, 24);
            ctx.clip();

            if (video && video.videoWidth > 0) {
                const vW = video.videoWidth;
                const vH = video.videoHeight;
                const targetRatio = photoW / photoH;
                let srcW = vW, srcH = vH, srcX = 0, srcY = 0;
                if (vW / vH > targetRatio) {
                    srcW = vH * targetRatio;
                    srcX = (vW - srcW) / 2;
                } else {
                    srcH = vW / targetRatio;
                    srcY = (vH - srcH) / 2;
                }
                ctx.translate(photoX + photoW, photoY);
                ctx.scale(-1, 1);
                ctx.drawImage(video, srcX, srcY, srcW, srcH, 0, 0, photoW, photoH);
            } else {
                ctx.fillStyle = '#1e1b4b';
                ctx.fillRect(photoX, photoY, photoW, photoH);
            }
        }
        ctx.restore();

        // 3. Draw Frame Overlay or Fallback Borders
        if (hasFrame) {
            ctx.drawImage(preloadedFrameImg, 0, 0, cardW, cardH);
        } else {
            ctx.strokeStyle = accentColor;
            ctx.lineWidth = 14;
            roundedRect(ctx, 20, 20, cardW - 40, cardH - 40, 36);
            ctx.stroke();

            // Header text
            if (KIOSK_CONFIG.headerTitle) {
                ctx.fillStyle = '#ffffff';
                ctx.font = '900 34px Outfit';
                ctx.textAlign = 'center';
                ctx.fillText(KIOSK_CONFIG.headerTitle, cardW / 2, 80);
            }

            if (KIOSK_CONFIG.headerSubtitle) {
                ctx.fillStyle = accentColor;
                ctx.font = '700 22px Outfit';
                ctx.textAlign = 'center';
                ctx.fillText(KIOSK_CONFIG.headerSubtitle, cardW / 2, 118);
            }
        }

        const activeName = (kioskState.currentPhotoFanName || kioskState.customer.name || 'BRIAN ARIBOWO').toUpperCase();

        // 4. Draw Club Logos & Real Match Details
        const layout = KIOSK_CONFIG.layoutConfig || {
            league_logo: { x: 15, y: 9, size: 65, visible: true },
            header_slogan: { x: 50, y: 5, font_size: 14, visible: true },
            home_logo: { x: 24, y: 68, size: 85, visible: true },
            home_name: { x: 24, y: 76, font_size: 14, visible: true },
            vs_badge: { x: 50, y: 74, font_size: 22, visible: false },
            away_logo: { x: 76, y: 68, size: 85, visible: true },
            away_name: { x: 76, y: 76, font_size: 14, visible: true },
            category_badge: { x: 50, y: 80, font_size: 16, visible: true },
            fan_name: { x: 50, y: 86, font_size: 18, visible: true },
            match_info: { x: 50, y: 94, font_size: 11, visible: true },
            qr_code: { x: 90.7, y: 82.4, size: 48, visible: true },
            footer_text: { x: 50, y: 97, font_size: 9, visible: true },
        };

        ctx.textBaseline = 'middle';
        ctx.textAlign = 'center';

        if (hasFrame) {
            // Draw League / Penyelenggara Logo
            if (layout.league_logo && layout.league_logo.visible !== false && preloadedLeagueLogo && (preloadedLeagueLogo.complete || preloadedLeagueLogo.naturalWidth > 0)) {
                const lX = (layout.league_logo.x / 100) * cardW;
                const lY = (layout.league_logo.y / 100) * cardH;
                const lSize = ((layout.league_logo.size || 65) / 400) * cardW;
                drawCenteredLogo(ctx, preloadedLeagueLogo, lX, lY, lSize);
            }

            // Draw Home Club Logo (Respect showTeamLogos checkbox)
            if (KIOSK_CONFIG.showTeamLogos && layout.home_logo && layout.home_logo.visible !== false && preloadedHomeLogo && (preloadedHomeLogo.complete || preloadedHomeLogo.naturalWidth > 0)) {
                const hX = (layout.home_logo.x / 100) * cardW;
                const hY = (layout.home_logo.y / 100) * cardH;
                const hSize = ((layout.home_logo.size || 85) / 400) * cardW;
                drawCenteredLogo(ctx, preloadedHomeLogo, hX, hY, hSize);
            }

            // Draw Away Club Logo (Respect showTeamLogos checkbox)
            if (KIOSK_CONFIG.showTeamLogos && layout.away_logo && layout.away_logo.visible !== false && preloadedAwayLogo && (preloadedAwayLogo.complete || preloadedAwayLogo.naturalWidth > 0)) {
                const aX = (layout.away_logo.x / 100) * cardW;
                const aY = (layout.away_logo.y / 100) * cardH;
                const aSize = ((layout.away_logo.size || 85) / 400) * cardW;
                drawCenteredLogo(ctx, preloadedAwayLogo, aX, aY, aSize);
            }

            // Home Team Name (Respect showTeamLogos checkbox)
            if (KIOSK_CONFIG.showTeamLogos && layout.home_name && layout.home_name.visible !== false && KIOSK_CONFIG.homeTeam) {
                const hnX = (layout.home_name.x / 100) * cardW;
                const hnY = (layout.home_name.y / 100) * cardH;
                const fontHn = Math.round(((layout.home_name.font_size || 14) / 400) * cardW);
                ctx.fillStyle = '#ffffff';
                ctx.font = `900 ${fontHn}px Outfit, sans-serif`;
                ctx.shadowColor = 'rgba(0,0,0,0.85)';
                ctx.shadowBlur = 8;
                ctx.fillText(KIOSK_CONFIG.homeTeam, hnX, hnY);
                ctx.shadowBlur = 0;
            }

            // Away Team Name (Respect showTeamLogos checkbox)
            if (KIOSK_CONFIG.showTeamLogos && layout.away_name && layout.away_name.visible !== false && KIOSK_CONFIG.awayTeam) {
                const anX = (layout.away_name.x / 100) * cardW;
                const anY = (layout.away_name.y / 100) * cardH;
                const fontAn = Math.round(((layout.away_name.font_size || 14) / 400) * cardW);
                ctx.fillStyle = '#ffffff';
                ctx.font = `900 ${fontAn}px Outfit, sans-serif`;
                ctx.shadowColor = 'rgba(0,0,0,0.85)';
                ctx.shadowBlur = 8;
                ctx.fillText(KIOSK_CONFIG.awayTeam, anX, anY);
                ctx.shadowBlur = 0;
            }

            // VS Badge (Only draw if explicitly set to visible: true; defaults to false as requested: "di foto nya hilangkan karena saya akan tempel pada desain gambarnya")
            if (KIOSK_CONFIG.showTeamLogos && layout.vs_badge && layout.vs_badge.visible === true) {
                const vsX = (layout.vs_badge.x / 100) * cardW;
                const vsY = (layout.vs_badge.y / 100) * cardH;
                const fontVs = Math.round(((layout.vs_badge.font_size || 22) / 400) * cardW);
                ctx.fillStyle = '#f59e0b';
                ctx.font = `900 ${fontVs}px monospace`;
                ctx.shadowColor = 'rgba(0,0,0,0.85)';
                ctx.shadowBlur = 8;
                ctx.fillText('VS', vsX, vsY);
                ctx.shadowBlur = 0;
            }

            // Header Slogan (Hide completely if both title and subtitle are empty/null)
            const hasHeaderTitle = KIOSK_CONFIG.headerTitle && KIOSK_CONFIG.headerTitle.trim() !== '';
            const hasHeaderSubtitle = KIOSK_CONFIG.headerSubtitle && KIOSK_CONFIG.headerSubtitle.trim() !== '';
            if (layout.header_slogan && layout.header_slogan.visible !== false && (hasHeaderTitle || hasHeaderSubtitle)) {
                const hsX = (layout.header_slogan.x / 100) * cardW;
                const hsY = (layout.header_slogan.y / 100) * cardH;
                const fontHs = Math.round(((layout.header_slogan.font_size || 14) / 400) * cardW);
                ctx.shadowColor = 'rgba(0,0,0,0.85)';
                ctx.shadowBlur = 6;
                if (hasHeaderTitle && hasHeaderSubtitle) {
                    ctx.fillStyle = '#ffffff';
                    ctx.font = `900 ${fontHs}px Outfit, sans-serif`;
                    ctx.fillText(KIOSK_CONFIG.headerTitle, hsX, hsY - (fontHs * 0.45));

                    ctx.fillStyle = accentColor;
                    ctx.font = `900 ${Math.round(fontHs * 0.7)}px Outfit, sans-serif`;
                    ctx.fillText(KIOSK_CONFIG.headerSubtitle, hsX, hsY + (fontHs * 0.55));
                } else if (hasHeaderTitle) {
                    ctx.fillStyle = '#ffffff';
                    ctx.font = `900 ${fontHs}px Outfit, sans-serif`;
                    ctx.fillText(KIOSK_CONFIG.headerTitle, hsX, hsY);
                } else if (hasHeaderSubtitle) {
                    ctx.fillStyle = accentColor;
                    ctx.font = `900 ${Math.round(fontHs * 0.7)}px Outfit, sans-serif`;
                    ctx.fillText(KIOSK_CONFIG.headerSubtitle, hsX, hsY);
                }
                ctx.shadowBlur = 0;
            }

            // Category Badge (Hide if showCategory is false or categoryLabelFormat is null/empty)
            const hasCatFormat = KIOSK_CONFIG.categoryLabelFormat && KIOSK_CONFIG.categoryLabelFormat.trim() !== '';
            if (KIOSK_CONFIG.showCategory && hasCatFormat && layout.category_badge && layout.category_badge.visible !== false) {
                const catX = (layout.category_badge.x / 100) * cardW;
                const catY = (layout.category_badge.y / 100) * cardH;
                const fontCat = Math.round(((layout.category_badge.font_size || 10) / 400) * cardW);
                const catName = (kioskState.selectedCategory && kioskState.selectedCategory.name) ? kioskState.selectedCategory.name.toUpperCase() : 'VIP BARAT';
                let catText = KIOSK_CONFIG.categoryLabelFormat.replace('{category}', catName);
                if (KIOSK_CONFIG.badgeText && KIOSK_CONFIG.badgeText.trim() !== '') {
                    catText = catText.replace('{badge}', KIOSK_CONFIG.badgeText.trim());
                } else {
                    catText = catText.replace('{badge}', '').trim();
                }
                ctx.fillStyle = accentColor;
                ctx.font = `900 ${fontCat}px Outfit, sans-serif`;
                ctx.shadowColor = 'rgba(0,0,0,0.85)';
                ctx.shadowBlur = 6;
                ctx.fillText(catText, catX, catY);
                ctx.shadowBlur = 0;
            }

            // Fan Name (With styled pill badge matching Layout Editor)
            if (KIOSK_CONFIG.showFanName && layout.fan_name && layout.fan_name.visible !== false) {
                const fnX = (layout.fan_name.x / 100) * cardW;
                const fnY = (layout.fan_name.y / 100) * cardH;
                const fontFn = Math.round(((layout.fan_name.font_size || 18) / 400) * cardW);
                ctx.font = `900 ${fontFn}px Outfit, sans-serif`;

                const metrics = ctx.measureText(activeName);
                const padX = fontFn * 0.9;
                const padY = fontFn * 0.45;
                const bW = metrics.width + padX * 2;
                const bH = fontFn + padY * 2;

                ctx.save();
                ctx.shadowColor = 'rgba(0,0,0,0.7)';
                ctx.shadowBlur = 14;
                ctx.shadowOffsetY = 4;
                ctx.fillStyle = primaryColor;
                roundedRect(ctx, fnX - (bW / 2), fnY - (bH / 2), bW, bH, bH / 2);
                ctx.fill();
                ctx.strokeStyle = accentColor;
                ctx.lineWidth = 3;
                ctx.stroke();
                ctx.restore();

                ctx.fillStyle = '#ffffff';
                ctx.shadowColor = 'rgba(0,0,0,0.85)';
                ctx.shadowBlur = 6;
                ctx.fillText(activeName, fnX, fnY);
                ctx.shadowBlur = 0;
            }

            // Match Details (Date & Venue only - do not duplicate header title)
            if (KIOSK_CONFIG.showMatchInfo && layout.match_info && layout.match_info.visible !== false) {
                const miParts = [];
                if (KIOSK_CONFIG.matchDate) miParts.push(KIOSK_CONFIG.matchDate);
                if (KIOSK_CONFIG.venue) miParts.push(KIOSK_CONFIG.venue);
                const matchInfoText = miParts.join('  •  ').toUpperCase();
                if (matchInfoText) {
                    const miX = (layout.match_info.x / 100) * cardW;
                    const miY = (layout.match_info.y / 100) * cardH;
                    const fontMi = Math.round(((layout.match_info.font_size || 11) / 400) * cardW);
                    ctx.fillStyle = '#f1f5f9';
                    ctx.font = `800 ${fontMi}px Outfit, sans-serif`;
                    ctx.shadowColor = 'rgba(0,0,0,0.85)';
                    ctx.shadowBlur = 6;
                    ctx.fillText(matchInfoText, miX, miY);
                    ctx.shadowBlur = 0;
                }
            }

            // QR Code (Respect showQr checkbox)
            if (KIOSK_CONFIG.showQr && layout.qr_code && layout.qr_code.visible !== false) {
                const qrX = (layout.qr_code.x / 100) * cardW;
                const qrY = (layout.qr_code.y / 100) * cardH;
                const qrSize = Math.round(((layout.qr_code.size || 48) / 400) * cardW);
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(qrX - (qrSize / 2), qrY - (qrSize / 2), qrSize, qrSize);
                ctx.fillStyle = '#000000';
                const cell = qrSize / 5;
                ctx.fillRect(qrX - (qrSize / 2) + cell * 0.5, qrY - (qrSize / 2) + cell * 0.5, cell * 1.5, cell * 1.5);
                ctx.fillRect(qrX + (qrSize / 2) - cell * 2, qrY - (qrSize / 2) + cell * 0.5, cell * 1.5, cell * 1.5);
                ctx.fillRect(qrX - (qrSize / 2) + cell * 0.5, qrY + (qrSize / 2) - cell * 2, cell * 1.5, cell * 1.5);
                ctx.fillStyle = '#ffffff';
                ctx.font = '800 11px Outfit, sans-serif';
                ctx.shadowColor = 'rgba(0,0,0,0.8)';
                ctx.shadowBlur = 4;
                ctx.fillText('SCAN', qrX, qrY + (qrSize / 2) + 14);
                ctx.shadowBlur = 0;
            }

            // Footer Text (Hide if footerText is null or empty)
            const hasFooterText = KIOSK_CONFIG.footerText && KIOSK_CONFIG.footerText.trim() !== '';
            if (hasFooterText && layout.footer_text && layout.footer_text.visible !== false) {
                const ftX = (layout.footer_text.x / 100) * cardW;
                const ftY = (layout.footer_text.y / 100) * cardH;
                const fontFt = Math.round(((layout.footer_text.font_size || 9) / 400) * cardW);
                ctx.fillStyle = '#cbd5e1';
                ctx.font = `700 ${fontFt}px Outfit, sans-serif`;
                ctx.shadowColor = 'rgba(0,0,0,0.8)';
                ctx.shadowBlur = 4;
                ctx.fillText(KIOSK_CONFIG.footerText.trim(), ftX, ftY);
                ctx.shadowBlur = 0;
            }
        } else {
            // Fallback Matchday Card Footer (when no custom frame)
            const footerY = 1120;
            ctx.fillStyle = 'rgba(10, 6, 18, 0.94)';
            roundedRect(ctx, 40, footerY, cardW - 80, 350, 20);
            ctx.fill();
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
            ctx.lineWidth = 2;
            ctx.stroke();

            // Draw Logos and match details on fallback footer ONLY if showTeamLogos is checked
            if (KIOSK_CONFIG.showTeamLogos) {
                const homeLogoX = (cardW / 2) - 140;
                const awayLogoX = (cardW / 2) + 140;
                const logosCenterY = footerY + 55;
                if (preloadedHomeLogo && preloadedHomeLogo.complete) {
                    drawCenteredLogo(ctx, preloadedHomeLogo, homeLogoX, logosCenterY, 70);
                }
                if (preloadedAwayLogo && preloadedAwayLogo.complete) {
                    drawCenteredLogo(ctx, preloadedAwayLogo, awayLogoX, logosCenterY, 70);
                }
                ctx.fillStyle = '#f59e0b';
                ctx.font = '900 24px monospace';
                ctx.fillText('VS', cardW / 2, logosCenterY);

                // Match Title (Persita VS Persib)
                ctx.fillStyle = '#ffffff';
                ctx.font = '900 34px Outfit, sans-serif';
                ctx.fillText(`${KIOSK_CONFIG.homeTeam}  VS  ${KIOSK_CONFIG.awayTeam}`, cardW / 2, footerY + 115);
            }

            // Date & Venue (Respect showMatchInfo)
            if (KIOSK_CONFIG.showMatchInfo) {
                ctx.fillStyle = '#cbd5e1';
                ctx.font = '600 20px Outfit, sans-serif';
                ctx.fillText(`${KIOSK_CONFIG.matchDate}  |  ${KIOSK_CONFIG.venue}`, cardW / 2, footerY + 148);
            }

            // Fan Name Badge Banner (Respect showFanName)
            if (KIOSK_CONFIG.showFanName) {
                const badgeY = footerY + 170;
                const bannerW = cardW - 200;
                const bannerH = 64;
                const bannerX = 100;
                ctx.fillStyle = primaryColor;
                roundedRect(ctx, bannerX, badgeY, bannerW, bannerH, 16);
                ctx.fill();
                ctx.strokeStyle = accentColor;
                ctx.lineWidth = 3;
                ctx.stroke();

                ctx.fillStyle = '#ffffff';
                ctx.font = '900 28px Outfit, sans-serif';
                ctx.fillText(activeName, cardW / 2, badgeY + bannerH / 2);
            }

            // Category Sub-badge (Respect showCategory and categoryLabelFormat)
            const hasCatFmt = KIOSK_CONFIG.categoryLabelFormat && KIOSK_CONFIG.categoryLabelFormat.trim() !== '';
            if (KIOSK_CONFIG.showCategory && hasCatFmt) {
                const catName = (kioskState.selectedCategory && kioskState.selectedCategory.name) ? kioskState.selectedCategory.name.toUpperCase() : 'VIP BARAT';
                let catText = KIOSK_CONFIG.categoryLabelFormat.replace('{category}', catName);
                if (KIOSK_CONFIG.badgeText && KIOSK_CONFIG.badgeText.trim() !== '') {
                    catText = catText.replace('{badge}', KIOSK_CONFIG.badgeText.trim());
                } else {
                    catText = catText.replace('{badge}', '').trim();
                }
                ctx.fillStyle = accentColor;
                ctx.font = '800 18px Outfit, sans-serif';
                ctx.fillText(catText, cardW / 2, footerY + 275);
            }

            // Bottom Slogan (Hide if footerText is null or empty)
            const hasFtText = KIOSK_CONFIG.footerText && KIOSK_CONFIG.footerText.trim() !== '';
            if (hasFtText) {
                ctx.fillStyle = '#cbd5e1';
                ctx.font = '700 16px Outfit, sans-serif';
                ctx.textAlign = 'left';
                ctx.fillText(KIOSK_CONFIG.footerText.trim(), 60, footerY + 315);
                ctx.textAlign = 'center';
            }

            // QR Code area (Respect showQr)
            if (KIOSK_CONFIG.showQr) {
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(cardW - 140, footerY + 230, 80, 80);
                ctx.fillStyle = '#000000';
                ctx.font = 'bold 10px monospace';
                ctx.fillText('QR CODE', cardW - 100, footerY + 270);
                ctx.fillStyle = '#ffffff';
                ctx.font = '800 10px Outfit, sans-serif';
                ctx.fillText('SCAN TO DOWNLOAD', cardW - 100, footerY + 325);
            }
        }

        // Export generated portrait card
        kioskState.generatedCardData = canvas.toDataURL('image/jpeg', 0.94);

        // Save in capturedPhotos history
        kioskState.capturedPhotos.push({
            index: kioskState.currentPhotoIndex,
            fanName: activeName,
            dataUrl: kioskState.generatedCardData
        });

        // Save card to backend & dispatch email
        savePhotoToBackend(kioskState.generatedCardData, activeName);

        // Update Screen 08 UI and advance
        updateScreen08UI();
        goToScreen(8);
    }

    // Update Screen 08 for Multi-Photo Stepping
    function updateScreen08UI() {
        const previewImg = document.getElementById('finalCardPreview');
        if (previewImg && kioskState.generatedCardData) {
            previewImg.src = kioskState.generatedCardData;
        }

        const titleEl = document.getElementById('screen08StatusTitle');
        const descEl = document.getElementById('screen08StatusDesc');
        if (titleEl) {
            titleEl.innerText = `Foto ${kioskState.currentPhotoIndex} dari ${kioskState.totalPhotos} Siap!`;
        }
        if (descEl) {
            descEl.innerText = `Foto suporter ke-${kioskState.currentPhotoIndex} berhasil diproses dan dikirim ke email.`;
        }

        // Render photo session pills
        const pillsWrap = document.getElementById('screen08PhotoPills');
        if (pillsWrap) {
            let pillsHtml = '';
            for (let i = 1; i <= kioskState.totalPhotos; i++) {
                const isDone = i <= kioskState.currentPhotoIndex;
                const bg = isDone ? 'background: #22c55e; color: #ffffff;' : 'background: #334155; color: #94a3b8;';
                pillsHtml += `<span style="padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 800; ${bg}">Foto #${i} ${isDone ? '✔' : ''}</span>`;
            }
            pillsWrap.innerHTML = pillsHtml;
        }

        // Action buttons
        const actionsWrap = document.getElementById('screen08Actions');
        if (actionsWrap) {
            if (kioskState.currentPhotoIndex < kioskState.totalPhotos) {
                const nextIndex = kioskState.currentPhotoIndex + 1;
                actionsWrap.innerHTML = `
                    <button type="button" class="btn-kiosk-primary" onclick="prepareNextPhoto()" style="background: var(--primary-orange); min-height: 52px;">
                        LANJUT AMBIL FOTO KE-${nextIndex} DARI ${kioskState.totalPhotos}
                    </button>
                    <button type="button" class="btn-kiosk-secondary" onclick="goToScreen(9)" style="min-height: 48px;">
                        SELESAI (LEWATI SISA FOTO)
                    </button>
                `;
            } else {
                actionsWrap.innerHTML = `
                    <button type="button" class="btn-kiosk-primary" onclick="goToScreen(9)" style="background: var(--primary-orange); min-height: 52px;">
                        SELESAI (LIHAT SEMUA TIKET & FOTO)
                    </button>
                `;
            }
        }
    }

    // Step to Next Photo
    function prepareNextPhoto() {
        kioskState.currentPhotoIndex++;
        updateScreen07UI();
        goToScreen(7);
    }

    // Render Screen 09 Finish Summary (All E-Vouchers and All Photos)
    function renderScreen09Summary() {
        const summaryText = document.getElementById('screen09SummaryText');
        if (summaryText) {
            summaryText.innerHTML = `Semua E-ticket (${kioskState.tickets.length} tiket) dan ${kioskState.capturedPhotos.length} foto Matchday Collectible telah dikirim ke email <strong>${kioskState.customer.email || 'Anda'}</strong>.<br>Selamat menikmati pertandingan!`;
        }

        // Render all ticket QR cards in Screen 09
        const grid = document.getElementById('screen09EvouchersGrid');
        if (grid) {
            let html = '';
            kioskState.tickets.forEach((t, idx) => {
                const badgeLabel = (kioskState.tickets.length === 1) ? 'E-VOUCHER TIKET RESMI' : `TIKET ${idx + 1} DARI ${kioskState.tickets.length}`;
                html += `
                    <div class="kiosk-ticket-card" style="min-width: 170px;">
                        <div class="ticket-card-badge">${badgeLabel}</div>
                        <div class="ticket-qr-wrap" style="padding: 6px;">${t.qr_svg}</div>
                        <div class="ticket-code-text" style="font-size: 12px;">${t.ticket_code}</div>
                        <div class="ticket-category-pill">${t.category_name}</div>
                        <div style="font-size: 10px; color: #cbd5e1; margin-top: 2px;">${t.visitor_name}</div>
                    </div>
                `;
            });
            grid.innerHTML = html;
        }

        // Render all captured collectible cards in Screen 09
        const photosSec = document.getElementById('screen09PhotosSection');
        const photosGrid = document.getElementById('screen09PhotosGrid');
        if (photosSec && photosGrid) {
            if (kioskState.capturedPhotos.length > 0) {
                photosSec.style.display = 'block';
                let pHtml = '';
                kioskState.capturedPhotos.forEach(p => {
                    pHtml += `
                        <div style="width: 110px; aspect-ratio: 2/3; border-radius: 10px; overflow: hidden; border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 4px 10px rgba(0,0,0,0.5);">
                            <img src="${p.dataUrl}" alt="Photo ${p.index}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    `;
                });
                photosGrid.innerHTML = pHtml;
            } else {
                photosSec.style.display = 'none';
            }
        }
    }

    // Rounded rectangle helper for Canvas
    function roundedRect(ctx, x, y, width, height, radius) {
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + width - radius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        ctx.lineTo(x + width, y + height - radius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        ctx.lineTo(x + radius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
    }

    // Save photo to server
    async function savePhotoToBackend(cardBase64, customFanName) {
        try {
            const res = await fetch(KIOSK_CONFIG.routes.savePhoto, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    event_id: KIOSK_CONFIG.eventId,
                    transaction_id: kioskState.transaction ? kioskState.transaction.transaction_id : null,
                    customer_name: customFanName || kioskState.customer.name,
                    customer_email: kioskState.customer.email,
                    customer_phone: kioskState.customer.phone,
                    category_name: kioskState.selectedCategory ? kioskState.selectedCategory.name : null,
                    card_data: cardBase64
                })
            });
            const data = await res.json();
            console.log('Photo stored successfully:', data);
        } catch (e) {
            console.warn('Error saving photo:', e);
        }
    }

    // Auto Reset Timer on Screen 09 (15 Seconds)
    function startAutoResetTimer() {
        let sec = 15;
        const display = document.getElementById('autoResetTimer');
        if (display) display.innerText = sec;

        clearInterval(kioskState.autoResetInterval);
        kioskState.autoResetInterval = setInterval(() => {
            sec--;
            if (display) display.innerText = sec;
            if (sec <= 0) {
                clearInterval(kioskState.autoResetInterval);
                resetToWelcome();
            }
        }, 1000);
    }

    function resetToWelcome() {
        clearInterval(kioskState.pollInterval);
        clearInterval(kioskState.autoResetInterval);
        if (kioskState.autoPhotoTimer) {
            clearInterval(kioskState.autoPhotoTimer);
            kioskState.autoPhotoTimer = null;
        }
        stopCamera();
        kioskState.isPaid = false;
        kioskState.isProcessingPayment = false;
        kioskState.quantity = 1;
        kioskState.totalPhotos = 1;
        kioskState.currentPhotoIndex = 1;
        kioskState.currentPhotoFanName = '';
        kioskState.capturedPhotos = [];
        kioskState.tickets = [];
        document.getElementById('qtyDisplay').innerText = '1';
        kioskState.transaction = null;
        kioskState.customer = { name: '', email: '', phone: '', nik: '', gender: '', birthdate: '' };
        const form = document.getElementById('userDataForm');
        if (form) form.reset();
        const btnSimulate = document.getElementById('btnSimulatePay');
        if (btnSimulate) {
            btnSimulate.disabled = false;
            btnSimulate.style.opacity = '1';
            btnSimulate.style.cursor = 'pointer';
            btnSimulate.innerHTML = '&check; Simulasi Bayar Instan (Test Mode)';
        }
        goToScreen(1);
    }

    // Modals
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.add('active');
    }

    function openRedeemModal() {
        openModal('redeemModal');
    }

    function openMerchModal() {
        openModal('merchModal');
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove('active');
    }

    async function lookupTicketRedeem() {
        const code = document.getElementById('redeemInputCode').value.trim();
        if (!code) return;

        const box = document.getElementById('redeemResultBox');
        box.style.display = 'block';
        box.innerHTML = '<div style="color: #cbd5e1;">Mencari data tiket...</div>';

        try {
            const res = await fetch(KIOSK_CONFIG.routes.redeemLookup, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code: code })
            });
            const data = await res.json();

            if (data.success) {
                const statusColor = data.is_redeemed ? '#f87171' : '#22c55e';
                const statusText = data.is_redeemed ? `SUDAH DITUKAR (${data.redeemed_at})` : 'TIKET VALID - SIAP DITUKAR';

                box.innerHTML = `
                    <div style="background: ${statusColor}; color: #000000; display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 800; margin-bottom: 8px;">
                        ${statusText}
                    </div>
                    <div style="font-size: 16px; font-weight: 800; color: #ffffff;">${data.event_name}</div>
                    <div style="font-size: 14px; color: #94a3b8; margin: 4px 0;">Pemegang: <strong>${data.visitor_name}</strong></div>
                    <div style="font-size: 14px; font-weight: 700; color: var(--primary-orange);">Kategori: ${data.category_name}</div>
                `;
            } else {
                box.innerHTML = `<div style="color: #f87171; font-weight: 700;">${data.message || 'Tiket tidak ditemukan.'}</div>`;
            }
        } catch (e) {
            box.innerHTML = '<div style="color: #f87171;">Terjadi kesalahan saat mencari tiket.</div>';
        }
    }

    // Close modal when clicking backdrop
    window.addEventListener('click', (e) => {
        if (e.target && e.target.classList && e.target.classList.contains('kiosk-modal-backdrop')) {
            e.target.classList.remove('active');
        }
    });

    // Keyboard navigation (R-32 accessibility)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal('redeemModal');
            closeModal('merchModal');
            closeModal('termsModal');
            closeModal('privacyModal');
        }
    });
</script>

</body>
</html>
