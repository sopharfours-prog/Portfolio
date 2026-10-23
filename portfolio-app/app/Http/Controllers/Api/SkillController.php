<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SkillRequest;
use App\Models\Skill;

class SkillController extends ResourceController
{
    protected string $model = Skill::class;

    protected string $request = SkillRequest::class;

    protected array $searchColumns = ['name'];

    protected function filters(): array
    {
        return ['status' => Skill::STATUSES, 'category' => Skill::CATEGORIES];
    }
}
