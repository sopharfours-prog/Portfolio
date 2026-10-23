<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_register_and_is_signed_in(): void
    {
        $this->post('/register', [
            'name' => 'Noy Visitor',
            'email' => 'noy@example.com',
            'password' => 'Secret-Pass-123',
            'password_confirmation' => 'Secret-Pass-123',
        ])->assertRedirect(route('home'));

        $member = Member::where('email', 'noy@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($member, 'member');
        $this->assertGuest('web');
        $this->assertTrue(Hash::check('Secret-Pass-123', $member->password));
    }

    public function test_registration_rejects_weak_password_and_duplicate_email(): void
    {
        Member::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register', [
            'name' => 'Someone',
            'email' => 'taken@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest('member');
    }

    public function test_visitor_can_log_in_and_out(): void
    {
        $member = Member::factory()->create(['password' => 'Secret-Pass-123']);

        $this->post('/login', ['email' => $member->email, 'password' => 'Secret-Pass-123'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($member, 'member');
        $this->assertNotNull($member->fresh()->last_login_at);

        $this->get('/')->assertOk()->assertSee($member->initials());

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest('member');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $member = Member::factory()->create(['password' => 'Secret-Pass-123']);

        $this->post('/login', ['email' => $member->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest('member');
    }

    public function test_signed_in_visitor_cannot_open_the_admin(): void
    {
        $this->signIn(Member::factory()->create(['password' => 'Secret-Pass-123']));

        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/projects')->assertRedirect(route('admin.login'));
        $this->getJson('/api/messages')->assertUnauthorized();
    }

    public function test_administrator_can_sign_in_on_the_site_login_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'password' => 'Secret-Pass-123']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'Secret-Pass-123'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'web');
        $this->assertGuest('member');
        $this->assertDatabaseHas('activity_logs', ['action' => 'login', 'user_id' => $admin->id]);
        $this->get('/admin')->assertOk();
    }

    public function test_wrong_administrator_password_on_site_login_is_rejected_and_logged(): void
    {
        $admin = User::factory()->create(['password' => 'Secret-Pass-123']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest('web');
        $this->assertDatabaseHas('activity_logs', ['action' => 'login_failed', 'user_id' => $admin->id]);
    }

    public function test_signed_in_administrator_is_shown_in_the_site_header(): void
    {
        $admin = User::factory()->create(['name' => 'Noy Admin', 'role' => User::ROLE_SUPER_ADMIN]);
        $this->actingAs($admin);

        $this->get('/')->assertOk()
            ->assertSee('Noy Admin')
            ->assertSee(route('admin.dashboard'), false)
            ->assertDontSee('class="nav-auth__login"', false);
        $this->get('/login')->assertRedirect(route('admin.dashboard'));
    }

    public function test_administrator_signing_out_from_the_site_returns_to_the_site(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]));

        $this->post('/admin/logout', ['to' => 'site'])->assertRedirect(route('home'));
        $this->assertGuest('web');
    }

    public function test_visitor_logout_keeps_admin_session(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $this->signIn(Member::factory()->create(['password' => 'Secret-Pass-123']));
        $this->actingAs($admin);

        $this->post('/logout');

        $this->assertGuest('member');
        $this->assertAuthenticatedAs($admin, 'web');
    }

    public function test_auth_pages_render_and_redirect_signed_in_visitors(): void
    {
        $this->get('/login')->assertOk()->assertSee('name="password"', false);
        $this->get('/register')->assertOk()->assertSee('name="password_confirmation"', false);

        $this->signIn(Member::factory()->create(['password' => 'Secret-Pass-123']));
        $this->get('/login')->assertRedirect(route('home'));
        $this->get('/register')->assertRedirect(route('home'));
    }

    /** Signs in through the real form (actingAs() would also switch the default guard to "member"). */
    private function signIn(Member $member): void
    {
        $this->post('/login', ['email' => $member->email, 'password' => 'Secret-Pass-123'])->assertRedirect(route('home'));
    }
}
