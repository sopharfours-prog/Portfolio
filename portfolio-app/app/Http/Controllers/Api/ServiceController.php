<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ServiceRequest;
use App\Models\Service;

class ServiceController extends ResourceController
{
    protected string $model = Service::class;

    protected string $request = ServiceRequest::class;

    protected array $searchColumns = ['title', 'description'];
}
