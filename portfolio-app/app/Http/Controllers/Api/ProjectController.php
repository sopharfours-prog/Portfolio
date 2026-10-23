<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class ProjectController extends ResourceController
{
    protected string $model = Project::class;

    protected string $request = ProjectRequest::class;

    protected array $searchColumns = ['title', 'client', 'summary', 'technologies'];

    protected array $with = ['images'];

    protected string $resource = ProjectResource::class;

    protected function filters(): array
    {
        return ['status' => Project::STATUSES, 'category' => Project::CATEGORIES];
    }

    /** Projects are addressed by slug or id. */
    protected function find(string $id): Model
    {
        return Project::where('slug', $id)->orWhere('id', ctype_digit($id) ? $id : 0)->firstOrFail();
    }
}
