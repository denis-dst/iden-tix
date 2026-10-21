<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Tenant;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withCount('events')->with('owner')->paginate(10);
        return view('superadmin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenants,email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
            'logo' => 'nullable|image|max:5120',
            'password' => 'required|string|min:8', // Added password field for the initial user
        ]);

        if ($validated['status'] === 'inactive') {
            $validated['status'] = 'suspended';
        }

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        if ($request->hasFile('logo')) {
            try {
                $validated['logo'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('logo'), 'tenants/logos', 400, 85);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Tenant logo upload failed: ' . $e->getMessage());
                $validated['logo'] = $request->file('logo')->store('tenants/logos', 'public');
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            // 1. Create the Tenant
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'logo' => $validated['logo'] ?? null,
                'status' => $validated['status'],
            ]);

            // 2. Create the User (Organizer)
            $user = \App\Models\User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'] . ' Admin',
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                'is_active' => true,
            ]);

            // 3. Assign Role
            $user->assignRole('Penyedia Event');

            // 4. Set Tenant Owner
            $tenant->update(['user_id' => $user->id]);
        });

        return redirect()->route('superadmin.tenants.index')->with('success', 'Organizer and User account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        return redirect()->route('superadmin.tenants.edit', $tenant);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenants,email,' . $tenant->id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
            'logo' => 'nullable|image|max:5120',
        ]);

        if ($validated['status'] === 'inactive') {
            $validated['status'] = 'suspended';
        }

        if ($tenant->name !== $validated['name']) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        if ($request->hasFile('logo')) {
            try {
                if ($tenant->logo) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($tenant->logo);
                }
                $validated['logo'] = \App\Services\ImageOptimizerService::uploadAndOptimize($request->file('logo'), 'tenants/logos', 400, 85);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Tenant logo upload failed: ' . $e->getMessage());
                $validated['logo'] = $request->file('logo')->store('tenants/logos', 'public');
            }
        }

        $tenant->update($validated);

        return redirect()->route('superadmin.tenants.index')->with('success', 'Organizer updated successfully.');
    }

    /**
     * Display a listing of the trashed resources.
     */
    public function trash()
    {
        $tenants = Tenant::onlyTrashed()->paginate(10);
        return view('superadmin.tenants.trash', compact('tenants'));
    }

    /**
     * Restore the specified resource from trash.
     */
    public function restore($id)
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $tenant->update(['status' => 'inactive']); // Reset to inactive on restore
        $tenant->restore();

        return redirect()->route('superadmin.tenants.trash')->with('success', 'Organizer restored successfully.');
    }

    /**
     * Permanently delete the specified resource from storage.
     */
    public function forceDelete($id)
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        if ($tenant->logo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($tenant->logo);
        }
        $tenant->forceDelete();

        return redirect()->route('superadmin.tenants.trash')->with('success', 'Organizer permanently deleted.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        $tenant->update(['status' => 'deleted']);
        $tenant->delete();
        return redirect()->route('superadmin.tenants.index')->with('success', 'Organizer moved to trash.');
    }
}
