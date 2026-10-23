<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\TestimonialRequest;
use App\Models\Testimonial;

class TestimonialController extends ResourceController
{
    protected string $model = Testimonial::class;

    protected string $request = TestimonialRequest::class;

    protected array $searchColumns = ['client_name', 'company', 'message'];
}
