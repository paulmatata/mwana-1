<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Creates the one and only "owner" account for the whole Mwana platform.
     * Change the email/password immediately after first login in production.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@mwana.app'],
            [
                'role' => 'super_admin',
                'name' => 'Mwana Super Admin',
                'phone' => null,
                'password' => Hash::make('ChangeMe123!'),
                'school_id' => null,
                'status' => 'active',
            ]
        );
    }
}
