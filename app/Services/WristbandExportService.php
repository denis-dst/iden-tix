<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Setting;
use App\Models\TicketCategory;
use App\Models\WristbandTemplate;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\JcTable;

class WristbandExportService
{
    protected ?string $fontBold = null;
    protected ?string $fontRegular = null;
    protected ?\GdImage $cachedDefaultBase = null;
    protected ?string $cachedDefaultKey = null;
    protected ?\GdImage $cachedCustomBase = null;
    protected ?string $cachedCustomKey = null;

    public function __construct()
    {
        $this->fontBold = $this->resolveFont(true);
        $this->fontRegular = $this->resolveFont(false);
    }

    public function __destruct()
    {
        $this->clearCanvasCache();
    }

    /**
     * Clear GD image base canvas caches.
     */
    public function clearCanvasCache(): void
    {
        if ($this->cachedDefaultBase) {
            imagedestroy($this->cachedDefaultBase);
            $this->cachedDefaultBase = null;
            $this->cachedDefaultKey = null;
        }

        if ($this->cachedCustomBase) {
            imagedestroy($this->cachedCustomBase);
            $this->cachedCustomBase = null;
            $this->cachedCustomKey = null;
        }
    }

    /**
     * Export wristbands to a .docx file.
     *
     * @param Collection $tickets
     * @param TicketCategory $category
     * @param Event $event
     * @param WristbandTemplate|null $template
     * @param string $paperSize 'f4' or 'a3'
     * @param string $mode 'default' or 'custom'
     * @return string Path to generated .docx file
     */
    public function exportDocx(
        Collection $tickets,
        TicketCategory $category,
        Event $event,
        ?WristbandTemplate $template = null,
        string $paperSize = 'f4',
        string $mode = 'default'
    ): string {
        // Prevent timeout during batch document processing
        @ini_set('max_execution_time', '300');
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $paperSize = strtolower($paperSize) === 'a3' ? 'a3' : 'f4';
        $mode = $mode === 'custom' ? 'custom' : 'default';

        $tempDir = storage_path('app/temp/wristbands_' . uniqid());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tickets = $tickets->values();
        $imageFiles = [];

        // Generate an image for each ticket
        foreach ($tickets as $idx => $ticket) {
            // Keep resetting time limit in long loops so server doesn't abort
            if ($idx % 50 === 0) {
                @set_time_limit(60);
            }

            $img = $this->generateWristbandImage($ticket, $category, $event, $template, $mode);
            $imgPath = $tempDir . '/wb_' . $idx . '.png';
            // Use fast PNG compression level 1 (10-20x faster than default level 6, 100% lossless)
            imagepng($img, $imgPath, 1);
            imagedestroy($img);
            $imageFiles[$idx] = $imgPath;
        }

        $phpWord = new PhpWord();

        // Configure document properties
        $docProps = $phpWord->getDocInfo();
        $docProps->setCreator('GenTix Ticketing');
        $docProps->setTitle('Wristbands - ' . $category->name);
        $docProps->setCompany($event->tenant?->name ?? 'GenTix');

        // Dimensions in cm
        $wbWidthCm = 21.5;
        $wbHeightCm = 2.2;
        $wbWidthPt = Converter::cmToPoint($wbWidthCm);
        $wbHeightPt = Converter::cmToPoint($wbHeightCm);
        $wbWidthTwip = Converter::cmToTwip($wbWidthCm);
        $wbHeightTwip = Converter::cmToTwip($wbHeightCm);

        if ($paperSize === 'a3') {
            // A3+ Landscape: 48 cm width x 31 cm height
            // 2 columns side by side (21.5 cm each) + 2.0 cm center gap = 45 cm.
            // Left margin: 1.5 cm, Right margin: 1.5 cm => Total 48.0 cm.
            // Height: 31.0 cm. 13 rows of 2.2 cm = 28.6 cm.
            // Top margin: 1.2 cm, Bottom margin: 1.2 cm => Total 31.0 cm.
            // Total wristbands per sheet: 13 rows x 2 columns = 26 wristbands.
            $pageWidthTwip = Converter::cmToTwip(48.0);
            $pageHeightTwip = Converter::cmToTwip(31.0);
            $marginTopTwip = Converter::cmToTwip(1.2);
            $marginBottomTwip = Converter::cmToTwip(1.2);
            $marginLeftTwip = Converter::cmToTwip(1.5);
            $marginRightTwip = Converter::cmToTwip(1.5);
            $gapWidthTwip = Converter::cmToTwip(2.0);

            $ticketsPerPage = 26;
            $rowsPerPage = 13;
            $chunks = $tickets->values()->chunk($ticketsPerPage);

            foreach ($chunks as $pageChunk) {
                $section = $phpWord->addSection([
                    'pageSizeW' => $pageWidthTwip,
                    'pageSizeH' => $pageHeightTwip,
                    'marginTop' => $marginTopTwip,
                    'marginBottom' => $marginBottomTwip,
                    'marginLeft' => $marginLeftTwip,
                    'marginRight' => $marginRightTwip,
                    'orientation' => 'landscape',
                ]);

                $table = $section->addTable([
                    'alignment' => JcTable::CENTER,
                    'cellMarginTop' => 0,
                    'cellMarginBottom' => 0,
                    'cellMarginLeft' => 0,
                    'cellMarginRight' => 0,
                ]);

                for ($rowIdx = 0; $rowIdx < $rowsPerPage; $rowIdx++) {
                    $row = $table->addRow($wbHeightTwip);

                    // Left Column (ticket index: 0 to 12)
                    $leftTicketIndex = $rowIdx;
                    $cellLeft = $row->addCell($wbWidthTwip);
                    if ($pageChunk->has($leftTicketIndex)) {
                        $globalIdx = $pageChunk->keys()[$leftTicketIndex];
                        $cellLeft->addImage($imageFiles[$globalIdx], [
                            'width' => $wbWidthPt,
                            'height' => $wbHeightPt,
                        ]);
                    }

                    // Spacer column
                    $cellGap = $row->addCell($gapWidthTwip);

                    // Right Column (ticket index: 13 to 25)
                    $rightTicketIndex = $rowIdx + $rowsPerPage;
                    $cellRight = $row->addCell($wbWidthTwip);
                    if ($pageChunk->has($rightTicketIndex)) {
                        $globalIdx = $pageChunk->keys()[$rightTicketIndex];
                        $cellRight->addImage($imageFiles[$globalIdx], [
                            'width' => $wbWidthPt,
                            'height' => $wbHeightPt,
                        ]);
                    }
                }
            }
        } else {
            // F4 Portrait: 21.5 cm width x 33.0 cm height
            // 1 column of 21.5 cm width, 15 rows of 2.2 cm height = 33.0 cm.
            // Margins: 0 mm. Total wristbands per sheet: 15 wristbands.
            $pageWidthTwip = Converter::cmToTwip(21.5);
            $pageHeightTwip = Converter::cmToTwip(33.0);

            $ticketsPerPage = 15;
            $chunks = $tickets->values()->chunk($ticketsPerPage);

            foreach ($chunks as $pageChunk) {
                $section = $phpWord->addSection([
                    'pageSizeW' => $pageWidthTwip,
                    'pageSizeH' => $pageHeightTwip,
                    'marginTop' => 0,
                    'marginBottom' => 0,
                    'marginLeft' => 0,
                    'marginRight' => 0,
                    'orientation' => 'portrait',
                ]);

                $table = $section->addTable([
                    'alignment' => JcTable::CENTER,
                    'cellMarginTop' => 0,
                    'cellMarginBottom' => 0,
                    'cellMarginLeft' => 0,
                    'cellMarginRight' => 0,
                ]);

                foreach ($pageChunk as $k => $ticket) {
                    $row = $table->addRow($wbHeightTwip);
                    $cell = $row->addCell($wbWidthTwip);
                    $cell->addImage($imageFiles[$k], [
                        'width' => $wbWidthPt,
                        'height' => $wbHeightPt,
                    ]);
                }
            }
        }

        $outDocx = $tempDir . '/wristbands_' . time() . '.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outDocx);

        // Delete temporary image files to save disk space
        foreach ($imageFiles as $imgFile) {
            if (file_exists($imgFile)) {
                @unlink($imgFile);
            }
        }

        $this->clearCanvasCache();

        return $outDocx;
    }

