<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $User = User::factory()->create();

        $response = $this
            ->actingAs($User)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $User = User::factory()->create();

        $response = $this
            ->actingAs($User)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $User->refresh();

        $this->assertSame('Test User', $User->name);
        $this->assertSame('test@example.com', $User->email);
        $this->assertNull($User->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $User = User::factory()->create();

        $response = $this
            ->actingAs($User)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $User->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($User->refresh()->email_verified_at);
    }

    public function test_User_can_delete_their_account(): void
    {
        $User = User::factory()->create();

        $response = $this
            ->actingAs($User)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($User->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $User = User::factory()->create();

        $response = $this
            ->actingAs($User)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('UserDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($User->fresh());
    }
}





