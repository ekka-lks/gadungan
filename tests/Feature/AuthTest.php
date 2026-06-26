<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@gadungguard.com',
            'password' => Hash::make('password'),
        ]);
    }

    /**
     * Test: unauthenticated user is redirected to login page.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    /**
     * Test: login page is accessible.
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /**
     * Test: admin can login with correct credentials.
     */
    public function test_admin_can_login_with_correct_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@gadungguard.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($this->admin);
    }

    /**
     * Test: login fails with wrong password.
     */
    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@gadungguard.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test: login fails with non-existent email.
     */
    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nobody@gadungguard.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test: authenticated user can access dashboard.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test: admin can logout.
     */
    public function test_admin_can_logout(): void
    {
        $response = $this->actingAs($this->admin)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test: already logged in user visiting /login is redirected to dashboard.
     */
    public function test_logged_in_user_is_redirected_from_login_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/login');
        $response->assertRedirect('/');
    }
}
