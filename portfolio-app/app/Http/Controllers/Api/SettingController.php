<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /** Keys that are safe to expose publicly. */
    private const PUBLIC = ['general', 'seo', 'hero', 'about', 'contact', 'language'];

    public function index(Request $request): JsonResponse
    {
        $settings = Setting::allCached();
        if (! $request->user('sanctum')) {
            $settings = array_intersect_key($settings, array_flip(self::PUBLIC));
        }

        return response()->json([
            'data' => $settings,
            'social_links' => SocialLink::active()->get(['platform', 'url']),
        ]);
    }

    /** PUT /api/settings/{key}  { "value": { ... } } — Super Admin only. */
    public function update(Request $request, string $key): JsonResponse
    {
        abort_unless(in_array($key, [...self::PUBLIC, 'appearance'], true), 404);
        $data = $request->validate(['value' => ['required', 'array']]);
        Setting::put($key, $data['value']);

        return response()->json(['data' => Setting::get($key)]);
    }
}
