<?php

namespace App\Http\Controllers\Site\Auth;

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** The site's sign-in page: visitor accounts (guard "member") and administrators (guard "web"). */
class MemberLoginController extends Controller
{
    public function create(): View
    {
        return view('site.auth.login', ['page' => 'login']);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        if (! Auth::guard('member')->attempt($credentials, $request->boolean('remember'))) {
            // One login page for everyone: administrators are sent on to the dashboard
            if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
                return AdminLoginController::completeLogin($request);
            }
            if (User::where('email', $credentials['email'])->exists()) {
                AdminLoginController::recordFailedLogin($credentials['email']);
            }

            // Same message whether the email exists or not
            throw ValidationException::withMessages(['email' => __('site.auth.failed')]);
        }

        $request->session()->regenerate();
        $member = Auth::guard('member')->user();
        $member->forceFill(['last_login_at' => now()])->save();
        if ($member->locale) {
            $request->session()->put('locale', $member->locale);
        }

        return redirect()->intended(route('home'))
            ->with('member_status', __('site.auth.welcomeBack', ['name' => $member->name]));
    }

    /** Signs out the visitor only; an admin session in the same browser stays signed in. */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('member')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('member_status', __('site.auth.signedOut'));
    }
}
