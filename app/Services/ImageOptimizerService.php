<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Upload an image, resize if necessary, and save as WebP format.
     *
     * @param UploadedFile $file
     * @param string $directory Relative directory in public storage (e.g. 'events/backgrounds')
     * @param int $maxWidth Max width to downscale large images (default: 1200)
     * @param int $quality WebP quality 0-100 (default: 80)
     * @return string Stored relative path (e.g. 'events/backgrounds/abc.webp')
     */
    public static function uploadAndOptimize(UploadedFile $file, string $directory = 'events/backgrounds', int $maxWidth = 1200, int $quality = 80): string
    {
        $relativeDir = trim($directory, '/');

        // Attempt WebP conversion using temp file if GD and imagewebp are available
        if (extension_loaded('gd') && function_exists('imagewebp')) {
            $tempPath = null;
            try {
                $filename = Str::random(40) . '.webp';
                $tempPath = tempnam(sys_get_temp_dir(), 'imgopt_');

                $sourcePath = $file->getRealPath();
                if ($sourcePath && file_exists($sourcePath)) {
                    $converted = self::convertAndSaveAsWebp($sourcePath, $tempPath, $maxWidth, $quality);

                    if ($converted && file_exists($tempPath) && filesize($tempPath) > 0) {
                        $storedPath = $relativeDir . '/' . $filename;
                        $stored = Storage::disk('public')->put($storedPath, file_get_contents($tempPath));
                        @unlink($tempPath);

                        if ($stored) {
                            return $storedPath;
                        }
                    }
                }

                if ($tempPath && file_exists($tempPath)) {
                    @unlink($tempPath);
                }
            } catch (\Throwable $e) {
                if ($tempPath && file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                \Illuminate\Support\Facades\Log::warning('ImageOptimizerService WebP conversion skipped: ' . $e->getMessage());
            }
        }

        // Safe fallback: Standard Laravel store using Storage disk
        try {
            return $file->store($relativeDir, 'public');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ImageOptimizerService fallback store failed: ' . $e->getMessage());
            $filename = Str::random(40) . '.' . ($file->getClientOriginalExtension() ?: 'png');
            $storedPath = $relativeDir . '/' . $filename;
            Storage::disk('public')->putFileAs($relativeDir, $file, $filename);
            return $storedPath;
        }
    }

    /**
     * Convert an image file to WebP and downscale if it exceeds max width.
     */
    public static function convertAndSaveAsWebp(string $sourcePath, string $targetPath, int $maxWidth = 1200, int $quality = 80): bool
    {
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            return false;
        }

        if (!file_exists($sourcePath)) {
            return false;
        }

        try {
            $imageInfo = @getimagesize($sourcePath);
            if (!$imageInfo) {
                return false;
            }

            $mime = $imageInfo['mime'];
            $srcImg = match ($mime) {
                'image/jpeg', 'image/jpg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($sourcePath) : false,
                'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($sourcePath) : false,
                'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
                default => false,
            };

            if (!$srcImg) {
                return false;
            }

            // Ensure true color for indexed/paletted PNGs so imagewebp does not fail
            if (function_exists('imageistruecolor') && !imageistruecolor($srcImg) && function_exists('imagepalettetotruecolor')) {
                imagepalettetotruecolor($srcImg);
            }

            if (function_exists('imagealphablending')) {
                imagealphablending($srcImg, false);
            }
            if (function_exists('imagesavealpha')) {
                imagesavealpha($srcImg, true);
            }

            $origW = imagesx($srcImg);
            $origH = imagesy($srcImg);

            if ($origW > $maxWidth && $origW > 0) {
                $newW = $maxWidth;
                $newH = (int) max(1, round($origH * ($maxWidth / $origW)));
                $dstImg = imagecreatetruecolor($newW, $newH);

                if (function_exists('imagealphablending')) {
                    imagealphablending($dstImg, false);
                }
                if (function_exists('imagesavealpha')) {
                    imagesavealpha($dstImg, true);
                }

                imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                imagedestroy($srcImg);
                $srcImg = $dstImg;
            }

            // Ensure target directory exists if a direct path is provided
            $dir = dirname($targetPath);
            if (!file_exists($dir)) {
                @mkdir($dir, 0755, true);
            }

            $result = @imagewebp($srcImg, $targetPath, $quality);
            imagedestroy($srcImg);

            return (bool) $result;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Optimize existing file in storage and generate .webp sibling if needed.
     */
    public static function optimizeFile(string $fullPath, int $maxWidth = 1200, int $quality = 80): ?string
    {
        if (!file_exists($fullPath)) {
            return null;
        }

        try {
            $webpPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $fullPath);
            if (self::convertAndSaveAsWebp($fullPath, $webpPath, $maxWidth, $quality)) {
                return $webpPath;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}
