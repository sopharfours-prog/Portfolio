<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Public contact form. Mirrors the client-side rules in the site JS. */
class ContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[\d\s+\-()]+$/'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'website' => ['prohibited'], // honeypot: real visitors never fill this hidden field
        ];
    }
}
