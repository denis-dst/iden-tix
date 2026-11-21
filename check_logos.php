<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$e = App\Models\Event::where('slug', 'persita-vs-persib-5193')->first();
echo "Home Team: " . $e->home_team_name . " | Path: " . $e->home_team_logo . " (Exists: " . (file_exists(public_path('storage/' . $e->home_team_logo)) ? 'YES' : 'NO') . ")\n";
echo "Away Team: " . $e->away_team_name . " | Path: " . $e->away_team_logo . " (Exists: " . (file_exists(public_path('storage/' . $e->away_team_logo)) ? 'YES' : 'NO') . ")\n";
echo "Wristband Home: " . ($e->meta['wristband_home_club_logo'] ?? 'none') . " (Exists: " . (isset($e->meta['wristband_home_club_logo']) && file_exists(public_path('storage/' . $e->meta['wristband_home_club_logo'])) ? 'YES' : 'NO') . ")\n";
echo "Wristband Away: " . ($e->meta['wristband_away_club_logo'] ?? 'none') . " (Exists: " . (isset($e->meta['wristband_away_club_logo']) && file_exists(public_path('storage/' . $e->meta['wristband_away_club_logo'])) ? 'YES' : 'NO') . ")\n";
echo "Wristband League: " . ($e->meta['wristband_league_logo'] ?? 'none') . " (Exists: " . (isset($e->meta['wristband_league_logo']) && file_exists(public_path('storage/' . $e->meta['wristband_league_logo'])) ? 'YES' : 'NO') . ")\n";
echo "Tenant: " . ($e->tenant?->name ?? 'none') . " | Logo: " . ($e->tenant?->logo ?? 'none') . " (Exists: " . (isset($e->tenant?->logo) && file_exists(public_path('storage/' . $e->tenant?->logo)) ? 'YES' : 'NO') . ")\n";

// List all files in wristbands/logos
$files = glob(public_path('storage/wristbands/logos/*'));
echo "Files in storage/wristbands/logos/:\n";
foreach ($files as $f) {
    echo " - " . basename($f) . " (" . filesize($f) . " bytes)\n";
}

// Check other logos in storage/ or public/images/
$other = glob(public_path('storage/logos/*'));
echo "Files in storage/logos/:\n";
foreach ($other as $f) {
    echo " - " . basename($f) . "\n";
}
