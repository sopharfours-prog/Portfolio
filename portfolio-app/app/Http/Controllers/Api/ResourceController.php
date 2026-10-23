<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Listing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * REST endpoints for portfolio content.
 *   GET    /api/{resource}        list (guests see published items only)
 *   GET    /api/{resource}/{id}   show
 *   POST   /api/{resource}        create  (auth)
 *   PUT    /api/{resource}/{id}   replace (auth)
 *   PATCH  /api/{resource}/{id}   partial: status / sort_order (auth)
 *   DELETE /api/{resource}/{id}   delete  (auth)
 * Writes reuse the admin FormRequests, so validation is identical.
 */
abstract class ResourceController extends Controller
{
    /** @var class-string<Model> */
    protected string $model;

    /** @var class-string<FormRequest> */
    protected string $request;

    protected array $searchColumns = [];

    protected array $with = [];

    protected string $resource = JsonResource::class;

    protected function filters(): array
    {
        return ['status' => $this->model::STATUSES];
    }

    public function index(Request $request): ResourceCollection
    {
        $query = $this->model::query()->with($this->with);
        if (! $request->user('sanctum')) {
            $query->where('status', 'published');
        }
        $listing = Listing::for($query, $request)->search($this->searchColumns)
            ->sorts(['order' => ['sort_order', 'asc'], 'newest' => ['created_at', 'desc'], 'oldest' => ['created_at', 'asc']], 'order');
        foreach ($this->filters() as $param => $allowed) {
            $listing->filter($param, $allowed);
        }

        return $this->resource::collection($listing->paginate(20));
    }

    public function show(Request $request, string $id): JsonResource
    {
        $item = $this->find($id);
        abort_if(! $request->user('sanctum') && $item->status !== 'published', 404);

        return new $this->resource($item->load($this->with));
    }

    public function store(): JsonResponse
    {
        $request = app($this->request);
        $item = $this->model::create($request->payload());

        return (new $this->resource($item->load($this->with)))->response()->setStatusCode(201);
    }

    public function update(Request $request, string $id): JsonResource
    {
        $item = $this->find($id);
        if ($request->isMethod('PATCH')) {
            $item->update($request->validate([
                'status' => ['sometimes', 'in:'.implode(',', $this->model::STATUSES)],
                'sort_order' => ['sometimes', 'integer', 'min:0'],
            ]));
        } else {
            $item->update(app($this->request)->payload());
        }

        return new $this->resource($item->fresh($this->with));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->find($id)->delete();

        return response()->json(null, 204);
    }

    protected function find(string $id): Model
    {
        return $this->model::findOrFail($id);
    }
}
