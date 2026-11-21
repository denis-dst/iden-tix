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
            return $rawPath;
        }

        $width = 1024;
        $height = 1536;
        $canvas = imagecreatetruecolor($width, $height);

        // Fill club primary color
        $primaryHex = $template->primary_color ?? '#5A2D82';
        [$r, $g, $b] = sscanf($primaryHex, "#%02x%02x%02x");
        $bgColor = imagecolorallocate($canvas, $r ?? 90, $g ?? 45, $b ?? 130);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $bgColor);

        // Load raw photo
        $rawImg = @imagecreatefromstring(file_get_contents($rawFullPath));
        if ($rawImg) {
            $rawW = imagesx($rawImg);
            $rawH = imagesy($rawImg);
            // Place fan photo in top 65% area
            $destW = $width - 80;
            $destH = 960;
            imagecopyresampled($canvas, $rawImg, 40, 60, 0, 0, $destW, $destH, $rawW, $rawH);
            imagedestroy($rawImg);
        }

        // Output to card file
        $outRelative = 'kiosk_photos/card_' . $token . '.jpg';
        $outFull = Storage::disk('public')->path($outRelative);
        imagejpeg($canvas, $outFull, 90);
        imagedestroy($canvas);

        return $outRelative;
    }
}
