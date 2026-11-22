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
            $showTeamLogos = $template ? ($template->show_team_logos ?? true) : true;
            $showMatchInfo = $template ? ($template->show_match_info ?? true) : true;
            $showFanName = $template ? ($template->show_fan_name ?? true) : true;
            $showCategory = $template ? ($template->show_category ?? true) : true;
            $showQr = $template ? ($template->show_qr ?? true) : true;

            // Draw League / Penyelenggara Logo
            if (!empty($layout['league_logo']['visible'] ?? true) && $event->effective_league_logo_url) {
                $leagueLogoX = round($width * (($layout['league_logo']['x'] ?? 15) / 100));
                $leagueLogoY = round($height * (($layout['league_logo']['y'] ?? 9) / 100));
                $leagueLogoSize = round((($layout['league_logo']['size'] ?? 65) / 400) * $width);
                $this->placeLogoOnCanvas($canvas, $event->effective_league_logo_url, $leagueLogoX, $leagueLogoY, $leagueLogoSize);
            }

            // Draw Home Club Logo (Respect show_team_logos)
            if ($showTeamLogos && !empty($layout['home_logo']['visible'] ?? true) && $event->effective_home_team_logo_url) {
                $homeLogoX = round($width * (($layout['home_logo']['x'] ?? 24) / 100));
                $homeLogoY = round($height * (($layout['home_logo']['y'] ?? 68) / 100));
                $homeLogoSize = round((($layout['home_logo']['size'] ?? 85) / 400) * $width);
                $this->placeLogoOnCanvas($canvas, $event->effective_home_team_logo_url, $homeLogoX, $homeLogoY, $homeLogoSize);
            }

            // Draw Away Club Logo (Respect show_team_logos)
            if ($showTeamLogos && !empty($layout['away_logo']['visible'] ?? true) && $event->effective_away_team_logo_url) {
                $awayLogoX = round($width * (($layout['away_logo']['x'] ?? 76) / 100));
                $awayLogoY = round($height * (($layout['away_logo']['y'] ?? 68) / 100));
                $awayLogoSize = round((($layout['away_logo']['size'] ?? 85) / 400) * $width);
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
                $amber = imagecolorallocate($canvas, 245, 158, 11);
                $muted = imagecolorallocate($canvas, 203, 213, 225);

                // Team Names (Respect show_team_logos)
                if ($showTeamLogos && !empty($layout['home_name']['visible'] ?? true) && !empty($event->effective_home_team_name)) {
                    $hnX = round($width * (($layout['home_name']['x'] ?? 24) / 100));
                    $hnY = round($height * (($layout['home_name']['y'] ?? 76) / 100));
                    $fontSizeHn = round((($layout['home_name']['font_size'] ?? 14) / 400) * $width);
                    $boxHome = imagettfbbox($fontSizeHn, 0, $fontFile, $event->effective_home_team_name);
                    $wH = abs($boxHome[2] - $boxHome[0]);
                    imagettftext($canvas, $fontSizeHn, 0, round($hnX - ($wH / 2)), $hnY, $white, $fontFile, $event->effective_home_team_name);
                }

                if ($showTeamLogos && !empty($layout['away_name']['visible'] ?? true) && !empty($event->effective_away_team_name)) {
                    $anX = round($width * (($layout['away_name']['x'] ?? 76) / 100));
                    $anY = round($height * (($layout['away_name']['y'] ?? 76) / 100));
                    $fontSizeAn = round((($layout['away_name']['font_size'] ?? 14) / 400) * $width);
                    $boxAway = imagettfbbox($fontSizeAn, 0, $fontFile, $event->effective_away_team_name);
                    $wA = abs($boxAway[2] - $boxAway[0]);
                    imagettftext($canvas, $fontSizeAn, 0, round($anX - ($wA / 2)), $anY, $white, $fontFile, $event->effective_away_team_name);
                }

                // VS Badge (Only draw if explicitly set to visible: true; defaults to false as requested: "di foto nya hilangkan karena saya akan tempel pada desain gambarnya")
                if ($showTeamLogos && !empty($layout['vs_badge']['visible']) && $layout['vs_badge']['visible'] === true) {
                    $vsX = round($width * (($layout['vs_badge']['x'] ?? 50) / 100));
                    $vsY = round($height * (($layout['vs_badge']['y'] ?? 74) / 100));
                    $fontSizeVs = round((($layout['vs_badge']['font_size'] ?? 22) / 400) * $width);
                    $boxVs = imagettfbbox($fontSizeVs, 0, $fontFile, 'VS');
                    $wVs = abs($boxVs[2] - $boxVs[0]);
                    imagettftext($canvas, $fontSizeVs, 0, round($vsX - ($wVs / 2)), $vsY, $amber, $fontFile, 'VS');
                }

                // Header Slogan (Hide if both header_title and header_subtitle are empty)
                $hasTitle = !empty(trim($template->header_title ?? ''));
                $hasSubtitle = !empty(trim($template->header_subtitle ?? ''));
                if (($hasTitle || $hasSubtitle) && !empty($layout['header_slogan']['visible'] ?? true)) {
                    $hsX = round($width * (($layout['header_slogan']['x'] ?? 50) / 100));
                    $hsY = round($height * (($layout['header_slogan']['y'] ?? 5) / 100));
                    $fontSizeHs = round((($layout['header_slogan']['font_size'] ?? 14) / 400) * $width);

                    if ($hasTitle) {
                        $boxT = imagettfbbox($fontSizeHs, 0, $fontFile, $template->header_title);
                        $wT = abs($boxT[2] - $boxT[0]);
                        imagettftext($canvas, $fontSizeHs, 0, round($hsX - ($wT / 2)), $hsY, $white, $fontFile, $template->header_title);
                    }
                    if ($hasSubtitle) {
                        $fontSizeSub = round($fontSizeHs * 0.7);
                        $subY = $hasTitle ? $hsY + round($fontSizeHs * 1.3) : $hsY;
                        $boxS = imagettfbbox($fontSizeSub, 0, $fontFile, $template->header_subtitle);
                        $wS = abs($boxS[2] - $boxS[0]);
                        imagettftext($canvas, $fontSizeSub, 0, round($hsX - ($wS / 2)), $subY, $orange, $fontFile, $template->header_subtitle);
                    }
                }

                // Match Details (Respect show_match_info - Date and Venue only)
                if ($showMatchInfo && !empty($layout['match_info']['visible'] ?? true)) {
                    $parts = array_filter([
                        $event->event_start_date ? $event->event_start_date->translatedFormat('d F Y') : null,
                        $event->venue ?: null,
                    ]);
                    $matchInfo = strtoupper(implode('   •   ', $parts));
                    if (!empty($matchInfo)) {
                        $miX = round($width * (($layout['match_info']['x'] ?? 50) / 100));
                        $miY = round($height * (($layout['match_info']['y'] ?? 94) / 100));
                        $fontSizeMi = round((($layout['match_info']['font_size'] ?? 11) / 400) * $width);
                        $boxMatch = imagettfbbox($fontSizeMi, 0, $fontFile, $matchInfo);
                        $wM = abs($boxMatch[2] - $boxMatch[0]);
                        imagettftext($canvas, $fontSizeMi, 0, round($miX - ($wM / 2)), $miY, $white, $fontFile, $matchInfo);
                    }
                }

                // Category Badge (Respect show_category & category_label_format)
                if ($showCategory && !empty($template->category_label_format) && !empty($layout['category_badge']['visible'] ?? true)) {
                    $catX = round($width * (($layout['category_badge']['x'] ?? 50) / 100));
                    $catY = round($height * (($layout['category_badge']['y'] ?? 80) / 100));
                    $fontSizeCat = round((($layout['category_badge']['font_size'] ?? 16) / 400) * $width);
                    $catText = strtoupper(str_replace('{category}', 'VIP BARAT', $template->category_label_format));
                    $boxCat = imagettfbbox($fontSizeCat, 0, $fontFile, $catText);
                    $wCat = abs($boxCat[2] - $boxCat[0]);
                    imagettftext($canvas, $fontSizeCat, 0, round($catX - ($wCat / 2)), $catY, $orange, $fontFile, $catText);
                }

                // Fan Name (Respect show_fan_name - Clean text without background as custom frame already has its ribbon)
                if ($showFanName && !empty($layout['fan_name']['visible'] ?? true)) {
                    $fanStr = strtoupper($fanName);
                    $fnX = round($width * (($layout['fan_name']['x'] ?? 50) / 100));
                    $fnY = round($height * (($layout['fan_name']['y'] ?? 86) / 100));
                    $fontSizeFn = round((($layout['fan_name']['font_size'] ?? 18) / 400) * $width);
                    $boxFan = imagettfbbox($fontSizeFn, 0, $fontFile, $fanStr);
                    $wF = abs($boxFan[2] - $boxFan[0]);

                    imagettftext($canvas, $fontSizeFn, 0, round($fnX - ($wF / 2)), $fnY, $white, $fontFile, $fanStr);
                }

                // Footer Text (Respect footer_text null/empty check)
                if (!empty(trim($template->footer_text ?? '')) && !empty($layout['footer_text']['visible'] ?? true)) {
                    $ftX = round($width * (($layout['footer_text']['x'] ?? 50) / 100));
                    $ftY = round($height * (($layout['footer_text']['y'] ?? 97) / 100));
                    $fontSizeFt = round((($layout['footer_text']['font_size'] ?? 9) / 400) * $width);
                    $ftText = strtoupper(trim($template->footer_text));
                    $boxFt = imagettfbbox($fontSizeFt, 0, $fontFile, $ftText);
                    $wFt = abs($boxFt[2] - $boxFt[0]);
                    imagettftext($canvas, $fontSizeFt, 0, round($ftX - ($wFt / 2)), $ftY, $muted, $fontFile, $ftText);
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
