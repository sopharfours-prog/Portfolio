<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactMessageRequest;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(ContactMessageRequest $request): JsonResponse|RedirectResponse
    {
        Message::create([
            ...$request->safe()->only(['name', 'email', 'phone', 'subject']),
            'body' => $request->input('message'),
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return $request->expectsJson()
            ? response()->json(['message' => __('site.contact.success')], 201)
            : back()->with('contact_success', __('site.contact.success'));
    }
}
