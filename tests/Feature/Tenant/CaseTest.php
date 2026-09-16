<?php

namespace Tests\Feature\Tenant;

use App\Models\Client;
use App\Models\LegalCase;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
        $this->user->tenants()->attach($this->tenant->id, [
            'is_owner' => true,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    public function test_cases_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->onTenant($this->tenant)
            ->get($this->tenantUrl($this->tenant, '/cases'));

        $response->assertOk();
    }

    public function test_user_can_list_legal_cases(): void
    {
        $client = Client::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'عميل القضية',
        ]);

        $case = LegalCase::create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'case_number' => 'CASE-2026-001',
            'title' => 'نزاع تجاري',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->onTenant($this->tenant)
            ->get($this->tenantUrl($this->tenant, '/cases'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Cases/Index')
            ->has('cases.data', 1)
            ->where('cases.data.0.case_number', 'CASE-2026-001')
            ->where('cases.data.0.title', 'نزاع تجاري')
        );
    }
}
