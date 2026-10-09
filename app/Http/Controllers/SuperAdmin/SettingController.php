<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        $globalNotifications = [
            'email_notifications_enabled' => Setting::where('key', 'global_email_notifications_enabled')->value('value') ?? true,
            'wa_notifications_enabled' => Setting::where('key', 'global_wa_notifications_enabled')->value('value') ?? true,
        ];
        $tenantRegistrationEnabled = (bool) (Setting::where('key', 'tenant_registration_enabled')->value('value') ?? true);
        $activeTheme = Setting::get('active_theme', 'default');

        return view('superadmin.settings.index', compact('settings', 'globalNotifications', 'tenantRegistrationEnabled', 'activeTheme'));
    }

    public function switchTheme(Request $request)
    {
        $theme = $request->input('theme', 'default');
        if (!in_array($theme, ['default', 'new-thema'])) {
            $theme = 'default';
        }

        Setting::updateOrCreate(
            ['key' => 'active_theme'],
            ['value' => $theme, 'group' => 'appearance']
        );

        \Illuminate\Support\Facades\Cache::forget('public_settings_map');

        $themeLabel = ($theme === 'new-thema') ? 'New Thema (TiketMart Style)' : 'Tema Utama (Default Classic)';
        return back()->with('success', "Tema frontend berhasil diubah ke {$themeLabel}!");
    }

    public function update(Request $request)
    {
        // Handle global notification settings
        Setting::updateOrCreate(
            ['key' => 'global_email_notifications_enabled'],
            ['value' => $request->boolean('global_email_notifications_enabled') ? '1' : '0', 'group' => 'notifications']
        );
        Setting::updateOrCreate(
            ['key' => 'global_wa_notifications_enabled'],
            ['value' => $request->boolean('global_wa_notifications_enabled') ? '1' : '0', 'group' => 'notifications']
        );

        // Handle tenant self-registration toggle
        Setting::updateOrCreate(
            ['key' => 'tenant_registration_enabled'],
            ['value' => $request->boolean('tenant_registration_enabled') ? '1' : '0', 'group' => 'features']
        );
        
        $fileKeys = ['app_logo', 'app_favicon', 'app_icon', 'wristband_league_logo'];

        foreach ($fileKeys as $key) {
            if (!$request->hasFile($key)) {
                continue;
            }

            $maxW = in_array($key, ['app_favicon', 'app_icon']) ? 192 : 400;
            $path = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file($key), 'settings', $maxW, 85);
            Setting::updateOrCreate(['key' => $key], [
                'value' => $path,
                'group' => Setting::where('key', $key)->value('group') ?? 'appearance',
            ]);
        }

        $excludedKeys = [
            '_token',
            'global_email_notifications_enabled',
            'global_wa_notifications_enabled',
            'tenant_registration_enabled',
        ];

        foreach ($request->except($excludedKeys) as $key => $value) {
            if (!in_array($key, $fileKeys)) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => Setting::where('key', $key)->value('group') ?? 'appearance']
                );
            }
        }

        \Illuminate\Support\Facades\Cache::forget('public_settings_map');
        \Illuminate\Support\Facades\Cache::forget('setting.global_email_notifications_enabled');
        \Illuminate\Support\Facades\Cache::forget('setting.global_wa_notifications_enabled');

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}
