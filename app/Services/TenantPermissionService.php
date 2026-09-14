<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantPermissionService
{
    public function matrix(): array
    {
        return config('permissions.matrix', []);
    }

    public function presets(): array
    {
        return config('permissions.presets', []);
    }

    public function allPermissionNames(): array
    {
        $names = [];
        foreach ($this->matrix() as $group) {
            $names = array_merge($names, array_keys($group['permissions']));
        }

        return $names;
    }

    public function permissionsForUser(User $user, Tenant $tenant): array
    {
        if ($user->is_super_admin || $user->isOwnerOf($tenant)) {
            return $this->allPermissionNames();
        }

        return DB::table('model_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->where('model_has_permissions.model_type', User::class)
            ->where('model_has_permissions.model_id', $user->id)
            ->where('model_has_permissions.tenant_id', $tenant->id)
            ->pluck('permissions.name')
            ->all();
    }

    public function sync(User $user, Tenant $tenant, array $permissionNames): void
    {
        $allowed = array_values(array_intersect($permissionNames, $this->allPermissionNames()));
        $permissionIds = Permission::whereIn('name', $allowed)->pluck('id');

        DB::table('model_has_permissions')
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->delete();

        $rows = $permissionIds->map(fn ($id) => [
            'permission_id' => $id,
            'model_type' => User::class,
            'model_id' => $user->id,
            'tenant_id' => $tenant->id,
        ])->all();

        if ($rows !== []) {
            DB::table('model_has_permissions')->insert($rows);
        }
    }

    public function userHas(User $user, Tenant $tenant, string $permission): bool
    {
        if ($user->is_super_admin || $user->isOwnerOf($tenant)) {
            return true;
        }

        return in_array($permission, $this->permissionsForUser($user, $tenant), true);
    }

    public function inertiaPayload(?User $user = null, ?Tenant $tenant = null): array
    {
        return [
            'matrix' => $this->matrix(),
            'presets' => collect($this->presets())->map(fn ($preset, $key) => [
                'key' => $key,
                'label' => $preset['label'],
                'permissions' => $preset['permissions'],
            ])->values()->all(),
            'selected' => ($user && $tenant) ? $this->permissionsForUser($user, $tenant) : [],
        ];
    }
}
