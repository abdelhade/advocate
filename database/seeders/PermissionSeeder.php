<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $matrix = config('permissions.matrix', []);

        foreach ($matrix as $group) {
            foreach ($group['permissions'] as $name => $label) {
                Permission::updateOrCreate(
                    ['name' => $name],
                    [
                        'guard_name' => 'web',
                        'description' => $group['label'].' — '.$label,
                    ]
                );
            }
        }
    }
}
