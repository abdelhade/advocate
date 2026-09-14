<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Admin 1: abdelhade
        Admin::updateOrCreate(
            ['username' => 'abdelhade'],
            [
                'name' => 'Abdelhade',
                'password' => Hash::make('Aa1234###'),
            ]
        );

        // Admin 2: hadi
        Admin::updateOrCreate(
            ['username' => 'hadi'],
            [
                'name' => 'Hadi',
                'password' => Hash::make('Aa1234###'),
            ]
        );
    }
}