    /**
     * Generate a wristband image (1270x130 px, standard 215mm x 22mm).
     */
    public function generateWristbandImage(
        object $ticket,
        TicketCategory $category,
        Event $event,
        ?WristbandTemplate $template = null,
        string $mode = 'default'
    ): \GdImage {
        $scale = 1270.0 / 215.0; // ~5.906976 px/mm
        $width = 1270;
        $height = (int) round(22.0 * $scale); // 130 px

        if ($mode === 'custom' && $template) {
            return $this->renderCustomWristband($ticket, $category, $event, $template, $width, $height, $scale);
        }

        return $this->renderDefaultWristband($ticket, $category, $event, $width, $height, $scale);
    }

    /**
     * Render a default Gentix wristband.
     */
    protected function renderDefaultWristband(
        object $ticket,
        TicketCategory $category,
        Event $event,
        int $width,
        int $height,
        float $scale
    ): \GdImage {
        $bandColor = $this->normalizeHex($category->hex_color ?? '#d71920');
        $palette = $this->contrastPalette($bandColor);

        // Build or reuse base image (everything except ticket QR code and ticket code)
        $cacheKey = $category->id . '_' . $event->id . '_' . $bandColor;
        if (!$this->cachedDefaultBase || $this->cachedDefaultKey !== $cacheKey) {
            if ($this->cachedDefaultBase) {
                imagedestroy($this->cachedDefaultBase);
            }
            $this->cachedDefaultBase = $this->buildDefaultBaseCanvas($category, $event, $width, $height, $scale, $bandColor, $palette);
            $this->cachedDefaultKey = $cacheKey;
        }

        // Clone base canvas
        $im = imagecreatetruecolor($width, $height);
        imagecopy($im, $this->cachedDefaultBase, 0, 0, 0, 0, $width, $height);

        // Coordinates for QR section: 20mm (from x: 37mm to 57mm)
        $xLeagueEnd = (int) round(37 * $scale);
        $xQrEnd = (int) round(57 * $scale);
        $qrBoxWidth = $xQrEnd - $xLeagueEnd;

        // Draw QR Code
        $code = $ticket->ticket_code ?? $ticket->wristband_code ?? 'WB-0000';
        $qrMatrix = $this->generateQrMatrix($code);
        $mw = $qrMatrix ? $qrMatrix->getWidth() : 21;
        $qrPx = 62;
        $modSize = $qrPx / $mw;
        $qrStartX = $xLeagueEnd + ($qrBoxWidth - $qrPx) / 2;
        $qrStartY = 13;

        $black = imagecolorallocate($im, 17, 24, 39);

        if ($qrMatrix) {
            for ($my = 0; $my < $mw; $my++) {
                for ($mx = 0; $mx < $mw; $mx++) {
                    if ($qrMatrix->get($mx, $my) === 1) {
                        $px = (int) round($qrStartX + $mx * $modSize);
                        $py = (int) round($qrStartY + $my * $modSize);
                        $pSize = (int) ceil($modSize);
                        imagefilledrectangle($im, $px, $py, $px + $pSize, $py + $pSize, $black);
                    }
                }
            }
        }

        // Ticket code text below QR
        $this->drawTextCentered($im, $code, $xLeagueEnd, $xQrEnd, $height - 18, 7.0, $black, $this->fontBold);

        return $im;
    }

