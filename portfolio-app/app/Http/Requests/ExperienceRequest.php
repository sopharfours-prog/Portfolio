<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesInput;
use App\Models\Experience;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExperienceRequest extends FormRequest
{
    use NormalizesInput;

    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_current' => $this->boolean('is_current')]);
    }

    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:150'],
            ...self::bilingualRules('position', true, 150),
            'location' => ['nullable', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'is_current' => ['boolean'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'required_if:is_current,false'],
            ...self::bilingualRules('description', false, 2000),
            'technologies' => ['nullable'],
            'image' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Experience::STATUSES)],
        ];
    }

    public function payload(): array
    {
        $current = $this->boolean('is_current');

        return [
            'company' => $this->input('company'),
            'position' => $this->bilingual('position'),
            'location' => $this->input('location'),
            'start_date' => $this->input('start_date'),
            'end_date' => $current ? null : $this->input('end_date'),
            'is_current' => $current,
            'description' => $this->bilingual('description'),
            'technologies' => $this->tags('technologies'),
            'image' => $this->input('image') ?: null,
            'sort_order' => (int) $this->input('sort_order', 0),
            'status' => $this->input('status'),
        ];
    }
}
