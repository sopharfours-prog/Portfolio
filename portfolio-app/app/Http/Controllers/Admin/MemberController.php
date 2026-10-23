<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Member;
use App\Support\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Visitor accounts created through the website's Sign up / Google / Apple (super admin only). */
class MemberController extends Controller
{
    public const METHODS = ['email', 'google', 'apple'];

    public function index(Request $request): View
    {
        $query = Member::query();

        // How they signed up: a Google / Apple link, or email + password only
        match ($request->query('method')) {
            'google' => $query->whereNotNull('google_id'),
            'apple' => $query->whereNotNull('apple_id'),
            'email' => $query->whereNull('google_id')->whereNull('apple_id'),
            default => null,
        };

        $listing = Listing::for($query, $request)
            ->search(['name', 'email'])
            ->sorts(['newest' => ['created_at', 'desc'], 'oldest' => ['created_at', 'asc'], 'name' => ['name', 'asc'], 'last_login' => ['last_login_at', 'desc']], 'newest');

        return view('admin.members.index', [
            'members' => $listing->paginate(20),
            'sort' => $listing->currentSort(),
            'total' => Member::count(),
            'newThisWeek' => Member::where('created_at', '>=', now()->subDays(7))->count(),
        ]);
    }

    public function destroy(Member $member): RedirectResponse
    {
        $member->delete();
        ActivityLog::record('deleted', null, ['type' => 'member'], target: $member->email);

        return redirect()->route('admin.members.index')->with('toast', ['success', __('Member deleted')]);
    }
}
