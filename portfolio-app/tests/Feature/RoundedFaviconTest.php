<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\RoundedFavicon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoundedFaviconTest extends TestCase
{
    use RefreshDatabase;

    private function uploadFavicon(string $path): void
    {
        $img = imagecreatetruecolor(300, 200);   // not square: gets centre-cropped
        imagefill($img, 0, 0, imagecolorallocate($img, 200, 30, 60));
        ob_start();
        imagepng($img);
        Storage::disk('public')->put($path, ob_get_clean());
        Setting::put('general', ['site_name' => 'SOPHAR', 'favicon' => $path]);
    }

    public function test_uploaded_favicon_is_served_with_transparent_rounded_corners(): void
    {
        Storage::fake('public');
        $this->uploadFavicon('site/icon.png');

        $this->get('/')->assertSee(RoundedFavicon::url(), false);

        $response = $this->get('/favicon-rounded.png')->assertOk()->assertHeader('Content-Type', 'image/png');
        $icon = imagecreatefromstring($response->getContent());
        $this->assertSame([128, 128], [imagesx($icon), imagesy($icon)]);
        $alpha = fn (int $x, int $y) => (imagecolorat($icon, $x, $y) >> 24) & 0x7F;
        $this->assertSame(127, $alpha(0, 0));      // corner: fully transparent
        $this->assertSame(127, $alpha(127, 127));
        $this->assertSame(0, $alpha(64, 64));      // centre: solid
        $this->assertSame(0, $alpha(64, 0));       // middle of the top edge: solid
    }

    public function test_a_new_upload_gets_a_new_url(): void
    {
        Storage::fake('public');
        $this->uploadFavicon('site/one.png');
        $first = RoundedFavicon::url();
        $this->uploadFavicon('site/two.png');

        $this->assertNotSame($first, RoundedFavicon::url());
    }

    public function test_without_a_custom_favicon_the_default_icon_is_used(): void
    {
        $this->seedSite();
        Setting::put('general', ['site_name' => 'SOPHAR']);

        $this->get('/')->assertSee(asset('favicon.svg'), false)->assertDontSee('favicon-rounded', false);
        $this->get('/favicon-rounded.png')->assertNotFound();
    }

    public function test_uploaded_logo_is_shown_in_the_website_header(): void
    {
        $this->seedSite();
        Setting::put('general', ['site_name' => 'SOPHAR', 'logo' => 'site/logo.webp']);
        $this->get('/')->assertSee('class="brand__logo" src="'.media_url('site/logo.webp').'"', false);

        Setting::put('general', ['site_name' => 'SOPHAR']);
        $this->get('/')->assertDontSee('class="brand__logo"', false)->assertSee('class="brand__sign"', false);
    }
}
