<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Support\ButtonColors;
use App\Support\InputNormalizer;
use App\Support\MailSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public const TABS = ['general', 'seo', 'social', 'appearance', 'language', 'mail'];

    public function edit(Request $request): View
    {
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'general';

        return view('admin.settings.edit', [
            'tab' => $tab,
            'settings' => Setting::allCached(),
            'socials' => SocialLink::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tab = $request->validate(['tab' => ['required', Rule::in(self::TABS)]])['tab'];
        $media = ['nullable', 'string', 'max:255'];

        match ($tab) {
            'general' => $this->save($request, 'general', [
                'site_name' => ['required', 'string', 'max:60'],
                'tagline.*' => ['nullable', 'string', 'max:120'],
                'description.*' => ['nullable', 'string', 'max:300'],
                'logo' => $media,
                'favicon' => $media,
            ], fn () => [
                'site_name' => $request->input('site_name'),
                'tagline' => InputNormalizer::bilingual($request->input('tagline')),
                'description' => InputNormalizer::bilingual($request->input('description')),
                'logo' => $request->input('logo'),
                'favicon' => $request->input('favicon'),
            ]),
            'seo' => $this->save($request, 'seo', [
                'meta_title.lo' => ['required', 'string', 'max:70'],
                'meta_title.en' => ['nullable', 'string', 'max:70'],
                'meta_description.lo' => ['required', 'string', 'max:170'],
                'meta_description.en' => ['nullable', 'string', 'max:170'],
                'keywords' => ['nullable', 'string', 'max:500'],
                'og_image' => $media,
            ], fn () => [
                'meta_title' => InputNormalizer::bilingual($request->input('meta_title')),
                'meta_description' => InputNormalizer::bilingual($request->input('meta_description')),
                'keywords' => implode(', ', InputNormalizer::tags($request->input('keywords'))),
                'og_image' => $request->input('og_image'),
            ]),
            'social' => $this->saveSocial($request),
            'appearance' => $this->save($request, 'appearance', [
                'default_theme' => ['required', Rule::in(['light', 'dark', 'system'])],
                'colors' => ['nullable', 'array'],
                'colors.*' => ['nullable', 'regex:'.ButtonColors::PATTERN],
                'reset_colors' => ['boolean'],
            ], fn () => [
                'default_theme' => $request->input('default_theme'),
                // Only valid colours for known keys are kept; "Reset" goes back to the defaults
                'colors' => $request->boolean('reset_colors') ? [] : collect(ButtonColors::COLORS)
                    ->mapWithKeys(fn ($item) => [$item[0] => strtolower((string) $request->input("colors.{$item[0]}", $item[1]))])
                    ->all(),
            ]),
            'mail' => $this->saveMail($request),
            'language' => $this->save($request, 'language', [
                'enabled' => ['required', 'array', 'min:1'],
                'enabled.*' => [Rule::in(array_keys(locales()))],
                'default' => ['required', Rule::in(array_keys(locales())), Rule::in($request->input('enabled', []))],
            ], fn () => ['enabled' => array_values($request->input('enabled')), 'default' => $request->input('default')]),
        };

        ActivityLog::record('updated', null, ['tab' => $tab], target: __('Settings').' · '.__(ucfirst($tab)));

        return redirect()->route('admin.settings.edit', ['tab' => $tab])->with('toast', ['success', __('Settings saved')]);
    }

    /** Outgoing email (SMTP). An empty password field keeps the saved one. */
    private function saveMail(Request $request): void
    {
        $smtp = $request->input('mode') === 'smtp';
        $data = $request->validate([
            'mode' => ['required', Rule::in(['log', 'smtp'])],
            'host' => [$smtp ? 'required' : 'nullable', 'string', 'max:190', 'regex:/^[A-Za-z0-9.\-]+$/'],
            'port' => [$smtp ? 'required' : 'nullable', 'integer', 'between:1,65535'],
            'encryption' => ['required', Rule::in(MailSettings::ENCRYPTIONS)],
            'username' => ['nullable', 'string', 'max:190'],
            'password' => ['nullable', 'string', 'max:500'],
            'from_address' => [$smtp ? 'required' : 'nullable', 'email', 'max:190'],
            'from_name' => ['nullable', 'string', 'max:100'],
        ]);

        $current = Setting::get('mail', []);
        Setting::put('mail', [
            ...collect($data)->except('password')->all(),
            'password' => filled($data['password'] ?? null) ? MailSettings::encrypt($data['password']) : ($current['password'] ?? null),
        ]);
    }

    /** Sends a test email to the signed-in admin with the saved settings and reports the result. */
    public function testMail(Request $request): RedirectResponse
    {
        $to = $request->user()->email;
        try {
            Mail::raw(__('This is a test email from :site. If you can read this, sending works.', ['site' => Setting::get('general.site_name', config('app.name'))]),
                fn ($message) => $message->to($to)->subject(__('Test email')));
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('admin.settings.edit', ['tab' => 'mail'])->with('toast', ['error', __('Sending failed: :error', ['error' => Str::limit($e->getMessage(), 200)])]);
        }

        $note = MailSettings::isTestMode() ? ' '.__('(Test mode: it was written to the log, not delivered.)') : '';

        return redirect()->route('admin.settings.edit', ['tab' => 'mail'])->with('toast', ['success', __('Test email sent to :email', ['email' => $to]).$note]);
    }

    private function save(Request $request, string $key, array $rules, \Closure $value): void
    {
        $request->validate($rules);
        Setting::put($key, $value());
    }

    private function saveSocial(Request $request): void
    {
        $request->validate([
            'socials' => ['required', 'array'],
            'socials.*.url' => ['nullable', 'url:http,https', 'max:255'],
        ]);
        foreach ($request->input('socials') as $platform => $values) {
            if (array_key_exists($platform, SocialLink::PLATFORMS)) {
                SocialLink::updateOrCreate(['platform' => $platform], [
                    'url' => $values['url'] ?? null,
                    'enabled' => filter_var($values['enabled'] ?? false, FILTER_VALIDATE_BOOL),
                ]);
            }
        }
    }
}
