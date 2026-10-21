<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Event;
use App\Models\Tenant;
use App\Models\WristbandTemplate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::with('tenant')->paginate(10);
        return view('superadmin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenants = Tenant::where('status', 'active')->get();
        return view('superadmin.events.create', compact('tenants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->filterEmptyFileUploads($request);

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'google_maps_url' => 'nullable|url|max:2048',
            'event_start_date' => 'required|date',
            'event_end_date' => 'required|date|after_or_equal:event_start_date',
            'gate_open_at' => 'required|date',
            'gate_close_at' => 'required|date|after:gate_open_at',
            'status' => 'required|in:draft,published,private,finished,cancelled',
            'banner_image' => 'nullable|image|max:2048',
            'evoucher_info' => 'nullable|string',
            'wristband_league_name' => 'nullable|string|max:255',
            'wristband_league_logo' => 'nullable|image|max:1024',
            'wristband_home_club_logo' => 'nullable|image|max:1024',
            'wristband_away_club_logo' => 'nullable|image|max:1024',
            'wristband_sponsor_logos' => 'nullable|array',
            'wristband_sponsor_logos.*' => 'nullable|image|max:1024',
            'wristband_mode' => 'nullable|in:default,custom',
            'wristband_custom_background' => 'nullable|image|max:10240',
            'wristband_custom_background_base64' => 'nullable|string',
            'type' => 'nullable|string|in:concert,sport,festival,seminar,other',
            'terms_conditions' => 'nullable|string',
        ]);

        $validated['type'] = $request->input('type', 'concert');
        $validated['slug'] = Str::slug($validated['name']) . '-' . rand(1000, 9999);
        $validated['meta'] = $this->buildWristbandMeta($request);
        unset(
            $validated['wristband_league_name'],
            $validated['wristband_league_logo'],
            $validated['wristband_home_club_logo'],
            $validated['wristband_away_club_logo'],
            $validated['wristband_sponsor_logos'],
            $validated['wristband_mode'],
            $validated['wristband_custom_background'],
            $validated['wristband_custom_background_base64'],
            $validated['wristband_columns_json']
        );

        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('banner_image'), 'events/banners', 1200, 80);
        }

        $event = Event::create($validated);
        $this->syncWristbandTemplate($request, $event);

        return redirect()->route('superadmin.events.index')->with('success', 'Event created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $event->load('wristbandTemplate');
        $tenants = Tenant::where('status', 'active')->get();
        return view('superadmin.events.edit', compact('event', 'tenants'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $this->filterEmptyFileUploads($request);

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'google_maps_url' => 'nullable|url|max:2048',
            'event_start_date' => 'required|date',
            'event_end_date' => 'required|date|after_or_equal:event_start_date',
            'gate_open_at' => 'required|date',
            'gate_close_at' => 'required|date|after:gate_open_at',
            'status' => 'required|in:draft,published,private,finished,cancelled',
            'banner_image' => 'nullable|image|max:2048',
            'evoucher_info' => 'nullable|string',
            'wristband_league_name' => 'nullable|string|max:255',
            'wristband_league_logo' => 'nullable|image|max:1024',
            'wristband_home_club_logo' => 'nullable|image|max:1024',
            'wristband_away_club_logo' => 'nullable|image|max:1024',
            'wristband_sponsor_logos' => 'nullable|array',
            'wristband_sponsor_logos.*' => 'nullable|image|max:1024',
            'wristband_mode' => 'nullable|in:default,custom',
            'wristband_custom_background' => 'nullable|image|max:10240',
            'wristband_custom_background_base64' => 'nullable|string',
            'type' => 'nullable|string|in:concert,sport,festival,seminar,other',
            'terms_conditions' => 'nullable|string',
        ]);
        $validated['type'] = $request->input('type', $event->type ?? 'concert');
        $validated['meta'] = $this->buildWristbandMeta($request, $event->meta ?? []);
        unset(
            $validated['wristband_league_name'],
            $validated['wristband_league_logo'],
            $validated['wristband_home_club_logo'],
            $validated['wristband_away_club_logo'],
            $validated['wristband_sponsor_logos'],
            $validated['wristband_mode'],
            $validated['wristband_custom_background'],
            $validated['wristband_custom_background_base64'],
            $validated['wristband_columns_json']
        );

        if ($request->hasFile('banner_image')) {
            if ($event->banner_image) {
                Storage::disk('public')->delete($event->banner_image);
            }
            $validated['banner_image'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('banner_image'), 'events/banners', 1200, 80);
        }

        $event->update($validated);
        $this->syncWristbandTemplate($request, $event);

        return redirect()->route('superadmin.events.index')->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('superadmin.events.index')->with('success', 'Event moved to trash.');
    }

    /**
     * Display a listing of trashed resources.
     */
    public function trash()
    {
        $events = Event::onlyTrashed()->with('tenant')->paginate(10);
        return view('superadmin.events.trash', compact('events'));
    }

    /**
     * Restore the specified resource.
     */
    public function restore($id)
    {
        $event = Event::withTrashed()->findOrFail($id);
        $event->restore();
        return redirect()->route('superadmin.events.trash')->with('success', 'Event restored successfully.');
    }

    /**
     * Permanently delete.
     */
    public function forceDelete($id)
    {
        $event = Event::withTrashed()->findOrFail($id);
        if ($event->banner_image) {
            Storage::disk('public')->delete($event->banner_image);
        }
        foreach (['wristband_league_logo', 'wristband_home_club_logo', 'wristband_away_club_logo'] as $key) {
            if (!empty($event->meta[$key])) {
                Storage::disk('public')->delete($event->meta[$key]);
            }
        }
        foreach (($event->meta['wristband_sponsor_logos'] ?? []) as $logo) {
            Storage::disk('public')->delete($logo);
        }
        $event->forceDelete();
        return redirect()->route('superadmin.events.trash')->with('success', 'Event permanently deleted.');
    }

    private function buildWristbandMeta(Request $request, array $current = []): array
    {
        $meta = $current;
        $meta['wristband_league_name'] = $request->input('wristband_league_name') ?: ($meta['wristband_league_name'] ?? null);

        foreach ([
            'wristband_league_logo',
            'wristband_home_club_logo',
            'wristband_away_club_logo',
        ] as $input) {
            if ($request->hasFile($input)) {
                if (!empty($meta[$input])) {
                    Storage::disk('public')->delete($meta[$input]);
                }
                $meta[$input] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file($input), 'wristbands/logos', 300, 85);
            }
        }

        if ($request->hasFile('wristband_sponsor_logos')) {
            foreach (($meta['wristband_sponsor_logos'] ?? []) as $logo) {
                Storage::disk('public')->delete($logo);
            }

            $meta['wristband_sponsor_logos'] = collect($request->file('wristband_sponsor_logos'))
                ->filter()
                ->map(fn ($file) => \App\Services\ImageOptimizerService::uploadAndOptimize($file, 'wristbands/sponsors', 300, 85))
                ->values()
                ->all();
        }

        return array_filter($meta, fn ($value) => filled($value));
    }

    /**
     * Duplikasi / Copy Event beserta seluruh kategori tiket dan gate
     * Kecuali kode verifikasi event (dibuatkan baru secara acak)
     */
    public function duplicate(Event $event)
    {
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. Replicate Event
            $newEvent = $event->replicate([
                'slug',
                'security_code',
                'status',
            ]);

            $newEvent->name = $event->name . ' (Salinan)';
            $newEvent->slug = \Illuminate\Support\Str::slug($newEvent->name) . '-' . rand(1000, 9999);
            $newEvent->status = 'draft';
            // Generate kode verifikasi event baru (6 digit angka acak)
            $newEvent->security_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $newEvent->created_at = now();
            $newEvent->updated_at = now();
            $newEvent->save();

            // 2. Replicate Ticket Categories
            $categoryMap = [];
            $originalCategories = \App\Models\TicketCategory::where('event_id', $event->id)->get();
            foreach ($originalCategories as $cat) {
                $newCat = $cat->replicate([
                    'event_id',
                    'sold_count',
                ]);
                $newCat->event_id = $newEvent->id;
                $newCat->sold_count = 0;
                $newCat->created_at = now();
                $newCat->updated_at = now();
                $newCat->save();

                $categoryMap[$cat->id] = $newCat->id;

                // Replicate Category-Level Wristband Template if any
                $catTemplates = WristbandTemplate::where('ticket_category_id', $cat->id)->get();
                foreach ($catTemplates as $catTmpl) {
                    $newCatTmpl = $catTmpl->replicate(['event_id', 'ticket_category_id']);
                    $newCatTmpl->event_id = $newEvent->id;
                    $newCatTmpl->ticket_category_id = $newCat->id;
                    $newCatTmpl->tenant_id = $newEvent->tenant_id;
                    $newCatTmpl->created_at = now();
                    $newCatTmpl->updated_at = now();
                    $newCatTmpl->save();
                }
            }

            // 3. Replicate Gates and sync category associations
            $originalGates = \App\Models\Gate::where('event_id', $event->id)->with('ticketCategories')->get();
            foreach ($originalGates as $gate) {
                $newGate = $gate->replicate([
                    'event_id',
                ]);
                $newGate->event_id = $newEvent->id;
                $newGate->created_at = now();
                $newGate->updated_at = now();
                $newGate->save();

                $mappedCategoryIds = [];
                foreach ($gate->ticketCategories as $gateCat) {
                    if (isset($categoryMap[$gateCat->id])) {
                        $mappedCategoryIds[] = $categoryMap[$gateCat->id];
                    }
                }
                if (!empty($mappedCategoryIds)) {
                    $newGate->ticketCategories()->sync($mappedCategoryIds);
                }
            }

            // 4. Replicate Event-Level Wristband Templates
            $eventTemplates = WristbandTemplate::where('event_id', $event->id)
                ->whereNull('ticket_category_id')
                ->get();
            foreach ($eventTemplates as $tmpl) {
                $newTmpl = $tmpl->replicate(['event_id']);
                $newTmpl->event_id = $newEvent->id;
                $newTmpl->tenant_id = $newEvent->tenant_id;
                $newTmpl->created_at = now();
                $newTmpl->updated_at = now();
                $newTmpl->save();
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('superadmin.events.edit', $newEvent)
                ->with('success', 'Event berhasil diduplikasi beserta seluruh kategori tiket dan gerbang gate! Kode verifikasi baru telah di-generate.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal menduplikasi event: ' . $e->getMessage());
        }
    }
    /**
     * Filter out empty or invalid file uploads from both Symfony FileBag and Laravel convertedFiles.
     */
    private function filterEmptyFileUploads(Request $request): void
    {
        $singleInputs = [
            'banner_image',
            'wristband_league_logo',
            'wristband_home_club_logo',
            'wristband_away_club_logo',
            'wristband_custom_background',
        ];

        $ref = new \ReflectionClass($request);
        $convertedProp = $ref->hasProperty('convertedFiles') ? $ref->getProperty('convertedFiles') : null;
        if ($convertedProp) {
            $convertedProp->setAccessible(true);
        }

        foreach ($singleInputs as $input) {
            if ($request->files->has($input)) {
                $file = $request->files->get($input);
                if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                    $request->files->remove($input);
                    if ($convertedProp) {
                        $converted = $convertedProp->getValue($request);
                        if (is_array($converted)) {
                            unset($converted[$input]);
                            $convertedProp->setValue($request, $converted);
                        }
                    }
                } elseif (!$file->isValid() && $request->filled($input . '_base64')) {
                    $request->files->remove($input);
                    if ($convertedProp) {
                        $converted = $convertedProp->getValue($request);
                        if (is_array($converted)) {
                            unset($converted[$input]);
                            $convertedProp->setValue($request, $converted);
                        }
                    }
                }
            }
        }

        if ($request->files->has('wristband_sponsor_logos')) {
            $files = $request->files->get('wristband_sponsor_logos');
            if (is_array($files)) {
                $filtered = array_filter($files, function ($f) {
                    return $f && $f->isValid() && $f->getError() !== UPLOAD_ERR_NO_FILE;
                });
                if (empty($filtered)) {
                    $request->files->remove('wristband_sponsor_logos');
                    if ($convertedProp) {
                        $converted = $convertedProp->getValue($request);
                        if (is_array($converted)) {
                            unset($converted['wristband_sponsor_logos']);
                            $convertedProp->setValue($request, $converted);
                        }
                    }
                } else {
                    $request->files->set('wristband_sponsor_logos', array_values($filtered));
                    if ($convertedProp) {
                        $converted = $convertedProp->getValue($request);
                        if (is_array($converted)) {
                            $converted['wristband_sponsor_logos'] = array_values($filtered);
                            $convertedProp->setValue($request, $converted);
                        }
                    }
                }
            }
        }
    }

    private function syncWristbandTemplate(Request $request, Event $event): WristbandTemplate
    {
        $template = WristbandTemplate::where('event_id', $event->id)->whereNull('ticket_category_id')->latest()->first()
            ?: WristbandTemplate::firstOrNew(['event_id' => $event->id]);
        $template->tenant_id = $event->tenant_id;
        $template->name = 'Wristband ' . $event->name;
        $template->mode = $request->input('wristband_mode', 'default') === 'custom' ? 'custom' : 'default';

        $base64Input = $request->input('wristband_custom_background_base64');
        $hasBase64Bg = !empty($base64Input) && str_starts_with($base64Input, 'data:image/');
        $hasFileBg = $request->hasFile('wristband_custom_background');

        if ($request->boolean('wristband_remove_background') && !$hasFileBg && !$hasBase64Bg) {
            if ($template->background_image) {
                Storage::disk('public')->delete($template->background_image);
                $template->background_image = null;
            }
        } elseif ($hasBase64Bg) {
            if ($template->background_image) {
                Storage::disk('public')->delete($template->background_image);
            }
            $data = substr($base64Input, strpos($base64Input, ',') + 1);
            $decoded = base64_decode($data);
            if ($decoded !== false) {
                $ext = 'webp';
                if (str_contains($base64Input, 'image/png')) {
                    $ext = 'png';
                } elseif (str_contains($base64Input, 'image/jpeg') || str_contains($base64Input, 'image/jpg')) {
                    $ext = 'jpg';
                }
                $filename = 'wristbands/backgrounds/' . \Illuminate\Support\Str::random(40) . '.' . $ext;
                Storage::disk('public')->put($filename, $decoded);
                $template->background_image = $filename;
            }
        } elseif ($hasFileBg) {
            if ($template->background_image) {
                Storage::disk('public')->delete($template->background_image);
            }
            $template->background_image = \App\Services\ImageOptimizerService::uploadAndOptimize(
                $request->file('wristband_custom_background'),
                'wristbands/backgrounds',
                2560,
                90
            );
        }

        if ($request->filled('wristband_columns_json')) {
            $decoded = json_decode($request->input('wristband_columns_json'), true);
            if (is_array($decoded)) {
                foreach ($decoded as $key => &$col) {
                    if (isset($col['x'])) {
                        $col['x'] = (float) str_replace(',', '.', (string) $col['x']);
                    }
                    if (isset($col['y'])) {
                        $col['y'] = (float) str_replace(',', '.', (string) $col['y']);
                    }
                    if (isset($col['qr_size'])) {
                        $col['qr_size'] = (float) str_replace(',', '.', (string) $col['qr_size']);
                    }
                }
                $template->columns_config = $decoded;
            }
        } elseif (!$template->columns_config) {
            $template->columns_config = WristbandTemplate::getDefaultColumns();
        }

        $template->save();
        return $template;
    }
}
