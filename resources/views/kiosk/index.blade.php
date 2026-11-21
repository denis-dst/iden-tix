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
            gap: 10px;
            width: 100%;
            max-width: 540px;
            margin-bottom: 24px;
            text-align: left;
            font-size: 12px;
            color: #cbd5e1;
            cursor: pointer;
        }

        .terms-checkbox-wrap input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-orange);
            cursor: pointer;
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

        /* Camera Screen (Screen 07) */
        .camera-container {
            width: 100%;
            max-width: 460px;
            height: 340px;
            background: #090d16;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .camera-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Mirror camera */
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

        /* Photo Preview (Screen 08) */
        .photo-preview-grid {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 32px;
            margin-bottom: 24px;
        }

        .collectible-card-container {
            width: 260px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7);
            border: 2px solid rgba(255, 255, 255, 0.15);
            background: #111827;
            position: relative;
        }

        .collectible-card-container img, .collectible-card-container canvas {
            display: block;
            width: 100%;
            height: auto;
        }

        .photo-status-sidebar {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            max-width: 220px;
        }

        .photo-status-sidebar svg {
            width: 56px;
            height: 56px;
            fill: var(--success-green);
            margin-bottom: 12px;
        }

        .photo-status-sidebar h5 {
            font-size: 16px;
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
            padding: 36px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 100%;
            max-width: 460px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .finish-title {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 1.5px;
            color: #ffffff;
            margin: 16px 0 8px 0;
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
                <h2 class="match-title">{{ $event->name ?? 'PERSITA VS PERSIJA' }}</h2>
                <div class="match-info-meta">
                    {{ $event->event_start_date ? $event->event_start_date->translatedFormat('d F Y | H:i') . ' WIB' : '20 OKTOBER 2026 | 15.30 WIB' }}
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

                <label class="terms-checkbox-wrap">
                    <input type="checkbox" id="termsCheck" required>
                    <span>Saya menyetujui Syarat & Ketentuan dan Kebijakan Privasi.</span>
                </label>

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
            <button type="button" class="btn-test-pay" onclick="simulatePaymentSuccess()">
                &check; Simulasi Bayar Instan (Test Mode)
            </button>
        </div>

        <!-- ==========================================
             SCREEN 06: KONFIRMASI PHOTO EXPERIENCE
             ========================================== -->
        <div class="kiosk-screen" id="screen06">
            <svg class="check-icon-large" viewBox="0 0 24 24" fill="#22c55e">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            <p class="email-sent-note">E-ticket telah dikirim ke email Anda.</p>

            <div class="free-experience-banner">
                <h4>Nikmati FREE PHOTO EXPERIENCE!</h4>
                <p>Abadikan momen spesial Anda dan dapatkan Matchday Collectible Card resmi.</p>
            </div>

            <div class="action-buttons-wrap">
                <button type="button" class="btn-kiosk-primary" onclick="goToScreen(7)" style="background: var(--primary-orange);">
                    YA, AMBIL FOTO
                </button>
                <button type="button" class="btn-kiosk-secondary" onclick="goToScreen(9)">
                    TIDAK, LEWATI
                </button>
            </div>
        </div>

        <!-- ==========================================
             SCREEN 07: TAKE PHOTO – CAMERA & COUNTDOWN
             ========================================== -->
        <div class="kiosk-screen" id="screen07">
            <h2 class="welcome-title" style="font-size: 24px; margin-bottom: 6px;">SIAP UNTUK FOTO?</h2>
            <p style="font-size: 14px; font-weight: 600; color: #94a3b8; margin-bottom: 16px;">Berdiri di depan kamera dan lihat ke layar.</p>

            <div class="camera-container">
                <video id="cameraVideo" class="camera-video" autoplay playsinline muted></video>

                <!-- Viewfinder Guides -->
                <div class="viewfinder-corner vf-top-left"></div>
                <div class="viewfinder-corner vf-top-right"></div>
                <div class="viewfinder-corner vf-bottom-left"></div>
                <div class="viewfinder-corner vf-bottom-right"></div>

                <!-- Animated Countdown Circle -->
                <div class="countdown-overlay" id="countdownOverlay">
                    <div class="countdown-circle" id="countdownNumber">3</div>
                </div>
            </div>

            <div style="font-size: 14px; font-weight: 800; letter-spacing: 2px; color: #cbd5e1; margin-bottom: 6px;">
                3 &bull; 2 &bull; 1 &bull; SMILE!
            </div>
            <div style="font-size: 12px; color: #94a3b8; margin-bottom: 20px;">
                Anda memiliki 1x kesempatan untuk mengambil gambar
            </div>

            <div class="action-buttons-wrap">
                <button type="button" class="btn-kiosk-primary" id="btnCapturePhoto" onclick="startPhotoCountdown()" style="max-width: 280px;">
                    AMBIL PHOTO
                </button>
            </div>
        </div>

        <!-- ==========================================
             SCREEN 08: PHOTO PREVIEW & EMAIL NOTIFICATION
             ========================================== -->
        <div class="kiosk-screen" id="screen08">
            <h2 class="welcome-title" style="font-size: 24px; margin-bottom: 20px;">FOTO KAMU SUDAH SIAP!</h2>

            <div class="photo-preview-grid">
                <!-- Matchday Collectible Card Preview -->
                <div class="collectible-card-container">
                    <img id="finalCardPreview" src="{{ asset('images/kiosk/matchday_card_sample.jpg') }}" alt="Matchday Collectible Preview">
                </div>

                <!-- Status Sidebar -->
                <div class="photo-status-sidebar">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <h5>Foto berhasil dikirim ke email Anda.</h5>
                    <p>Silakan periksa inbox email Anda untuk mengunduh kartu ini.</p>
                </div>
            </div>

            <div style="font-size: 13px; font-weight: 800; letter-spacing: 1px; color: #94a3b8; margin-bottom: 20px;">
                PHOTO PREVIEW
            </div>

            <div class="action-buttons-wrap">
                <button type="button" class="btn-kiosk-primary" onclick="goToScreen(9)" style="max-width: 280px;">
                    SELESAI
                </button>
            </div>
        </div>

        <!-- ==========================================
             SCREEN 09: SELESAI – AUTO RESET MENU AWAL
             ========================================== -->
        <div class="kiosk-screen" id="screen09">
            <div class="finish-card">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="#22c55e">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <h2 class="finish-title">TERIMA KASIH!</h2>
                <p class="finish-desc">
                    E-ticket dan foto telah dikirim ke email Anda.<br>
                    Selamat menikmati event!
                </p>

                <button type="button" class="btn-kiosk-primary" onclick="resetToWelcome()" style="width: 100%; max-width: 320px;">
                    KEMBALI KE MENU AWAL... (<span id="autoResetTimer">5</span>s)
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
     KIOSK JAVASCRIPT ENGINE
     ========================================== -->
<script>
    // Configuration & State
    const KIOSK_CONFIG = {
        tenantSlug: '{{ $tenant->slug }}',
        eventId: {{ $event->id }},
        eventName: @json($event->name ?? 'PERSITA VS PERSIJA'),
        matchDate: @json($event->event_start_date ? $event->event_start_date->translatedFormat('d F Y | H:i') . ' WIB' : '20 OKTOBER 2026 | 15.30 WIB'),
        venue: @json($event->venue ?? 'STADION INDOMILK ARENA'),
        homeTeam: @json($event->home_team_name ?? 'PERSITA'),
        awayTeam: @json($event->away_team_name ?? 'PERSIJA'),
        primaryColor: @json($photoboothTemplate->primary_color ?? '#5A2D82'),
        secondaryColor: @json($photoboothTemplate->secondary_color ?? '#F95700'),
        headerTitle: @json($photoboothTemplate->header_title ?? 'I LEAGUE 2026/2027'),
        headerSubtitle: @json($photoboothTemplate->header_subtitle ?? 'FOOTBALL UNITES US'),
        badgeText: @json($photoboothTemplate->badge_text ?? 'MATCHDAY COLLECTIBLE'),
        categoryLabelFormat: @json($photoboothTemplate->category_label_format ?? '{category} • MATCHDAY COLLECTIBLE'),
        footerText: @json($photoboothTemplate->footer_text ?? 'SATU LIGA, SATU SEMANGAT'),
        showCategory: {{ ($photoboothTemplate->show_category ?? true) ? 'true' : 'false' }},
        showFanName: {{ ($photoboothTemplate->show_fan_name ?? true) ? 'true' : 'false' }},
        showQr: {{ ($photoboothTemplate->show_qr ?? true) ? 'true' : 'false' }},
        sampleCardUrl: '{{ asset('images/kiosk/matchday_card_sample.jpg') }}',
        routes: {
            checkout: '{{ route('kiosk.checkout', $tenant->slug) }}',
            status: '{{ url('kiosk/' . $tenant->slug . '/status') }}',
            simulatePay: '{{ url('kiosk/' . $tenant->slug . '/simulate-pay') }}',
            savePhoto: '{{ route('kiosk.photo.save', $tenant->slug) }}',
            redeemLookup: '{{ route('kiosk.redeem.lookup', $tenant->slug) }}'
        }
    };

    let kioskState = {
        currentScreen: 1,
        selectedCategory: null,
        quantity: 1,
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
        autoResetInterval: null
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
            document.getElementById('stepIndicator').innerText = `SCREEN 0${screenNumber}/09`;
        }

        // Screen specific hooks
        if (screenNumber === 4) {
            calculateTotal();
        } else if (screenNumber === 7) {
            startCamera();
        } else if (screenNumber !== 7) {
            stopCamera();
        }

        if (screenNumber === 9) {
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

    // Quantity Stepper
    function updateQty(delta) {
        let newQty = kioskState.quantity + delta;
        if (newQty >= 1 && newQty <= 10) {
            kioskState.quantity = newQty;
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
                    onPaymentSuccess();
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
                onPaymentSuccess();
            }
        } catch (e) {
            console.error('Simulate payment error:', e);
        }
    }

    // Payment Success Transition
    function onPaymentSuccess() {
        document.getElementById('waitingStatusText').innerText = 'PEMBAYARAN BERHASIL!';
        setTimeout(() => {
            goToScreen(6);
        }, 1200);
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
    // CAMERA & PHOTOBOOTH ENGINE
    // ============================================================

    async function startCamera() {
        const video = document.getElementById('cameraVideo');
        try {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                kioskState.cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' },
                    audio: false
                });
                video.srcObject = kioskState.cameraStream;
                video.play();
            }
        } catch (err) {
            console.warn('Webcam not accessible, using placeholder frame:', err);
        }
    }

    function stopCamera() {
        if (kioskState.cameraStream) {
            kioskState.cameraStream.getTracks().forEach(track => track.stop());
            kioskState.cameraStream = null;
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

    // Capture & Composite Card Image
    function capturePhotoNow() {
        // 1. Flash effect
        const flash = document.getElementById('flashOverlay');
        flash.classList.add('flash');
        setTimeout(() => flash.classList.remove('flash'), 200);

        const video = document.getElementById('cameraVideo');
        const canvas = document.getElementById('cardCanvas');
        const ctx = canvas.getContext('2d');

        const cardW = 1024;
        const cardH = 1536;
        canvas.width = cardW;
        canvas.height = cardH;

        // Draw Club Theme Background Gradient
        const primaryColor = KIOSK_CONFIG.primaryColor || '#5A2D82';
        const accentColor = KIOSK_CONFIG.secondaryColor || '#F95700';

        const grad = ctx.createLinearGradient(0, 0, 0, cardH);
        grad.addColorStop(0, '#110b1a');
        grad.addColorStop(0.35, primaryColor);
        grad.addColorStop(0.7, '#0f0817');
        grad.addColorStop(1, '#000000');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, cardW, cardH);

        // Draw Fan Captured Photo in upper/center area
        const photoY = 160;
        const photoW = cardW - 120; // 904
        const photoH = 920;
        const photoX = 60;

        ctx.save();
        // Clip rounded rectangle for fan photo
        roundedRect(ctx, photoX, photoY, photoW, photoH, 24);
        ctx.clip();

        if (video.videoWidth > 0) {
            // Mirror image draw
            ctx.translate(photoX + photoW, photoY);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, photoW, photoH);
        } else {
            // Fallback draw sample fan photo
            ctx.fillStyle = '#1e1b4b';
            ctx.fillRect(0, 0, photoW, photoH);
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 36px Outfit';
            ctx.textAlign = 'center';
            ctx.fillText('MATCHDAY FAN PHOTO', photoX + (photoW / 2), photoY + (photoH / 2));
        }
        ctx.restore();

        // Card Outer Border
        ctx.strokeStyle = accentColor;
        ctx.lineWidth = 14;
        roundedRect(ctx, 20, 20, cardW - 40, cardH - 40, 36);
        ctx.stroke();

        // Header Branding
        ctx.fillStyle = '#ffffff';
        ctx.font = '900 34px Outfit';
        ctx.textAlign = 'center';
        ctx.fillText(KIOSK_CONFIG.headerTitle || 'I LEAGUE 2026/2027', cardW / 2, 80);

        ctx.fillStyle = accentColor;
        ctx.font = '700 22px Outfit';
        ctx.fillText(KIOSK_CONFIG.headerSubtitle || 'FOOTBALL UNITES US', cardW / 2, 118);

        // Bottom Info Banner / Matchday Card Footer
        const footerY = 1120;
        ctx.fillStyle = 'rgba(10, 6, 18, 0.94)';
        roundedRect(ctx, 40, footerY, cardW - 80, 350, 20);
        ctx.fill();
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Match Title (Persita VS Persija)
        ctx.fillStyle = '#ffffff';
        ctx.font = '900 42px Outfit';
        ctx.textAlign = 'center';
        ctx.fillText(`${KIOSK_CONFIG.homeTeam}  VS  ${KIOSK_CONFIG.awayTeam}`, cardW / 2, footerY + 60);

        // Date & Venue
        ctx.fillStyle = '#cbd5e1';
        ctx.font = '600 20px Outfit';
        ctx.fillText(`${KIOSK_CONFIG.matchDate}  |  ${KIOSK_CONFIG.venue}`, cardW / 2, footerY + 100);

        // Fan Name Badge Banner (Chevron / Ribbon Style)
        const badgeY = footerY + 125;
        const bannerW = cardW - 200;
        const bannerH = 72;
        const bannerX = 100;

        // Draw Chevron Ribbon
        ctx.beginPath();
        ctx.moveTo(bannerX, badgeY + bannerH / 2);
        ctx.lineTo(bannerX + 30, badgeY);
        ctx.lineTo(bannerX + bannerW - 30, badgeY);
        ctx.lineTo(bannerX + bannerW, badgeY + bannerH / 2);
        ctx.lineTo(bannerX + bannerW - 30, badgeY + bannerH);
        ctx.lineTo(bannerX + 30, badgeY + bannerH);
        ctx.closePath();
        ctx.fillStyle = primaryColor;
        ctx.fill();
        ctx.strokeStyle = accentColor;
        ctx.lineWidth = 3;
        ctx.stroke();

        if (KIOSK_CONFIG.showFanName) {
            ctx.fillStyle = '#ffffff';
            ctx.font = '900 28px Outfit';
            ctx.fillText((kioskState.customer.name || 'BRIAN ARIBOWO').toUpperCase(), cardW / 2, badgeY + 45);
        }

        // Dynamic Category / Collectible Sub-badge
        let catText = KIOSK_CONFIG.badgeText || 'MATCHDAY COLLECTIBLE';
        if (KIOSK_CONFIG.showCategory) {
            const catName = (kioskState.selectedCategory && kioskState.selectedCategory.name) ? kioskState.selectedCategory.name.toUpperCase() : 'VIP BARAT';
            const fmt = KIOSK_CONFIG.categoryLabelFormat || '{category} • MATCHDAY COLLECTIBLE';
            catText = fmt.replace('{category}', catName);
        }

        ctx.fillStyle = accentColor;
        ctx.font = '800 18px Outfit';
        ctx.letterSpacing = '2px';
        ctx.fillText(catText, cardW / 2, badgeY + 105);

        // Bottom Slogan
        ctx.fillStyle = '#cbd5e1';
        ctx.font = '700 16px Outfit';
        ctx.textAlign = 'left';
        ctx.fillText(KIOSK_CONFIG.footerText || 'SATU LIGA, SATU SEMANGAT', 60, footerY + 315);

        // QR Code area text
        if (KIOSK_CONFIG.showQr) {
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(cardW - 140, footerY + 230, 80, 80);
            ctx.fillStyle = '#000000';
            ctx.font = 'bold 10px monospace';
            ctx.textAlign = 'center';
            ctx.fillText('QR CODE', cardW - 100, footerY + 275);

            ctx.fillStyle = '#ffffff';
            ctx.font = '800 10px Outfit';
            ctx.fillText('SCAN TO DOWNLOAD', cardW - 100, footerY + 325);
        }

        // Export generated card
        kioskState.generatedCardData = canvas.toDataURL('image/jpeg', 0.92);
        document.getElementById('finalCardPreview').src = kioskState.generatedCardData;

        // Save card to backend & dispatch email
        savePhotoToBackend(kioskState.generatedCardData);

        // Advance to Screen 08
        goToScreen(8);
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
    async function savePhotoToBackend(cardBase64) {
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
                    customer_name: kioskState.customer.name,
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

    // Auto Reset Timer on Screen 09 (5 Seconds)
    function startAutoResetTimer() {
        let sec = 5;
        const display = document.getElementById('autoResetTimer');
        display.innerText = sec;

        clearInterval(kioskState.autoResetInterval);
        kioskState.autoResetInterval = setInterval(() => {
            sec--;
            display.innerText = sec;
            if (sec <= 0) {
                clearInterval(kioskState.autoResetInterval);
                resetToWelcome();
            }
        }, 1000);
    }

    function resetToWelcome() {
        clearInterval(kioskState.pollInterval);
        clearInterval(kioskState.autoResetInterval);
        stopCamera();
        kioskState.quantity = 1;
        document.getElementById('qtyDisplay').innerText = '1';
        kioskState.transaction = null;
        kioskState.customer = { name: '', email: '', phone: '', nik: '', gender: '', birthdate: '' };
        const form = document.getElementById('userDataForm');
        if (form) form.reset();
        goToScreen(1);
    }

    // Modals
    function openRedeemModal() {
        document.getElementById('redeemModal').classList.add('active');
    }

    function openMerchModal() {
        document.getElementById('merchModal').classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
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

    // Keyboard navigation (R-32 accessibility)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal('redeemModal');
            closeModal('merchModal');
        }
    });
</script>

</body>
</html>
