<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Services\TenantContext;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        $tenantId = app(TenantContext::class)->id();
        return $tenantId && $user->tenants()->where('tenants.id', $tenantId)->exists();
    }

    public function view(User $user, Document $document): bool
    {
        $tenantId = app(TenantContext::class)->id();
        return $tenantId && $document->tenant_id === $tenantId;
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function create(User $user): bool
    {
        $tenantId = app(TenantContext::class)->id();
        return $tenantId && $user->tenants()->where('tenants.id', $tenantId)->exists();
    }

    public function delete(User $user, Document $document): bool
    {
        $tenantId = app(TenantContext::class)->id();
        if (!$tenantId || $document->tenant_id !== $tenantId) {
            return false;
        }

        return $user->is_super_admin || $user->isOwnerOf($tenantId) || $document->uploader_id === $user->id;
    }
}
