<?php

namespace App\Services;

use App\Models\Tenant;

class TenantContext
{
    protected ?Tenant $tenant = null;

    /**
     * Set the current active tenant context.
     */
    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * Get the current active tenant instance.
     */
    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the current active tenant ID.
     */
    public function id(): ?string
    {
        return $this->tenant?->id;
    }

    /**
     * Check if a tenant context is currently set.
     */
    public function check(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * Clear the tenant context.
     */
    public function clear(): void
    {
        $this->tenant = null;
    }
}
