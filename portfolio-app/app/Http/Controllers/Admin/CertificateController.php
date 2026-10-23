<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CertificateRequest;
use App\Models\Certificate;
use Illuminate\Http\RedirectResponse;

class CertificateController extends SortableResourceController
{
    protected string $model = Certificate::class;

    protected string $name = 'certificates';

    protected array $searchColumns = ['title', 'issuer'];

    protected function label(): string
    {
        return __('Certificate');
    }

    public function store(CertificateRequest $request): RedirectResponse
    {
        $this->persist($request);

        return $this->saved(new Certificate, __(':item created successfully', ['item' => $this->label()]));
    }

    public function update(CertificateRequest $request, int $id): RedirectResponse
    {
        $item = $this->persist($request, Certificate::findOrFail($id));

        return $this->saved($item, __(':item updated successfully', ['item' => $this->label()]));
    }
}
