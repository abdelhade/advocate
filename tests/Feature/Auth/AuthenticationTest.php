<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->onTenant($this->tenant)
            ->get($this->tenantUrl($this->tenant, '/login'));

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();
        $user->tenants()->attach($this->tenant->id, [
            'is_owner' => true,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->onTenant($this->tenant)
            ->post($this->tenantUrl($this->tenant, '/login'), [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();
        $user->tenants()->attach($this->tenant->id, [
            'is_owner' => true,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->onTenant($this->tenant)
            ->post($this->tenantUrl($this->tenant, '/login'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();
        $user->tenants()->attach($this->tenant->id, [
            'is_owner' => true,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->onTenant($this->tenant)
            ->post($this->tenantUrl($this->tenant, '/logout'));

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
