<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$e = App\Models\Event::where('slug', 'persita-vs-persib-5193')->first();
echo json_encode([
    'home_team_name' => $e->home_team_name,
    'away_team_name' => $e->away_team_name,
    'home_team_logo' => $e->home_team_logo,
    'away_team_logo' => $e->away_team_logo,
    'meta' => $e->meta,
    'effective_home' => $e->effective_home_team_logo_url,
    'effective_away' => $e->effective_away_team_logo_url,
    'effective_league' => $e->effective_league_logo_url,
    'tenant_logo' => $e->tenant?->logo
], JSON_PRETTY_PRINT) . PHP_EOL;
