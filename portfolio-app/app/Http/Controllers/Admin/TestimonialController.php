<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TestimonialRequest;
use App\Models\Testimonial;
use App\Support\ImageUploader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;

class TestimonialController extends SortableResourceController
{
    protected string $model = Testimonial::class;

    protected string $name = 'testimonials';

    protected array $searchColumns = ['client_name', 'company', 'message'];

    protected function label(): string
    {
        return __('Testimonial');
    }

    public function store(TestimonialRequest $request): RedirectResponse
    {
        $this->persist($request);

        return $this->saved(new Testimonial, __(':item created successfully', ['item' => $this->label()]));
    }

    public function update(TestimonialRequest $request, int $id): RedirectResponse
    {
        $item = $this->persist($request, Testimonial::findOrFail($id));

        return $this->saved($item, __(':item updated successfully', ['item' => $this->label()]));
    }

    /** Adds photo upload / removal on top of the shared persistence. */
    protected function persist(FormRequest $request, ?Model $item = null): Model
    {
        $item = parent::persist($request, $item);
        if ($request->hasFile('photo')) {
            $item->update(['photo' => app(ImageUploader::class)->store($request->file('photo'), 'testimonials')->path]);
        } elseif ($request->boolean('remove_photo')) {
            $item->update(['photo' => null]);
        }

        return $item;
    }
}
