<?php

namespace App\Support;

use App\Models\Message;
use App\Models\PageView;
use App\Models\Project;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Aggregates first-party page views for the dashboard. */
class Analytics
{
    /**
     * Daily (≤ 90 days) or monthly (1 year) buckets.
     *
     * @return array{labels: list<string>, visitors: list<int>, page_views: list<int>, project_views: list<int>, contacts: list<int>}
     */
    public function series(int $days): array
    {
        $monthly = $days > 90;
        $start = $monthly ? now()->startOfMonth()->subMonths(11) : today()->subDays($days - 1);
        $format = $monthly ? '%Y-%m' : '%Y-%m-%d';

        $views = PageView::where('viewed_on', '>=', $start)
            ->selectRaw("DATE_FORMAT(viewed_on, '{$format}') as bucket, COUNT(*) as views, COUNT(DISTINCT visitor) as visitors, SUM(project_id IS NOT NULL) as projects")
            ->groupBy('bucket')->get()->keyBy('bucket');

        $contacts = Message::where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as bucket, COUNT(*) as total")
            ->groupBy('bucket')->pluck('total', 'bucket');

        $buckets = $this->buckets($start, $monthly, $days);

        return [
            'labels' => $buckets->map(fn (Carbon $d) => $monthly ? $d->translatedFormat('M') : ($days <= 7 ? $d->translatedFormat('D') : $d->translatedFormat('j M')))->all(),
            'visitors' => $buckets->map(fn ($d) => (int) ($views[$d->format($monthly ? 'Y-m' : 'Y-m-d')]->visitors ?? 0))->all(),
            'page_views' => $buckets->map(fn ($d) => (int) ($views[$d->format($monthly ? 'Y-m' : 'Y-m-d')]->views ?? 0))->all(),
            'project_views' => $buckets->map(fn ($d) => (int) ($views[$d->format($monthly ? 'Y-m' : 'Y-m-d')]->projects ?? 0))->all(),
            'contacts' => $buckets->map(fn ($d) => (int) ($contacts[$d->format($monthly ? 'Y-m' : 'Y-m-d')] ?? 0))->all(),
        ];
    }

    /** Totals for the last $days and growth vs. the previous period (percent, null if no baseline). */
    public function compare(int $days): array
    {
        $current = $this->totals(today()->subDays($days - 1), now());
        $previous = $this->totals(today()->subDays($days * 2 - 1), today()->subDays($days)->endOfDay());

        return [
            'current' => $current,
            'growth' => collect($current)->map(fn ($value, $key) => $previous[$key] > 0 ? round(($value - $previous[$key]) / $previous[$key] * 100, 1) : null)->all(),
        ];
    }

    public function topProjects(int $days, int $limit = 5): Collection
    {
        $counts = PageView::whereNotNull('project_id')->where('viewed_on', '>=', today()->subDays($days - 1))
            ->select('project_id', DB::raw('COUNT(*) as total'))->groupBy('project_id')->orderByDesc('total')->limit($limit)
            ->pluck('total', 'project_id');

        return Project::whereIn('id', $counts->keys())->get()
            ->map(fn ($project) => ['project' => $project, 'views' => (int) $counts[$project->id]])
            ->sortByDesc('views')->values();
    }

    private function totals(Carbon $from, Carbon $to): array
    {
        $row = PageView::whereBetween('viewed_on', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('COUNT(*) as views, COUNT(DISTINCT visitor) as visitors, SUM(project_id IS NOT NULL) as projects')->first();

        return [
            'visitors' => (int) $row->visitors,
            'page_views' => (int) $row->views,
            'project_views' => (int) $row->projects,
            'contacts' => Message::whereBetween('created_at', [$from, $to])->count(),
        ];
    }

    private function buckets(Carbon $start, bool $monthly, int $days): Collection
    {
        $count = $monthly ? 12 : $days;

        return collect(range(0, $count - 1))->map(fn ($i) => $monthly ? $start->copy()->addMonths($i) : $start->copy()->addDays($i));
    }
}
