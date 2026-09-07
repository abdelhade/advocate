<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Tenant;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's central database.
     */
    public function run(): void
    {
        // Check if tenant already exists
        if (!Tenant::find('office1')) {
            $tenant = Tenant::create(['id' => 'office1']);
            $tenant->domains()->create(['domain' => 'office1.localhost']);
        }
    }
}
