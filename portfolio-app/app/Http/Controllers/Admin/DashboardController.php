<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Project;
use App\Models\Service;
use App\Support\Analytics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Analytics $analytics): View
    {
        $month = $analytics->compare(30);

        return view('admin.dashboard', [
            'stats' => [
                'projects' => Project::count(),
                'projectsPublished' => Project::published()->count(),
                'projectsGrowth' => $this->growth(
                    Project::where('created_at', '>=', now()->subDays(30))->count(),
                    Project::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count()
                ),
                'services' => Service::count(),
                'servicesActive' => Service::published()->count(),
                'messages' => Message::whereIn('folder', ['inbox', 'archived'])->count(),
                'messagesNew' => Message::view('unread')->count(),
                'views' => $month['current']['page_views'],
                'viewsGrowth' => $month['growth']['page_views'],
                'viewsSpark' => $analytics->series(14)['page_views'],
            ],
            'recentProjects' => Project::with('cover')->latest()->limit(5)->get(),
            'recentMessages' => Message::whereIn('folder', ['inbox'])->latest()->limit(5)->get(),
            'topProjects' => $analytics->topProjects(30),
        ]);
    }

    /** Chart data for the Website Overview card: ?range=7|30|90|365 */
    public function analytics(Request $request, Analytics $analytics): JsonResponse
    {
        $days = (int) $request->query('range', 7);
        $days = in_array($days, [7, 30, 90, 365], true) ? $days : 7;

        return response()->json([
            'series' => $analytics->series($days),
            'totals' => $analytics->compare($days),
        ]);
    }

    private function growth(int $current, int $previous): ?float
    {
        return $previous > 0 ? round(($current - $previous) / $previous * 100, 1) : null;
    }
}
