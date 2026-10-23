<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Admin user management (super admin only). */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $listing = Listing::for(User::query(), $request)
            ->search(['name', 'email'])
            ->filter('role', User::ROLES)
            ->sorts(['newest' => ['created_at', 'desc'], 'name' => ['name', 'asc'], 'last_login' => ['last_login_at', 'desc']], 'name');

        return view('admin.users.index', ['users' => $listing->paginate(15), 'sort' => $listing->currentSort()]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['role' => User::ROLE_EDITOR, 'locale' => 'lo'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = User::create($this->validated($request));
        ActivityLog::record('created', $user, target: $user->email);

        return redirect()->route('admin.users.index')->with('toast', ['success', __('User created successfully')]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        if ($this->wouldRemoveLastSuperAdmin($user, $data['role'])) {
            return back()->withErrors(['role' => __('At least one Super Admin is required.')])->withInput();
        }
        $passwordChanged = ! empty($data['password']);
        if (! $passwordChanged) {
            unset($data['password']);
        }
        $user->update($data);
        if ($passwordChanged) {
            $user->endOtherSessions($request->session()->getId());
        }
        ActivityLog::record('updated', $user, target: $user->email);

        return redirect()->route('admin.users.index')->with('toast', ['success', __('User updated successfully')]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('toast', ['error', __('You cannot delete your own account.')]);
        }
        if ($this->wouldRemoveLastSuperAdmin($user, null)) {
            return back()->with('toast', ['error', __('At least one Super Admin is required.')]);
        }
        $user->endOtherSessions();
        $user->delete();
        ActivityLog::record('deleted', null, target: $user->email);

        return redirect()->route('admin.users.index')->with('toast', ['success', __('User deleted')]);
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user?->id)],
            'role' => ['required', Rule::in(User::ROLES)],
            'locale' => ['required', Rule::in(array_keys(locales()))],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ]);
    }

    private function wouldRemoveLastSuperAdmin(User $user, ?string $newRole): bool
    {
        return $user->isSuperAdmin() && $newRole !== User::ROLE_SUPER_ADMIN
            && User::where('role', User::ROLE_SUPER_ADMIN)->count() <= 1;
    }
}