    /**
     * Build the reusable base canvas for default mode.
     */
    protected function buildDefaultBaseCanvas(
        TicketCategory $category,
        Event $event,
        int $width,
        int $height,
        float $scale,
        string $bandColor,
        array $palette
    ): \GdImage {
        $im = imagecreatetruecolor($width, $height);
        imagealphablending($im, true);
        imagesavealpha($im, true);

        $white = imagecolorallocate($im, 255, 255, 255);
        $grayBorder = imagecolorallocate($im, 229, 231, 235);
        $leagueBlue = imagecolorallocate($im, 15, 92, 199);

        // 1. Left Blank Space: 22mm (x: 0 to 22mm)
        $xLeftEnd = (int) round(22 * $scale);
        imagefilledrectangle($im, 0, 0, $xLeftEnd, $height, $white);
        imageline($im, $xLeftEnd, 0, $xLeftEnd, $height, $grayBorder);

        // 2. League Logo Section: 15mm (x: 22mm to 37mm)
        $xLeagueEnd = (int) round(37 * $scale);
        imagefilledrectangle($im, $xLeftEnd, 0, $xLeagueEnd, $height, $leagueBlue);

        // Draw League Logo or text
        $leagueLogoPath = $this->resolveAssetPath(
            $event->meta['wristband_league_logo'] ?? $event->tenant?->meta['wristband_league_logo'] ?? Setting::get('wristband_league_logo')
        );
        $leagueImg = $this->loadGdImage($leagueLogoPath);
        if ($leagueImg) {
            $this->drawImageFitted($im, $leagueImg, $xLeftEnd + 6, 8, $xLeagueEnd - 6, $height - 8);
            imagedestroy($leagueImg);
        } else {
            $this->drawTextCentered($im, "SUPER", $xLeftEnd, $xLeagueEnd, 56, 10.0, $white, $this->fontBold);
            $this->drawTextCentered($im, "LEAGUE", $xLeftEnd, $xLeagueEnd, 78, 10.0, $white, $this->fontBold);
        }

        // 3. Ticket Band: 150mm (x: 37mm to 187mm)
        $xBandEnd = (int) round(187 * $scale);
        $bandWidth = $xBandEnd - $xLeagueEnd;

        // Gradient
        [$rDark, $gDark, $bDark] = $this->hexToRgb($palette['dark']);
        [$rPri, $gPri, $bPri] = $this->hexToRgb($bandColor);
        [$rLight, $gLight, $bLight] = $this->hexToRgb($palette['light']);

        for ($x = $xLeagueEnd; $x <= $xBandEnd; $x++) {
            $progress = ($x - $xLeagueEnd) / max(1, $bandWidth);
            if ($progress < 0.26) {
                $p = $progress / 0.26;
                $r = (int) round($rDark + $p * ($rPri - $rDark));
                $g = (int) round($gDark + $p * ($gPri - $gDark));
                $b = (int) round($bDark + $p * ($bPri - $bDark));
            } elseif ($progress < 0.52) {
                $p = ($progress - 0.26) / 0.26;
                $r = (int) round($rPri + $p * ($rLight - $rPri));
                $g = (int) round($gPri + $p * ($gLight - $gPri));
                $b = (int) round($bPri + $p * ($bLight - $bPri));
            } else {
                $p = ($progress - 0.52) / 0.48;
                $r = (int) round($rLight + $p * ($rDark - $rLight));
                $g = (int) round($gLight + $p * ($gDark - $gLight));
                $b = (int) round($bLight + $p * ($bDark - $bLight));
            }
            $col = imagecolorallocate($im, max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
            imageline($im, $x, 0, $x, $height, $col);
        }

        // Subtle diagonal repeating stripes
        $stripeCol = imagecolorallocatealpha($im, 255, 255, 255, 115);
        $stripeGap = (int) round(6 * $scale);
        for ($sx = $xLeagueEnd - $height; $sx < $xBandEnd + $height; $sx += $stripeGap) {
            imageline($im, $sx, $height, $sx + (int)($height * 0.4), 0, $stripeCol);
        }

        // Top & bottom edge borders (1.2mm = ~7px)
        $edgeH = (int) round(1.2 * $scale);
        [$rEdge, $gEdge, $bEdge] = $this->hexToRgb($palette['dark']);
        $edgeCol = imagecolorallocate($im, (int)($rEdge * 0.7), (int)($gEdge * 0.7), (int)($bEdge * 0.7));
        imagefilledrectangle($im, $xLeagueEnd, 0, $xBandEnd, $edgeH, $edgeCol);
        imagefilledrectangle($im, $xLeagueEnd, $height - $edgeH, $xBandEnd, $height, $edgeCol);

        // QR Container box: 20mm (x: 37mm to 57mm)
        $xQrEnd = (int) round(57 * $scale);
        imagefilledrectangle($im, $xLeagueEnd + 2, $edgeH + 1, $xQrEnd - 2, $height - $edgeH - 1, $white);
        imageline($im, $xQrEnd, $edgeH, $xQrEnd, $height - $edgeH, $grayBorder);

        // Category Section: 25mm (x: 57mm to 82mm)
        $xCatEnd = (int) round(82 * $scale);
        [$rText, $gText, $bText] = $this->hexToRgb($palette['text']);
        $textColor = imagecolorallocate($im, $rText, $gText, $bText);
        $catName = strtoupper(trim($category->name));
        $this->drawTextAutoFit($im, $catName, $xQrEnd + 6, $xCatEnd - 6, 75, 18.0, 10.0, $textColor, $this->fontBold);

        // Home Club Logo Section: 15mm (x: 82mm to 97mm)
        $xHomeEnd = (int) round(97 * $scale);
        $homeLogoPath = $this->resolveAssetPath(
            $event->meta['wristband_home_club_logo'] ?? $event->tenant?->logo ?? null
        );
        $homeImg = $this->loadGdImage($homeLogoPath);
        if ($homeImg) {
            $this->drawImageFitted($im, $homeImg, $xCatEnd + 4, 18, $xHomeEnd - 4, $height - 18);
            imagedestroy($homeImg);
        } else {
            // Circle placeholder with home initials
            $initials = collect(explode(' ', $event->name))->filter()->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(3)->join('') ?: 'HOME';
            $this->drawCirclePlaceholder($im, ($xCatEnd + $xHomeEnd) / 2, $height / 2, 34, $initials, $textColor, $this->fontBold);
        }

        // Event Details Section: 39mm (x: 97mm to 136mm)
        $xEvEnd = (int) round(136 * $scale);
        // Panel background
        $panelCol = imagecolorallocatealpha($im, 0, 0, 0, 65);
        imagefilledrectangle($im, $xHomeEnd + 4, 10, $xEvEnd - 4, $height - 10, $panelCol);

        $leagueTitle = strtoupper($event->meta['wristband_league_name'] ?? $event->tenant?->meta['wristband_league_name'] ?? 'SUPER LEAGUE 2026');
        $this->drawTextAutoFit($im, $leagueTitle, $xHomeEnd + 8, $xEvEnd - 8, 32, 7.5, 5.0, $textColor, $this->fontBold);

        $eventName = strtoupper($event->name);
        [$rAccent, $gAccent, $bAccent] = $this->hexToRgb($palette['accentText']);
        $accentColor = imagecolorallocate($im, $rAccent, $gAccent, $bAccent);
        $this->drawTextAutoFit($im, $eventName, $xHomeEnd + 8, $xEvEnd - 8, 65, 13.0, 8.0, $accentColor, $this->fontBold);

        $dateStr = strtoupper($event->event_start_date ? $event->event_start_date->format('l, d M Y') : 'HARI ACARA');
        if ($event->gate_open_at) {
            $dateStr .= ' • ' . $event->gate_open_at->format('H.i') . ' WIB';
        }
        $venueStr = strtoupper(trim(($event->venue ?? '') . ($event->city ? ', ' . $event->city : '')));
        $evDetails = $dateStr . ($venueStr ? ' | ' . $venueStr : '');
        $this->drawTextAutoFit($im, $evDetails, $xHomeEnd + 8, $xEvEnd - 8, 96, 7.0, 5.0, $textColor, $this->fontRegular);

        // Away Club Logo Section: 15mm (x: 136mm to 151mm)
        $xAwayEnd = (int) round(151 * $scale);
        $awayLogoPath = $this->resolveAssetPath($event->meta['wristband_away_club_logo'] ?? null);
        $awayImg = $this->loadGdImage($awayLogoPath);
        if ($awayImg) {
            $this->drawImageFitted($im, $awayImg, $xEvEnd + 4, 18, $xAwayEnd - 4, $height - 18);
            imagedestroy($awayImg);
        } else {
            $this->drawCirclePlaceholder($im, ($xEvEnd + $xAwayEnd) / 2, $height / 2, 34, 'AWAY', $textColor, $this->fontBold);
        }

        // Sponsors Section: 36mm (x: 151mm to 187mm)
        $sponsorLogos = $event->meta['wristband_sponsor_logos'] ?? $event->tenant?->meta['wristband_sponsor_logos'] ?? [];
        $sponsorNames = $event->meta['wristband_sponsor_names'] ?? $event->tenant?->meta['wristband_sponsor_names'] ?? ['GenTix', 'BRI', 'Super Soccer', 'Adidas'];

        $this->renderSponsorsSection($im, $xAwayEnd, $xBandEnd, $height, $sponsorLogos, $sponsorNames, $textColor);

        // 4. Right Blank Space: 28mm (x: 187mm to 215mm)
        imagefilledrectangle($im, $xBandEnd, 0, $width, $height, $white);
        imageline($im, $xBandEnd, 0, $xBandEnd, $height, $grayBorder);

        return $im;
    }

    /**
     * Render a custom template wristband.
     */
    protected function renderCustomWristband(
        object $ticket,
        TicketCategory $category,
        Event $event,
        WristbandTemplate $template,
        int $width,
        int $height,
        float $scale
    ): \GdImage {
        $cacheKey = $template->id . '_' . $category->id . '_' . $event->id . '_' . ($template->updated_at?->timestamp ?? 0) . '_' . ($template->background_image ?? '');
        if (!$this->cachedCustomBase || $this->cachedCustomKey !== $cacheKey) {
            if ($this->cachedCustomBase) {
                imagedestroy($this->cachedCustomBase);
            }
            $this->cachedCustomBase = $this->buildCustomBaseCanvas($category, $event, $template, $width, $height, $scale);
            $this->cachedCustomKey = $cacheKey;
        }

        // Clone pre-rendered base canvas (background image & static texts)
        $im = imagecreatetruecolor($width, $height);
        imagecopy($im, $this->cachedCustomBase, 0, 0, 0, 0, $width, $height);

        $code = $ticket->ticket_code ?? $ticket->wristband_code ?? 'WB-0000';
        $cols = $template->getMergedColumnsConfig();
        $white = imagecolorallocate($im, 255, 255, 255);

        // Render only ticket-specific dynamic elements
        foreach ($cols as $cKey => $col) {
            if (empty($col['enabled']) || !in_array($cKey, ['qr_code', 'wristband_code', 'seat_number'])) {
                continue;
            }

            $xMm = (float) str_replace(',', '.', (string)($col['x'] ?? 0));
            $yMm = (float) str_replace(',', '.', (string)($col['y'] ?? 0));
            $xPx = (int) round($xMm * $scale);
            $yPx = (int) round($yMm * $scale);

            $colHex = $this->normalizeHex($col['color'] ?? '#111827');
            [$cr, $cg, $cb] = $this->hexToRgb($colHex);
            $textColor = imagecolorallocate($im, $cr, $cg, $cb);

            $isBold = !empty($col['font_weight']) && (int)$col['font_weight'] >= 700;
            $font = $isBold ? $this->fontBold : $this->fontRegular;

            $ptSize = (float) preg_replace('/[^0-9.]/', '', (string)($col['font_size'] ?? '6')) ?: 6.0;
            $fontSize = max(5.0, $ptSize * 1.5);

            if ($cKey === 'qr_code') {
                $qrSizeMm = (float) str_replace(',', '.', (string)($col['qr_size'] ?? 14.0));
                $qrSizePx = (int) round($qrSizeMm * $scale);

                // Draw small white box with margin
                imagefilledrectangle($im, $xPx, $yPx, $xPx + $qrSizePx, $yPx + $qrSizePx, $white);

                $qrMatrix = $this->generateQrMatrix($code);
                if ($qrMatrix) {
                    $mw = $qrMatrix->getWidth();
                    $modSize = $qrSizePx / $mw;
                    $black = imagecolorallocate($im, 0, 0, 0);
                    for ($my = 0; $my < $mw; $my++) {
                        for ($mx = 0; $mx < $mw; $mx++) {
                            if ($qrMatrix->get($mx, $my) === 1) {
                                $px = (int) round($xPx + $mx * $modSize);
                                $py = (int) round($yPx + $my * $modSize);
                                $pSize = (int) ceil($modSize);
                                imagefilledrectangle($im, $px, $py, $px + $pSize, $py + $pSize, $black);
                            }
                        }
                    }
                }
            } elseif ($cKey === 'wristband_code') {
                $this->drawTextBaseline($im, $code, $xPx, $yPx, $fontSize, $textColor, $font);
            } elseif ($cKey === 'seat_number') {
                $sStr = '#' . sprintf('%04d', $ticket->index ?? 1);
                $this->drawTextBaseline($im, $sStr, $xPx, $yPx, $fontSize, $textColor, $font);
            }
        }

        return $im;
    }

    /**
     * Build the reusable base canvas for custom mode (background and static texts).
     */
    protected function buildCustomBaseCanvas(
        TicketCategory $category,
        Event $event,
        WristbandTemplate $template,
        int $width,
        int $height,
        float $scale
    ): \GdImage {
        $im = imagecreatetruecolor($width, $height);
        imagealphablending($im, true);
        imagesavealpha($im, true);

        $white = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, $width, $height, $white);

        // Load & resample background image once
        $bgPath = $this->resolveAssetPath($template->background_image);
        $bgImg = $this->loadGdImage($bgPath);
        if ($bgImg) {
            imagecopyresampled($im, $bgImg, 0, 0, 0, 0, $width, $height, imagesx($bgImg), imagesy($bgImg));
            imagedestroy($bgImg);
        }

        $cols = $template->getMergedColumnsConfig();

        // Render static texts once
        foreach ($cols as $cKey => $col) {
            if (empty($col['enabled']) || in_array($cKey, ['qr_code', 'wristband_code', 'seat_number'])) {
                continue;
            }

            $xMm = (float) str_replace(',', '.', (string)($col['x'] ?? 0));
            $yMm = (float) str_replace(',', '.', (string)($col['y'] ?? 0));
            $xPx = (int) round($xMm * $scale);
            $yPx = (int) round($yMm * $scale);

            $colHex = $this->normalizeHex($col['color'] ?? '#111827');
            [$cr, $cg, $cb] = $this->hexToRgb($colHex);
            $textColor = imagecolorallocate($im, $cr, $cg, $cb);

            $isBold = !empty($col['font_weight']) && (int)$col['font_weight'] >= 700;
            $font = $isBold ? $this->fontBold : $this->fontRegular;

            // Approximate pt font size to px
            $ptSize = (float) preg_replace('/[^0-9.]/', '', (string)($col['font_size'] ?? '6')) ?: 6.0;
            $fontSize = max(5.0, $ptSize * 1.5);

            if ($cKey === 'category_name') {
                $this->drawTextBaseline($im, strtoupper($category->name), $xPx, $yPx, $fontSize, $textColor, $font);
            } elseif ($cKey === 'event_name') {
                $this->drawTextBaseline($im, strtoupper($event->name), $xPx, $yPx, $fontSize, $textColor, $font);
            } elseif ($cKey === 'event_date') {
                $dStr = $event->event_start_date ? $event->event_start_date->format('d M Y') : '';
                if ($event->gate_open_at) {
                    $dStr .= ' • ' . $event->gate_open_at->format('H.i') . ' WIB';
                }
                $this->drawTextBaseline($im, strtoupper($dStr), $xPx, $yPx, $fontSize, $textColor, $font);
            } elseif ($cKey === 'venue') {
                $vStr = strtoupper(trim(($event->venue ?? '') . ($event->city ? ', ' . $event->city : '')));
                $this->drawTextBaseline($im, $vStr, $xPx, $yPx, $fontSize, $textColor, $font);
            } elseif ($cKey === 'price') {
                $pStr = $category->price > 0 ? 'Rp ' . number_format($category->price, 0, ',', '.') : 'FREE';
                $this->drawTextBaseline($im, $pStr, $xPx, $yPx, $fontSize, $textColor, $font);
            } elseif ($cKey === 'custom_text') {
                $cStr = strtoupper($col['custom_value'] ?? 'NON-REFUNDABLE');
                $this->drawTextBaseline($im, $cStr, $xPx, $yPx, $fontSize, $textColor, $font);
            }
        }

        return $im;
    }

