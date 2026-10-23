<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            self::recordFailedLogin($credentials['email']);

            // Same message whether the email exists or not
            throw ValidationException::withMessages(['email' => __('These credentials do not match our records.')]);
        }

        return self::completeLogin($request);
    }

    /** Session, last-login time, interface language and activity log after a successful admin sign-in (also used by the site's /login). */
    public static function completeLogin(Request $request): RedirectResponse
    {
        $request->session()->regenerate();
        $user = $request->user();
        $user->forceFill(['last_login_at' => now()])->save();
        $request->session()->put('admin_locale', $user->locale);
        ActivityLog::record('login', null, [], 'success', $user->id, $user->name);

        return redirect()->intended(route('admin.dashboard'));
    }

    public static function recordFailedLogin(string $email): void
    {
        $user = User::where('email', $email)->first();
        ActivityLog::record('login_failed', null, [], 'failed', $user?->id, $email);
    }

    public function destroy(Request $request): RedirectResponse
    {
        ActivityLog::record('logout', null, [], 'success', $request->user()?->id, $request->user()?->name);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Signed out from the website header: stay on the website
        if ($request->input('to') === 'site') {
            return redirect()->route('home')->with('member_status', __('site.auth.signedOut'));
        }

        return redirect()->route('admin.login')->with('toast', ['success', __('You have been signed out.')]);
    }
}
