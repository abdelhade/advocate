<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
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

    public function test_profile_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->onTenant($this->tenant)
            ->get($this->tenantUrl($this->tenant, '/profile'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->onTenant($this->tenant)
            ->patch($this->tenantUrl($this->tenant, '/profile'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->user->refresh();

        $this->assertSame('Test User', $this->user->name);
        $this->assertSame('test@example.com', $this->user->email);
        $this->assertNull($this->user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->onTenant($this->tenant)
            ->patch($this->tenantUrl($this->tenant, '/profile'), [
                'name' => 'Test User',
                'email' => $this->user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($this->user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->onTenant($this->tenant)
            ->delete($this->tenantUrl($this->tenant, '/profile'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted($this->user);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->onTenant($this->tenant)
            ->from($this->tenantUrl($this->tenant, '/profile'))
            ->delete($this->tenantUrl($this->tenant, '/profile'), [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($this->user->fresh());
    }
}