    /**
     * Helper to draw text with top-left origin instead of baseline.
     */
    protected function drawTextBaseline(
        \GdImage $im,
        string $text,
        int $x,
        int $y,
        float $fontSize,
        int $color,
        string $font
    ): void {
        $box = imagettfbbox($fontSize, 0, $font, $text);
        $fontHeight = abs($box[7] - $box[1]);
        imagettftext($im, $fontSize, 0, $x, $y + (int) round($fontHeight), $color, $font, $text);
    }

    /**
     * Draw text centered horizontally between $xLeft and $xRight.
     */
    protected function drawTextCentered(
        \GdImage $im,
        string $text,
        int $xLeft,
        int $xRight,
        int $yBaseline,
        float $fontSize,
        int $color,
        string $font
    ): void {
        $box = imagettfbbox($fontSize, 0, $font, $text);
        $textWidth = abs($box[4] - $box[0]);
        $x = (int) round($xLeft + (($xRight - $xLeft) - $textWidth) / 2);
        imagettftext($im, $fontSize, 0, $x, $yBaseline, $color, $font, $text);
    }

    /**
     * Draw text within boundaries, auto-scaling font size to fit width.
     */
    protected function drawTextAutoFit(
        \GdImage $im,
        string $text,
        int $xLeft,
        int $xRight,
        int $yBaseline,
        float $maxSize,
        float $minSize,
        int $color,
        string $font
    ): void {
        $availWidth = $xRight - $xLeft;
        $size = $maxSize;

        while ($size >= $minSize) {
            $box = imagettfbbox($size, 0, $font, $text);
            $w = abs($box[4] - $box[0]);
            if ($w <= $availWidth) {
                break;
            }
            $size -= 0.5;
        }

        imagettftext($im, $size, 0, $xLeft, $yBaseline, $color, $font, $text);
    }

