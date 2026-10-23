<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_describes_an_installable_app(): void
    {
        $response = $this->get('/manifest.webmanifest')->assertOk()->assertHeader('Content-Type', 'application/manifest+json');

        $response->assertJsonPath('display', 'standalone')->assertJsonPath('start_url', '/?source=pwa');
        $sizes = collect($response->json('icons'))->pluck('sizes');
        $this->assertTrue($sizes->contains('192x192') && $sizes->contains('512x512'));
        $this->assertContains('maskable', collect($response->json('icons'))->pluck('purpose')->all());
        foreach ($response->json('icons') as $icon) {
            $this->assertFileExists(public_path(parse_url($icon['src'], PHP_URL_PATH)));
        }
    }

    public function test_pages_link_the_manifest_and_offline_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('rel="manifest"', false);
        $this->get('/offline')->assertOk()->assertSee(__('site.offline.title'));
        $this->assertFileExists(public_path('sw.js'));
    }
}
