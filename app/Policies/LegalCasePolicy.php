<?php

namespace App\Policies;

use App\Models\LegalCase;
use App\Models\User;
use App\Services\TenantContext;

class LegalCasePolicy
{
    /**
     * Determine whether the user can view any cases.
     */
    public function viewAny(User $user): bool
    {
        $tenantId = app(TenantContext::class)->id();
        return $tenantId && $user->tenants()->where('tenants.id', $tenantId)->exists();
    }

    /**
     * Determine whether the user can view the specific case.
     */
    public function view(User $user, LegalCase $legalCase): bool
    {
        $tenantId = app(TenantContext::class)->id();
        if (!$tenantId || $legalCase->tenant_id !== $tenantId) {
            return false;
        }

        if ($user->is_super_admin || $user->isOwnerOf($tenantId)) {
            return true;
        }

        return $legalCase->primary_lawyer_id === $user->id
            || $legalCase->assignedUsers()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create cases.
     */
    public function create(User $user): bool
    {
        $tenantId = app(TenantContext::class)->id();
        return $tenantId && $user->tenants()->where('tenants.id', $tenantId)->exists();
    }

    /**
     * Determine whether the user can update the case.
     */
    public function update(User $user, LegalCase $legalCase): bool
    {
        return $this->view($user, $legalCase);
    }

    /**
     * Determine whether the user can delete the case.
     */
    public function delete(User $user, LegalCase $legalCase): bool
    {
        $tenantId = app(TenantContext::class)->id();
        if (!$tenantId || $legalCase->tenant_id !== $tenantId) {
            return false;
        }

        return $user->is_super_admin || $user->isOwnerOf($tenantId);
    }
}
