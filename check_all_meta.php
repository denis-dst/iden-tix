<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$events = App\Models\Event::whereNotNull('meta')->get();
foreach ($events as $ev) {
    if (isset($ev->meta['wristband_league_logo']) || isset($ev->meta['wristband_home_club_logo'])) {
        echo "Event {$ev->id} ({$ev->name}):\n";
        echo "  league: " . ($ev->meta['wristband_league_logo'] ?? 'none') . "\n";
        echo "  home: " . ($ev->meta['wristband_home_club_logo'] ?? 'none') . "\n";
        echo "  away: " . ($ev->meta['wristband_away_club_logo'] ?? 'none') . "\n";
    }
}
