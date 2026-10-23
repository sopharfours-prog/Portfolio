<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesInput;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    use NormalizesInput;

    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            ...self::bilingualRules('title', true, 120),
            ...self::bilingualRules('description', true, 600),
            'features' => ['nullable', 'array'],
            'icon' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Service::STATUSES)],
        ];
    }

    public function payload(): array
    {
        return [
            'title' => $this->bilingual('title'),
            'description' => $this->bilingual('description'),
            'features' => $this->bilingualLines('features'),
            'icon' => $this->input('icon'),
            'sort_order' => (int) $this->input('sort_order', 0),
            'status' => $this->input('status'),
        ];
    }
}
