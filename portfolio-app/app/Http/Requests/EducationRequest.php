<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesInput;
use App\Models\Education;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EducationRequest extends FormRequest
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
            'institution' => ['required', 'string', 'max:150'],
            ...self::bilingualRules('degree', true, 150),
            ...self::bilingualRules('field', false, 150),
            'location' => ['nullable', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'is_current' => ['boolean'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'required_if:is_current,false'],
            ...self::bilingualRules('description', false, 2000),
            'image' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Education::STATUSES)],
        ];
    }

    public function payload(): array
    {
        $current = $this->boolean('is_current');

        return [
            'institution' => $this->input('institution'),
            'degree' => $this->bilingual('degree'),
            'field' => $this->bilingual('field'),
            'location' => $this->input('location'),
            'start_date' => $this->input('start_date'),
            'end_date' => $current ? null : $this->input('end_date'),
            'is_current' => $current,
            'description' => $this->bilingual('description'),
            'image' => $this->input('image') ?: null,
            'sort_order' => (int) $this->input('sort_order', 0),
            'status' => $this->input('status'),
        ];
    }
}
