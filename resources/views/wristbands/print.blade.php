<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Wristbands - {{ $category->name }}</title>
    <style>
        @page {
            size: 215mm 330mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .page {
            width: 215mm;
            height: 330mm;
            background: #fff;
            margin: 0 auto;
            page-break-after: always;
            display: flex;
            flex-direction: column;
        }

        .wristband {
            width: 215mm;
            height: 22mm;
            border-bottom: 1px dashed #cfcfcf;
            display: flex;
            align-items: stretch;
            overflow: hidden;
            background: #fff;
        }

        .blank-space {
            width: 22mm;
            min-width: 22mm;
            background: #fff;
            border-right: 1px solid #e5e7eb;
        }

        .league-logo {
            width: 15mm;
            min-width: 15mm;
            height: 100%;
            padding: 1mm;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f5cc7;
            color: #fff;
            text-align: center;
            font-weight: 900;
            font-size: 4pt;
            line-height: 1.05;
            text-transform: uppercase;
            overflow: hidden;
        }

        .league-logo img,
        .club-logo img,
        .sponsor-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
        }

        .ticket-band {
            flex: 1;
            min-width: 0;
            display: grid;
            grid-template-columns: 20mm 25mm 15mm minmax(39mm, 1fr) 15mm 36mm;
            align-items: stretch;
            background:
                linear-gradient(90deg, var(--band-dark) 0%, var(--band-primary) 26%, var(--band-light) 52%, var(--band-dark) 100%),
                repeating-linear-gradient(-12deg, rgba(255,255,255,0.08), rgba(255,255,255,0.08) 1mm, transparent 1mm, transparent 6mm);
            color: var(--band-text);
            position: relative;
        }

        .ticket-band::before,
        .ticket-band::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            height: 1.2mm;
            background: rgba(0,0,0,0.8);
            background: var(--band-edge);
            z-index: 0;
        }

        .ticket-band::before { top: 0; }
        .ticket-band::after { bottom: 0; }

        .ticket-band > * {
            position: relative;
            z-index: 1;
            height: 100%;
            min-height: 0;
            overflow: hidden;
        }

        .qr-section {
            padding: 1mm 1.6mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #fff;
            color: #111827;
            border-right: 1px solid rgba(255,255,255,0.2);
        }

        .qr-section svg {
            width: 10.5mm;
            height: 10.5mm;
            flex: 0 0 auto;
        }

        .qr-section .code {
            max-width: 100%;
            margin-top: 0.5mm;
            font-size: 3.4pt;
            font-weight: 900;
            line-height: 1;
            text-align: center;
            word-break: break-all;
        }

        .category-section {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 2mm;
            color: var(--band-text);
            text-align: center;
            text-transform: uppercase;
            font-weight: 900;
            font-size: 13pt;
            line-height: 1;
            letter-spacing: 0;
            text-shadow: var(--band-text-shadow);
            overflow-wrap: anywhere;
        }

        .club-logo {
            padding: 2.5mm 1.6mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .club-placeholder {
            width: 12mm;
            height: 12mm;
            border: 1px solid rgba(255,255,255,0.55);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7pt;
            font-weight: 900;
            color: var(--band-text);
        }

        .event-section {
            padding: 1.3mm 1.4mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: var(--band-panel);
            clip-path: polygon(7% 0, 93% 0, 100% 100%, 0 100%);
        }

        .league-title {
            margin-bottom: 0.5mm;
            font-size: 4.2pt;
            font-weight: 900;
            line-height: 1;
            letter-spacing: 0;
            text-transform: uppercase;
            color: var(--band-text);
            overflow-wrap: anywhere;
        }

        .event-name {
            max-width: 100%;
            font-size: 8.4pt;
            font-weight: 900;
            line-height: 0.95;
            text-transform: uppercase;
            color: var(--band-accent-text);
            overflow-wrap: anywhere;
        }

        .event-details {
            margin-top: 0.7mm;
            max-width: 100%;
            font-size: 3.8pt;
            font-weight: 800;
            line-height: 1.05;
            text-transform: uppercase;
            color: var(--band-text);
            overflow-wrap: anywhere;
        }

        .sponsor-section {
            padding: 2.2mm 2mm;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-auto-rows: 3.1mm;
            gap: 0.7mm 1.2mm;
            align-content: center;
        }

        .sponsor-item {
            min-width: 0;
            min-height: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--band-text);
            font-size: 3pt;
            font-weight: 900;
            line-height: 1;
            text-align: center;
            text-transform: uppercase;
        }

        .right-blank {
            width: 28mm;
            min-width: 28mm;
            background: #fff;
            border-left: 1px solid #e5e7eb;
        }

        .empty-slot {
            height: 22mm;
            border-bottom: 1px dashed #eee;
            display: flex;
            align-items: center;
            padding-left: 10mm;
            font-size: 8pt;
            color: #cbd5e1;
        }

        @media print {
            html, body { 
                background: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .page { 
                margin: 0 !important; 
                border: none !important; 
                box-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .wristband, .wristband-custom, .ticket-band, .league-logo, .qr-section, .event-section, .category-section, .club-logo, .sponsor-section {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .no-print { display: none !important; }
        }

        .wristband-custom {
            position: relative;
            width: 215mm;
            height: 22mm;
            border-bottom: 1px dashed #cfcfcf;
            overflow: hidden;
            background-color: #ffffff;
            background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: center;
            box-sizing: border-box;
        }

        .wristband-custom .wb-element {
            position: absolute;
            z-index: 10;
            box-sizing: border-box;
            transform-origin: top left;
        }

        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
            width: 100%;
        }
        .btn:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="controls no-print" style="width: 260px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
            <div style="font-size: 11px; font-weight: bold; color: #1e293b;">
                {{ $category->name }}
            </div>
            @if(($activeMode ?? 'default') === 'custom')
                <span style="font-size: 8px; font-weight: bold; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 2px 6px; border-radius: 9999px;">Mode Custom</span>
            @else
                <span style="font-size: 8px; font-weight: bold; background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; padding: 2px 6px; border-radius: 9999px;">Mode Default</span>
            @endif
        </div>
        <div style="font-size: 10px; color: #64748b; margin-bottom: 8px;">
            Total Stok/Kuota: <strong>{{ $categoryQuota ?? $category->quota }}</strong> Gelang<br>
            Menampilkan: <strong>{{ $startNumber ?? 1 }}</strong> s/d <strong>{{ ($startNumber ?? 1) + count($tickets) - 1 }}</strong> ({{ count($tickets) }} gelang)
        </div>

        <!-- Mode Switcher Tabs -->
        <div style="display: flex; gap: 4px; margin-bottom: 10px; background: #f1f5f9; padding: 3px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <a href="{{ route('organizer.categories.print-wristbands', ['category' => $category, 'mode' => 'default', 'start' => $startNumber ?? 1, 'count' => count($tickets)]) }}"
               style="flex: 1; text-align: center; padding: 6px 4px; font-size: 10px; font-weight: 800; border-radius: 6px; text-decoration: none; transition: all 0.2s; {{ ($activeMode ?? 'default') !== 'custom' ? 'background: #ffffff; color: #4338ca; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #64748b;' }}">
                Mode Default
            </a>
            <a href="{{ route('organizer.categories.print-wristbands', ['category' => $category, 'mode' => 'custom', 'start' => $startNumber ?? 1, 'count' => count($tickets)]) }}"
               style="flex: 1; text-align: center; padding: 6px 4px; font-size: 10px; font-weight: 800; border-radius: 6px; text-decoration: none; transition: all 0.2s; {{ ($activeMode ?? 'default') === 'custom' ? 'background: #ffffff; color: #15803d; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #64748b;' }}">
                Mode Custom
            </a>
        </div>

        <button class="btn" onclick="window.print()">🖨️ Cetak {{ count($tickets) }} Gelang</button>

        @if(($activeMode ?? 'default') === 'custom' && (!$wristbandTemplate || !$wristbandTemplate->background_image))
            <div style="margin-top: 8px; padding: 6px 8px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; font-size: 8.5px; color: #92400e; line-height: 1.3;">
                ⚠️ <strong>Background belum ada:</strong> Gambar background belum diunggah atau belum tersimpan di event.
            </div>
        @endif

        <div style="margin-top: 8px;">
            <a href="{{ route('organizer.events.edit', $event) }}" target="_blank" style="display: block; text-align: center; font-size: 9px; color: #6b21a8; background: #faf5ff; border: 1px solid #e9d5ff; padding: 5px 8px; border-radius: 6px; font-weight: 700; text-decoration: none;">
                ✏️ Edit Desain & Background Gelang
            </a>
        </div>

        <form method="GET" action="{{ route('organizer.categories.print-wristbands', $category) }}" style="margin-top: 10px; border-top: 1px solid #e2e8f0; padding-top: 8px;">
            <input type="hidden" name="mode" value="{{ $activeMode ?? 'default' }}">
            <div style="font-size: 9px; font-weight: bold; color: #475569; margin-bottom: 4px;">Cetak Sebagian (Batch):</div>
            <div style="display: flex; gap: 4px; align-items: center; margin-bottom: 6px;">
                <input type="number" name="start" value="{{ $startNumber ?? 1 }}" min="1" placeholder="Mulai" style="width: 50%; font-size: 10px; padding: 4px; border: 1px solid #cbd5e1; border-radius: 4px;">
                <input type="number" name="count" value="{{ count($tickets) }}" min="1" max="5000" placeholder="Jumlah" style="width: 50%; font-size: 10px; padding: 4px; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>
            <button type="submit" style="width: 100%; font-size: 9px; padding: 4px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; font-weight: bold;">Terapkan Batch</button>
        </form>

        <p style="font-size: 8pt; color: #475569; margin-top: 10px; line-height: 1.4; border-top: 1px solid #e2e8f0; padding-top: 8px;">
            <strong>Setting Cetak Browser:</strong><br>
            • Ukuran Kertas: <strong>F4 / Folio</strong> (215 x 330 mm)<br>
            • Margins: <strong>None / Minimum</strong><br>
            • Centang: <strong>Background graphics</strong><br>
            • Skala (Scale): <strong>100% / Default</strong>
        </p>
    </div>

    @php
        $tenant = $event->tenant;
        $eventMeta = $event->meta ?? [];
        $tenantMeta = $tenant?->meta ?? [];

        $assetUrl = function ($path) {
            if (!$path) return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
                return $path;
            }
            return Storage::url($path);
        };

        $normalizeHex = function ($hex, $fallback = '#d71920') {
            $hex = trim((string) $hex);
            $hex = ltrim($hex, '#');

            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }

            return preg_match('/^[0-9a-fA-F]{6}$/', $hex) ? '#' . strtoupper($hex) : $fallback;
        };

        $hexToRgb = function ($hex) {
            $hex = ltrim($hex, '#');
            return [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
            ];
        };

        $mixHex = function ($hex, array $target, $weight) use ($hexToRgb) {
            [$r, $g, $b] = $hexToRgb($hex);
            $weight = max(0, min(1, $weight));

            return sprintf(
                '#%02X%02X%02X',
                round($r + (($target[0] - $r) * $weight)),
                round($g + (($target[1] - $g) * $weight)),
                round($b + (($target[2] - $b) * $weight))
            );
        };

        $contrastPalette = function ($hex) use ($hexToRgb, $mixHex) {
            [$r, $g, $b] = $hexToRgb($hex);
            $luminance = ((0.299 * $r) + (0.587 * $g) + (0.114 * $b)) / 255;
            $isLight = $luminance > 0.58;

            return [
                'text' => $isLight ? '#111827' : '#FFFFFF',
                'accentText' => $isLight ? '#0F172A' : '#FFFFFF',
                'shadow' => $isLight ? 'none' : '1px 1px 0 rgba(0,0,0,0.35)',
                'dark' => $mixHex($hex, [0, 0, 0], $isLight ? 0.22 : 0.55),
                'light' => $mixHex($hex, [255, 255, 255], $isLight ? 0.18 : 0.26),
                'edge' => $isLight ? 'rgba(15, 23, 42, 0.28)' : 'rgba(0, 0, 0, 0.72)',
                'panel' => $isLight ? 'rgba(255, 255, 255, 0.62)' : 'rgba(0, 0, 0, 0.68)',
            ];
        };

        $leagueName = $eventMeta['wristband_league_name'] ?? $tenantMeta['wristband_league_name'] ?? 'BRI Super League 2025-26';
        $leagueLogo = $assetUrl($eventMeta['wristband_league_logo'] ?? $tenantMeta['wristband_league_logo'] ?? \App\Models\Setting::get('wristband_league_logo'));
        $homeLogo = $assetUrl($eventMeta['wristband_home_club_logo'] ?? $tenant?->logo ?? null);
        $awayLogo = $assetUrl($eventMeta['wristband_away_club_logo'] ?? null);
        $sponsorLogos = $eventMeta['wristband_sponsor_logos'] ?? $tenantMeta['wristband_sponsor_logos'] ?? [];
        $sponsorNames = $eventMeta['wristband_sponsor_names'] ?? $tenantMeta['wristband_sponsor_names'] ?? ['IdenTix', 'BRI', 'Super Soccer', 'Adidas', 'Coca Cola', 'Vidio', 'DRX', 'AFG'];
    @endphp

    @foreach($tickets->chunk(15) as $chunk)
    <div class="page">
        @foreach($chunk as $ticket)
        @php
            $ticketCategoryName = $ticket->category?->name ?? $category->name;
            $homeInitial = collect(explode(' ', $event->name))->filter()->map(fn($word) => strtoupper(substr($word, 0, 1)))->take(3)->join('');
            $bandColor = $normalizeHex($ticket->category?->hex_color ?? $category->hex_color ?? '#d71920');
            $bandPalette = $contrastPalette($bandColor);
        @endphp

        @if(($activeMode ?? 'default') === 'custom' && isset($wristbandTemplate) && $wristbandTemplate)
            {{-- Custom Mode: 15 tickets with custom uploaded background and dynamic columns --}}
            <div class="wristband-custom" style="{{ $wristbandTemplate->getBackgroundImageUrl() ? "background-image: url('{$wristbandTemplate->getBackgroundImageUrl()}');" : "background-color: #ffffff;" }}">
                @php $cols = $wristbandTemplate->getMergedColumnsConfig(); @endphp
                @foreach($cols as $cKey => $col)
                    @if(!empty($col['enabled']))
                        @php
                            $x = (float) str_replace(',', '.', (string)($col['x'] ?? 0));
                            $y = (float) str_replace(',', '.', (string)($col['y'] ?? 0));
                            $color = $col['color'] ?? '#111827';
                            $fontSize = $col['font_size'] ?? '6pt';
                            $fontWeight = $col['font_weight'] ?? '700';
                            $align = $col['align'] ?? 'left';
                        @endphp

                        @if($cKey === 'qr_code')
                            @php $qrSizeMm = (float) str_replace(',', '.', (string)($col['qr_size'] ?? 14)); @endphp
                            <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; width: {{ $qrSizeMm }}mm; height: {{ $qrSizeMm }}mm; display: flex; align-items: center; justify-content: center; background: #ffffff; padding: 0.5mm; border-radius: 1px; box-shadow: 0 0 1px rgba(0,0,0,0.15);">
                                {!! QrCode::size((int)($qrSizeMm * 3.78))->margin(0)->generate($ticket->ticket_code) !!}
                            </div>
                        @elseif($cKey === 'wristband_code')
                            <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; line-height: 1;">
                                {{ $ticket->ticket_code }}
                            </div>
                        @elseif($cKey === 'category_name')
                            <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; text-transform: uppercase; line-height: 1;">
                                {{ $ticketCategoryName }}
                            </div>
                        @elseif($cKey === 'event_name')
                            <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; text-transform: uppercase; line-height: 1; max-width: 90mm; overflow: hidden; text-overflow: ellipsis;">
                                {{ $event->name }}
                            </div>
                        @elseif($cKey === 'event_date')
                            <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; text-transform: uppercase; line-height: 1;">
                                {{ $event->event_start_date->format('d M Y') }}
                                @if($event->gate_open_at) • {{ $event->gate_open_at->format('H.i') }} WIB @endif
                            </div>
                        @elseif($cKey === 'venue')
                            <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; text-transform: uppercase; line-height: 1; max-width: 90mm; overflow: hidden; text-overflow: ellipsis;">
                                {{ $event->venue }}{{ $event->city ? ', ' . $event->city : '' }}
                            </div>
                        @elseif($cKey === 'seat_number')
                            <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; line-height: 1;">
                                #{{ sprintf('%04d', $ticket->index ?? 1) }}
                            </div>
                        @elseif($cKey === 'price')
                            <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; line-height: 1;">
                                {{ $category->price > 0 ? 'Rp ' . number_format($category->price, 0, ',', '.') : 'FREE' }}
                            </div>
                        @elseif($cKey === 'custom_text')
                            <div class="wb-element" style="left: {{ $x }}mm; top: {{ $y }}mm; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: {{ $color }}; text-align: {{ $align }}; white-space: nowrap; line-height: 1;">
                                {{ $col['custom_value'] ?? 'NON-REFUNDABLE' }}
                            </div>
                        @endif
                    @endif
                @endforeach
            </div>
        @else
            {{-- Default Mode: Standard IdenTix Wristband Layout --}}
            <div class="wristband">
                <div class="blank-space"></div>

                <div class="league-logo">
                    @if($leagueLogo)
                        <img src="{{ $leagueLogo }}" alt="League Logo">
                    @else
                        <div>SUPER<br>LEAGUE</div>
                    @endif
                </div>

                <div class="ticket-band" style="--band-primary: {{ $bandColor }}; --band-dark: {{ $bandPalette['dark'] }}; --band-light: {{ $bandPalette['light'] }}; --band-edge: {{ $bandPalette['edge'] }}; --band-panel: {{ $bandPalette['panel'] }}; --band-text: {{ $bandPalette['text'] }}; --band-accent-text: {{ $bandPalette['accentText'] }}; --band-text-shadow: {{ $bandPalette['shadow'] }};">
                    <div class="qr-section">
                        {!! QrCode::size(62)->margin(0)->generate($ticket->ticket_code) !!}
                        <div class="code">{{ $ticket->ticket_code }}</div>
                    </div>

                    <div class="category-section">{{ $ticketCategoryName }}</div>

                    <div class="club-logo">
                        @if($homeLogo)
                            <img src="{{ $homeLogo }}" alt="Home Club Logo">
                        @else
                            <div class="club-placeholder">{{ $homeInitial ?: 'HOME' }}</div>
                        @endif
                    </div>

                    <div class="event-section">
                        <div class="league-title">{{ $leagueName }}</div>
                        <div class="event-name">{{ $event->name }}</div>
                        <div class="event-details">
                            {{ $event->event_start_date->format('l, d M Y') }}
                            @if($event->gate_open_at)
                                - KICK OFF {{ $event->gate_open_at->format('H.i') }} WIB
                            @endif
                            <br>{{ $event->venue }}{{ $event->city ? ', ' . $event->city : '' }}
                        </div>
                    </div>

                    <div class="club-logo">
                        @if($awayLogo)
                            <img src="{{ $awayLogo }}" alt="Away Club Logo">
                        @else
                            <div class="club-placeholder">AWAY</div>
                        @endif
                    </div>

                    <div class="sponsor-section">
                        @forelse($sponsorLogos as $sponsorLogo)
                            @php $sponsorLogoUrl = $assetUrl($sponsorLogo); @endphp
                            @if($sponsorLogoUrl)
                                <div class="sponsor-item"><img src="{{ $sponsorLogoUrl }}" alt="Sponsor Logo"></div>
                            @endif
                        @empty
                            @foreach($sponsorNames as $sponsorName)
                                <div class="sponsor-item">{{ $sponsorName }}</div>
                            @endforeach
                        @endforelse
                    </div>
                </div>

                <div class="right-blank"></div>
            </div>
        @endif
        @endforeach

        @for($i = count($chunk); $i < 15; $i++)
            <div class="empty-slot">Empty Wristband Slot</div>
        @endfor
    </div>
    @endforeach
</body>
</html>
