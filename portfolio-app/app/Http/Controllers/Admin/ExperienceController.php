<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ExperienceRequest;
use App\Models\Experience;
use Illuminate\Http\RedirectResponse;

class ExperienceController extends SortableResourceController
{
    protected string $model = Experience::class;

    protected string $name = 'experience';

    protected array $searchColumns = ['company', 'position', 'location'];

    protected function label(): string
    {
        return __('Experience');
    }

    public function store(ExperienceRequest $request): RedirectResponse
    {
        $this->persist($request);

        return $this->saved(new Experience, __(':item created successfully', ['item' => $this->label()]));
    }

    public function update(ExperienceRequest $request, int $id): RedirectResponse
    {
        $item = $this->persist($request, Experience::findOrFail($id));

        return $this->saved($item, __(':item updated successfully', ['item' => $this->label()]));
    }
}
