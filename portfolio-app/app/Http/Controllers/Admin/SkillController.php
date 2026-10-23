<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SkillRequest;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;

class SkillController extends SortableResourceController
{
    protected string $model = Skill::class;

    protected string $name = 'skills';

    protected array $searchColumns = ['name'];

    protected function filters(): array
    {
        return ['status' => Skill::STATUSES, 'category' => Skill::CATEGORIES];
    }

    protected function label(): string
    {
        return __('Skill');
    }

    public function store(SkillRequest $request): RedirectResponse
    {
        $this->persist($request);

        return $this->saved(new Skill, __(':item created successfully', ['item' => $this->label()]));
    }

    public function update(SkillRequest $request, int $id): RedirectResponse
    {
        $item = $this->persist($request, Skill::findOrFail($id));

        return $this->saved($item, __(':item updated successfully', ['item' => $this->label()]));
    }
}
