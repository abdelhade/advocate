<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        [$user, $tenant] = $this->createTenantUser();

        $response = $this->actingAs($user)
            ->onTenant($tenant)
            ->get($this->tenantUrl($tenant, '/confirm-password'));

        $response->assertStatus(200);
    }

    public function test_password_can_be_confirmed(): void
    {
        [$user, $tenant] = $this->createTenantUser();

        $response = $this->actingAs($user)
            ->onTenant($tenant)
            ->post($this->tenantUrl($tenant, '/confirm-password'), [
                'password' => 'password',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        [$user, $tenant] = $this->createTenantUser();

        $response = $this->actingAs($user)
            ->onTenant($tenant)
            ->post($this->tenantUrl($tenant, '/confirm-password'), [
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors();
    }
}
