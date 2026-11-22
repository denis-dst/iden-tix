<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PhotoboothTemplate;
use App\Services\ImageOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoboothController extends Controller
{
    /**
     * Show Photobooth Template settings for an event / match.
     */
    public function edit(Event $event)
    {
        $this->authorizeTenant($event);

        $template = PhotoboothTemplate::firstOrCreate(
            ['event_id' => $event->id],
            [
                'tenant_id'       => $event->tenant_id,
                'name'            => $event->name . ' Matchday Template',
                'is_active'       => true,
                'primary_color'   => '#5A2D82',
                'secondary_color' => '#F95700',
                'header_title'    => $event->season_name ?: 'I LEAGUE 2026/2027',
                'header_subtitle' => 'FOOTBALL UNITES US',
                'badge_text'      => 'MATCHDAY COLLECTIBLE',
                'footer_text'     => 'SATU LIGA, SATU SEMANGAT',
                'show_qr'         => true,
                'show_fan_name'   => true,
                'show_match_info' => true,
                'show_team_logos' => true,
            ]
        );

        return view('organizer.photobooth.edit', compact('event', 'template'));
    }

    /**
     * Update Photobooth Template for an event / match.
     */
    public function update(Request $request, Event $event)
    {
        $this->authorizeTenant($event);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'is_active'       => 'nullable|boolean',
            'primary_color'   => ['required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'secondary_color' => ['required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'header_title'    => 'nullable|string|max:100',
            'header_subtitle' => 'nullable|string|max:100',
            'badge_text'            => 'nullable|string|max:100',
            'category_label_format' => 'nullable|string|max:100',
            'footer_text'           => 'nullable|string|max:150',
            'show_qr'               => 'nullable|boolean',
            'show_fan_name'         => 'nullable|boolean',
            'show_category'         => 'nullable|boolean',
            'show_match_info'       => 'nullable|boolean',
            'show_team_logos'       => 'nullable|boolean',
            'layout_config'         => 'nullable',
            'frame_overlay'         => 'nullable|image|mimes:png|max:4096',
            'background_image'      => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'league_logo'           => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'home_team_logo'        => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'away_team_logo'        => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'remove_frame_overlay'    => 'nullable|boolean',
            'remove_background_image' => 'nullable|boolean',
        ]);

        $template = PhotoboothTemplate::firstOrNew(['event_id' => $event->id]);
        $template->tenant_id = $event->tenant_id;
        $template->name = $validated['name'];
        $template->is_active = $request->has('is_active');
        $template->primary_color = $validated['primary_color'];
        $template->secondary_color = $validated['secondary_color'];
        $template->header_title = $validated['header_title'] ?? 'I LEAGUE 2026/2027';
        $template->header_subtitle = $validated['header_subtitle'] ?? 'FOOTBALL UNITES US';
        $template->badge_text = $validated['badge_text'] ?? 'MATCHDAY COLLECTIBLE';
        $template->category_label_format = $validated['category_label_format'] ?? '{category} • MATCHDAY COLLECTIBLE';
        $template->footer_text = $validated['footer_text'] ?? 'SATU LIGA, SATU SEMANGAT';
        $template->show_qr = $request->has('show_qr');
        $template->show_fan_name = $request->has('show_fan_name');
        $template->show_category = $request->has('show_category');
        $template->show_match_info = $request->has('show_match_info');
        $template->show_team_logos = $request->has('show_team_logos');

        // Handle Layout Drag & Drop Config
        if ($request->filled('layout_config')) {
            $raw = $request->input('layout_config');
            $layout = is_array($raw) ? $raw : json_decode($raw, true);
            if ($layout) {
                $template->layout_config = $layout;
            }
        }

        // Handle Custom Frame Overlay Upload or Removal
        if ($request->hasFile('frame_overlay')) {
            if ($template->frame_overlay && Storage::disk('public')->exists($template->frame_overlay)) {
                Storage::disk('public')->delete($template->frame_overlay);
            }
            $template->frame_overlay = $request->file('frame_overlay')->store('photobooth/frames', 'public');
        } elseif ($request->boolean('remove_frame_overlay')) {
            if ($template->frame_overlay && Storage::disk('public')->exists($template->frame_overlay)) {
                Storage::disk('public')->delete($template->frame_overlay);
            }
            $template->frame_overlay = null;
        }

        // Handle Custom Background Backdrop Upload or Removal
        if ($request->hasFile('background_image')) {
            if ($template->background_image && Storage::disk('public')->exists($template->background_image)) {
                Storage::disk('public')->delete($template->background_image);
            }
            $template->background_image = ImageOptimizerService::uploadAndOptimize(
                $request->file('background_image'),
                'photobooth/backgrounds',
                1200,
                85
            );
        } elseif ($request->boolean('remove_background_image')) {
            if ($template->background_image && Storage::disk('public')->exists($template->background_image)) {
                Storage::disk('public')->delete($template->background_image);
            }
            $template->background_image = null;
        }

        // Handle Quick Logo Uploads directly from Photobooth Editor
        $eventUpdated = false;
        if ($request->hasFile('league_logo')) {
            $path = $request->file('league_logo')->store('wristbands/logos', 'public');
            $meta = $event->meta ?? [];
            $meta['wristband_league_logo'] = $path;
            $event->meta = $meta;
            $eventUpdated = true;
        }
        if ($request->hasFile('home_team_logo')) {
            $path = $request->file('home_team_logo')->store('wristbands/logos', 'public');
            $event->home_team_logo = $path;
            $eventUpdated = true;
        }
        if ($request->hasFile('away_team_logo')) {
            $path = $request->file('away_team_logo')->store('wristbands/logos', 'public');
            $event->away_team_logo = $path;
            $eventUpdated = true;
        }
        if ($eventUpdated) {
            $event->save();
        }

        $template->save();

        return redirect()
            ->route('organizer.photobooth.edit', $event)
            ->with('success', 'Template Photobooth pertandingan berhasil diperbarui!');
    }

    /**
     * Show Tenant-level Master Photobooth Template settings.
     */
    public function editMaster(Request $request)
    {
        $tenant = auth()->user()->tenant;
        if (!$tenant && auth()->user()->tenant_id) {
            $tenant = \App\Models\Tenant::find(auth()->user()->tenant_id);
        }
        if (!$tenant && auth()->user()->hasRole('Superadmin')) {
            $tenant = \App\Models\Tenant::first();
        }
        if (!$tenant) {
            abort(403, 'Tenant tidak ditemukan.');
        }

        $template = $tenant->getEffectivePhotoboothTemplate();

        // Sample match for live preview
        $sampleEvent = Event::where('tenant_id', $tenant->id)->with('ticketCategories')->latest()->first()
            ?: Event::with('ticketCategories')->first();

        return view('organizer.photobooth.master', compact('tenant', 'template', 'sampleEvent'));
    }

    /**
     * Update Tenant-level Master Photobooth Template settings.
     */
    public function updateMaster(Request $request)
    {
        $tenant = auth()->user()->tenant;
        if (!$tenant && auth()->user()->tenant_id) {
            $tenant = \App\Models\Tenant::find(auth()->user()->tenant_id);
        }
        if (!$tenant && auth()->user()->hasRole('Superadmin')) {
            $tenant = \App\Models\Tenant::first();
        }
        if (!$tenant) {
            abort(403, 'Tenant tidak ditemukan.');
        }

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'is_active'             => 'nullable|boolean',
            'primary_color'         => ['required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'secondary_color'       => ['required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'header_title'          => 'nullable|string|max:100',
            'header_subtitle'       => 'nullable|string|max:100',
            'badge_text'            => 'nullable|string|max:100',
            'category_label_format' => 'nullable|string|max:100',
            'footer_text'           => 'nullable|string|max:150',
            'show_qr'               => 'nullable|boolean',
            'show_fan_name'         => 'nullable|boolean',
            'show_category'         => 'nullable|boolean',
            'show_match_info'       => 'nullable|boolean',
            'show_team_logos'       => 'nullable|boolean',
            'frame_overlay'         => 'nullable|image|mimes:png|max:4096',
            'background_image'      => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'remove_frame_overlay'    => 'nullable|boolean',
            'remove_background_image' => 'nullable|boolean',
        ]);

        $template = PhotoboothTemplate::firstOrNew([
            'tenant_id' => $tenant->id,
            'event_id'  => null,
        ]);

        $template->name = $validated['name'];
        $template->is_active = $request->has('is_active');
        $template->primary_color = $validated['primary_color'];
        $template->secondary_color = $validated['secondary_color'];
        $template->header_title = $validated['header_title'] ?? 'I LEAGUE 2026/2027';
        $template->header_subtitle = $validated['header_subtitle'] ?? 'FOOTBALL UNITES US';
        $template->badge_text = $validated['badge_text'] ?? 'MATCHDAY COLLECTIBLE';
        $template->category_label_format = $validated['category_label_format'] ?? '{category} • MATCHDAY COLLECTIBLE';
        $template->footer_text = $validated['footer_text'] ?? 'SATU LIGA, SATU SEMANGAT';
        $template->show_qr = $request->has('show_qr');
        $template->show_fan_name = $request->has('show_fan_name');
        $template->show_category = $request->has('show_category');
        $template->show_match_info = $request->has('show_match_info');
        $template->show_team_logos = $request->has('show_team_logos');

        if ($request->hasFile('frame_overlay')) {
            if ($template->frame_overlay && Storage::disk('public')->exists($template->frame_overlay)) {
                Storage::disk('public')->delete($template->frame_overlay);
            }
            $template->frame_overlay = $request->file('frame_overlay')->store('photobooth/frames', 'public');
        } elseif ($request->boolean('remove_frame_overlay')) {
            if ($template->frame_overlay && Storage::disk('public')->exists($template->frame_overlay)) {
                Storage::disk('public')->delete($template->frame_overlay);
            }
            $template->frame_overlay = null;
        }

        if ($request->hasFile('background_image')) {
            if ($template->background_image && Storage::disk('public')->exists($template->background_image)) {
                Storage::disk('public')->delete($template->background_image);
            }
            $template->background_image = ImageOptimizerService::uploadAndOptimize(
                $request->file('background_image'),
                'photobooth/backgrounds',
                1200,
                85
            );
        } elseif ($request->boolean('remove_background_image')) {
            if ($template->background_image && Storage::disk('public')->exists($template->background_image)) {
                Storage::disk('public')->delete($template->background_image);
            }
            $template->background_image = null;
        }

        $template->save();

        return redirect()
            ->route('organizer.photobooth.master')
            ->with('success', 'Master Template Photobooth Klub berhasil diperbarui dan diterapkan ke seluruh pertandingan!');
    }

    /**
     * Kiosk Launcher view in Organizer panel.
     */
    public function kioskLauncher(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $tenant = auth()->user()->tenant;

        $events = Event::where('tenant_id', $tenantId)
            ->whereIn('status', ['published', 'private'])
            ->with(['photoboothTemplate', 'ticketCategories'])
            ->orderByDesc('event_start_date')
            ->get();

        return view('organizer.kiosk.launcher', compact('tenant', 'events'));
    }

    /**
     * Instantly reset / remove frame overlay and background image for an event template.
     */
    public function resetImages(Request $request, Event $event)
    {
        $this->authorizeTenant($event);

        $template = PhotoboothTemplate::where('event_id', $event->id)->first();
        $target = $request->input('target', 'all'); // 'all', 'frame', 'background'

        if ($template) {
            if (in_array($target, ['all', 'frame'])) {
                if ($template->frame_overlay && Storage::disk('public')->exists($template->frame_overlay)) {
                    Storage::disk('public')->delete($template->frame_overlay);
                }
                $template->frame_overlay = null;
            }

            if (in_array($target, ['all', 'background'])) {
                if ($template->background_image && Storage::disk('public')->exists($template->background_image)) {
                    Storage::disk('public')->delete($template->background_image);
                }
                $template->background_image = null;
            }

            $template->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gambar template berhasil direset ke mode polos.',
            ]);
        }

        return redirect()
            ->route('organizer.photobooth.edit', $event)
            ->with('success', 'Gambar template berhasil direset ke mode polos.');
    }

    /**
     * Save drag & drop layout configuration.
     */
    public function saveLayout(Request $request, Event $event)
    {
        $this->authorizeTenant($event);

        $template = PhotoboothTemplate::firstOrNew(['event_id' => $event->id]);
        $template->tenant_id = $event->tenant_id;
        if (!$template->name) {
            $template->name = $event->name . ' Matchday Template';
        }

        $raw = $request->input('layout_config');
        $layout = is_array($raw) ? $raw : json_decode($raw, true);

        if ($layout) {
            $template->layout_config = $layout;
            $template->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Posisi kolom berhasil disimpan!',
            'layout_config' => $template->getEffectiveLayoutConfig()
        ]);
    }

    /**
     * Reset drag & drop layout configuration to default.
     */
    public function resetLayout(Request $request, Event $event)
    {
        $this->authorizeTenant($event);

        $template = PhotoboothTemplate::where('event_id', $event->id)->first();
        if ($template) {
            $template->layout_config = null;
            $template->save();
        }

        $t = $template ?? new PhotoboothTemplate();
        return response()->json([
            'success' => true,
            'message' => 'Posisi kolom dikembalikan ke default.',
            'layout_config' => $t->getEffectiveLayoutConfig()
        ]);
    }

    /**
     * Instantly reset / remove frame overlay and background image for tenant master template.
     */
    public function resetMasterImages(Request $request)
    {
        $tenant = auth()->user()->tenant;
        if (!$tenant && auth()->user()->tenant_id) {
            $tenant = \App\Models\Tenant::find(auth()->user()->tenant_id);
        }
        if (!$tenant && auth()->user()->hasRole('Superadmin')) {
            $tenant = \App\Models\Tenant::first();
        }
        if (!$tenant) {
            abort(403, 'Tenant tidak ditemukan.');
        }

        $template = PhotoboothTemplate::where('tenant_id', $tenant->id)->whereNull('event_id')->first();
        $target = $request->input('target', 'all');

        if ($template) {
            if (in_array($target, ['all', 'frame'])) {
                if ($template->frame_overlay && Storage::disk('public')->exists($template->frame_overlay)) {
                    Storage::disk('public')->delete($template->frame_overlay);
                }
                $template->frame_overlay = null;
            }

            if (in_array($target, ['all', 'background'])) {
                if ($template->background_image && Storage::disk('public')->exists($template->background_image)) {
                    Storage::disk('public')->delete($template->background_image);
                }
                $template->background_image = null;
            }

            $template->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gambar master template berhasil direset ke mode polos.',
            ]);
        }

        return redirect()
            ->route('organizer.photobooth.master')
            ->with('success', 'Gambar master template berhasil direset ke mode polos.');
    }

    /**
     * Instantly reset / remove frame overlay and background image for an event template.
     */
    public function resetImages(Request $request, Event $event)
    {
        $this->authorizeTenant($event);

        $template = PhotoboothTemplate::where('event_id', $event->id)->first();
        $target = $request->input('target', 'all'); // 'all', 'frame', 'background'

        if ($template) {
            if (in_array($target, ['all', 'frame'])) {
                if ($template->frame_overlay && Storage::disk('public')->exists($template->frame_overlay)) {
                    Storage::disk('public')->delete($template->frame_overlay);
                }
                $template->frame_overlay = null;
            }

            if (in_array($target, ['all', 'background'])) {
                if ($template->background_image && Storage::disk('public')->exists($template->background_image)) {
                    Storage::disk('public')->delete($template->background_image);
                }
                $template->background_image = null;
            }

            $template->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gambar template berhasil direset ke mode polos.',
            ]);
        }

        return redirect()
            ->route('organizer.photobooth.edit', $event)
            ->with('success', 'Gambar template berhasil direset ke mode polos.');
    }

    /**
     * Instantly reset / remove frame overlay and background image for tenant master template.
     */
    public function resetMasterImages(Request $request)
    {
        $tenant = auth()->user()->tenant;
        if (!$tenant && auth()->user()->tenant_id) {
            $tenant = \App\Models\Tenant::find(auth()->user()->tenant_id);
        }
        if (!$tenant && auth()->user()->hasRole('Superadmin')) {
            $tenant = \App\Models\Tenant::first();
        }
        if (!$tenant) {
            abort(403, 'Tenant tidak ditemukan.');
        }

        $template = PhotoboothTemplate::where('tenant_id', $tenant->id)->whereNull('event_id')->first();
        $target = $request->input('target', 'all');

        if ($template) {
            if (in_array($target, ['all', 'frame'])) {
                if ($template->frame_overlay && Storage::disk('public')->exists($template->frame_overlay)) {
                    Storage::disk('public')->delete($template->frame_overlay);
                }
                $template->frame_overlay = null;
            }

            if (in_array($target, ['all', 'background'])) {
                if ($template->background_image && Storage::disk('public')->exists($template->background_image)) {
                    Storage::disk('public')->delete($template->background_image);
                }
                $template->background_image = null;
            }

            $template->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gambar master template berhasil direset ke mode polos.',
            ]);
        }

        return redirect()
            ->route('organizer.photobooth.master')
            ->with('success', 'Gambar master template berhasil direset ke mode polos.');
    }

    private function authorizeTenant(Event $event): void
    {
        if ($event->tenant_id !== auth()->user()->tenant_id && !auth()->user()->hasRole('Superadmin')) {
            abort(403, 'Akses ke event ini tidak diizinkan.');
        }
    }
}
