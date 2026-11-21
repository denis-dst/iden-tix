<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$e = App\Models\Event::where('slug', 'persita-vs-persib-5193')->first();
$homePath = Storage::disk('public')->path($e->home_team_logo);
echo "Path: $homePath\n";
if (file_exists($homePath)) {
    $content = file_get_contents($homePath);
    $img = @imagecreatefromstring($content);
    echo "GD imagecreatefromstring: " . ($img ? "SUCCESS (" . imagesx($img) . "x" . imagesy($img) . ")" : "FAILED") . "\n";
    if (!$img) {
        $imgWebp = @imagecreatefromwebp($homePath);
        echo "GD imagecreatefromwebp: " . ($imgWebp ? "SUCCESS" : "FAILED") . "\n";
    }
}
