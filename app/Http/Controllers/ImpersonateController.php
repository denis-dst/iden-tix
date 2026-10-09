<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonateController extends Controller
{
    /**
     * Start impersonating a user.
     */
    public function impersonate(Request $request, User $user)
    {
        $currentUser = Auth::user();
        $isSuperadmin = $currentUser && $currentUser->hasRole('Superadmin');
        $isImpersonating = $request->session()->has('impersonator_id');

        // Only Superadmins or currently active impersonators are permitted
        if (!$isSuperadmin && !$isImpersonating) {
            abort(403, 'Akses tidak diizinkan. Hanya Superadmin yang dapat melakukan impersonasi.');
        }

        // Determine the root admin ID
        $originalAdminId = $request->session()->get('impersonator_id', $currentUser->id);
        $originalAdmin = User::find($originalAdminId);

        if (!$originalAdmin || !$originalAdmin->hasRole('Superadmin')) {
            abort(403, 'Sesi administrator tidak valid.');
        }

        // Disallow impersonating any Superadmin account
        if ($user->hasRole('Superadmin')) {
            return back()->with('error', 'Tidak dapat melakukan impersonasi pada akun Superadmin.');
        }

        // Disallow impersonating oneself
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda saat ini sudah login dengan akun ini.');
        }

        // Log the impersonation action
        Log::info('Superadmin started impersonating user', [
            'admin_id' => $originalAdminId,
            'admin_name' => $originalAdmin->name,
            'target_user_id' => $user->id,
            'target_user_name' => $user->name,
            'target_role' => $user->getRoleNames()->first(),
            'target_tenant_id' => $user->tenant_id,
        ]);

        // Login as the target user (SessionGuard::login automatically migrates/regenerates the session)
        Auth::login($user);

        // Store impersonation metadata in the active session
        $request->session()->put('impersonator_id', $originalAdminId);
        $request->session()->put('impersonated_user_id', $user->id);
        $request->session()->save();

        $roleName = $user->getRoleNames()->first() ?? 'Pengguna';

        if ($user->hasRole('Petugas Gate')) {
            return redirect()->route('organizer.gate.index')->with('success', "Berhasil masuk sebagai {$user->name} (Petugas Gate).");
        }

        if ($user->hasRole('Petugas Loket')) {
            return redirect()->route('organizer.checkin.index')->with('success', "Berhasil masuk sebagai {$user->name} (Petugas Checkin).");
        }

        if ($user->hasRole('Penyedia Event')) {
            return redirect()->route('organizer.dashboard')->with('success', "Berhasil masuk sebagai {$user->name} (Penyedia Event).");
        }

        return redirect()->route('dashboard')->with('success', "Berhasil masuk sebagai {$user->name} ({$roleName}).");
    }

    /**
     * Impersonate into a specific role (tenant, gate, or checkin) for a given tenant.
     */
    public function impersonateTenantRole(Request $request, \App\Models\Tenant $tenant, string $role)
    {
        $currentUser = Auth::user();
        $isSuperadmin = $currentUser && $currentUser->hasRole('Superadmin');
        $isImpersonating = $request->session()->has('impersonator_id');

        if (!$isSuperadmin && !$isImpersonating) {
            abort(403, 'Akses tidak diizinkan. Hanya Superadmin yang dapat melakukan impersonasi.');
        }

        $targetUser = null;
        $role = strtolower($role);

        if ($role === 'tenant' || $role === 'organizer') {
            $targetUser = $tenant->owner ?? $tenant->users()->role('Penyedia Event')->first() ?? $tenant->users()->first();
        } elseif ($role === 'gate') {
            $targetUser = $tenant->users()->role('Petugas Gate')->first();
            
            // Auto-provision an operational gate crew user if none exists for this tenant
            if (!$targetUser) {
                $targetUser = User::create([
                    'name' => 'Petugas Gate - ' . $tenant->name,
                    'email' => 'gate_tenant_' . $tenant->id . '_' . time() . '@iden-tix.com',
                    'password' => bcrypt('password123'),
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                ]);
                $targetUser->assignRole('Petugas Gate');
            }
        } elseif ($role === 'checkin' || $role === 'loket') {
            $targetUser = $tenant->users()->role('Petugas Loket')->first();

            // Auto-provision an operational checkin crew user if none exists for this tenant
            if (!$targetUser) {
                $targetUser = User::create([
                    'name' => 'Petugas Checkin - ' . $tenant->name,
                    'email' => 'checkin_tenant_' . $tenant->id . '_' . time() . '@iden-tix.com',
                    'password' => bcrypt('password123'),
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                ]);
                $targetUser->assignRole('Petugas Loket');
            }
        }

        if (!$targetUser) {
            return back()->with('error', "User target untuk role {$role} tidak dapat ditemukan.");
        }

        return $this->impersonate($request, $targetUser);
    }

    /**
     * Impersonate into a specific role (gate, checkin, or tenant) for a specific event.
     */
    public function impersonateEventRole(Request $request, \App\Models\Event $event, string $role)
    {
        $tenant = $event->tenant;
        if (!$tenant) {
            return back()->with('error', 'Event ini tidak memiliki relasi Tenant yang valid.');
        }

        $role = strtolower($role);
        $redirectResponse = $this->impersonateTenantRole($request, $tenant, $role);

        // For Gate role, direct straight to this event's gate setup
        if ($role === 'gate') {
            session(['gate_event_id' => $event->id]);
            return redirect()->route('organizer.gate.setup', $event)->with('success', "Masuk sebagai Petugas Gate untuk event: {$event->name}");
        }

        // For Checkin role, direct straight to this event's redeem verify or checkin index
        if ($role === 'checkin' || $role === 'loket') {
            return redirect()->route('organizer.redeem.verify', $event)->with('success', "Masuk sebagai Petugas Check-in untuk event: {$event->name}");
        }

        return $redirectResponse;
    }

    /**
     * Leave impersonation and restore original Superadmin session.
     */
    public function leave(Request $request)
    {
        $originalAdminId = $request->session()->get('impersonator_id');

        $adminUser = null;
        if ($originalAdminId) {
            $adminUser = User::find($originalAdminId);
        }

        // Fallback: If impersonator_id wasn't in session or admin wasn't found, find active Superadmin
        if (!$adminUser || !$adminUser->hasRole('Superadmin')) {
            $adminUser = User::role('Superadmin')->first();
        }

        if (!$adminUser) {
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('login')->withErrors(['email' => 'Sesi Superadmin tidak ditemukan. Silakan login kembali.']);
        }

        Log::info('Superadmin left impersonation', [
            'admin_id' => $adminUser->id,
            'previous_user_id' => Auth::id(),
        ]);

        // Clean up all impersonation and operational gate/redeem session keys
        $request->session()->forget([
            'impersonator_id',
            'impersonated_user_id',
            'gate_event_id',
            'gate_id',
            'gate_name',
            'gate_mode',
            'gate_category_id',
            'gate_allowed_categories',
            'gate_auto_timer',
            'redeem_event_id',
        ]);

        // Login as the original superadmin (SessionGuard::login automatically migrates/regenerates session ID)
        Auth::login($adminUser);
        $request->session()->save();

        return redirect()->route('superadmin.dashboard')->with('success', 'Berhasil kembali ke sesi Superadmin.');
    }
}
