<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesInput;
use App\Models\Skill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SkillRequest extends FormRequest
{
    use NormalizesInput;

    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            ...self::bilingualRules('label', false, 60),
            'category' => ['required', Rule::in(Skill::CATEGORIES)],
            'icon' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/'],
            'level' => ['nullable', 'integer', 'between:0,100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Skill::STATUSES)],
        ];
    }

    public function payload(): array
    {
        return [...$this->validated(), 'label' => $this->bilingual('label'), 'sort_order' => (int) $this->input('sort_order', 0)];
    }
}
