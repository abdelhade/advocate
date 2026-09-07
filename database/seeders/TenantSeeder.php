<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the tenant database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        if (!User::where('email', 'admin@office1.com')->exists()) {
            User::factory()->create([
                'name' => 'Tenant Admin',
                'email' => 'admin@office1.com',
                'password' => bcrypt('password'),
            ]);
        }
    }
}
