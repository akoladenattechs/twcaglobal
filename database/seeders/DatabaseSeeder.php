<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RbacSeeder::class);

        // Ensure Superadmin user exists
        $superAdminRole = \App\Models\Role::where('is_super_admin', true)->first();
        if ($superAdminRole) {
            \App\Models\User::firstOrCreate(
                ['email' => 'admin@twcaglobal.org'],
                [
                    'name' => 'Super Admin',
                    'username' => 'superadmin',
                    'password' => \Illuminate\Support\Facades\Hash::make('Admin123!'),
                    'role_id' => $superAdminRole->id,
                    'status' => 'active',
                    'is_active' => true,
                ]
            );
        }
    }
}
