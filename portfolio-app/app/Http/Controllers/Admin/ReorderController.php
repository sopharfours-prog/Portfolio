<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Service;
use App\Models\Skill;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Persists drag & drop order: POST { ids: [3, 1, 2], start: 0 } */
class ReorderController extends Controller
{
    private const MODELS = [
        'projects' => Project::class,
        'services' => Service::class,
        'skills' => Skill::class,
        'experience' => Experience::class,
        'education' => Education::class,
        'testimonials' => Testimonial::class,
        'certificates' => Certificate::class,
    ];

    public function __invoke(Request $request, string $type): JsonResponse
    {
        abort_unless(isset(self::MODELS[$type]), 404);
        $data = $request->validate([
            'ids' => ['required', 'array', 'max:500'],
            'ids.*' => ['integer'],
            'start' => ['nullable', 'integer', 'min:0'],
        ]);

        $model = self::MODELS[$type];
        $start = (int) ($data['start'] ?? 0);
        DB::transaction(function () use ($model, $data, $start) {
            foreach ($data['ids'] as $index => $id) {
                $model::whereKey($id)->update(['sort_order' => $start + $index]);
            }
        });
        ActivityLog::record('reordered', null, ['count' => count($data['ids'])], target: __(ucfirst($type)));

        return response()->json(['message' => __('Order saved')]);
    }
}
