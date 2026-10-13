<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketCategory;
use App\Models\WristbandTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $status = $request->get('status');
        $q = $request->get('q');

        $query = Event::where('tenant_id', $tenantId)
            ->withCount(['ticketCategories', 'gates']);

        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('venue', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%")
                    ->orWhere('id', $q);
            });
        }

        $events = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $allEventsQuery = Event::where('tenant_id', $tenantId)
            ->orderBy('event_start_date', 'desc');

        if (!empty($status) && $status !== 'all') {
            $allEventsQuery->where('status', $status);
        }

        $allEvents = $allEventsQuery->get(['id', 'name', 'event_start_date', 'venue', 'city', 'status']);

        // Status counts for filter tabs
        $statusCounts = [
            'all' => Event::where('tenant_id', $tenantId)->count(),
            'published' => Event::where('tenant_id', $tenantId)->where('status', 'published')->count(),
            'draft' => Event::where('tenant_id', $tenantId)->where('status', 'draft')->count(),
            'cancelled' => Event::where('tenant_id', $tenantId)->where('status', 'cancelled')->count(),
        ];

        return view('organizer.events.index', compact('events', 'allEvents', 'statusCounts', 'status', 'q'));
    }

    public function create()
    {
        return view('organizer.events.create');
    }

    public function store(Request $request)
    {
        // Filter out empty/invalid file uploads before validation to prevent Laravel from failing on nullable fields
        $this->filterEmptyFileUploads($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'google_maps_url' => 'nullable|url|max:2048',
            'description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'event_start_date' => 'required|date',
            'event_end_date' => 'required|date|after_or_equal:event_start_date',
            'background_image' => 'nullable|image|max:10240',
            'security_code' => 'nullable|string|size:6',
            'is_free' => 'nullable|boolean',
            'max_tickets_per_transaction' => 'nullable|integer|min:1',
            'umroh_question_enabled' => 'nullable|boolean',
            'custom_question_text' => 'nullable|string|max:255',
            'custom_question_type' => 'nullable|in:text,select',
            'custom_question_options' => 'nullable|string',
            'evoucher_info' => 'nullable|string',
            'purchase_flow' => 'required|in:redeem,evoucher,print,both',
            'thermal_paper_width_mm' => 'nullable|integer|min:40|max:120',
            'thermal_paper_height_mm' => 'nullable|integer|min:60|max:300',
            'wristband_league_name' => 'nullable|string|max:255',
            'wristband_league_logo' => 'nullable|image|max:1024',
            'wristband_home_club_logo' => 'nullable|image|max:1024',
            'wristband_away_club_logo' => 'nullable|image|max:1024',
            'wristband_sponsor_logos' => 'nullable|array',
            'wristband_sponsor_logos.*' => 'nullable|image|max:1024',
            'wristband_mode' => 'nullable|in:default,custom',
            'wristband_custom_background' => 'nullable|image|max:10240',
            'wristband_custom_background_base64' => 'nullable|string',
            'wristband_columns_json' => 'nullable|string',
            'proof_ig_required' => 'nullable|boolean',
            'proof_review_required' => 'nullable|boolean',
            'registration_proofs_json' => 'nullable|string',
            'evoucher_page_mode' => 'nullable|in:1_page,multi_page',
        ]);

        $validated['is_free'] = $request->boolean('is_free');
        $validated['max_tickets_per_transaction'] = $request->input('is_free') ? $request->integer('max_tickets_per_transaction', 1) : 1;
        $validated['umroh_question_enabled'] = $request->boolean('umroh_question_enabled');
        $validated['thermal_paper_width_mm'] = $request->integer('thermal_paper_width_mm', 80);
        $validated['thermal_paper_height_mm'] = $request->integer('thermal_paper_height_mm', 160);


        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['status'] = 'draft';
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']) . '-' . rand(1000, 9999);
        
        $meta = $this->buildWristbandMeta($request);
        $proofs = $this->parseRegistrationProofs($request);
        $meta['registration_proofs'] = $proofs;
        $meta['proof_ig_required'] = collect($proofs)->first(fn($p) => $p['id'] === 'proof_ig')['is_required'] ?? false;
        $meta['proof_review_required'] = collect($proofs)->first(fn($p) => $p['id'] === 'proof_review')['is_required'] ?? false;
        if ($validated['umroh_question_enabled']) {
            $meta['custom_question_text'] = $request->input('custom_question_text', 'Alumni Grup Keberangkatan Tanggal Berapa?');
            $meta['custom_question_type'] = $request->input('custom_question_type', 'text');
            if ($meta['custom_question_type'] === 'select') {
                $optionsStr = $request->input('custom_question_options', '');
                $optionsArray = collect(explode("\n", $optionsStr))
                    ->map(fn($o) => trim($o))
                    ->filter()
                    ->values()
                    ->all();
                $meta['custom_question_options'] = $optionsArray;
            }
        }
        $validated['meta'] = $meta;
        unset(
            $validated['wristband_league_name'],
            $validated['wristband_league_logo'],
            $validated['wristband_home_club_logo'],
            $validated['wristband_away_club_logo'],
            $validated['wristband_sponsor_logos'],
            $validated['wristband_mode'],
            $validated['wristband_custom_background'],
            $validated['wristband_custom_background_base64'],
            $validated['wristband_columns_json'],
            $validated['proof_ig_required'],
            $validated['proof_review_required']
        );
        
        if (empty($validated['security_code'])) {
            $validated['security_code'] = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        }

        if ($request->hasFile('background_image')) {
            $validated['background_image'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('background_image'), 'events/backgrounds', 1200, 80);
        }

        $event = Event::create($validated);
        $this->syncWristbandTemplate($request, $event);

        return redirect()->route('organizer.events.edit', $event)->with('success', 'Event created. Now add ticket categories.');
    }

    public function edit(Event $event)
    {
        $this->authorizeTenant($event);
        
        $event->load(['ticketCategories', 'wristbandTemplate']);
        return view('organizer.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeTenant($event);

        // Only remove empty file inputs when no file was chosen
        $this->filterEmptyFileUploads($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'google_maps_url' => 'nullable|url|max:2048',
            'description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'event_start_date' => 'required|date',
            'event_end_date' => 'required|date|after_or_equal:event_start_date',
            'status' => 'required|in:draft,published,cancelled',
            'background_image' => 'nullable|image|max:10240',
            'security_code' => 'required|string|size:6',
            'is_free' => 'nullable|boolean',
            'max_tickets_per_transaction' => 'nullable|integer|min:1',
            'umroh_question_enabled' => 'nullable|boolean',
            'custom_question_text' => 'nullable|string|max:255',
            'custom_question_type' => 'nullable|in:text,select',
            'custom_question_options' => 'nullable|string',
            'evoucher_info' => 'nullable|string',
            'purchase_flow' => 'required|in:redeem,evoucher,print,both',
            'thermal_paper_width_mm' => 'nullable|integer|min:40|max:120',
            'thermal_paper_height_mm' => 'nullable|integer|min:60|max:300',
            'wristband_league_name' => 'nullable|string|max:255',
            'wristband_league_logo' => 'nullable|image|max:5120',
            'wristband_home_club_logo' => 'nullable|image|max:5120',
            'wristband_away_club_logo' => 'nullable|image|max:5120',
            'wristband_sponsor_logos' => 'nullable|array',
            'wristband_sponsor_logos.*' => 'nullable|image|max:5120',
            'wristband_mode' => 'nullable|in:default,custom',
            'wristband_custom_background' => 'nullable|image|max:10240',
            'wristband_custom_background_base64' => 'nullable|string',
            'wristband_columns_json' => 'nullable|string',
            'proof_ig_required' => 'nullable|boolean',
            'proof_review_required' => 'nullable|boolean',
            'registration_proofs_json' => 'nullable|string',
            'evoucher_page_mode' => 'nullable|in:1_page,multi_page',
        ]);

        $validated['is_free'] = $request->boolean('is_free');
        $validated['max_tickets_per_transaction'] = $request->input('is_free') ? $request->integer('max_tickets_per_transaction', 1) : 1;
        $validated['umroh_question_enabled'] = $request->boolean('umroh_question_enabled');
        $validated['thermal_paper_width_mm'] = $request->integer('thermal_paper_width_mm', 80);
        $validated['thermal_paper_height_mm'] = $request->integer('thermal_paper_height_mm', 160);

        $meta = $this->buildWristbandMeta($request, $event->meta ?? []);
        $proofs = $this->parseRegistrationProofs($request);
        $meta['registration_proofs'] = $proofs;
        $meta['proof_ig_required'] = collect($proofs)->first(fn($p) => $p['id'] === 'proof_ig')['is_required'] ?? false;
        $meta['proof_review_required'] = collect($proofs)->first(fn($p) => $p['id'] === 'proof_review')['is_required'] ?? false;
        if ($validated['umroh_question_enabled']) {
            $meta['custom_question_text'] = $request->input('custom_question_text', 'Alumni Grup Keberangkatan Tanggal Berapa?');
            $meta['custom_question_type'] = $request->input('custom_question_type', 'text');
            if ($meta['custom_question_type'] === 'select') {
                $optionsStr = $request->input('custom_question_options', '');
                $optionsArray = collect(explode("\n", $optionsStr))
                    ->map(fn($o) => trim($o))
                    ->filter()
                    ->values()
                    ->all();
                $meta['custom_question_options'] = $optionsArray;
            } else {
                unset($meta['custom_question_options']);
            }
        } else {
            unset($meta['custom_question_text'], $meta['custom_question_type'], $meta['custom_question_options']);
        }
        $validated['meta'] = $meta;
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

        if ($request->hasFile('background_image')) {
            if ($event->background_image) Storage::disk('public')->delete($event->background_image);
            $validated['background_image'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('background_image'), 'events/backgrounds', 1200, 80);
        }

        $event->update($validated);
        $this->syncWristbandTemplate($request, $event);

        return redirect()->route('organizer.events.edit', $event)->with('success', 'Event updated successfully.');
    }

    private function authorizeTenant(Event $event)
    {
        if ($event->tenant_id !== auth()->user()->tenant_id && !auth()->user()->hasRole('Superadmin')) {
            abort(403, 'Unauthorized access to this event');
        }
    }

    private function buildWristbandMeta(Request $request, array $current = []): array
    {
        $meta = $current;
        $meta['wristband_league_name'] = $request->input('wristband_league_name') ?: ($meta['wristband_league_name'] ?? null);
        $meta['evoucher_page_mode'] = $request->input('evoucher_page_mode', $meta['evoucher_page_mode'] ?? '1_page');

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
     * Parse custom or legacy registration proofs from the request.
     */
    private function parseRegistrationProofs(Request $request): array
    {
        $proofs = [];
        if ($request->filled('registration_proofs_json')) {
            $decoded = json_decode($request->input('registration_proofs_json'), true);
            if (is_array($decoded)) {
                foreach ($decoded as $proof) {
                    if (empty($proof['label'])) {
                        continue;
                    }
                    $proofs[] = [
                        'id' => !empty($proof['id']) ? $proof['id'] : ('proof_' . \Illuminate\Support\Str::random(8)),
                        'label' => $proof['label'],
                        'instruction' => $proof['instruction'] ?? '',
                        'link' => $proof['link'] ?? null,
                        'is_required' => isset($proof['is_required']) ? filter_var($proof['is_required'], FILTER_VALIDATE_BOOLEAN) : false,
                    ];
                }
            }
        } else {
            // Fallback for compatibility/default form submissions
            if ($request->has('proof_ig_required') || $request->has('proof_review_required')) {
                if ($request->boolean('proof_ig_required')) {
                    $proofs[] = [
                        'id' => 'proof_ig',
                        'label' => 'Bukti follow IG',
                        'instruction' => 'Klik untuk follow @batikumrah dan ambil screenshot',
                        'link' => 'https://www.instagram.com/batikumrah?igsh=MTFibTFtOHF3dGp4MQ==',
                        'is_required' => true,
                    ];
                }
                if ($request->boolean('proof_review_required')) {
                    $proofs[] = [
                        'id' => 'proof_review',
                        'label' => 'Bukti Google Review',
                        'instruction' => 'Isi Google Review lalu ambil screenshot',
                        'link' => 'https://bit.ly/googlereviewbatik',
                        'is_required' => true,
                    ];
                }
            }
        }
        return $proofs;
    }

    /**
     * Internal helper to perform a complete duplication of an event and all its related entities:
     * - Event model attributes (clean new slug, status, new security code)
     * - Ticket Categories (replicated with sold_count = 0)
     * - Gates & Category associations
     * - Event-level Wristband Template (mode, background, columns config, layout)
     * - Category-level Wristband Templates
     */
    public function performDuplicateEvent(Event $event, array $options = []): Event
    {
        $index = $options['index'] ?? 1;
        $totalCopies = $options['total_copies'] ?? 1;
        $namingPattern = $options['naming_pattern'] ?? 'copy'; // 'copy', 'session', 'match', 'stage', 'custom'
        $customPrefix = $options['custom_prefix'] ?? null;
        $status = $options['status'] ?? 'draft';
        $offsetMode = $options['date_offset_mode'] ?? 'none'; // 'none', 'hours', 'days'
        $offsetValue = (int) ($options['date_offset_value'] ?? 0);

        // 1. Calculate Name
        $baseName = trim(preg_replace('/\s*\((Salinan|Sesi|Match|Stage).*$/i', '', $event->name));
        if ($namingPattern === 'session') {
            $newName = $baseName . ' - Sesi ' . $index;
        } elseif ($namingPattern === 'match') {
            $newName = $baseName . ' - Match ' . $index;
        } elseif ($namingPattern === 'stage') {
            $newName = $baseName . ' - Stage ' . $index;
        } elseif ($namingPattern === 'custom' && !empty($customPrefix)) {
            $newName = trim($customPrefix) . ' ' . $index;
        } else {
            $newName = $totalCopies > 1 ? $baseName . ' (Salinan ' . $index . ')' : $baseName . ' (Salinan)';
        }

        // 2. Calculate Dates with offset if requested
        $startDate = $event->event_start_date ? $event->event_start_date->copy() : now();
        $endDate = $event->event_end_date ? $event->event_end_date->copy() : $startDate->copy()->addHours(3);
        $gateOpen = $event->gate_open_at ? $event->gate_open_at->copy() : null;
        $gateClose = $event->gate_close_at ? $event->gate_close_at->copy() : null;

        if ($offsetMode === 'hours' && $offsetValue > 0) {
            $hoursToAdd = ($index - 1) * $offsetValue;
            $startDate->addHours($hoursToAdd);
            $endDate->addHours($hoursToAdd);
            if ($gateOpen) $gateOpen->addHours($hoursToAdd);
            if ($gateClose) $gateClose->addHours($hoursToAdd);
        } elseif ($offsetMode === 'days' && $offsetValue > 0) {
            $daysToAdd = ($index - 1) * $offsetValue;
            $startDate->addDays($daysToAdd);
            $endDate->addDays($daysToAdd);
            if ($gateOpen) $gateOpen->addDays($daysToAdd);
            if ($gateClose) $gateClose->addDays($daysToAdd);
        }

        // 3. Replicate Event
        $newEvent = $event->replicate([
            'slug',
            'security_code',
            'status',
            'event_start_date',
            'event_end_date',
            'gate_open_at',
            'gate_close_at',
        ]);

        $newEvent->name = $newName;
        $newEvent->slug = \Illuminate\Support\Str::slug($newName) . '-' . strtolower(\Illuminate\Support\Str::random(5));
        $newEvent->status = in_array($status, ['draft', 'published']) ? $status : 'draft';
        $newEvent->event_start_date = $startDate;
        $newEvent->event_end_date = $endDate;
        $newEvent->gate_open_at = $gateOpen;
        $newEvent->gate_close_at = $gateClose;
        // Generate a fresh unique 6-digit PIN
        $newEvent->security_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $newEvent->created_at = now();
        $newEvent->updated_at = now();
        $newEvent->save();

        // 4. Replicate Ticket Categories
        $categoryMap = [];
        $originalCategories = TicketCategory::where('event_id', $event->id)->get();
        foreach ($originalCategories as $cat) {
            $newCat = $cat->replicate([
                'event_id',
                'sold_count',
            ]);
            $newCat->event_id = $newEvent->id;
            $newCat->tenant_id = $newEvent->tenant_id;
            $newCat->sold_count = 0;
            $newCat->created_at = now();
            $newCat->updated_at = now();
            $newCat->save();

            $categoryMap[$cat->id] = $newCat->id;

            // 4a. Replicate Category-Level Wristband Template if any
            $catTemplates = WristbandTemplate::where('ticket_category_id', $cat->id)->get();
            foreach ($catTemplates as $catTmpl) {
                $newCatTmpl = $catTmpl->replicate([
                    'event_id',
                    'ticket_category_id',
                ]);
                $newCatTmpl->event_id = $newEvent->id;
                $newCatTmpl->ticket_category_id = $newCat->id;
                $newCatTmpl->tenant_id = $newEvent->tenant_id;
                $newCatTmpl->created_at = now();
                $newCatTmpl->updated_at = now();
                $newCatTmpl->save();
            }
        }

        // 5. Replicate Gates and sync category associations
        $originalGates = \App\Models\Gate::where('event_id', $event->id)->with('ticketCategories')->get();
        foreach ($originalGates as $gate) {
            $newGate = $gate->replicate([
                'event_id',
            ]);
            $newGate->event_id = $newEvent->id;
            $newGate->tenant_id = $newEvent->tenant_id;
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

        // 6. Replicate Event-Level Wristband Templates (model gelang tiket event)
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

        return $newEvent;
    }

    /**
     * Single Event Duplicate (with optional multi-copy support).
     */
    public function duplicate(Request $request, Event $event)
    {
        $this->authorizeTenant($event);

        $copies = max(1, min(30, (int) $request->input('copies', 1)));
        $namingPattern = $request->input('naming_pattern', 'copy');
        $status = $request->input('status', 'draft');
        $offsetMode = $request->input('date_offset_mode', 'none');
        $offsetValue = (int) $request->input('date_offset_value', 0);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $lastCreated = null;
            for ($i = 1; $i <= $copies; $i++) {
                $lastCreated = $this->performDuplicateEvent($event, [
                    'index' => $i,
                    'total_copies' => $copies,
                    'naming_pattern' => $namingPattern,
                    'status' => $status,
                    'date_offset_mode' => $offsetMode,
                    'date_offset_value' => $offsetValue,
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            if ($copies > 1) {
                return redirect()->route('organizer.events.index')
                    ->with('success', "Berhasil menduplikasi {$copies} event sekaligus beserta seluruh tiket, gate, dan model gelang tiket (wristband)!");
            }

            return redirect()->route('organizer.events.edit', $lastCreated)
                ->with('success', 'Event berhasil diduplikasi beserta seluruh kategori tiket, gerbang gate, dan model gelang tiket (wristband)!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal menduplikasi event: ' . $e->getMessage());
        }
    }

    /**
     * Bulk Duplication of single or multiple selected events.
     */
    public function bulkDuplicate(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'source_event_id' => 'nullable|exists:events,id',
            'event_ids' => 'nullable|array',
            'event_ids.*' => 'exists:events,id',
            'copies_count' => 'nullable|integer|min:1|max:30',
            'naming_pattern' => 'required|in:copy,session,match,stage,custom',
            'custom_prefix' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
            'date_offset_mode' => 'required|in:none,hours,days',
            'date_offset_value' => 'nullable|integer|min:0|max:100',
        ]);

        $copiesCount = max(1, min(30, (int) $request->input('copies_count', 1)));
        $namingPattern = $request->input('naming_pattern', 'copy');
        $customPrefix = $request->input('custom_prefix');
        $status = $request->input('status', 'draft');
        $offsetMode = $request->input('date_offset_mode', 'none');
        $offsetValue = (int) $request->input('date_offset_value', 0);

        // Gather source events
        $eventIds = (array) $request->input('event_ids', []);
        if ($request->filled('source_event_id')) {
            $eventIds[] = $request->input('source_event_id');
        }
        $eventIds = array_unique(array_filter($eventIds));

        if (empty($eventIds)) {
            return back()->with('error', 'Pilih minimal 1 event untuk diduplikasi.');
        }

        $sourceEvents = Event::where('tenant_id', $tenantId)
            ->whereIn('id', $eventIds)
            ->get();

        if ($sourceEvents->isEmpty()) {
            return back()->with('error', 'Event tidak ditemukan atau bukan milik tenant Anda.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $totalCreated = 0;

            foreach ($sourceEvents as $sourceEvent) {
                for ($i = 1; $i <= $copiesCount; $i++) {
                    $this->performDuplicateEvent($sourceEvent, [
                        'index' => $i,
                        'total_copies' => $copiesCount,
                        'naming_pattern' => $namingPattern,
                        'custom_prefix' => $customPrefix,
                        'status' => $status,
                        'date_offset_mode' => $offsetMode,
                        'date_offset_value' => $offsetValue,
                    ]);
                    $totalCreated++;
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('organizer.events.index')
                ->with('success', "Sukses! Berhasil menduplikasi {$totalCreated} event baru secara massal beserta seluruh kategori tiket, gate, dan model gelang tiket (wristband)!");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal melakukan duplikasi massal: ' . $e->getMessage());
        }
    }


    /**
     * Filter out empty or invalid file uploads from both Symfony FileBag and Laravel convertedFiles.
     */
    private function filterEmptyFileUploads(Request $request): void
    {
        $singleInputs = [
            'background_image',
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
                    // Remove failed upload if base64 payload is available so validation passes
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
                $filtered = array_filter($files, function ($file) {
                    return $file && $file->isValid() && $file->getError() !== UPLOAD_ERR_NO_FILE;
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
