<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('site.home', [
            'page' => 'home',
            'projects' => $this->publishedProjects()->where('featured', true)->limit(6)->get(),
            'projectTotal' => Project::published()->count(),
            'categoryCounts' => $this->categoryCounts(),
            'skills' => Skill::published()->orderBy('sort_order')->get(),
            'services' => Service::published()->orderBy('sort_order')->get(),
            'experiences' => Experience::published()->orderBy('sort_order')->get(),
            'education' => Education::published()->orderBy('sort_order')->get(),
            'certificates' => Certificate::published()->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::published()->orderBy('sort_order')->get(),
        ]);
    }

    public function about(): View
    {
        return view('site.about', [
            'page' => 'about',
            'skills' => Skill::published()->orderBy('sort_order')->get(),
            'experiences' => Experience::published()->orderBy('sort_order')->get(),
            'education' => Education::published()->orderBy('sort_order')->get(),
            'certificates' => Certificate::published()->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::published()->orderBy('sort_order')->get(),
        ]);
    }

    public function projects(): View
    {
        return view('site.projects', [
            'page' => 'projects',
            'projects' => $this->publishedProjects()->get(),
            'projectTotal' => Project::published()->count(),
            'categoryCounts' => $this->categoryCounts(),
        ]);
    }

    /** Full page, or just the modal body when requested by the gallery (?partial=1). */
    public function project(Request $request, Project $project): View
    {
        abort_unless($project->status === 'published', 404);
        $project->load('images');
        if (! $request->user()) {
            $project->increment('views');
        }

        $siblings = $this->publishedProjects()->get(['id', 'slug', 'title']);
        $index = $siblings->search(fn ($p) => $p->id === $project->id);
        $data = [
            'page' => 'projects',
            'project' => $project,
            'number' => $index + 1,
            'prev' => $siblings[($index - 1 + $siblings->count()) % $siblings->count()],
            'next' => $siblings[($index + 1) % $siblings->count()],
        ];

        return view($request->boolean('partial') ? 'site.partials.project-detail' : 'site.project', $data);
    }

    public function contact(): View
    {
        return view('site.contact', ['page' => 'contact']);
    }

    public function language(Request $request, string $locale): RedirectResponse
    {
        if (in_array($locale, Setting::get('language.enabled', array_keys(locales())), true)) {
            $request->session()->put('locale', $locale);
            $request->user('member')?->update(['locale' => $locale]);
        }
        $back = url()->previous();

        return redirect(str_starts_with($back, url('/')) && ! str_contains($back, '/lang/') ? $back : route('home'));
    }

    public function sitemap(): Response
    {
        $urls = collect([route('home'), route('about'), route('projects.index'), route('contact')])
            ->merge($this->publishedProjects()->get()->map(fn ($p) => route('projects.show', $p)));

        return response()->view('site.sitemap', ['urls' => $urls, 'updated' => Project::max('updated_at')])
            ->header('Content-Type', 'application/xml');
    }

    private function publishedProjects()
    {
        return Project::published()->with('cover')->ordered();
    }

    private function categoryCounts()
    {
        return Project::published()->selectRaw('category, COUNT(*) as total')->groupBy('category')->pluck('total', 'category');
    }
}
