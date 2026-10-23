<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AdminNotification;
use App\Models\Message;
use App\Models\PageView;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagesAndSiteTest extends TestCase
{
    use RefreshDatabase;

    private array $contact = ['name' => 'John Doe', 'email' => 'john@example.com', 'subject' => 'New website', 'message' => 'Hello, I would like a website.'];

    public function test_contact_form_stores_message_and_notifies_admins(): void
    {
        $admin = $this->admin();
        $editor = $this->admin(User::ROLE_EDITOR);

        $this->postJson('/contact', $this->contact)->assertCreated();

        $this->assertDatabaseHas('messages', ['email' => 'john@example.com', 'folder' => 'inbox', 'read_at' => null]);
        $this->assertSame(2, AdminNotification::where('type', 'message')->count());
        $this->assertSame(1, $admin->adminNotifications()->unread()->count());
        $this->assertSame(1, $editor->adminNotifications()->unread()->count());
    }

    public function test_contact_form_keeps_an_optional_phone_number(): void
    {
        $data = ['name' => 'Bee', 'email' => 'bee@example.com', 'subject' => 'Hello', 'message' => 'A message that is long enough.'];

        $this->post('/contact', [...$data, 'phone' => '+856 20 1234 5678'])->assertSessionHasNoErrors();
        $this->post('/contact', $data)->assertSessionHasNoErrors();                              // phone is optional
        $this->post('/contact', [...$data, 'phone' => 'call me maybe'])->assertSessionHasErrors('phone');

        $this->assertSame(['+856 20 1234 5678', null], Message::orderBy('id')->pluck('phone')->all());
    }

    public function test_contact_form_validates_blocks_bots_and_is_rate_limited(): void
    {
        $this->postJson('/contact', ['name' => '', 'email' => 'nope', 'subject' => '', 'message' => 'short'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'subject', 'message']);
        $this->postJson('/contact', [...$this->contact, 'website' => 'http://spam.example'])->assertUnprocessable();
        $this->assertDatabaseCount('messages', 0);

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/contact', $this->contact)->assertCreated();
        }
        $this->postJson('/contact', $this->contact)->assertStatus(429);   // 5 requests per 10 minutes, counting the two above
    }

    public function test_message_text_is_escaped_in_the_admin(): void
    {
        $this->actingAs($this->admin());
        $message = Message::create(['name' => '<script>alert(1)</script>', 'email' => 'x@example.com', 'subject' => 'Hi <b>there</b>', 'body' => '<img src=x onerror=alert(1)>']);

        $this->get("/admin/messages?message={$message->id}")->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false);
    }

    public function test_inbox_views_read_state_archive_trash_and_search(): void
    {
        $this->actingAs($this->admin());
        $a = Message::create(['name' => 'Alice', 'email' => 'a@example.com', 'subject' => 'Branding', 'body' => 'Logo please']);
        $b = Message::create(['name' => 'Bob', 'email' => 'b@example.com', 'subject' => 'Website', 'body' => 'Need a site']);

        $this->get('/admin/messages?view=unread')->assertViewHas('messages', fn ($m) => $m->total() === 2);
        $this->get("/admin/messages?message={$a->id}")->assertOk()->assertSee('Logo please');
        $this->assertNotNull($a->fresh()->read_at);          // opening marks it read
        $this->get('/admin/messages?view=read')->assertViewHas('messages', fn ($m) => $m->total() === 1);
        $this->get('/admin/messages?search=bob')->assertViewHas('messages', fn ($m) => $m->total() === 1);

        $this->patch("/admin/messages/{$a->id}", ['action' => 'unread']);
        $this->assertNull($a->fresh()->read_at);
        $this->patch("/admin/messages/{$a->id}", ['action' => 'archive']);
        $this->get('/admin/messages?view=archived')->assertViewHas('messages', fn ($m) => $m->total() === 1);
        $this->patch("/admin/messages/{$b->id}", ['action' => 'trash']);
        $this->get('/admin/messages')->assertViewHas('messages', fn ($m) => $m->total() === 0);
        $this->patch("/admin/messages/{$b->id}", ['action' => 'restore']);
        $this->assertSame('inbox', $b->fresh()->folder);

        $this->patch("/admin/messages/{$b->id}", ['action' => 'trash']);
        $this->delete('/admin/messages/trash');
        $this->assertDatabaseMissing('messages', ['id' => $b->id]);
        $this->assertDatabaseHas('messages', ['id' => $a->id]);
    }

    public function test_notifications_can_be_read_and_cleared_only_by_their_owner(): void
    {
        $admin = $this->admin();
        $other = $this->admin(User::ROLE_EDITOR);
        AdminNotification::notifyAll('system', 'System Update', 'Details', null);
        $mine = $admin->adminNotifications()->first();
        $theirs = $other->adminNotifications()->first();
        $this->actingAs($admin);

        $this->getJson('/admin/notifications')->assertOk()->assertJsonPath('unread', 1)->assertJsonCount(1, 'items');
        $this->postJson("/admin/notifications/{$theirs->id}/read")->assertNotFound();
        $this->postJson("/admin/notifications/{$mine->id}/read")->assertOk();
        $this->assertNotNull($mine->fresh()->read_at);

        AdminNotification::notifyAll('system', 'Another', null, null);
        $this->postJson('/admin/notifications/read-all')->assertOk();
        $this->assertSame(0, $admin->adminNotifications()->unread()->count());
        $this->postJson('/admin/notifications/clear')->assertOk();
        $this->assertSame(0, $admin->adminNotifications()->count());
        $this->assertSame(2, $other->adminNotifications()->count());
    }

    public function test_activity_log_records_changes_and_can_be_filtered(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $this->post('/admin/skills', ['name' => 'Git', 'category' => 'tools', 'icon' => 'git', 'status' => 'published']);
        $skill = Skill::first();
        $this->put("/admin/skills/{$skill->id}", ['name' => 'Git SCM', 'category' => 'tools', 'icon' => 'git', 'status' => 'published']);
        $this->delete("/admin/skills/{$skill->id}");

        $this->assertSame(['created', 'updated', 'deleted'], ActivityLog::where('subject_type', 'Skill')->orderBy('id')->pluck('action')->all());
        $this->assertSame($admin->id, ActivityLog::first()->user_id);
        $this->assertNotNull(ActivityLog::first()->ip);

        $this->get('/admin/activity?action=deleted')->assertOk()->assertViewHas('logs', fn ($logs) => $logs->total() === 1);
        $this->get('/admin/activity?search=Git')->assertViewHas('logs', fn ($logs) => $logs->total() === 3);
    }

    public function test_public_site_shows_database_content_in_both_languages(): void
    {
        $this->seedSite();
        Project::create(['slug' => 'live', 'title' => 'Live Project', 'category' => 'web', 'summary' => ['lo' => 'ສະຫຼຸບລາວ', 'en' => 'English summary'], 'status' => 'published', 'featured' => true]);
        Project::create(['slug' => 'secret', 'title' => 'Secret Draft', 'category' => 'web', 'summary' => ['lo' => 'x'], 'status' => 'draft', 'featured' => true]);
        SocialLink::create(['platform' => 'github', 'url' => 'https://github.com/example', 'enabled' => true]);
        SocialLink::create(['platform' => 'facebook', 'url' => 'https://facebook.com/example', 'enabled' => false]);

        $this->get('/')->assertOk()->assertSee('ສະບາຍດີ')->assertSee('ສະຫຼຸບລາວ')->assertSee('Live Project')
            ->assertDontSee('Secret Draft')->assertSee('github.com/example')->assertDontSee('facebook.com/example')
            ->assertSee('<html lang="lo"', false);

        $this->get('/lang/en')->assertRedirect();
        $this->get('/')->assertSee('Hello')->assertSee('English summary')->assertSee('<html lang="en"', false);

        foreach (['/about', '/projects', '/contact', '/projects/live', '/projects/live?partial=1', '/sitemap.xml', '/robots.txt'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/projects/secret')->assertNotFound();
        $this->get('/sitemap.xml')->assertSee('/projects/live')->assertDontSee('/projects/secret');
        $this->get('/about.html')->assertRedirect('/about');
    }

    public function test_admin_edits_appear_on_the_public_site_immediately(): void
    {
        $this->seedSite();
        $this->get('/')->assertSee('ນັກພັດທະນາ')->assertSee('lang-switch', false)->assertDontSee('NEWNAME');

        $this->actingAs($this->admin());
        $this->put('/admin/website/hero', ['name' => 'NEWNAME', 'greeting' => ['lo' => 'ສະບາຍດີ'], 'title' => ['lo' => 'ຕຳແໜ່ງໃໝ່'], 'description' => ['lo' => 'ຂໍ້ຄວາມໃໝ່']]);
        $this->put('/admin/settings', ['tab' => 'language', 'enabled' => ['lo'], 'default' => 'lo']);

        $this->get('/')->assertSee('NEWNAME')->assertSee('ຕຳແໜ່ງໃໝ່')->assertDontSee('lang-switch', false);
    }

    public function test_page_views_are_tracked_without_storing_the_ip(): void
    {
        $this->seedSite();
        $project = Project::create(['slug' => 'tracked', 'title' => 'Tracked', 'category' => 'web', 'summary' => ['lo' => 'x'], 'status' => 'published']);

        $this->get('/', ['User-Agent' => 'Mozilla/5.0 Chrome']);
        $this->get('/projects/tracked', ['User-Agent' => 'Mozilla/5.0 Chrome']);
        $this->get('/', ['User-Agent' => 'Googlebot/2.1']);          // bots are ignored

        $this->assertSame(2, PageView::count());
        $this->assertSame($project->id, PageView::whereNotNull('project_id')->value('project_id'));
        $this->assertSame(1, $project->fresh()->views);
        $this->assertSame(64, strlen(PageView::first()->visitor));
        $this->assertStringNotContainsString('127.0.0.1', PageView::first()->visitor);
    }
}
