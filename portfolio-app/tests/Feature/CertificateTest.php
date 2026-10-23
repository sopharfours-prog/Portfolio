<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_manages_certificates_and_the_gallery_shows_published_ones(): void
    {
        $this->seedSite();
        $this->actingAs($this->admin(User::ROLE_EDITOR));

        $this->get('/admin/certificates')->assertOk();
        $this->post('/admin/certificates', [
            'title' => ['lo' => 'ໃບຢັ້ງຢືນ Laravel', 'en' => 'Laravel certificate'], 'issuer' => 'Laracasts',
            'issued_on' => '2024-05-01', 'image' => 'certificates/laravel.webp', 'status' => 'published',
        ])->assertRedirect(route('admin.certificates.index'));
        Certificate::factory()->draft()->create(['title' => ['lo' => 'Hidden draft']]);

        $certificate = Certificate::published()->firstOrFail();
        $this->assertSame('Laracasts', $certificate->issuer);
        $this->assertDatabaseHas('activity_logs', ['action' => 'created', 'subject_type' => 'Certificate']);

        $this->get('/')->assertSee('id="certificates"', false)->assertSee('ໃບຢັ້ງຢືນ Laravel')
            ->assertSee($certificate->imageUrl(), false)->assertDontSee('Hidden draft');

        $this->post('/admin/certificates', ['title' => ['lo' => 'x'], 'status' => 'published'])->assertSessionHasErrors('image');

        $this->delete("/admin/certificates/{$certificate->id}");
        Certificate::query()->delete();
        $this->get('/')->assertDontSee('id="certificates"', false);
    }

    public function test_experience_logo_is_saved_and_shown(): void
    {
        $this->seedSite();
        $this->actingAs($this->admin());

        $this->post('/admin/experience', [
            'company' => 'Acme', 'position' => ['lo' => 'ນັກພັດທະນາ'], 'start_date' => '2020-01-01', 'is_current' => 1,
            'image' => 'site/acme-logo.png', 'status' => 'published',
        ])->assertSessionHasNoErrors();

        $this->assertSame('site/acme-logo.png', Experience::first()->image);
        $this->get('/')->assertSee(media_url('site/acme-logo.png'), false)->assertSee('ນັກພັດທະນາ <span class="entry-card__sep">-</span> Acme', false);
    }
}
