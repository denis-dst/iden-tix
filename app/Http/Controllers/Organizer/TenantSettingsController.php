<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;

class TenantSettingsController extends Controller
{
    public function editTerms()
    {
        $tenant = Tenant::findOrFail(auth()->user()->tenant_id);
        $isSuperadmin = auth()->user()->hasRole('Superadmin');
        return view('organizer.settings.terms', compact('tenant', 'isSuperadmin'));
    }

    public function updateTerms(Request $request)
    {
        $request->validate([
            'terms_conditions' => 'nullable|string',
            'email_notifications_enabled' => 'nullable|boolean',
            'wa_notifications_enabled' => 'nullable|boolean',
            'max_tickets_per_person' => 'nullable|integer|min:1|max:100',
        ]);

        $tenant = Tenant::findOrFail(auth()->user()->tenant_id);
        
        $meta = $tenant->meta ?? [];

        // Notification toggles are strictly managed by Superadmin
        if (auth()->user()->hasRole('Superadmin')) {
            $meta['email_notifications_enabled'] = $request->boolean('email_notifications_enabled');
            $meta['wa_notifications_enabled'] = $request->boolean('wa_notifications_enabled');
        }

        // Maximum tickets / order quantity per person
        if ($request->filled('max_tickets_per_person')) {
            $meta['max_tickets_per_person'] = max(1, (int) $request->input('max_tickets_per_person'));
        }

        $tenant->update([
            'terms_conditions' => $request->terms_conditions,
            'meta' => $meta,
        ]);

        return redirect()->back()->with('success', 'Pengaturan tenant berhasil diperbarui.');
    }
}
