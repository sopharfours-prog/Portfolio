<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesInput;
use App\Models\Testimonial;
use App\Support\ImageUploader;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestimonialRequest extends FormRequest
{
    use NormalizesInput;

    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:120'],
            ...self::bilingualRules('position', false, 150),
            'company' => ['nullable', 'string', 'max:150'],
            'photo' => ImageUploader::rules(required: false),
            'remove_photo' => ['boolean'],
            ...self::bilingualRules('message', true, 1500),
            'rating' => ['required', 'integer', 'between:1,5'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Testimonial::STATUSES)],
        ];
    }

    public function payload(): array
    {
        return [
            'client_name' => $this->input('client_name'),
            'position' => $this->bilingual('position'),
            'company' => $this->input('company'),
            'message' => $this->bilingual('message'),
            'rating' => (int) $this->input('rating'),
            'sort_order' => (int) $this->input('sort_order', 0),
            'status' => $this->input('status'),
        ];
    }
}
