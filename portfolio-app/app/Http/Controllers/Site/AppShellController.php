<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Progressive Web App pieces: the web app manifest (name, colours and icons come from
 * the admin settings) and the offline page the service worker (public/sw.js) shows.
 */
class AppShellController extends Controller
{
    public function manifest(): JsonResponse
    {
        $settings = Setting::allCached();
        $name = $settings['general']['site_name'] ?? config('app.name');
        $icon = fn (string $file, int $size, string $purpose) => [
            'src' => asset("icons/{$file}"), 'sizes' => "{$size}x{$size}", 'type' => 'image/png', 'purpose' => $purpose,
        ];

        return response()->json([
            'id' => '/',
            'name' => $name.' — '.(tr($settings['general']['tagline'] ?? null) ?? __('site.footer.tagline')),
            'short_name' => $name,
            'description' => tr($settings['seo']['meta_description'] ?? null) ?? __('site.meta.home.description'),
            'lang' => app()->getLocale(),
            'start_url' => '/?source=pwa',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#fbfcfe',
            'theme_color' => '#ffffff',
            'categories' => ['portfolio', 'design', 'business'],
            'icons' => [
                $icon('icon-192.png', 192, 'any'),
                $icon('icon-512.png', 512, 'any'),
                $icon('icon-maskable-192.png', 192, 'maskable'),
                $icon('icon-maskable-512.png', 512, 'maskable'),
            ],
            'shortcuts' => [
                ['name' => __('site.nav.projects'), 'url' => route('projects.index'), 'icons' => [$icon('icon-192.png', 192, 'any')]],
                ['name' => __('site.nav.contact'), 'url' => route('contact'), 'icons' => [$icon('icon-192.png', 192, 'any')]],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function offline(): View
    {
        return view('site.offline');
    }
}
