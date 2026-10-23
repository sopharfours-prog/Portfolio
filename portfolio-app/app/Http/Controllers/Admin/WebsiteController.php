<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Support\InputNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/** Hero, About and Contact content of the public website (stored in settings). */
class WebsiteController extends Controller
{
    public function index(): View
    {
        return view('admin.website.index');
    }

    public function hero(): View
    {
        return view('admin.website.hero', ['hero' => Setting::get('hero', [])]);
    }

    public function updateHero(Request $request): RedirectResponse
    {
        $bilingual = ['label', 'greeting', 'title', 'subtitle', 'description', 'badge', 'location', 'primary_label', 'secondary_label'];
        $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'image' => ['nullable', 'string', 'max:255'],
            'primary_url' => ['nullable', 'string', 'max:255', 'regex:/^(#|\/|https?:\/\/)/'],
            'secondary_url' => ['nullable', 'string', 'max:255', 'regex:/^(#|\/|https?:\/\/)/'],
            ...collect($bilingual)->flatMap(fn ($f) => [
                "{$f}.lo" => [in_array($f, ['greeting', 'title', 'description'], true) ? 'required' : 'nullable', 'string', 'max:500'],
                "{$f}.en" => ['nullable', 'string', 'max:500'],
            ])->all(),
        ]);

        Setting::put('hero', [
            ...collect($bilingual)->mapWithKeys(fn ($f) => [$f => InputNormalizer::bilingual($request->input($f))])->all(),
            ...$request->only(['name', 'image', 'primary_url', 'secondary_url']),
        ]);

        return $this->saved(__('Hero section'));
    }

    public function about(): View
    {
        return view('admin.website.about', ['about' => Setting::get('about', []), 'socials' => SocialLink::orderBy('sort_order')->get()]);
    }

    public function updateAbout(Request $request): RedirectResponse
    {
        $bilingual = [
            'page_title', 'page_title_accent', 'subtitle', 'cta_label',
            'headline', 'introduction', 'biography', 'focus_title', 'focus', 'philosophy_title', 'philosophy', 'skills_summary',
        ];
        $request->validate([
            'image' => ['nullable', 'string', 'max:255'],
            'education_image' => ['nullable', 'string', 'max:255'],
            'page_title.lo' => ['required', 'string', 'max:60'],
            'cta_url' => ['nullable', 'string', 'max:255', 'regex:/^(#|\/|https?:\/\/|mailto:)/'],
            'headline.lo' => ['required', 'string', 'max:200'],
            'introduction.lo' => ['required', 'string', 'max:2000'],
            '*.lo' => ['nullable', 'string', 'max:2000'],
            '*.en' => ['nullable', 'string', 'max:2000'],
            'stats' => ['array', 'max:6'],
            'stats.*.value' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'stats.*.suffix' => ['nullable', 'string', 'max:5'],
            'stats.*.label.lo' => ['nullable', 'string', 'max:60'],
            'stats.*.label.en' => ['nullable', 'string', 'max:60'],
            ...$this->socialRules(),
        ]);

        $stats = collect($request->input('stats', []))
            ->filter(fn ($s) => filled($s['value'] ?? null) && filled($s['label']['lo'] ?? null))
            ->map(fn ($s) => ['value' => (int) $s['value'], 'suffix' => $s['suffix'] ?? '', 'label' => array_filter(Arr::only($s['label'], ['lo', 'en']))])
            ->values()->all();

        Setting::put('about', [
            ...collect($bilingual)->mapWithKeys(fn ($f) => [$f => InputNormalizer::bilingual($request->input($f))])->all(),
            'image' => $request->input('image'),
            'education_image' => $request->input('education_image') ?: null,
            'cta_url' => $request->input('cta_url') ?: '#contact',
            'stats' => $stats,
        ]);
        $this->saveSocials($request);

        return $this->saved(__('About section'));
    }

    public function contact(): View
    {
        return view('admin.website.contact', ['contact' => Setting::get('contact', []), 'socials' => SocialLink::orderBy('sort_order')->get()]);
    }

    public function updateContact(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[\d\s+\-()]+$/'],
            'address.lo' => ['nullable', 'string', 'max:200'],
            'address.en' => ['nullable', 'string', 'max:200'],
            'reply_note.lo' => ['nullable', 'string', 'max:200'],
            'reply_note.en' => ['nullable', 'string', 'max:200'],
            'intro_title.lo' => ['nullable', 'string', 'max:120'],
            'intro_title.en' => ['nullable', 'string', 'max:120'],
            'intro.lo' => ['nullable', 'string', 'max:300'],
            'intro.en' => ['nullable', 'string', 'max:300'],
            'why_title.lo' => ['nullable', 'string', 'max:120'],
            'why_title.en' => ['nullable', 'string', 'max:120'],
            'why_text.lo' => ['nullable', 'string', 'max:400'],
            'why_text.en' => ['nullable', 'string', 'max:400'],
            'footer_text.lo' => ['nullable', 'string', 'max:300'],
            'footer_text.en' => ['nullable', 'string', 'max:300'],
            ...$this->socialRules(),
        ]);

        Setting::put('contact', [
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => InputNormalizer::bilingual($request->input('address')),
            'reply_note' => InputNormalizer::bilingual($request->input('reply_note')),
            'intro_title' => InputNormalizer::bilingual($request->input('intro_title')),
            'intro' => InputNormalizer::bilingual($request->input('intro')),
            'why_title' => InputNormalizer::bilingual($request->input('why_title')),
            'why_text' => InputNormalizer::bilingual($request->input('why_text')),
            'footer_text' => InputNormalizer::bilingual($request->input('footer_text')),
        ]);
        $this->saveSocials($request);

        return $this->saved(__('Contact information'));
    }

    private function socialRules(): array
    {
        return [
            'socials' => ['nullable', 'array'],
            'socials.*.url' => ['nullable', 'url:http,https', 'max:255'],
            'socials.*.enabled' => ['nullable', 'boolean'],
        ];
    }

    private function saveSocials(Request $request): void
    {
        foreach ((array) $request->input('socials', []) as $platform => $values) {
            if (! array_key_exists($platform, SocialLink::PLATFORMS)) {
                continue;
            }
            SocialLink::updateOrCreate(['platform' => $platform], [
                'url' => $values['url'] ?? null,
                'enabled' => filter_var($values['enabled'] ?? false, FILTER_VALIDATE_BOOL),
            ]);
        }
    }

    private function saved(string $section): RedirectResponse
    {
        ActivityLog::record('updated', null, [], target: $section);

        return back()->with('toast', ['success', __(':item updated successfully', ['item' => $section])]);
    }
}
