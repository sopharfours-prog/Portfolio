<?php

namespace App\Providers;

use App\Models\AdminNotification;
use App\Models\Message;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\User;
use App\Support\MailSettings;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use SocialiteProviders\Apple\Provider as AppleProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Outgoing email account from Admin → Settings → Email (overrides .env when set)
        MailSettings::apply();

        // "Sign in with Apple" is a community Socialite provider; Google is built in
        Event::listen(fn (SocialiteWasCalled $event) => $event->extendSocialite('apple', AppleProvider::class));

        // Roles: super admins can do everything; editors manage content, messages and media only
        Gate::define('super-admin', fn (User $user) => $user->isSuperAdmin());

        // Reset links point at the admin reset page
        ResetPassword::createUrlUsing(fn (User $user, string $token) => route('admin.password.reset', ['token' => $token, 'email' => $user->email]));

        Password::defaults(fn () => Password::min(10)->letters()->mixedCase()->numbers());

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(20)->by($request->ip()),
        ]);
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinutes(10, 5)->by($request->ip()));
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));

        Paginator::defaultView('admin.partials.pagination');

        // Shared public-site data for the layout and every site view. Kept on the request
        // object so it is loaded once per request and never leaks into the next one.
        View::composer(['layouts.site', 'site.*'], function ($view) {
            $attributes = request()->attributes;
            if (! $attributes->has('site.shared')) {
                $attributes->set('site.shared', [
                    'settings' => Setting::allCached(),
                    'socials' => SocialLink::active()->get(),
                ]);
            }
            $view->with($attributes->get('site.shared'));
        });

        View::composer('admin.layouts.app', function ($view) {
            $user = auth()->user();
            $view->with([
                'unreadMessages' => Message::view('unread')->count(),
                'notifications' => $user?->adminNotifications()->limit(8)->get() ?? collect(),
                'unreadNotifications' => $user ? AdminNotification::where('user_id', $user->id)->unread()->count() : 0,
            ]);
        });
    }
}
