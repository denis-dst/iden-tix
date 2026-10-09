<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'identix-org'],
            [
                'name' => 'IdenTix Organization',
                'email' => 'contact@iden-tix.com',
                'status' => 'active',
            ]
        );

        $events = [
            [
                'name' => 'Neon Horizon Live: Summer Tour',
                'description' => 'Experience the ultimate synth-rock performance under the stars. Limited backstage passes available.',
                'venue' => 'Skyline Arena',
                'city' => 'Jakarta',
                'background_image' => 'concert.png',
                'event_start_date' => now()->addDays(30)->setTime(20, 0),
                'event_end_date' => now()->addDays(30)->setTime(23, 0),
                'status' => 'published',
            ],
            [
                'name' => 'Global AI Summit 2026',
                'description' => 'Connect with world-class engineers and visionaries defining the future of artificial intelligence.',
                'venue' => 'Innovation Center',
                'city' => 'Bandung',
                'background_image' => 'tech.png',
                'event_start_date' => now()->addDays(45)->setTime(9, 0),
                'event_end_date' => now()->addDays(47)->setTime(17, 0),
                'status' => 'published',
            ],
            [
                'name' => 'Shadows of Eternity',
                'description' => 'A gripping theatrical masterpiece by the Royal Drama Company. A story of love and redemption.',
                'venue' => 'Grand Theater',
                'city' => 'Yogyakarta',
                'background_image' => 'hero.png',
                'event_start_date' => now()->addDays(60)->setTime(19, 30),
                'event_end_date' => now()->addDays(60)->setTime(22, 00),
                'status' => 'published',
            ],
        ];

        foreach ($events as $eventData) {
            $eventData['tenant_id'] = $tenant->id;
            $eventData['slug'] = Str::slug($eventData['name']);
            Event::updateOrCreate(['slug' => $eventData['slug']], $eventData);
        }
    }
}
