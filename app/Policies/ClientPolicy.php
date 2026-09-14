<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use App\Services\TenantContext;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        $tenantId = app(TenantContext::class)->id();
        return $tenantId && $user->tenants()->where('tenants.id', $tenantId)->exists();
    }

    public function view(User $user, Client $client): bool
    {
        $tenantId = app(TenantContext::class)->id();
        return $tenantId && $client->tenant_id === $tenantId;
    }

    public function create(User $user): bool
    {
        $tenantId = app(TenantContext::class)->id();
        return $tenantId && $user->tenants()->where('tenants.id', $tenantId)->exists();
    }

    public function update(User $user, Client $client): bool
    {
        return $this->view($user, $client);
    }

    public function delete(User $user, Client $client): bool
    {
        $tenantId = app(TenantContext::class)->id();
        if (!$tenantId || $client->tenant_id !== $tenantId) {
            return false;
        }

        return $user->is_super_admin || $user->isOwnerOf($tenantId);
    }
}
