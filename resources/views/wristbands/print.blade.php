<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Wristbands - {{ $category->name }} ({{ strtoupper($paperSize ?? 'F4') }})</title>
    <style>
        @if(($paperSize ?? 'f4') === 'a3')
        @page {
            size: 480mm 310mm;
            margin: 0;
        }
        @else
        @page {
            size: 215mm 330mm;
            margin: 0;
        }
        @endif

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
            background: #f1f5f9;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* F4 Page Setup */
        .page, .page-f4 {
            width: 215mm;
            height: 330mm;
            background: #fff;
            margin: 0 auto 20px auto;
            page-break-after: always;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        /* A3+ Page Setup (31 cm x 48 cm Landscape: 480mm wide x 310mm high) */
        .page-a3 {
            width: 480mm;
            height: 310mm;
            background: #fff;
            margin: 0 auto 25px auto;
            page-break-after: always;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: stretch;
            padding: 12mm 15mm;
            box-sizing: border-box;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .column-a3 {
            width: 215mm;
            min-width: 215mm;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .wristband {
            width: 215mm;
            height: 22mm;
            min-height: 22mm;
            max-height: 22mm;
            border-bottom: 1px dashed #cfcfcf;
            display: flex;
            align-items: stretch;
            overflow: hidden;
            background: #fff;
            box-sizing: border-box;
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
            min-height: 22mm;
            max-height: 22mm;
            border-bottom: 1px dashed #eee;
            display: flex;
            align-items: center;
            padding-left: 10mm;
            font-size: 8pt;
            color: #cbd5e1;
            box-sizing: border-box;
        }

        .wristband-custom {
            position: relative;
            width: 215mm;
            height: 22mm;
            min-height: 22mm;
            max-height: 22mm;
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

        /* Controls Panel */
        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ffffff;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            z-index: 1000;
            width: 290px;
            font-size: 12px;
        }

        .btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 12px;
            width: 100%;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-docx {
            background: #0284c7;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 12px;
            width: 100%;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-docx:hover {
            background: #0369a1;
            transform: translateY(-1px);
        }

        @media print {
            html, body { 
                background: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .page, .page-f4, .page-a3 { 
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
    </style>
</head>
<body>
    <div class="controls no-print">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div>
                <div style="font-size: 12px; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ $category->name }}
                </div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                    {{ $event->name }}
                </div>
            </div>
            @if(($activeMode ?? 'default') === 'custom')
                <span style="font-size: 8px; font-weight: 800; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 2px 6px; border-radius: 9999px; white-space: nowrap;">Mode Custom</span>
            @else
                <span style="font-size: 8px; font-weight: 800; background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; padding: 2px 6px; border-radius: 9999px; white-space: nowrap;">Mode Default</span>
            @endif
        </div>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; font-size: 10px; color: #475569; margin-bottom: 10px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                <span>Total Stok/Kuota:</span>
                <strong style="color: #0f172a;">{{ $categoryQuota ?? $category->quota }} Gelang</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                <span>Menampilkan:</span>
                <strong style="color: #0f172a;">{{ $startNumber ?? 1 }} s/d {{ ($startNumber ?? 1) + count($tickets) - 1 }} ({{ count($tickets) }} gelang)</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Kebutuhan Kertas:</span>
                @if(($paperSize ?? 'f4') === 'a3')
                    <strong style="color: #0284c7;">{{ ceil(count($tickets) / 26) }} Lembar A3+ (26/lembar)</strong>
                @else
                    <strong style="color: #4338ca;">{{ ceil(count($tickets) / 15) }} Lembar F4 (15/lembar)</strong>
                @endif
            </div>
            @if(($categoryQuota ?? $category->quota) > count($tickets))
                <div style="margin-top: 5px; padding-top: 5px; border-top: 1px dashed #cbd5e1; font-size: 8.5px; color: #0369a1; line-height: 1.3;">
                    💡 Batch aktif: {{ count($tickets) }} gelang dari total {{ $categoryQuota ?? $category->quota }} kuota. Gunakan form batch di bawah untuk mencetak rentang lainnya.
                </div>
            @endif
        </div>

        <!-- Paper Size Switcher (F4 vs A3+) -->
        <div style="margin-bottom: 10px;">
            <div style="font-size: 9px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
                <span>Pilih Ukuran Kertas:</span>
                @if(($paperSize ?? 'f4') === 'a3')
                    <span style="font-size: 8px; background: #e0f2fe; color: #0369a1; padding: 1px 5px; border-radius: 4px; font-weight: 700;">Hemat Ruang (2 Kolom)</span>
                @else
                    <span style="font-size: 8px; background: #ede9fe; color: #6d28d9; padding: 1px 5px; border-radius: 4px; font-weight: 700;">1 Kolom Standar</span>
                @endif
            </div>
            <div style="display: flex; gap: 4px; background: #f1f5f9; padding: 3px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <a href="{{ route('organizer.categories.print-wristbands', ['category' => $category, 'paper' => 'f4', 'mode' => $activeMode ?? 'default', 'start' => $startNumber ?? 1, 'count' => count($tickets)]) }}"
                   style="flex: 1; text-align: center; padding: 6px 4px; font-size: 10px; font-weight: 800; border-radius: 6px; text-decoration: none; transition: all 0.2s; {{ ($paperSize ?? 'f4') !== 'a3' ? 'background: #ffffff; color: #4338ca; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #64748b;' }}">
                    📄 F4 (21.5 x 33 cm)
                </a>
                <a href="{{ route('organizer.categories.print-wristbands', ['category' => $category, 'paper' => 'a3', 'mode' => $activeMode ?? 'default', 'start' => $startNumber ?? 1, 'count' => count($tickets)]) }}"
                   style="flex: 1; text-align: center; padding: 6px 4px; font-size: 10px; font-weight: 800; border-radius: 6px; text-decoration: none; transition: all 0.2s; {{ ($paperSize ?? 'f4') === 'a3' ? 'background: #ffffff; color: #0284c7; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #64748b;' }}">
                    📰 A3+ (31 x 48 cm)
                </a>
            </div>
        </div>

        <!-- Mode Switcher Tabs -->
        <div style="margin-bottom: 12px;">
            <div style="font-size: 9px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 4px;">
                Desain Gelang:
            </div>
            <div style="display: flex; gap: 4px; background: #f1f5f9; padding: 3px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <a href="{{ route('organizer.categories.print-wristbands', ['category' => $category, 'paper' => $paperSize ?? 'f4', 'mode' => 'default', 'start' => $startNumber ?? 1, 'count' => count($tickets)]) }}"
                   style="flex: 1; text-align: center; padding: 5px 4px; font-size: 10px; font-weight: 800; border-radius: 6px; text-decoration: none; transition: all 0.2s; {{ ($activeMode ?? 'default') !== 'custom' ? 'background: #ffffff; color: #4338ca; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #64748b;' }}">
                    Mode Default
                </a>
                <a href="{{ route('organizer.categories.print-wristbands', ['category' => $category, 'paper' => $paperSize ?? 'f4', 'mode' => 'custom', 'start' => $startNumber ?? 1, 'count' => count($tickets)]) }}"
                   style="flex: 1; text-align: center; padding: 5px 4px; font-size: 10px; font-weight: 800; border-radius: 6px; text-decoration: none; transition: all 0.2s; {{ ($activeMode ?? 'default') === 'custom' ? 'background: #ffffff; color: #15803d; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #64748b;' }}">
                    Mode Custom
                </a>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px;">
            <button class="btn" onclick="window.print()">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                Cetak Browser Sekarang
            </button>

            <!-- Export DOCX -->
            <a href="{{ route('organizer.categories.export-wristbands-docx', ['category' => $category, 'paper' => $paperSize ?? 'f4', 'mode' => $activeMode ?? 'default', 'start' => $startNumber ?? 1, 'count' => count($tickets)]) }}"
               class="btn-docx">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Export .docx ({{ strtoupper($paperSize ?? 'F4') }})
            </a>

            <!-- Quick alternative docx download link -->
            @php $altPaper = ($paperSize ?? 'f4') === 'a3' ? 'f4' : 'a3'; @endphp
            <a href="{{ route('organizer.categories.export-wristbands-docx', ['category' => $category, 'paper' => $altPaper, 'mode' => $activeMode ?? 'default', 'start' => $startNumber ?? 1, 'count' => count($tickets)]) }}"
               style="text-align: center; font-size: 9px; color: #0369a1; text-decoration: underline; font-weight: 600;">
                Atau download .docx versi {{ strtoupper($altPaper) }} ({{ $altPaper === 'a3' ? '31x48cm 2 Kolom' : '21.5x33cm' }})
            </a>
        </div>

        @if(($activeMode ?? 'default') === 'custom' && (!$wristbandTemplate || !$wristbandTemplate->background_image))
            <div style="margin-bottom: 8px; padding: 6px 8px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; font-size: 8.5px; color: #92400e; line-height: 1.3;">
                ⚠️ <strong>Background belum ada:</strong> Gambar background belum diunggah atau belum tersimpan di event.
            </div>
        @endif

        <div style="margin-bottom: 10px;">
            <a href="{{ route('organizer.events.edit', $event) }}" target="_blank" style="display: block; text-align: center; font-size: 9.5px; color: #6b21a8; background: #faf5ff; border: 1px solid #e9d5ff; padding: 5px 8px; border-radius: 6px; font-weight: 700; text-decoration: none;">
                ✏️ Edit Desain & Background Gelang
            </a>
        </div>

        <!-- Batch Filtering Form -->
        <form method="GET" action="{{ route('organizer.categories.print-wristbands', $category) }}" style="border-top: 1px solid #e2e8f0; padding-top: 8px;">
            <input type="hidden" name="mode" value="{{ $activeMode ?? 'default' }}">
            <input type="hidden" name="paper" value="{{ $paperSize ?? 'f4' }}">
            <div style="font-size: 9px; font-weight: bold; color: #475569; margin-bottom: 4px;">Cetak Sebagian (Batch):</div>
            <div style="display: flex; gap: 4px; align-items: center; margin-bottom: 6px;">
                <input type="number" name="start" value="{{ $startNumber ?? 1 }}" min="1" placeholder="Mulai" style="width: 50%; font-size: 10px; padding: 4px; border: 1px solid #cbd5e1; border-radius: 4px;">
                <input type="number" name="count" value="{{ count($tickets) }}" min="1" max="5000" placeholder="Jumlah" style="width: 50%; font-size: 10px; padding: 4px; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>
            <button type="submit" style="width: 100%; font-size: 9px; padding: 5px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; font-weight: bold; color: #334155;">Terapkan Batch</button>
        </form>

        <!-- Browser Print Guidelines -->
        <div style="font-size: 8pt; color: #475569; margin-top: 10px; line-height: 1.4; border-top: 1px solid #e2e8f0; padding-top: 8px;">
            <strong style="color: #1e293b;">Panduan Cetak Browser ({{ strtoupper($paperSize ?? 'F4') }}):</strong><br>
            @if(($paperSize ?? 'f4') === 'a3')
                • Kertas: <strong>A3+ / Super A3 (310 x 480 mm)</strong><br>
                • Orientasi: <strong>Landscape (Mendatar)</strong><br>
                • Tata Letak: <strong>2 Kolom (13 x 2 = 26 Gelang/Lembar)</strong><br>
            @else
                • Kertas: <strong>F4 / Folio (215 x 330 mm)</strong><br>
                • Orientasi: <strong>Portrait (Tegak)</strong><br>
                • Tata Letak: <strong>1 Kolom (15 Gelang/Lembar)</strong><br>
            @endif
            • Margins: <strong>None / Minimum</strong><br>
            • Centang: <strong>Background graphics</strong><br>
            • Skala (Scale): <strong>100% / Default</strong>
        </div>
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

    @if(($paperSize ?? 'f4') === 'a3')
        {{-- A3+ Mode: 2 Columns of 13 wristbands (26 wristbands per sheet) --}}
        @foreach($tickets->chunk(26) as $chunk)
        <div class="page-a3">
            @php
                $leftChunk = $chunk->slice(0, 13);
                $rightChunk = $chunk->slice(13, 13);
            @endphp

            <!-- Left Column: 13 Wristbands -->
            <div class="column-a3">
                @foreach($leftChunk as $ticket)
                    @include('wristbands.partials.single-wristband', ['ticket' => $ticket])
                @endforeach
                @for($i = count($leftChunk); $i < 13; $i++)
                    <div class="empty-slot">Empty Wristband Slot</div>
                @endfor
            </div>

            <!-- Right Column: 13 Wristbands -->
            <div class="column-a3">
                @foreach($rightChunk as $ticket)
                    @include('wristbands.partials.single-wristband', ['ticket' => $ticket])
                @endforeach
                @for($i = count($rightChunk); $i < 13; $i++)
                    <div class="empty-slot">Empty Wristband Slot</div>
                @endfor
            </div>
        </div>
        @endforeach
    @else
        {{-- F4 Mode: 1 Column of 15 wristbands (15 wristbands per sheet) --}}
        @foreach($tickets->chunk(15) as $chunk)
        <div class="page page-f4">
            @foreach($chunk as $ticket)
                @include('wristbands.partials.single-wristband', ['ticket' => $ticket])
            @endforeach

            @for($i = count($chunk); $i < 15; $i++)
                <div class="empty-slot">Empty Wristband Slot</div>
            @endfor
        </div>
        @endforeach
    @endif
</body>
</html>
