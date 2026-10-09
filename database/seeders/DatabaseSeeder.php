<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles
        $superadminRole = Role::create(['name' => 'Superadmin']);
        $providerRole = Role::create(['name' => 'Penyedia Event']);
        $cashierRole = Role::create(['name' => 'Petugas Loket']);
        $gateRole = Role::create(['name' => 'Petugas Gate']);

        // 2. Create Initial Tenant (System Central)
        $centralTenant = Tenant::create([
            'name' => 'IdenTix Central',
            'slug' => 'identix-central',
            'email' => 'admin@iden-tix.com',
            'status' => 'active'
        ]);

        // 3. Create Superadmin User
        $superadmin = User::create([
            'name' => 'Super Admin IdenTix',
            'email' => 'superadmin@iden-tix.com',
            'password' => Hash::make('identix123'),
            'tenant_id' => $centralTenant->id,
            'is_active' => true
        ]);
        $superadmin->assignRole($superadminRole);

        // 4. Create Example Event Provider
        $eoTenant = Tenant::create([
            'name' => 'Example EO',
            'slug' => 'example-eo',
            'email' => 'contact@example-eo.com',
            'status' => 'active'
        ]);

        $provider = User::create([
            'name' => 'Provider Manager',
            'email' => 'eo@example.com',
            'password' => Hash::make('eo123'),
            'tenant_id' => $eoTenant->id,
            'is_active' => true
        ]);
        $provider->assignRole($providerRole);

        // 5. Create Staff for EO
        $cashier = User::create([
            'name' => 'Cashier Staff',
            'email' => 'cashier@example.com',
            'password' => Hash::make('cashier123'),
            'tenant_id' => $eoTenant->id,
            'is_active' => true
        ]);
        $cashier->assignRole($cashierRole);

        $gateStaff = User::create([
            'name' => 'Gate Staff',
            'email' => 'gate@example.com',
            'password' => Hash::make('gate123'),
            'tenant_id' => $eoTenant->id,
            'is_active' => true
        ]);
        $gateStaff->assignRole($gateRole);
    }
}
