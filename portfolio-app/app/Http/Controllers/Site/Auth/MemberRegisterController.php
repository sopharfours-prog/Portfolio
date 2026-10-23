<?php

namespace App\Http\Controllers\Site\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Creates a visitor account on the public website and signs it in. */
class MemberRegisterController extends Controller
{
    public function create(): View
    {
        return view('site.auth.register', ['page' => 'register']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:members,email'],
            'password' => ['required', 'string', 'max:200', 'confirmed', Password::defaults()],
        ]);

        $member = Member::create([...$data, 'locale' => app()->getLocale()]);
        $member->forceFill(['last_login_at' => now()])->save();

        Auth::guard('member')->login($member);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('member_status', __('site.auth.welcome', ['name' => $member->name]));
    }
}
