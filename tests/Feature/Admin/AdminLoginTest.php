<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_screen_can_be_rendered(): void
    {
        $this->get('http://localhost/admin/login')
            ->assertOk();
    }

    public function test_admin_can_authenticate(): void
    {
        Admin::create([
            'name' => 'Hadi',
            'username' => 'hadi',
            'password' => 'Aa1234###',
        ]);

        $response = $this->post('http://localhost/admin/login', [
            'username' => 'hadi',
            'password' => 'Aa1234###',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated('admin');
    }

    public function test_admin_login_rejects_invalid_credentials(): void
    {
        Admin::create([
            'name' => 'Hadi',
            'username' => 'hadi',
            'password' => 'Aa1234###',
        ]);

        $response = $this->from('http://localhost/admin/login')
            ->post('http://localhost/admin/login', [
                'username' => 'hadi',
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest('admin');
    }
}
