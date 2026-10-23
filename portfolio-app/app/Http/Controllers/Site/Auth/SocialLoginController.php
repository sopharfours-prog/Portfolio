<?php

namespace App\Http\Controllers\Site\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

/**
 * "Continue with Google / Apple" for visitor accounts (guard "member"). Administrators keep
 * email + password, so a social account can never reach the admin.
 *
 * The account is found by its provider ID, otherwise linked to a visitor account with the same
 * (provider-verified) email, otherwise created. Apple posts its callback cross-site, so it runs
 * stateless with an encrypted single-purpose nonce cookie instead of the session.
 */
class SocialLoginController extends Controller
{
    public const PROVIDERS = ['google', 'apple'];

    /** Providers whose credentials are set in .env (only these get a button). */
    public static function enabled(): array
    {
        return array_values(array_filter(self::PROVIDERS, fn (string $p) => filled(config("services.{$p}.client_id"))));
    }

    public function redirect(string $provider): SymfonyRedirect|RedirectResponse
    {
        return $this->driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $driver = $this->driver($provider);

        try {
            $social = $driver->user();
        } catch (Throwable $e) {
            // Cancelled on the provider's page, expired attempt, or a forged callback
            report($e);

            return redirect()->route('member.login')->withErrors(['email' => __('site.auth.socialFailed')]);
        }

        if (blank($social->getId()) || blank($social->getEmail())) {
            return redirect()->route('member.login')->withErrors(['email' => __('site.auth.socialNoEmail')]);
        }

        $member = $this->findOrCreateMember($provider, $social);

        Auth::guard('member')->login($member, remember: true);
        $request->session()->regenerate();
        $member->forceFill(['last_login_at' => now()])->save();
        if ($member->locale) {
            $request->session()->put('locale', $member->locale);
        }

        return redirect()->intended(route('home'))
            ->with('member_status', __('site.auth.welcomeBack', ['name' => $member->name]));
    }

    private function findOrCreateMember(string $provider, SocialUser $social): Member
    {
        $column = "{$provider}_id";
        $email = Str::lower($social->getEmail());

        $member = Member::where($column, $social->getId())->first()
            ?? Member::where('email', $email)->first();

        if ($member) {
            // Link the provider to an existing account (Google and Apple only return verified emails)
            if ($member->{$column} !== $social->getId()) {
                $member->forceFill([$column => $social->getId()])->save();
            }

            return $member;
        }

        return Member::create([
            'name' => Str::limit(trim((string) $social->getName()) ?: Str::before($email, '@'), 100, ''),
            'email' => $email,
            $column => $social->getId(),
            'locale' => app()->getLocale(),
        ]);
    }

    private function driver(string $provider): Provider
    {
        abort_unless(in_array($provider, self::enabled(), true), 404);

        $driver = Socialite::driver($provider);
        if ($provider === 'apple') {
            $driver = $driver->stateless()->cookieNonce()->scopes(['name', 'email']);
        }

        return $driver;
    }
}
