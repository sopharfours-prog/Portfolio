<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EducationRequest;
use App\Models\Education;
use Illuminate\Http\RedirectResponse;

class EducationController extends SortableResourceController
{
    protected string $model = Education::class;

    protected string $name = 'education';

    protected array $searchColumns = ['institution', 'degree', 'field', 'location'];

    protected function label(): string
    {
        return __('Education');
    }

    public function store(EducationRequest $request): RedirectResponse
    {
        $this->persist($request);

        return $this->saved(new Education, __(':item created successfully', ['item' => $this->label()]));
    }

    public function update(EducationRequest $request, int $id): RedirectResponse
    {
        $item = $this->persist($request, Education::findOrFail($id));

        return $this->saved($item, __(':item updated successfully', ['item' => $this->label()]));
    }
}
