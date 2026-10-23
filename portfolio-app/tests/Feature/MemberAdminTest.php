<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_ups_appear_in_the_admin_and_notify_admins(): void
    {
        $admin = $this->admin();

        $this->post('/register', [
            'name' => 'Candidate One', 'email' => 'candidate@example.com',
            'password' => 'Secret-Pass-123', 'password_confirmation' => 'Secret-Pass-123',
        ])->assertRedirect(route('home'));
        $this->post('/logout');

        $this->assertDatabaseHas('admin_notifications', ['user_id' => $admin->id, 'type' => 'member', 'body' => 'Candidate One — candidate@example.com']);

        $this->actingAs($admin)->get('/admin/members')->assertOk()->assertSee('Candidate One')->assertSee('candidate@example.com');
    }

    public function test_members_can_be_searched_filtered_and_deleted(): void
    {
        Member::factory()->create(['name' => 'Email Person', 'email' => 'mail@example.com']);
        $google = Member::factory()->create(['name' => 'Google Person', 'google_id' => 'g-1']);
        $this->actingAs($this->admin());

        $this->get('/admin/members?method=google')->assertSee('Google Person')->assertDontSee('Email Person');
        $this->get('/admin/members?method=email')->assertSee('Email Person')->assertDontSee('Google Person');
        $this->get('/admin/members?search=mail@example')->assertSee('Email Person')->assertDontSee('Google Person');

        $this->delete("/admin/members/{$google->id}")->assertRedirect(route('admin.members.index'));
        $this->assertModelMissing($google);
        $this->assertDatabaseHas('activity_logs', ['action' => 'deleted', 'target' => $google->email]);
    }

    public function test_only_super_admins_can_see_members(): void
    {
        $member = Member::factory()->create();

        $this->get('/admin/members')->assertRedirect(route('admin.login'));
        $this->actingAs($this->admin(User::ROLE_EDITOR));
        $this->get('/admin/members')->assertForbidden();
        $this->delete("/admin/members/{$member->id}")->assertForbidden();
        $this->assertModelExists($member);
    }
}
