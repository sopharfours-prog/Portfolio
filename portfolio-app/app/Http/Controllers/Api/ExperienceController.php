<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ExperienceRequest;
use App\Models\Experience;

class ExperienceController extends ResourceController
{
    protected string $model = Experience::class;

    protected string $request = ExperienceRequest::class;

    protected array $searchColumns = ['company', 'position'];
}
