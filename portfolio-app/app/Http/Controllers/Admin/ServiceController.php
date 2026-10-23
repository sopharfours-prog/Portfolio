<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;

class ServiceController extends SortableResourceController
{
    protected string $model = Service::class;

    protected string $name = 'services';

    protected array $searchColumns = ['title', 'description'];

    protected function label(): string
    {
        return __('Service');
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $this->persist($request);

        return $this->saved(new Service, __(':item created successfully', ['item' => $this->label()]));
    }

    public function update(ServiceRequest $request, int $id): RedirectResponse
    {
        $item = $this->persist($request, Service::findOrFail($id));

        return $this->saved($item, __(':item updated successfully', ['item' => $this->label()]));
    }
}
