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
            'badge_text'      => 'nullable|string|max:100',
            'footer_text'     => 'nullable|string|max:150',
            'show_qr'         => 'nullable|boolean',
            'show_fan_name'   => 'nullable|boolean',
            'show_match_info' => 'nullable|boolean',
            'show_team_logos' => 'nullable|boolean',
            'frame_overlay'   => 'nullable|image|mimes:png|max:4096',
            'background_image'=> 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
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
        $template->footer_text = $validated['footer_text'] ?? 'SATU LIGA, SATU SEMANGAT';
        $template->show_qr = $request->has('show_qr');
        $template->show_fan_name = $request->has('show_fan_name');
        $template->show_match_info = $request->has('show_match_info');
        $template->show_team_logos = $request->has('show_team_logos');

        // Handle Custom Frame Overlay Upload (PNG with cutout)
        if ($request->hasFile('frame_overlay')) {
            if ($template->frame_overlay && Storage::disk('public')->exists($template->frame_overlay)) {
                Storage::disk('public')->delete($template->frame_overlay);
            }
            $template->frame_overlay = $request->file('frame_overlay')->store('photobooth/frames', 'public');
        }

        // Handle Custom Background Backdrop
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
        }

        $template->save();

        return redirect()
            ->route('organizer.photobooth.edit', $event)
            ->with('success', 'Template Photobooth pertandingan berhasil diperbarui!');
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

    private function authorizeTenant(Event $event): void
    {
        if ($event->tenant_id !== auth()->user()->tenant_id && !auth()->user()->hasRole('Superadmin')) {
            abort(403, 'Akses ke event ini tidak diizinkan.');
        }
    }
}
