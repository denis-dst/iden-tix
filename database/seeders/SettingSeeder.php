<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'app_name', 'value' => 'IdenTix', 'group' => 'general'],
            ['key' => 'app_tagline', 'value' => 'Connecting Generations Through Every Gate', 'group' => 'general'],
            ['key' => 'meta_description', 'value' => 'IdenTix is the ultimate destination for discovery and access to the world\'s most exciting live events.', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'virtusunity@gmail.com', 'group' => 'general'],
            ['key' => 'contact_phone', 'value' => '083878537818', 'group' => 'general'],
            ['key' => 'address', 'value' => 'DUSUN MANDAH INDUK 00/001 MANDAH, NATAR, LAMPUNG SELATAN, LAMPUNG 35362', 'group' => 'general'],
            
            // Social Media
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/identix', 'group' => 'social'],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/identix', 'group' => 'social'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/identix', 'group' => 'social'],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/identix', 'group' => 'social'],
            
            // Appearance
            ['key' => 'hero_title', 'value' => 'Connecting Generations Through Every Gate.', 'group' => 'appearance'],
            ['key' => 'hero_subtitle', 'value' => 'Bridging the gap between Identity and Tickets. Experience high-tech event management that\'s simple enough for everyone.', 'group' => 'appearance'],
            ['key' => 'app_logo', 'value' => null, 'group' => 'appearance'],
            ['key' => 'app_favicon', 'value' => null, 'group' => 'appearance'],
            ['key' => 'app_icon', 'value' => null, 'group' => 'appearance'],
            ['key' => 'wristband_league_logo', 'value' => null, 'group' => 'appearance'],
            ['key' => 'active_theme', 'value' => 'default', 'group' => 'appearance'],
            ['key' => 'footer_text', 'value' => '&copy; 2026 IdenTix. All rights reserved.', 'group' => 'general'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
