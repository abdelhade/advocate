<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        [, $tenant] = $this->createTenantUser();

        $response = $this->onTenant($tenant)
            ->get($this->tenantUrl($tenant, '/forgot-password'));

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        [$user, $tenant] = $this->createTenantUser();

        $this->onTenant($tenant)
            ->post($this->tenantUrl($tenant, '/forgot-password'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        [$user, $tenant] = $this->createTenantUser();

        $this->onTenant($tenant)
            ->post($this->tenantUrl($tenant, '/forgot-password'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($tenant) {
            $response = $this->onTenant($tenant)
                ->get($this->tenantUrl($tenant, '/reset-password/'.$notification->token));

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        [$user, $tenant] = $this->createTenantUser();

        $this->onTenant($tenant)
            ->post($this->tenantUrl($tenant, '/forgot-password'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user, $tenant) {
            $response = $this->onTenant($tenant)
                ->post($this->tenantUrl($tenant, '/reset-password'), [
                    'token' => $notification->token,
                    'email' => $user->email,
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect('/login');

            return true;
        });
    }
}
