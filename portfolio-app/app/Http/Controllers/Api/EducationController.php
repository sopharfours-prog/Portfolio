<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\EducationRequest;
use App\Models\Education;

class EducationController extends ResourceController
{
    protected string $model = Education::class;

    protected string $request = EducationRequest::class;

    protected array $searchColumns = ['institution', 'degree', 'field'];
}
