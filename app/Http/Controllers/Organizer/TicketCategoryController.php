<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketCategoryController extends Controller
{
    public function create(Event $event)
    {
        $this->authorizeTenant($event);
        return view('organizer.tickets.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeTenant($event);
        $this->filterEmptyFileUploads($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => $event->is_free ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'service_fee' => 'nullable|numeric|min:0',
            'nik_restriction' => 'nullable|string|max:255',
            'nik_restriction_message' => 'nullable|string|max:255',
            'badge_text' => 'nullable|string|max:255',
            'quota' => 'required|integer|min:1',
            'sale_start_at' => 'nullable|date',
            'sale_end_at' => 'nullable|date|after:sale_start_at',
            'hex_color' => 'nullable|string|size:7',
            'category_image' => 'nullable|image|max:1024',
            'background_image' => 'nullable|image|max:2048',
        ]);

        $data = $validated;
        $data['event_id'] = $event->id;
        $data['tenant_id'] = $event->tenant_id;
        $data['hex_color'] = !empty($data['hex_color']) ? $data['hex_color'] : '#6366F1';
        if ($event->is_free) {
            $data['price'] = 0;
            $data['service_fee'] = 0;
        } else {
            $data['service_fee'] = max(0, (float) $request->input('service_fee', 0));
        }

        if ($request->hasFile('category_image')) {
            $data['category_image'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('category_image'), 'tickets/categories', 600, 80);
        }

        if ($request->hasFile('background_image')) {
            $data['background_image'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('background_image'), 'tickets/backgrounds', 1200, 80);
        }

        TicketCategory::create($data);

        return redirect()->route('organizer.events.edit', $event)->with('success', 'Ticket category created.');
    }

    public function edit(Event $event, TicketCategory $category)
    {
        $this->authorizeTenant($event);
        $event = $this->resolveEventForCategory($event, $category);

        return view('organizer.tickets.edit', compact('event', 'category'));
    }

    public function update(Request $request, Event $event, TicketCategory $category)
    {
        $this->authorizeTenant($event);
        $event = $this->resolveEventForCategory($event, $category);
        $this->filterEmptyFileUploads($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => $event->is_free ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'service_fee' => 'nullable|numeric|min:0',
            'nik_restriction' => 'nullable|string|max:255',
            'nik_restriction_message' => 'nullable|string|max:255',
            'badge_text' => 'nullable|string|max:255',
            'quota' => 'required|integer|min:1',
            'sale_start_at' => 'nullable|date',
            'sale_end_at' => 'nullable|date|after:sale_start_at',
            'hex_color' => 'nullable|string|size:7',
            'category_image' => 'nullable|image|max:1024',
            'background_image' => 'nullable|image|max:2048',
        ]);

        $data = $validated;
        if ($event->is_free) {
            $data['price'] = 0;
            $data['service_fee'] = 0;
        } else {
            $data['service_fee'] = max(0, (float) $request->input('service_fee', 0));
        }
        if (isset($data['hex_color']) && empty($data['hex_color'])) {
            $data['hex_color'] = $category->hex_color ?? '#6366F1';
        }

        if ($request->hasFile('category_image')) {
            if ($category->category_image) Storage::disk('public')->delete($category->category_image);
            $data['category_image'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('category_image'), 'tickets/categories', 600, 80);
        }

        if ($request->hasFile('background_image')) {
            if ($category->background_image) Storage::disk('public')->delete($category->background_image);
            $data['background_image'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('background_image'), 'tickets/backgrounds', 1200, 80);
        }

        $category->update($data);

        return redirect()->route('organizer.events.edit', $event)->with('success', 'Ticket category updated.');
    }

    public function destroy(Event $event, TicketCategory $category)
    {
        $this->authorizeTenant($event);
        $event = $this->resolveEventForCategory($event, $category);

        $category->delete();
        return redirect()->route('organizer.events.edit', $event)->with('success', 'Ticket category deleted.');
    }

    private function filterEmptyFileUploads(Request $request): void
    {
        $singleInputs = ['category_image', 'background_image'];

        $ref = new \ReflectionClass($request);
        $convertedProp = $ref->hasProperty('convertedFiles') ? $ref->getProperty('convertedFiles') : null;
        if ($convertedProp) {
            $convertedProp->setAccessible(true);
        }

        foreach ($singleInputs as $input) {
            if ($request->files->has($input)) {
                $file = $request->files->get($input);
                if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE || !$file->isValid()) {
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
    }

    private function authorizeTenant(Event $event)
    {
        if ($event->tenant_id != auth()->user()->tenant_id && !auth()->user()->hasRole('Superadmin')) {
            abort(403, 'Unauthorized access to this event');
        }
    }

    private function resolveEventForCategory(Event $event, TicketCategory $category): Event
    {
        $sameTenant = (int) $category->tenant_id === (int) $event->tenant_id;

        if (!$sameTenant) {
            abort(403, 'Unauthorized access to this ticket category');
        }

        if ((int) $category->event_id === (int) $event->id) {
            return $event;
        }

        $categoryEvent = $category->event;

        if (!$categoryEvent || (int) $categoryEvent->tenant_id !== (int) $event->tenant_id) {
            abort(403, 'Unauthorized access to this ticket category');
        }

        return $categoryEvent;
    }
}
