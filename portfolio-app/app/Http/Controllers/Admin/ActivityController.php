<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\Listing;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::query()->with('user');
        if ($request->filled('user')) {
            $query->where('user_id', $request->integer('user'));
        }
        $listing = Listing::for($query, $request)
            ->search(['action', 'target', 'subject_type', 'ip'])
            ->filter('status', ['success', 'failed'])
            ->filter('action', ActivityLog::distinct()->pluck('action')->all())
            ->sorts(['newest' => ['created_at', 'desc'], 'oldest' => ['created_at', 'asc']], 'newest');

        return view('admin.activity.index', [
            'logs' => $listing->paginate(20),
            'sort' => $listing->currentSort(),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'actions' => ActivityLog::distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
