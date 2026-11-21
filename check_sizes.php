<?php
require __DIR__ . '/vendor/autoload.php';
$files = [
    'public/storage/wristbands/logos/ZSrAaf7ryljdvQBgAT2e6OpHDmsQHWe84jWkhAKe.webp',
    'public/storage/wristbands/logos/t7RxIJhNN2MBJb83dtnztsyudVhQRVTxMG36mAL4.webp',
    'public/storage/wristbands/logos/EBTbrswNBJ6IE9irK9JztlGDmBcIpfMTA45BByBd.webp',
    'public/storage/wristbands/logos/f3Ml9FD7Uhf1qSHwWWVpWEanxXDTit99LhPozf42.webp',
];
foreach ($files as $f) {
    if (file_exists($f)) {
        $size = getimagesize($f);
        echo "$f: " . ($size ? "{$size[0]}x{$size[1]} ({$size['mime']})" : "unknown") . "\n";
    }
}
