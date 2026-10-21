<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Korwil;
use App\Models\KorwilAllocation;
use App\Models\Event;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KorwilManagementController extends Controller
{
    private function getTenantId()
    {
        return Auth::user()->tenant_id;
    }

    public function index()
    {
        $tenantId = $this->getTenantId();

        $korwils = Korwil::where('tenant_id', $tenantId)
            ->with(['coordinator:id,name,email'])
            ->withCount(['members', 'allocations'])
            ->latest()
            ->get();

        $allocations = KorwilAllocation::where('tenant_id', $tenantId)
            ->with([
                'korwil:id,name,code',
                'event:id,name',
                'category:id,name',
                'memberConsents' => fn ($q) => $q->select(['id', 'korwil_allocation_id', 'status'])
            ])
            ->latest()
            ->paginate(15);

        $events = Event::where('tenant_id', $tenantId)
            ->whereIn('status', ['published', 'private'])
            ->select(['id', 'name', 'event_start_date'])
            ->latest('event_start_date')
            ->get();

        $categories = TicketCategory::where('tenant_id', $tenantId)
            ->select(['id', 'name', 'event_id'])
            ->get();

        return view('organizer.korwil.index', compact('korwils', 'allocations', 'events', 'categories'));
    }

    public function storeKorwil(Request $request)
    {
        $tenantId = $this->getTenantId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'region' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string',
        ]);

        Korwil::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? strtoupper(\Illuminate\Support\Str::random(6)),
            'region' => $validated['region'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_verified' => true,
        ]);

        return back()->with('success', 'Basis Suporter (Korwil) berhasil didaftarkan!');
    }

    public function storeAllocation(Request $request)
    {
        $tenantId = $this->getTenantId();

        $validated = $request->validate([
            'korwil_id' => 'required|exists:korwils,id',
            'event_id' => 'required|exists:events,id',
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'allocated_quota' => 'required|integer|min:1',
        ]);

        KorwilAllocation::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'korwil_id' => $validated['korwil_id'],
                'event_id' => $validated['event_id'],
                'ticket_category_id' => $validated['ticket_category_id'],
            ],
            [
                'allocated_quota' => $validated['allocated_quota'],
                'status' => 'approved',
            ]
        );

        return back()->with('success', 'Alokasi kuota tiket matchday untuk Korwil berhasil diset!');
    }
}
