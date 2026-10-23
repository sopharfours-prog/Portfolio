<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\CvFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CvTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Setting::put('general', ['site_name' => 'SOPHAR']);
    }

    private function pdf(string $name = 'my-cv.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF");
    }

    public function test_super_admin_uploads_downloads_and_removes_a_cv(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/cv')->assertOk();
        $this->post('/admin/cv', ['file_en' => $this->pdf(), 'show_on_site' => 1])->assertSessionHasNoErrors();

        $file = CvFile::get('en');
        $this->assertNotNull($file);
        Storage::disk('local')->assertExists($file['path']);
        $this->assertSame('my-cv.pdf', $file['name']);

        $this->get('/admin/cv/en/download')->assertOk()->assertHeader('Content-Type', 'application/pdf');

        // Replacing deletes the old file
        $this->post('/admin/cv', ['file_en' => $this->pdf('new.pdf'), 'show_on_site' => 1]);
        Storage::disk('local')->assertMissing($file['path']);

        $this->delete('/admin/cv/en')->assertRedirect();
        $this->assertNull(CvFile::get('en'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'deleted']);
    }

    public function test_only_pdf_files_are_accepted(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/cv', ['file_en' => UploadedFile::fake()->create('cv.docx', 10, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')])
            ->assertSessionHasErrors('file_en');
        $this->assertNull(CvFile::get('en'));
    }

    public function test_files_over_the_size_limit_get_a_clear_message(): void
    {
        $this->actingAs($this->admin());

        $tooBig = UploadedFile::fake()->create('huge.pdf', intdiv(CvFile::maxBytes(), 1024) + 100, 'application/pdf');
        $this->post('/admin/cv', ['file_lo' => $tooBig])->assertSessionHasErrors(['file_lo' => __('This file is too large. The maximum is :size MB.', ['size' => round(CvFile::maxBytes() / 1024 / 1024, 1)])]);
        $this->assertNull(CvFile::get('lo'));
    }

    public function test_website_button_and_public_download_follow_the_visibility_switch(): void
    {
        $this->actingAs($this->admin())->post('/admin/cv', ['file_en' => $this->pdf(), 'show_on_site' => 1]);
        auth()->logout();

        // Only an English CV exists: Lao visitors get it too
        $this->get('/')->assertSee(url('/cv/'), false);
        $this->get('/cv/lo')->assertOk()->assertDownload('sophar-CV-LO.pdf');

        CvFile::setPublic(false);
        $this->get('/')->assertDontSee(url('/cv/'), false);
        $this->get('/cv/en')->assertNotFound();
    }

    public function test_editors_and_guests_cannot_manage_the_cv(): void
    {
        $this->get('/admin/cv')->assertRedirect(route('admin.login'));

        $this->actingAs($this->admin(User::ROLE_EDITOR));
        $this->get('/admin/cv')->assertForbidden();
        $this->post('/admin/cv', ['file_en' => $this->pdf()])->assertForbidden();
    }
}
