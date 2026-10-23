<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
            'recentActivity' => $request->user()->activityLogs()->latest('created_at')->limit(6)->get(),
        ]);
    }

    public function update(Request $request, ImageUploader $uploader): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'locale' => ['required', Rule::in(array_keys(locales()))],
            'avatar' => ImageUploader::rules(required: false),
            'remove_avatar' => ['boolean'],
        ]);

        $user->fill(collect($data)->only(['name', 'email', 'locale'])->all());
        if ($request->hasFile('avatar')) {
            $user->avatar = $uploader->store($request->file('avatar'), 'avatars')->path;
        } elseif ($request->boolean('remove_avatar')) {
            $user->avatar = null;
        }
        $user->save();
        $request->session()->put('admin_locale', $user->locale);
        ActivityLog::record('updated', null, [], target: __('Profile'));

        return back()->with('toast', ['success', __('Profile updated successfully')]);
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => $request->input('password')]);
        $request->user()->endOtherSessions($request->session()->getId());
        ActivityLog::record('password_changed', null, [], target: $request->user()->email);

        return back()->with('toast', ['success', __('Password changed successfully')]);
    }
}