    /**
     * Render the sponsor grid section.
     */
    protected function renderSponsorsSection(
        \GdImage $im,
        int $xStart,
        int $xEnd,
        int $height,
        array $logos,
        array $names,
        int $textColor
    ): void {
        $cols = 3;
        $rows = 2;
        $cellW = ($xEnd - $xStart) / $cols;
        $cellH = ($height - 24) / $rows;
        $topPad = 12;

        $items = !empty($logos) ? array_values($logos) : array_values($names);

        $idx = 0;
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                if (!isset($items[$idx])) {
                    break;
                }
                $item = $items[$idx];
                $cellX1 = (int) round($xStart + $c * $cellW + 2);
                $cellX2 = (int) round($xStart + ($c + 1) * $cellW - 2);
                $cellY1 = (int) round($topPad + $r * $cellH + 2);
                $cellY2 = (int) round($topPad + ($r + 1) * $cellH - 2);

                if (!empty($logos)) {
                    $path = $this->resolveAssetPath($item);
                    $logoImg = $this->loadGdImage($path);
                    if ($logoImg) {
                        $this->drawImageFitted($im, $logoImg, $cellX1, $cellY1, $cellX2, $cellY2);
                        imagedestroy($logoImg);
                    }
                } else {
                    $this->drawTextAutoFit($im, strtoupper((string)$item), $cellX1, $cellX2, (int)($cellY1 + $cellH * 0.7), 6.5, 4.0, $textColor, $this->fontBold);
                }

                $idx++;
            }
        }
    }

    /**
     * Draw an image fitted into a bounding box keeping aspect ratio.
     */
    protected function drawImageFitted(
        \GdImage $dest,
        \GdImage $src,
        int $x1,
        int $y1,
        int $x2,
        int $y2
    ): void {
        $boxW = $x2 - $x1;
        $boxH = $y2 - $y1;
        $srcW = imagesx($src);
        $srcH = imagesy($src);

        if ($srcW <= 0 || $srcH <= 0 || $boxW <= 0 || $boxH <= 0) {
            return;
        }

        $scale = min($boxW / $srcW, $boxH / $srcH);
        $newW = (int) round($srcW * $scale);
        $newH = (int) round($srcH * $scale);

        $destX = (int) round($x1 + ($boxW - $newW) / 2);
        $destY = (int) round($y1 + ($boxH - $newH) / 2);

        imagecopyresampled($dest, $src, $destX, $destY, 0, 0, $newW, $newH, $srcW, $srcH);
    }

    /**
     * Draw circle placeholder with initial text.
     */
    protected function drawCirclePlaceholder(
        \GdImage $im,
        float $cx,
        float $cy,
        int $diameter,
        string $text,
        int $color,
        string $font
    ): void {
        imagearc($im, (int)$cx, (int)$cy, $diameter, $diameter, 0, 360, $color);
        $this->drawTextCentered($im, $text, (int)($cx - $diameter / 2), (int)($cx + $diameter / 2), (int)($cy + 5), 7.0, $color, $font);
    }

    /**
     * Generate QR byte matrix using BaconQrCode.
     */
    protected function generateQrMatrix(string $code): ?\BaconQrCode\Encoder\ByteMatrix
    {
        try {
            $qr = @Encoder::encode($code, ErrorCorrectionLevel::M());
            return $qr->getMatrix();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Resolve file system path for asset.
     */
    protected function resolveAssetPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (file_exists($path)) {
            return $path;
        }

        // Storage public disk path
        $storagePath = Storage::disk('public')->path($path);
        if (file_exists($storagePath)) {
            return $storagePath;
        }

        $publicPath = public_path($path);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        return null;
    }

    /**
     * Load image as GD resource.
     */
    protected function loadGdImage(?string $path): ?\GdImage
    {
        if (!$path || !file_exists($path)) {
            return null;
        }

        $content = @file_get_contents($path);
        if (!$content) {
            return null;
        }

        $img = @imagecreatefromstring($content);
        return $img ?: null;
    }

    /**
     * Resolve font path with cross-platform fallbacks.
     */
    protected function resolveFont(bool $bold = false): string
    {
        $filename = $bold ? 'arialbd.ttf' : 'arial.ttf';
        $bundled = resource_path('fonts/' . $filename);
        if (file_exists($bundled)) {
            return $bundled;
        }

        $winFont = 'C:/Windows/Fonts/' . $filename;
        if (file_exists($winFont)) {
            return $winFont;
        }

        $linuxFallbacks = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans' . ($bold ? '-Bold' : '') . '.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-' . ($bold ? 'Bold' : 'Regular') . '.ttf',
        ];
        foreach ($linuxFallbacks as $fb) {
            if (file_exists($fb)) {
                return $fb;
            }
        }

        return $bundled;
    }

    /**
     * Convert hex to RGB array.
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Normalize hex color string.
     */
    protected function normalizeHex(?string $hex, string $fallback = '#D71920'): string
    {
        $hex = trim((string) $hex);
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return preg_match('/^[0-9a-fA-F]{6}$/', $hex) ? '#' . strtoupper($hex) : $fallback;
    }

    /**
     * Generate complementary contrast palette.
     */
    protected function contrastPalette(string $hex): array
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $luminance = ((0.299 * $r) + (0.587 * $g) + (0.114 * $b)) / 255;
        $isLight = $luminance > 0.58;

        return [
            'text' => $isLight ? '#111827' : '#FFFFFF',
            'accentText' => $isLight ? '#0F172A' : '#FFFFFF',
            'dark' => $this->mixHex($hex, [0, 0, 0], $isLight ? 0.22 : 0.55),
            'light' => $this->mixHex($hex, [255, 255, 255], $isLight ? 0.18 : 0.26),
        ];
    }

    /**
     * Mix hex color with target RGB.
     */
    protected function mixHex(string $hex, array $target, float $weight): string
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $weight = max(0.0, min(1.0, $weight));
        return sprintf(
            '#%02X%02X%02X',
            round($r + (($target[0] - $r) * $weight)),
            round($g + (($target[1] - $g) * $weight)),
            round($b + (($target[2] - $b) * $weight))
        );
    }
}
