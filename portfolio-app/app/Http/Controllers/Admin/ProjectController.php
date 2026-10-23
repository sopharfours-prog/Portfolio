<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\AdminNotification;
use App\Models\Media;
use App\Models\Project;
use App\Support\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public const SORTS = [
        'newest' => ['created_at', 'desc'],
        'oldest' => ['created_at', 'asc'],
        'title' => ['title', 'asc'],
        'views' => ['views', 'desc'],
        'order' => ['sort_order', 'asc'],
    ];

    public function index(Request $request): View
    {
        $listing = Listing::for(Project::query()->with('cover'), $request)
            ->search(['title', 'client', 'slug', 'summary', 'technologies'])
            ->filter('status', Project::STATUSES)
            ->filter('category', Project::CATEGORIES)
            ->sorts(self::SORTS, 'newest');

        return view('admin.projects.index', [
            'projects' => $listing->paginate(10),
            'sort' => $listing->currentSort(),
            'counts' => Project::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.form', ['project' => new Project(['status' => 'draft', 'category' => 'web']), 'gallery' => collect()]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = DB::transaction(function () use ($request) {
            $project = Project::create([...$request->payload(), 'sort_order' => (int) Project::max('sort_order') + 1]);
            $this->syncGallery($project, $request);
            $this->handlePublishing($project, null);

            return $project;
        });

        return redirect()->route('admin.projects.edit', $project)->with('toast', ['success', __('Project created successfully')]);
    }

    public function show(Project $project): RedirectResponse
    {
        return redirect()->route('projects.show', $project);
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', ['project' => $project, 'gallery' => $this->galleryFor($project)]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $previousStatus = $project->status;
        DB::transaction(function () use ($request, $project, $previousStatus) {
            $project->update($request->payload());
            $this->syncGallery($project, $request);
            $this->handlePublishing($project, $previousStatus);
        });

        return redirect()->route('admin.projects.edit', $project)->with('toast', ['success', __('Project updated successfully')]);
    }

    public function duplicate(Project $project): RedirectResponse
    {
        $copy = DB::transaction(function () use ($project) {
            $copy = $project->replicate(['views', 'published_at']);
            $copy->title = $project->title.' ('.__('Copy').')';
            $copy->slug = $this->uniqueSlug($project->slug.'-copy');
            $copy->status = 'draft';
            $copy->featured = false;
            $copy->views = 0;
            $copy->save();
            foreach ($project->images as $image) {
                $copy->images()->create($image->only(['path', 'width', 'height', 'is_cover', 'sort_order']));
            }

            return $copy;
        });

        return redirect()->route('admin.projects.edit', $copy)->with('toast', ['success', __('Project duplicated as a draft')]);
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('toast', ['success', __('Project deleted')]);
    }

    /**
     * The gallery widget posts `gallery[]` (media ids, in display order) and `cover` (media id).
     * Images are replaced wholesale; files stay in the Media Library.
     */
    private function syncGallery(Project $project, Request $request): void
    {
        if (! $request->has('gallery_present')) {
            return; // API clients that don't send a gallery leave images untouched
        }
        $ids = collect($request->input('gallery', []))->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $media = Media::whereIn('id', $ids)->get()->keyBy('id');
        $cover = (int) $request->input('cover', $ids->first());

        $project->images()->delete();
        $ids->filter(fn ($id) => $media->has($id))->values()->each(function ($id, $order) use ($project, $media, $cover) {
            $item = $media[$id];
            $project->images()->create([
                'path' => $item->path, 'width' => $item->width, 'height' => $item->height,
                'is_cover' => $id === $cover, 'sort_order' => $order,
            ]);
        });

        // Guarantee exactly one cover when there are images
        if ($project->images()->exists() && ! $project->images()->where('is_cover', true)->exists()) {
            $project->images()->orderBy('sort_order')->first()->update(['is_cover' => true]);
        }
        $project->unsetRelation('images')->unsetRelation('cover');
    }

    private function handlePublishing(Project $project, ?string $previousStatus): void
    {
        if ($project->status === 'published' && $previousStatus !== 'published') {
            $project->forceFill(['published_at' => $project->published_at ?? now()])->saveQuietly();
            AdminNotification::notifyAll('project', __('Project published'), $project->title, route('admin.projects.edit', $project));
        }
    }

    /** Current images mapped to Media Library items for the gallery widget. */
    private function galleryFor(Project $project)
    {
        $media = Media::whereIn('path', $project->images->pluck('path'))->get()->keyBy('path');

        return $project->images->map(fn ($image) => [
            'id' => $media[$image->path]->id ?? null,
            'url' => $image->url(),
            'name' => $media[$image->path]->name ?? basename($image->path),
            'cover' => $image->is_cover,
        ])->filter(fn ($item) => $item['id'])->values();
    }

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base);
        $i = 2;
        while (Project::where('slug', $slug)->exists()) {
            $slug = Str::slug($base).'-'.$i++;
        }

        return $slug;
    }
}
