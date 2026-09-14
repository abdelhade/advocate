<?php

namespace Tests;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function tenantHost(Tenant $tenant): string
    {
        return $tenant->slug.'.'.config('tenancy.tenant_base_domain', 'localhost');
    }

    protected function onTenant(Tenant $tenant): static
    {
        $host = $this->tenantHost($tenant);

        return $this->withServerVariables([
            'HTTP_HOST' => $host,
            'SERVER_NAME' => $host,
        ]);
    }

    protected function tenantUrl(Tenant $tenant, string $path = '/'): string
    {
        return 'http://'.$this->tenantHost($tenant).'/'.ltrim($path, '/');
    }

    /**
     * @return array{0: User, 1: Tenant}
     */
    protected function createTenantUser(array $userAttrs = [], array $tenantAttrs = []): array
    {
        $tenant = Tenant::factory()->create($tenantAttrs);
        $user = User::factory()->create($userAttrs);
        $user->tenants()->attach($tenant->id, [
            'is_owner' => true,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return [$user, $tenant];
    }
}
