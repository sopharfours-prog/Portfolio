<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesInput;
use App\Models\Certificate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CertificateRequest extends FormRequest
{
    use NormalizesInput;

    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            ...self::bilingualRules('title', true, 150),
            'issuer' => ['nullable', 'string', 'max:150'],
            'issued_on' => ['nullable', 'date'],
            'image' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Certificate::STATUSES)],
        ];
    }

    public function payload(): array
    {
        return [
            'title' => $this->bilingual('title'),
            'issuer' => $this->input('issuer'),
            'issued_on' => $this->input('issued_on') ?: null,
            'image' => $this->input('image'),
            'sort_order' => (int) $this->input('sort_order', 0),
            'status' => $this->input('status'),
        ];
    }
}
