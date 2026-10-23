<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\ImageUploader;
use App\Support\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    private const TYPES = ['image/webp', 'image/jpeg', 'image/png', 'image/svg+xml'];

    public function index(Request $request): View|JsonResponse
    {
        $listing = Listing::for(Media::query()->with('uploader'), $request)
            ->search(['name', 'original_name', 'alt', 'path'])
            ->filter('type', self::TYPES, 'mime_type')
            ->sorts([
                'newest' => ['created_at', 'desc'], 'oldest' => ['created_at', 'asc'],
                'name' => ['name', 'asc'], 'largest' => ['size', 'desc'],
            ], 'newest');
        $items = $listing->paginate($request->expectsJson() ? 24 : 30);

        // JSON is used by the "choose from library" picker in forms
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $items->getCollection()->map(fn (Media $m) => $this->toArray($m)),
                'next' => $items->nextPageUrl(),
            ]);
        }

        return view('admin.media.index', ['items' => $items, 'sort' => $listing->currentSort(), 'types' => self::TYPES]);
    }

    /** Upload one or more files (drag & drop). Always JSON. */
    public function store(Request $request, ImageUploader $uploader): JsonResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ImageUploader::rules(),
        ], [
            'files.*.max' => __('Each file must be smaller than :size MB.', ['size' => ImageUploader::MAX_KB / 1024]),
            'files.*.extensions' => __('Allowed file types: :types.', ['types' => implode(', ', ImageUploader::EXTENSIONS)]),
            'files.*.mimetypes' => __('Allowed file types: :types.', ['types' => implode(', ', ImageUploader::EXTENSIONS)]),
        ]);

        $folder = in_array($request->input('folder'), ['projects', 'site', 'uploads', 'avatars'], true) ? $request->input('folder') : 'uploads';
        $media = collect($request->file('files'))->map(fn ($file) => $uploader->store($file, $folder));

        return response()->json([
            'message' => trans_choice(':count file uploaded|:count files uploaded', $media->count()),
            'data' => $media->map(fn (Media $m) => $this->toArray($m)),
        ], 201);
    }

    public function update(Request $request, Media $medium): RedirectResponse|JsonResponse
    {
        $medium->update($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'alt' => ['nullable', 'string', 'max:255'],
        ]));

        return $request->expectsJson()
            ? response()->json(['message' => __('Media updated'), 'data' => $this->toArray($medium)])
            : back()->with('toast', ['success', __('Media updated')]);
    }

    public function destroy(Request $request, Media $medium): RedirectResponse|JsonResponse
    {
        $medium->delete();

        return $request->expectsJson()
            ? response()->json(['message' => __('File deleted')])
            : back()->with('toast', ['success', __('File deleted')]);
    }

    private function toArray(Media $m): array
    {
        return [
            'id' => $m->id,
            'name' => $m->name,
            'original_name' => $m->original_name,
            'url' => $m->url(),
            'path' => $m->path,
            'mime' => $m->mime_type,
            'size' => $m->humanSize(),
            'dimensions' => $m->dimensions(),
            'alt' => $m->alt,
            'uploaded' => $m->created_at?->translatedFormat('j M Y'),
            'uploader' => $m->uploader?->name,
            'update_url' => route('admin.media.update', $m),
        ];
    }
}
