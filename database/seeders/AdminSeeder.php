<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Plain passwords — Admin model casts `password` => hashed (do not Hash::make here)
        Admin::updateOrCreate(
            ['username' => 'abdelhade'],
            [
                'name' => 'Abdelhade',
                'password' => 'Aa1234###',
            ]
        );

        Admin::updateOrCreate(
            ['username' => 'hadi'],
            [
                'name' => 'Hadi',
                'password' => 'Aa1234###',
            ]
        );
    }
}
