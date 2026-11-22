<?php

namespace App\Services;

use App\Mail\KioskPhotoMail;
use App\Models\Event;
use App\Models\KioskPhoto;
use App\Models\PhotoboothTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PhotoboothService
{
    /**
     * Process and store a kiosk photobooth capture.
     *
     * @param array $data
     * @return KioskPhoto
     */
    public function saveKioskPhoto(array $data): KioskPhoto
    {
        $token = Str::random(32);
        $directory = 'kiosk_photos';
        Storage::disk('public')->makeDirectory($directory);

        $cardPath = null;
        $rawPath = null;

        // 1. Process Composite Card Image (from Canvas base64)
        if (!empty($data['card_data'])) {
            $cardImageContent = $this->decodeBase64($data['card_data']);
            if ($cardImageContent) {
                $filename = 'card_' . $token . '.jpg';
                $cardPath = $directory . '/' . $filename;
                Storage::disk('public')->put($cardPath, $cardImageContent);
            }
        }

        // 2. Process Raw Camera Photo (if provided)
        if (!empty($data['raw_photo_data'])) {
            $rawImageContent = $this->decodeBase64($data['raw_photo_data']);
            if ($rawImageContent) {
                $filename = 'raw_' . $token . '.jpg';
                $rawPath = $directory . '/' . $filename;
                Storage::disk('public')->put($rawPath, $rawImageContent);
            }
        }

        // 3. Fallback: If card_data was not sent, synthesize it from raw photo using GD
        if (!$cardPath && $rawPath) {
            $event = Event::with('tenant')->find($data['event_id']);
            $template = $event ? $event->getEffectivePhotoboothTemplate() : null;
            $cardPath = $this->synthesizeMatchdayCard($rawPath, $event, $template, $data['customer_name'] ?? 'FAN', $token);
        }

        // 4. Create KioskPhoto record
        $kioskPhoto = KioskPhoto::create([
            'tenant_id'      => $data['tenant_id'],
            'event_id'       => $data['event_id'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'customer_name'  => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'category_name'  => $data['category_name'] ?? null,
            'original_photo' => $rawPath,
            'card_photo'     => $cardPath ?? 'images/kiosk/matchday_card_sample.jpg',
            'token'          => $token,
            'download_count' => 0,
        ]);

        // 5. Send Email with Photo
        if (!empty($kioskPhoto->customer_email)) {
            try {
                Mail::to($kioskPhoto->customer_email)->send(new KioskPhotoMail($kioskPhoto));
                $kioskPhoto->update(['email_sent_at' => now()]);
            } catch (\Throwable $e) {
                Log::warning('Failed sending kiosk photo email: ' . $e->getMessage());
            }
        }

        return $kioskPhoto;
    }

    /**
     * Decode base64 image string to binary.
     */
    protected function decodeBase64(string $base64Data): ?string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            $decoded = base64_decode($base64Data);
            return $decoded ?: null;
        }

        $decoded = base64_decode($base64Data, true);
        return $decoded ?: null;
    }

    /**
     * Synthesize matchday collectible card using GD when only raw photo is present.
     */
    public function synthesizeMatchdayCard(string $rawPath, ?Event $event, ?PhotoboothTemplate $template, string $fanName, string $token): string
    {
        $rawFullPath = Storage::disk('public')->path($rawPath);
        if (!file_exists($rawFullPath)) {
            if (file_exists(public_path($rawPath))) {
                $rawFullPath = public_path($rawPath);
            } elseif (file_exists($rawPath)) {
                $rawFullPath = $rawPath;
            } else {
                return $rawPath;
            }
        }

        $width = 1024;
        $height = 1536;
        $canvas = imagecreatetruecolor($width, $height);

        // 1. Fill background (gradient or custom background)
        $primaryHex = $template->primary_color ?? '#5A2D82';
        [$r, $g, $b] = sscanf($primaryHex, "#%02x%02x%02x");
        $bgColor = imagecolorallocate($canvas, $r ?? 90, $g ?? 45, $b ?? 130);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $bgColor);

        if ($template && $template->background_image && Storage::disk('public')->exists($template->background_image)) {
            $bgImg = @imagecreatefromstring(Storage::disk('public')->get($template->background_image));
            if ($bgImg) {
                imagecopyresampled($canvas, $bgImg, 0, 0, 0, 0, $width, $height, imagesx($bgImg), imagesy($bgImg));
                imagedestroy($bgImg);
            }
        }

        // 2. Load and place raw fan photo
        $rawImg = @imagecreatefromstring(file_get_contents($rawFullPath));
        if ($rawImg) {
            $rawW = imagesx($rawImg);
            $rawH = imagesy($rawImg);
            $destX = 35;
            $destY = 45;
            $destW = $width - 70;
            $destH = 1180;
            imagecopyresampled($canvas, $rawImg, $destX, $destY, 0, 0, $destW, $destH, $rawW, $rawH);
            imagedestroy($rawImg);
        }

        // 3. Draw Frame Overlay if available
        if ($template && $template->frame_overlay && Storage::disk('public')->exists($template->frame_overlay)) {
            $frame = @imagecreatefrompng(Storage::disk('public')->path($template->frame_overlay));
            if ($frame) {
                imagecopyresampled($canvas, $frame, 0, 0, 0, 0, $width, $height, imagesx($frame), imagesy($frame));
                imagedestroy($frame);
            }
        }

        // 4. Draw Club Logos & Graphics using Layout Configuration
        $layout = $template ? $template->getEffectiveLayoutConfig() : (new PhotoboothTemplate())->getEffectiveLayoutConfig();

        if ($event) {
            // Draw League / Penyelenggara Logo
            if (!empty($layout['league_logo']['visible'] ?? true)) {
                $leagueLogoX = round($width * (($layout['league_logo']['x'] ?? 14) / 100));
                $leagueLogoY = round($height * (($layout['league_logo']['y'] ?? 6) / 100));
                $leagueLogoSize = round((($layout['league_logo']['size'] ?? 65) / 400) * $width);
                $this->placeLogoOnCanvas($canvas, $event->effective_league_logo_url, $leagueLogoX, $leagueLogoY, $leagueLogoSize);
            }

            // Draw Home Club Logo
            if (!empty($layout['home_logo']['visible'] ?? true)) {
                $homeLogoX = round($width * (($layout['home_logo']['x'] ?? 24) / 100));
                $homeLogoY = round($height * (($layout['home_logo']['y'] ?? 72) / 100));
                $homeLogoSize = round((($layout['home_logo']['size'] ?? 80) / 400) * $width);
                $this->placeLogoOnCanvas($canvas, $event->effective_home_team_logo_url, $homeLogoX, $homeLogoY, $homeLogoSize);
            }

            // Draw Away Club Logo
            if (!empty($layout['away_logo']['visible'] ?? true)) {
                $awayLogoX = round($width * (($layout['away_logo']['x'] ?? 76) / 100));
                $awayLogoY = round($height * (($layout['away_logo']['y'] ?? 72) / 100));
                $awayLogoSize = round((($layout['away_logo']['size'] ?? 80) / 400) * $width);
                $this->placeLogoOnCanvas($canvas, $event->effective_away_team_logo_url, $awayLogoX, $awayLogoY, $awayLogoSize);
            }

            // 5. Draw Typography using Layout Configuration
            $fontFile = null;
            $possibleFonts = [
                public_path('fonts/Outfit-Bold.ttf'),
                'C:/Windows/Fonts/arialbd.ttf',
                'C:/Windows/Fonts/calibrib.ttf'
            ];
            foreach ($possibleFonts as $f) {
                if (file_exists($f)) {
                    $fontFile = $f;
                    break;
                }
            }

            if ($fontFile) {
                $white = imagecolorallocate($canvas, 255, 255, 255);
                $navy = imagecolorallocate($canvas, 30, 27, 75);
                $orange = imagecolorallocate($canvas, 249, 87, 0);

                // Team Names
                if (!empty($layout['home_name']['visible'] ?? true)) {
                    $hnX = round($width * (($layout['home_name']['x'] ?? 24) / 100));
                    $hnY = round($height * (($layout['home_name']['y'] ?? 81) / 100));
                    $fontSizeHn = round((($layout['home_name']['font_size'] ?? 14) / 400) * $width * 0.75);
                    $boxHome = imagettfbbox($fontSizeHn, 0, $fontFile, $event->effective_home_team_name);
                    $wH = abs($boxHome[2] - $boxHome[0]);
                    imagettftext($canvas, $fontSizeHn, 0, round($hnX - ($wH / 2)), $hnY, $white, $fontFile, $event->effective_home_team_name);
                }

                if (!empty($layout['away_name']['visible'] ?? true)) {
                    $anX = round($width * (($layout['away_name']['x'] ?? 76) / 100));
                    $anY = round($height * (($layout['away_name']['y'] ?? 81) / 100));
                    $fontSizeAn = round((($layout['away_name']['font_size'] ?? 14) / 400) * $width * 0.75);
                    $boxAway = imagettfbbox($fontSizeAn, 0, $fontFile, $event->effective_away_team_name);
                    $wA = abs($boxAway[2] - $boxAway[0]);
                    imagettftext($canvas, $fontSizeAn, 0, round($anX - ($wA / 2)), $anY, $white, $fontFile, $event->effective_away_team_name);
                }

                // VS Badge
                if (!empty($layout['vs_badge']['visible'] ?? true)) {
                    $vsX = round($width * (($layout['vs_badge']['x'] ?? 50) / 100));
                    $vsY = round($height * (($layout['vs_badge']['y'] ?? 73) / 100));
                    $fontSizeVs = round((($layout['vs_badge']['font_size'] ?? 22) / 400) * $width * 0.75);
                    $boxVs = imagettfbbox($fontSizeVs, 0, $fontFile, 'VS');
                    $wVs = abs($boxVs[2] - $boxVs[0]);
                    imagettftext($canvas, $fontSizeVs, 0, round($vsX - ($wVs / 2)), $vsY, $orange, $fontFile, 'VS');
                }

                // Match Details in White Bar
                if (!empty($layout['match_info']['visible'] ?? true)) {
                    $dateStr = $event->event_start_date ? $event->event_start_date->translatedFormat('d F Y') : 'MATCHDAY';
                    $venueStr = $event->venue ?: 'STADION';
                    $seasonStr = $template->header_title ?? ($event->season_name ?: 'I LEAGUE 2026/2027');
                    $matchInfo = strtoupper("{$dateStr}   |   {$venueStr}   |   {$seasonStr}");
                    $miX = round($width * (($layout['match_info']['x'] ?? 50) / 100));
                    $miY = round($height * (($layout['match_info']['y'] ?? 86) / 100));
                    $fontSizeMi = round((($layout['match_info']['font_size'] ?? 11) / 400) * $width * 0.75);
                    $boxMatch = imagettfbbox($fontSizeMi, 0, $fontFile, $matchInfo);
                    $wM = abs($boxMatch[2] - $boxMatch[0]);
                    imagettftext($canvas, $fontSizeMi, 0, round($miX - ($wM / 2)), $miY, $white, $fontFile, $matchInfo);
                }

                // Fan Name
                if (!empty($layout['fan_name']['visible'] ?? true)) {
                    $fanStr = strtoupper($fanName);
                    $fnX = round($width * (($layout['fan_name']['x'] ?? 50) / 100));
                    $fnY = round($height * (($layout['fan_name']['y'] ?? 93) / 100));
                    $fontSizeFn = round((($layout['fan_name']['font_size'] ?? 16) / 400) * $width * 0.75);
                    $boxFan = imagettfbbox($fontSizeFn, 0, $fontFile, $fanStr);
                    $wF = abs($boxFan[2] - $boxFan[0]);
                    imagettftext($canvas, $fontSizeFn, 0, round($fnX - ($wF / 2)), $fnY, $white, $fontFile, $fanStr);
                }
            }
        }

        // Output to card file
        $outRelative = 'kiosk_photos/card_' . $token . '.jpg';
        $outFull = Storage::disk('public')->path($outRelative);
        imagejpeg($canvas, $outFull, 92);
        imagedestroy($canvas);

        return $outRelative;
    }

    /**
     * Helper to load and draw centered logo on GD canvas.
     */
    protected function placeLogoOnCanvas($canvas, ?string $logoUrlOrPath, int $cx, int $cy, int $maxDiameter): void
    {
        if (empty($logoUrlOrPath)) return;

        $content = null;
        if (str_starts_with($logoUrlOrPath, 'data:')) {
            $content = $this->decodeBase64($logoUrlOrPath);
        } elseif (str_starts_with($logoUrlOrPath, 'http')) {
            $parsed = parse_url($logoUrlOrPath, PHP_URL_PATH);
            $localRel = ltrim(preg_replace('#^/storage/#', '', $parsed), '/');
            if (Storage::disk('public')->exists($localRel)) {
                $content = Storage::disk('public')->get($localRel);
            } elseif (file_exists(public_path('storage/' . $localRel))) {
                $content = file_get_contents(public_path('storage/' . $localRel));
            } else {
                $content = @file_get_contents($logoUrlOrPath);
            }
        } elseif (file_exists($logoUrlOrPath)) {
            $content = file_get_contents($logoUrlOrPath);
        } elseif (file_exists(public_path('storage/' . $logoUrlOrPath))) {
            $content = file_get_contents(public_path('storage/' . $logoUrlOrPath));
        } elseif (Storage::disk('public')->exists($logoUrlOrPath)) {
            $content = Storage::disk('public')->get($logoUrlOrPath);
        }

        if (!$content) return;

        $logo = @imagecreatefromstring($content);
        if (!$logo) return;

        $lw = imagesx($logo);
        $lh = imagesy($logo);
        if ($lw <= 0 || $lh <= 0) return;

        $aspect = $lw / $lh;
        $w = $maxDiameter;
        $h = $maxDiameter;
        if ($aspect > 1) {
            $h = $maxDiameter / $aspect;
        } else {
            $w = $maxDiameter * $aspect;
        }

        imagecopyresampled($canvas, $logo, round($cx - ($w / 2)), round($cy - ($h / 2)), 0, 0, round($w), round($h), $lw, $lh);
        imagedestroy($logo);
    }
}
