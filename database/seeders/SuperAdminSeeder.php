<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@iden-tix.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Ngehek599!'),
                'is_active' => true,
            ]
        );

        $user->assignRole('Superadmin');
    }
}
