<?php

namespace App\Http\Controllers\Site\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** "My profile" for signed-in visitors (guard "member"): registration details, name / language and password. */
class MemberAccountController extends Controller
{
    public function show(Request $request): View
    {
        return view('site.auth.account', ['page' => 'account', 'member' => $request->user('member')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $member = $request->user('member');
        $data = $request->validateWithBag('profile', [
            'name' => ['required', 'string', 'max:100'],
            'locale' => ['required', Rule::in(array_keys(locales()))],
        ]);

        $member->update($data);
        $request->session()->put('locale', $data['locale']);

        return redirect()->route('member.account')->with('member_status', __('site.account.saved'));
    }

    /** Change the password; Google / Apple-only accounts can set a first one without a current password. */
    public function password(Request $request): RedirectResponse
    {
        $member = $request->user('member');
        $request->validateWithBag('password', [
            'current_password' => [$member->password ? 'required' : 'nullable', 'current_password:member'],
            'password' => ['required', 'string', 'max:200', 'confirmed', Password::defaults()],
        ]);

        $member->update(['password' => $request->input('password')]);
        $request->session()->regenerate();

        return redirect()->route('member.account')->with('member_status', __('site.account.passwordSaved'));
    }
}
