<?php

namespace Tests\Feature;

use App\Models\Education;
use App\Models\Experience;
use App\Models\Media;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs($this->admin());
    }

    public function test_project_crud_with_gallery_duplicate_and_delete(): void
    {
        $images = collect(['a.png', 'b.png'])->map(fn ($name) => $this->postJson('/admin/media', ['files' => [UploadedFile::fake()->image($name, 800, 600)]])->json('data.0.id'));

        $this->post('/admin/projects', $this->projectData(['gallery_present' => 1, 'gallery' => $images->all(), 'cover' => $images[1]]))->assertRedirect();
        $project = Project::firstWhere('slug', 'test-project');

        $this->assertSame('published', $project->status);
        $this->assertNotNull($project->published_at);
        $this->assertSame(['One', 'Two'], $project->features['en']);
        $this->assertSame(['Laravel', 'MySQL'], $project->technologies);
        $this->assertCount(2, $project->images);
        $this->assertTrue($project->images[1]->is_cover);
        $this->assertDatabaseHas('admin_notifications', ['type' => 'project']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'created', 'subject_type' => 'Project']);

        $this->put("/admin/projects/{$project->slug}", $this->projectData(['title' => 'Renamed', 'slug' => 'test-project', 'action' => 'draft', 'gallery_present' => 1, 'gallery' => [$images[0]]]))->assertRedirect();
        $project->refresh();
        $this->assertSame(['Renamed', 'draft'], [$project->title, $project->status]);
        $this->assertCount(1, $project->images);
        $this->assertTrue($project->images->first()->is_cover);

        $this->post("/admin/projects/{$project->slug}/duplicate")->assertRedirect();
        $this->assertDatabaseHas('projects', ['slug' => 'test-project-copy', 'status' => 'draft']);

        $this->delete("/admin/projects/{$project->slug}")->assertRedirect(route('admin.projects.index'));
        $this->assertDatabaseMissing('projects', ['slug' => 'test-project']);
        $this->assertDatabaseMissing('project_images', ['project_id' => $project->id]);
    }

    public function test_project_validation(): void
    {
        $this->post('/admin/projects', ['title' => '', 'category' => 'nope', 'url' => 'javascript:alert(1)'])
            ->assertSessionHasErrors(['title', 'category', 'summary.lo', 'url']);

        Project::create(['slug' => 'taken', 'title' => 'Taken', 'category' => 'web', 'summary' => ['lo' => 'x']]);
        $this->post('/admin/projects', $this->projectData(['slug' => 'taken']))->assertSessionHasErrors('slug');
    }

    public function test_project_list_search_filter_sort_and_pagination(): void
    {
        foreach (range(1, 12) as $i) {
            Project::create(['slug' => "p{$i}", 'title' => $i === 3 ? 'Coffee Shop' : "Project {$i}", 'category' => $i % 2 ? 'web' : 'mobile', 'summary' => ['lo' => 'x'], 'status' => $i > 10 ? 'draft' : 'published']);
        }

        $this->get('/admin/projects')->assertOk()->assertViewHas('projects', fn ($p) => $p->total() === 12 && $p->count() === 10 && $p->lastPage() === 2);
        $this->get('/admin/projects?page=2')->assertViewHas('projects', fn ($p) => $p->count() === 2);
        $this->get('/admin/projects?search=coffee')->assertViewHas('projects', fn ($p) => $p->total() === 1);
        $this->get('/admin/projects?status=draft')->assertViewHas('projects', fn ($p) => $p->total() === 2);
        $this->get('/admin/projects?category=mobile&status=published')->assertViewHas('projects', fn ($p) => $p->total() === 5);
        $this->get('/admin/projects?sort=title')->assertViewHas('projects', fn ($p) => $p->first()->title === 'Coffee Shop');
    }

    public function test_service_skill_experience_and_testimonial_crud(): void
    {
        $this->post('/admin/services', ['title' => ['lo' => 'ບໍລິການ', 'en' => 'Service'], 'description' => ['lo' => 'ລາຍລະອຽດ'], 'features' => ['lo' => "ກ\nຂ"], 'icon' => 'code-slash', 'status' => 'published'])->assertRedirect(route('admin.services.index'));
        $service = Service::first();
        $this->assertSame(['ກ', 'ຂ'], $service->features['lo']);
        $this->put("/admin/services/{$service->id}", ['title' => ['lo' => 'ໃໝ່'], 'description' => ['lo' => 'x'], 'icon' => 'phone', 'status' => 'draft'])->assertSessionHasNoErrors();
        $this->assertSame(['ໃໝ່', 'phone', 'draft'], [$service->fresh()->title['lo'], $service->fresh()->icon, $service->fresh()->status]);
        $this->delete("/admin/services/{$service->id}");
        $this->assertDatabaseCount('services', 0);

        $this->post('/admin/skills', ['name' => 'PHP', 'category' => 'backend', 'icon' => 'filetype-php', 'level' => 85, 'status' => 'published'])->assertSessionHasNoErrors();
        $this->post('/admin/skills', ['name' => 'Bad', 'category' => 'backend', 'icon' => '<svg>', 'level' => 150, 'status' => 'published'])->assertSessionHasErrors(['icon', 'level']);
        $skill = Skill::first();
        $this->put("/admin/skills/{$skill->id}", ['name' => 'PHP 8', 'category' => 'backend', 'icon' => 'filetype-php', 'status' => 'published'])->assertSessionHasNoErrors();
        $this->assertSame('PHP 8', $skill->fresh()->name);
        $this->delete("/admin/skills/{$skill->id}");
        $this->assertDatabaseCount('skills', 0);

        $this->post('/admin/experience', ['company' => 'Acme', 'position' => ['lo' => 'ນັກພັດທະນາ', 'en' => 'Developer'], 'start_date' => '2024-01-01', 'is_current' => 1, 'technologies' => 'PHP, JS', 'status' => 'published'])->assertSessionHasNoErrors();
        $experience = Experience::first();
        $this->assertTrue($experience->is_current);
        $this->assertNull($experience->end_date);
        $this->post('/admin/experience', ['company' => 'Acme', 'position' => ['lo' => 'x'], 'start_date' => '2024-01-01', 'end_date' => '2023-01-01', 'status' => 'published'])->assertSessionHasErrors('end_date');
        $this->delete("/admin/experience/{$experience->id}");
        $this->assertDatabaseCount('experiences', 0);

        $this->post('/admin/testimonials', ['client_name' => 'Jane', 'message' => ['lo' => 'ດີຫຼາຍ', 'en' => 'Great'], 'rating' => 5, 'status' => 'hidden', 'photo' => UploadedFile::fake()->image('jane.jpg', 200, 200)])->assertSessionHasNoErrors();
        $testimonial = Testimonial::first();
        $this->assertSame('hidden', $testimonial->status);
        Storage::disk('public')->assertExists($testimonial->photo);
        $this->delete("/admin/testimonials/{$testimonial->id}");
        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_skills_show_technical_bars_and_professional_rings(): void
    {
        $this->seedSite();
        $this->post('/admin/skills', ['name' => 'HTML', 'category' => 'frontend', 'icon' => 'filetype-html', 'level' => 95, 'status' => 'published'])->assertSessionHasNoErrors();
        $this->post('/admin/skills', [
            'name' => 'Creativity', 'label' => ['lo' => 'ຄວາມຄິດສ້າງສັນ', 'en' => 'Creativity'],
            'category' => 'soft', 'icon' => 'lightbulb', 'level' => 90, 'status' => 'published',
        ])->assertSessionHasNoErrors();

        $this->assertEquals(['lo' => 'ຄວາມຄິດສ້າງສັນ', 'en' => 'Creativity'], Skill::where('category', 'soft')->first()->label);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('class="skill-bar"', $html);
        $this->assertStringContainsString('aria-valuenow="95"', $html);
        $this->assertStringContainsString('class="skill-ring"', $html);
        $this->assertStringContainsString('ຄວາມຄິດສ້າງສັນ: 90%', $html);   // Lao name on the Lao page

        $this->get('/lang/en');
        $this->get('/')->assertSee('Creativity: 90%')->assertSee('Professional skills');
    }

    public function test_education_image_is_saved_and_shown_on_the_website(): void
    {
        $this->seedSite();
        $this->post('/admin/education', [
            'institution' => 'NUOL', 'degree' => ['lo' => 'ປະລິນຍາຕີ'], 'start_date' => '2017-09-01', 'end_date' => '2021-07-01',
            'image' => 'site/nuol-certificate.webp', 'status' => 'published',
        ])->assertSessionHasNoErrors();

        $item = Education::first();
        $this->assertSame('site/nuol-certificate.webp', $item->image);
        $this->get("/admin/education/{$item->id}/edit")->assertOk()->assertSee('site/nuol-certificate.webp', false);
        $this->get('/')->assertSee($item->imageUrl(), false)->assertSee('class="entry-card__logo"', false);

        // Removing the image (empty value) hides it again
        $this->put("/admin/education/{$item->id}", ['institution' => 'NUOL', 'degree' => ['lo' => 'ປະລິນຍາຕີ'], 'start_date' => '2017-09-01', 'end_date' => '2021-07-01', 'image' => '', 'status' => 'published']);
        $this->assertNull($item->fresh()->image);
        $this->get('/')->assertDontSee('nuol-certificate.webp', false);
    }

    public function test_education_photo_uses_its_own_image_or_falls_back_to_the_profile_image(): void
    {
        $this->seedSite();
        Education::create(['institution' => 'NUOL', 'degree' => ['lo' => 'ປະລິນຍາຕີ'], 'start_date' => '2017-09-01', 'end_date' => '2021-07-01', 'status' => 'published']);
        Setting::put('about', [...Setting::get('about', []), 'image' => 'site/profile.webp', 'education_image' => null]);

        $this->get('/')->assertSee('class="edu__photo"', false)->assertSee(media_url('site/profile.webp'), false);

        Setting::put('about', [...Setting::get('about', []), 'education_image' => 'site/graduation.webp']);
        $this->get('/')->assertSee(media_url('site/graduation.webp'), false);
    }

    public function test_education_crud_and_public_section(): void
    {
        $this->seedSite();
        $this->post('/admin/education', [
            'institution' => 'National University of Laos', 'degree' => ['lo' => 'ປະລິນຍາຕີ', 'en' => 'Bachelor'], 'field' => ['en' => 'Computer Science'],
            'start_date' => '2017-09-01', 'end_date' => '2021-07-01', 'status' => 'published',
        ])->assertRedirect(route('admin.education.index'));
        $item = Education::first();
        $this->assertSame('Bachelor', $item->degree['en']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'created', 'subject_type' => 'Education']);

        $this->get('/admin/education')->assertOk()->assertSee('National University of Laos');
        $this->get("/admin/education/{$item->id}/edit")->assertOk();
        $this->get('/')->assertSee('id="education"', false)->assertSee('ປະລິນຍາຕີ')->assertSee('2017 — 2021');
        $this->getJson('/api/education')->assertOk()->assertJsonPath('data.0.institution', 'National University of Laos');

        $this->post('/admin/education', ['institution' => 'X', 'degree' => ['lo' => 'x'], 'start_date' => '2020-01-01', 'end_date' => '2019-01-01', 'status' => 'published'])->assertSessionHasErrors('end_date');
        $this->put("/admin/education/{$item->id}", ['institution' => 'NUOL', 'degree' => ['lo' => 'ປະລິນຍາໂທ'], 'start_date' => '2023-09-01', 'is_current' => 1, 'status' => 'draft'])->assertSessionHasNoErrors();
        $this->assertSame(['NUOL', true, null], [$item->fresh()->institution, $item->fresh()->is_current, $item->fresh()->end_date]);
        $this->get('/')->assertDontSee('id="education"', false);     // drafts are hidden, and an empty section is not rendered

        $this->delete("/admin/education/{$item->id}");
        $this->assertDatabaseCount('education', 0);
    }

    public function test_drag_and_drop_order_is_saved(): void
    {
        $ids = collect(['A', 'B', 'C'])->map(fn ($name, $i) => Skill::create(['name' => $name, 'category' => 'tools', 'sort_order' => $i])->id);

        $this->postJson('/admin/reorder/skills', ['ids' => [$ids[2], $ids[0], $ids[1]]])->assertOk();

        $this->assertSame(['C', 'A', 'B'], Skill::orderBy('sort_order')->pluck('name')->all());
        $this->postJson('/admin/reorder/users', ['ids' => [1]])->assertNotFound();
    }

    public function test_media_upload_validation_rename_and_delete(): void
    {
        $response = $this->postJson('/admin/media', ['files' => [UploadedFile::fake()->image('photo.png', 3000, 1500)]])->assertCreated();
        $media = Media::find($response->json('data.0.id'));

        $this->assertSame('image/webp', $media->mime_type);   // PNG is re-encoded as WebP
        $this->assertSame(2400, $media->width);               // and scaled down to the 2400px limit
        Storage::disk('public')->assertExists($media->path);

        // Executables, wrong MIME types, disguised files and oversized files are refused
        $this->postJson('/admin/media', ['files' => [UploadedFile::fake()->create('shell.php', 10, 'application/x-php')]])->assertUnprocessable();
        $this->postJson('/admin/media', ['files' => [UploadedFile::fake()->createWithContent('fake.png', '<?php echo 1;')]])->assertUnprocessable();
        $this->postJson('/admin/media', ['files' => [UploadedFile::fake()->create('big.jpg', 6000, 'image/jpeg')]])->assertUnprocessable();
        $this->postJson('/admin/media', ['files' => [UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')]])->assertUnprocessable();

        $this->putJson("/admin/media/{$media->id}", ['name' => 'Renamed', 'alt' => 'Alt text'])->assertOk();
        $this->assertSame(['Renamed', 'Alt text'], [$media->fresh()->name, $media->fresh()->alt]);

        $this->get('/admin/media?search=Renamed')->assertOk()->assertViewHas('items', fn ($items) => $items->total() === 1);

        $this->deleteJson("/admin/media/{$media->id}")->assertOk();
        Storage::disk('public')->assertMissing($media->path);
    }

    public function test_uploaded_svg_is_sanitised(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10" onload="alert(1)"><script>alert(2)</script><a href="javascript:alert(3)"><rect width="10" height="10"/></a></svg>';
        $id = $this->postJson('/admin/media', ['files' => [UploadedFile::fake()->createWithContent('logo.svg', $svg)]])->assertCreated()->json('data.0.id');

        $stored = Storage::disk('public')->get(Media::find($id)->path);
        $this->assertStringNotContainsStringIgnoringCase('script', $stored);
        $this->assertStringNotContainsString('onload', $stored);
        $this->assertStringNotContainsString('javascript:', $stored);
        $this->assertStringContainsString('<rect', $stored);

        $entity = '<!DOCTYPE svg [<!ENTITY x SYSTEM "file:///etc/passwd">]><svg xmlns="http://www.w3.org/2000/svg">&x;</svg>';
        $this->postJson('/admin/media', ['files' => [UploadedFile::fake()->createWithContent('xxe.svg', $entity)]])->assertUnprocessable();
    }

    public function test_website_content_and_settings_can_be_updated(): void
    {
        $this->put('/admin/website/hero', ['name' => 'SOPHAR', 'greeting' => ['lo' => 'ສະບາຍດີ', 'en' => 'Hi'], 'title' => ['lo' => 'ນັກອອກແບບ'], 'description' => ['lo' => 'ຂໍ້ຄວາມ'], 'primary_url' => '#projects'])->assertSessionHasNoErrors();
        $this->assertSame('Hi', Setting::get('hero.greeting.en'));
        $this->put('/admin/website/hero', ['name' => 'X', 'greeting' => ['lo' => 'a'], 'title' => ['lo' => 'b'], 'description' => ['lo' => 'c'], 'primary_url' => 'javascript:alert(1)'])->assertSessionHasErrors('primary_url');

        $this->put('/admin/website/about', [
            'page_title' => ['lo' => 'ກ່ຽວກັບ', 'en' => 'About'], 'page_title_accent' => ['lo' => 'ຂ້ອຍ', 'en' => 'Me'],
            'subtitle' => ['lo' => 'ນັກພັດທະນາ', 'en' => 'Developer'], 'cta_label' => ['lo' => 'ຕິດຕໍ່', 'en' => 'Contact Me'], 'cta_url' => '#contact',
            'headline' => ['lo' => 'ຫົວຂໍ້'], 'introduction' => ['lo' => 'ແນະນຳ'],
            'stats' => [['value' => 7, 'suffix' => '+', 'label' => ['lo' => 'ປີ', 'en' => 'Years']], ['value' => '', 'suffix' => '', 'label' => ['lo' => '']]],
            'socials' => ['github' => ['url' => 'https://github.com/example', 'enabled' => 1], 'telegram' => ['url' => '', 'enabled' => 0]],
        ])->assertSessionHasNoErrors();
        $this->assertCount(1, Setting::get('about.stats'));
        $this->assertSame(7, Setting::get('about.stats.0.value'));
        $this->assertSame(['Me', '#contact'], [Setting::get('about.page_title_accent.en'), Setting::get('about.cta_url')]);
        $this->put('/admin/website/about', ['page_title' => ['lo' => 'x'], 'headline' => ['lo' => 'x'], 'introduction' => ['lo' => 'x'], 'cta_url' => 'javascript:alert(1)'])->assertSessionHasErrors('cta_url');
        $this->put('/admin/website/about', ['headline' => ['lo' => 'x'], 'introduction' => ['lo' => 'x']])->assertSessionHasErrors('page_title.lo');

        // The About page shows the saved content, with the navigation on "About"
        Setting::put('seo', ['meta_title' => ['lo' => 't'], 'meta_description' => ['lo' => 'd']]);
        Setting::put('general', ['site_name' => 'SOPHAR']);
        $this->get('/lang/lo');
        $this->get('/about')->assertOk()->assertSee('about-hero', false)->assertSee('ກ່ຽວກັບ')->assertSee('ນັກພັດທະນາ')->assertSee('ແນະນຳ')
            ->assertSee('href="#contact"', false)->assertSee('id="contact"', false)->assertSee('data-page="about"', false);
        $this->get('/lang/en');
        $this->get('/about')->assertSee('About')->assertSee('Contact Me')->assertSee('Developer');
        $this->get('/lang/lo');
        $this->assertTrue(SocialLink::firstWhere('platform', 'github')->enabled);
        $this->assertFalse(SocialLink::firstWhere('platform', 'telegram')->enabled);

        $this->put('/admin/website/contact', ['email' => 'me@example.com', 'phone' => '+856 20 1234 5678', 'address' => ['lo' => 'ວຽງຈັນ']])->assertSessionHasNoErrors();
        $this->assertSame('me@example.com', Setting::get('contact.email'));
        $this->get('/')->assertSee(__('site.contact.text'))->assertSee(__('site.footer.madeBy'));   // defaults while empty

        $this->put('/admin/website/contact', [
            'email' => 'me@example.com', 'phone' => '+856 20 1234 5678',
            'intro' => ['lo' => 'ຂຽນຫາຂ້ອຍໄດ້ທຸກເວລາ'], 'footer_text' => ['lo' => 'ນັກພັດທະນາເວັບ ຢູ່ວຽງຈັນ'],
        ])->assertSessionHasNoErrors();
        $this->get('/')->assertSee('ຂຽນຫາຂ້ອຍໄດ້ທຸກເວລາ')->assertSee('ນັກພັດທະນາເວັບ ຢູ່ວຽງຈັນ')->assertSee('+856 20 1234 5678')->assertSee('me@example.com');

        $this->put('/admin/settings', ['tab' => 'seo', 'meta_title' => ['lo' => 'ຊື່', 'en' => 'Title'], 'meta_description' => ['lo' => 'ອະທິບາຍ'], 'keywords' => 'a, b'])->assertSessionHasNoErrors();
        $this->put('/admin/settings', ['tab' => 'appearance', 'default_theme' => 'dark'])->assertSessionHasNoErrors();
        $this->put('/admin/settings', ['tab' => 'language', 'enabled' => ['en'], 'default' => 'lo'])->assertSessionHasErrors('default');
        $this->put('/admin/settings', ['tab' => 'language', 'enabled' => ['lo', 'en'], 'default' => 'en'])->assertSessionHasNoErrors();
        $this->assertSame(['dark', 'en', 'Title'], [Setting::get('appearance.default_theme'), Setting::get('language.default'), Setting::get('seo.meta_title.en')]);
    }

    public function test_admin_language_switch_and_every_page_renders_in_both_languages(): void
    {
        $this->seedSite();
        Project::create(['slug' => 'demo', 'title' => 'Demo', 'category' => 'web', 'summary' => ['lo' => 'x'], 'status' => 'published']);

        foreach (['lo' => 'ໂຄງການ', 'en' => 'Projects'] as $locale => $word) {
            $this->post("/admin/locale/{$locale}");
            $this->assertSame($locale, auth()->user()->fresh()->locale);
            foreach (['/admin', '/admin/projects', '/admin/projects/create', '/admin/projects/demo/edit', '/admin/services', '/admin/skills', '/admin/experience', '/admin/education', '/admin/testimonials',
                '/admin/messages', '/admin/media', '/admin/website', '/admin/website/hero', '/admin/website/about', '/admin/website/contact',
                '/admin/settings', '/admin/settings?tab=language', '/admin/profile', '/admin/users', '/admin/activity'] as $url) {
                $this->get($url)->assertOk()->assertSee($word);
            }
        }
        $this->getJson('/admin/analytics?range=30')->assertOk()->assertJsonCount(30, 'series.labels')->assertJsonStructure(['totals' => ['current' => ['visitors', 'page_views'], 'growth']]);
        $this->getJson('/admin/analytics?range=365')->assertOk()->assertJsonCount(12, 'series.labels');
    }
}
