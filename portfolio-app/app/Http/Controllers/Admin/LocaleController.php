<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LocaleController extends Controller
{
    /** Admin interface language (LA | EN). */
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, locales()), 404);
        $request->session()->put('admin_locale', $locale);
        $request->user()?->update(['locale' => $locale]);

        return back();
    }

    /** Icon names for the icon picker (cached by the browser). */
    public function icons(): JsonResponse
    {
        $names = collect(File::files(resource_path('icons')))
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->sort()->values();

        return response()->json($names)->setMaxAge(86400)->setPublic();
    }

    /** A single icon as an image, for the picker grid. */
    public function icon(string $name): BinaryFileResponse
    {
        $file = resource_path('icons/'.preg_replace('/[^a-z0-9-]/', '', $name).'.svg');
        abort_unless(is_file($file), 404);

        return response()->file($file, ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'public, max-age=604800']);
    }
}
