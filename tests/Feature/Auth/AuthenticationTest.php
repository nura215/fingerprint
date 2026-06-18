<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_Users_can_authenticate_using_the_login_screen(): void
    {
        $User = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $User->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_Users_can_not_authenticate_with_invalid_password(): void
    {
        $User = User::factory()->create();

        $this->post('/login', [
            'email' => $User->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_Users_can_logout(): void
    {
        $User = User::factory()->create();

        $response = $this->actingAs($User)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}





