<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_require_login(): void
    {
        foreach (['/admin', '/admin/projects', '/admin/media', '/admin/settings', '/admin/activity'] as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }
    }

    public function test_user_can_log_in_and_out(): void
    {
        $user = $this->admin();

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'Secret-Pass-123'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'login', 'user_id' => $user->id]);

        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected_and_logged(): void
    {
        $user = $this->admin();

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseHas('activity_logs', ['action' => 'login_failed', 'status' => 'failed']);
    }

    public function test_login_is_rate_limited(): void
    {
        $user = $this->admin();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'Secret-Pass-123'])->assertStatus(429);
    }

    public function test_passwords_are_stored_hashed(): void
    {
        $user = $this->admin();

        $this->assertNotSame('Secret-Pass-123', $user->getRawOriginal('password'));
        $this->assertTrue(Hash::check('Secret-Pass-123', $user->getRawOriginal('password')));
    }

    public function test_requests_without_csrf_token_are_rejected(): void
    {
        $user = $this->admin();
        // Laravel skips CSRF checks while unit testing, so exercise the middleware as in production
        $this->app['env'] = 'production';

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'Secret-Pass-123'])->assertStatus(419);
    }

    public function test_password_reset_link_can_be_requested_and_used(): void
    {
        Notification::fake();
        $user = $this->admin();

        $this->post('/admin/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        // Unknown emails get the same answer (no account discovery)
        $this->post('/admin/forgot-password', ['email' => 'nobody@example.com'])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $this->post('/admin/reset-password', [
                'token' => $notification->token, 'email' => $user->email,
                'password' => 'Brand-New-Pass-456', 'password_confirmation' => 'Brand-New-Pass-456',
            ])->assertRedirect(route('admin.login'));

            return true;
        });
        $this->assertTrue(Hash::check('Brand-New-Pass-456', $user->fresh()->password));
    }

    public function test_editor_cannot_access_super_admin_areas(): void
    {
        $this->actingAs($this->admin(User::ROLE_EDITOR));

        foreach (['/admin/settings', '/admin/users', '/admin/activity', '/admin/website/hero'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->put('/admin/settings', ['tab' => 'general', 'site_name' => 'Hacked'])->assertForbidden();
        foreach (['/admin', '/admin/projects', '/admin/services', '/admin/skills', '/admin/experience', '/admin/testimonials', '/admin/messages', '/admin/media', '/admin/profile'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_super_admin_can_manage_users_but_not_remove_the_last_super_admin(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->post('/admin/users', [
            'name' => 'New Editor', 'email' => 'new@example.com', 'role' => 'editor', 'locale' => 'en',
            'password' => 'Editor-Pass-123', 'password_confirmation' => 'Editor-Pass-123',
        ])->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'role' => 'editor']);

        $this->put("/admin/users/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'role' => 'editor', 'locale' => 'en'])
            ->assertSessionHasErrors('role');
        $this->delete("/admin/users/{$admin->id}");
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'super_admin']);
    }

    public function test_profile_and_password_can_be_updated(): void
    {
        $user = $this->admin();
        $this->actingAs($user);

        $this->put('/admin/profile', ['name' => 'Renamed', 'email' => $user->email, 'locale' => 'lo'])->assertSessionHasNoErrors();
        $this->assertSame('Renamed', $user->fresh()->name);

        $this->put('/admin/profile/password', ['current_password' => 'nope', 'password' => 'Another-Pass-789', 'password_confirmation' => 'Another-Pass-789'])
            ->assertSessionHasErrorsIn('password', 'current_password');
        $this->put('/admin/profile/password', ['current_password' => 'Secret-Pass-123', 'password' => 'Another-Pass-789', 'password_confirmation' => 'Another-Pass-789'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('Another-Pass-789', $user->fresh()->password));
        $this->assertTrue(ActivityLog::where('action', 'password_changed')->exists());
    }

    public function test_changing_password_ends_other_logins(): void
    {
        config(['session.driver' => 'database']);
        $user = $this->admin();
        $user->forceFill(['remember_token' => 'old-token'])->save();
        DB::table('sessions')->insert([
            ['id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()],
            ['id' => 'someone-else', 'user_id' => null, 'payload' => '', 'last_activity' => time()],
        ]);

        $this->actingAs($user)->put('/admin/profile/password', [
            'current_password' => 'Secret-Pass-123',
            'password' => 'New-Secret-Pass-456',
            'password_confirmation' => 'New-Secret-Pass-456',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('sessions', ['id' => 'other-device']);
        $this->assertDatabaseHas('sessions', ['id' => 'someone-else']);
        $this->assertNotSame('old-token', $user->fresh()->remember_token);
    }
}
