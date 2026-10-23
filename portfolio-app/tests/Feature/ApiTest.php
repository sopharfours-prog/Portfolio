<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_read_published_content_only(): void
    {
        Project::create(['slug' => 'pub', 'title' => 'Published', 'category' => 'web', 'summary' => ['lo' => 'x'], 'status' => 'published']);
        Project::create(['slug' => 'draft', 'title' => 'Draft', 'category' => 'mobile', 'summary' => ['lo' => 'x'], 'status' => 'draft']);
        Setting::put('general', ['site_name' => 'SOPHAR']);
        Setting::put('appearance', ['default_theme' => 'dark']);

        $this->getJson('/api/projects')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'pub')->assertJsonStructure(['data' => [['cover_url', 'public_url']], 'meta' => ['total']]);
        $this->getJson('/api/projects/pub')->assertOk()->assertJsonPath('data.title', 'Published');
        $this->getJson('/api/projects/draft')->assertNotFound();
        $this->getJson('/api/projects?status=draft')->assertJsonCount(0, 'data');
        foreach (['services', 'skills', 'experiences', 'testimonials'] as $resource) {
            $this->getJson("/api/{$resource}")->assertOk();
        }
        $this->getJson('/api/settings')->assertOk()->assertJsonPath('data.general.site_name', 'SOPHAR')->assertJsonMissingPath('data.appearance');
    }

    public function test_writes_require_authentication(): void
    {
        $project = Project::create(['slug' => 'p', 'title' => 'P', 'category' => 'web', 'summary' => ['lo' => 'x'], 'status' => 'published']);

        $this->postJson('/api/projects', $this->projectData())->assertUnauthorized();
        $this->putJson('/api/projects/p', $this->projectData())->assertUnauthorized();
        $this->deleteJson('/api/projects/p')->assertUnauthorized();
        $this->getJson('/api/messages')->assertUnauthorized();
        $this->getJson('/api/media')->assertUnauthorized();
        $this->putJson('/api/settings/general', ['value' => ['site_name' => 'X']])->assertUnauthorized();
        // Without an Accept header the API still answers in JSON
        $this->post('/api/projects', [])->assertUnauthorized()->assertHeader('Content-Type', 'application/json');
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'title' => 'P']);
    }

    public function test_authenticated_crud_with_get_post_put_patch_delete(): void
    {
        Sanctum::actingAs($this->admin(User::ROLE_EDITOR));

        $this->postJson('/api/projects', $this->projectData(['technologies' => ['Vue', 'Laravel'], 'features' => ['en' => ['A', 'B']]]))
            ->assertCreated()->assertJsonPath('data.slug', 'test-project')->assertJsonPath('data.technologies', ['Vue', 'Laravel'])->assertJsonPath('data.features.en', ['A', 'B']);
        $this->postJson('/api/projects', ['title' => ''])->assertUnprocessable()->assertJsonValidationErrors(['title', 'category', 'summary.lo']);

        $this->putJson('/api/projects/test-project', $this->projectData(['title' => 'Via API', 'slug' => 'test-project']))->assertOk()->assertJsonPath('data.title', 'Via API');
        $this->patchJson('/api/projects/test-project', ['status' => 'archived'])->assertOk()->assertJsonPath('data.status', 'archived');
        $this->patchJson('/api/projects/test-project', ['status' => 'nope'])->assertUnprocessable();
        $this->getJson('/api/projects?status=archived')->assertJsonCount(1, 'data');   // authenticated users see every status
        $this->deleteJson('/api/projects/test-project')->assertNoContent();
        $this->assertDatabaseCount('projects', 0);

        $id = $this->postJson('/api/services', ['title' => ['lo' => 'ກ'], 'description' => ['lo' => 'ຂ'], 'icon' => 'phone', 'status' => 'published'])->assertCreated()->json('data.id');
        $this->patchJson("/api/services/{$id}", ['sort_order' => 4])->assertOk();
        $this->assertSame(4, Service::find($id)->sort_order);
    }

    public function test_messages_api_and_settings_permissions(): void
    {
        $this->postJson('/api/messages', ['name' => 'Jo', 'email' => 'jo@example.com', 'subject' => 'Hi', 'message' => 'A long enough message.'])->assertCreated();
        $message = Message::first();

        Sanctum::actingAs($this->admin(User::ROLE_EDITOR));
        $this->getJson('/api/messages')->assertOk()->assertJsonCount(1, 'data');
        $this->patchJson("/api/messages/{$message->id}", ['read' => true, 'folder' => 'archived'])->assertOk();
        $this->assertSame('archived', $message->fresh()->folder);
        $this->assertNotNull($message->fresh()->read_at);
        $this->putJson('/api/settings/general', ['value' => ['site_name' => 'Nope']])->assertForbidden();   // editors can't change settings

        Sanctum::actingAs($this->admin());
        $this->putJson('/api/settings/general', ['value' => ['site_name' => 'Renamed']])->assertOk();
        $this->assertSame('Renamed', Setting::get('general.site_name'));
        $this->putJson('/api/settings/unknown', ['value' => ['a' => 1]])->assertNotFound();
        $this->deleteJson("/api/messages/{$message->id}")->assertNoContent();
    }
}
