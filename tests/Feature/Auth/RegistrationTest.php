<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use App\Notifications\OfficeRegistrationConfirmation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SubscriptionPlanSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();

        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'office_name' => 'مكتب الاختبار',
            'subdomain' => 'testoffice',
            'domain' => 'localhost',
            'email' => 'test@example.com',
            'phone' => '01012345678',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('tenants', [
            'slug' => 'testoffice',
            'domain' => 'jalsateg.com',
            'phone' => '01012345678',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'phone' => '01012345678',
        ]);
        $this->assertStringContainsString('testoffice.localhost', $response->json('redirect_url'));
        $this->assertStringContainsString('verify-email', $response->json('redirect_url'));

        $user = User::where('email', 'test@example.com')->first();
        Notification::assertSentTo($user, OfficeRegistrationConfirmation::class);
    }

    public function test_phone_must_be_valid_egyptian_mobile(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'office_name' => 'مكتب الاختبار',
            'subdomain' => 'badphone',
            'domain' => 'localhost',
            'email' => 'phone@example.com',
            'phone' => '12345',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_registration_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/register', [
            'name' => 'Another Owner',
            'office_name' => 'مكتب آخر',
            'subdomain' => 'anotheroffice',
            'domain' => 'localhost',
            'email' => 'existing@example.com',
            'phone' => '01099999999',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
        $this->assertGuest();
        $this->assertDatabaseMissing('tenants', ['slug' => 'anotheroffice']);
    }
}
