<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactMessageRequest;
use App\Models\Message;
use App\Support\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    /** Public: submit the contact form (rate limited). */
    public function store(ContactMessageRequest $request): JsonResponse
    {
        $message = Message::create([
            ...$request->safe()->only(['name', 'email', 'phone', 'subject']),
            'body' => $request->input('message'),
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return response()->json(['id' => $message->id, 'message' => __('site.contact.success')], 201);
    }

    public function index(Request $request): ResourceCollection
    {
        $view = in_array($request->query('view'), Message::VIEWS, true) ? $request->query('view') : 'inbox';

        return JsonResource::collection(
            Listing::for(Message::query()->view($view), $request)->search(['name', 'email', 'subject', 'body'])
                ->sorts(['newest' => ['created_at', 'desc'], 'oldest' => ['created_at', 'asc']], 'newest')->paginate(20)
        );
    }

    public function show(Message $message): JsonResource
    {
        return new JsonResource($message);
    }

    public function update(Request $request, Message $message): JsonResource
    {
        $data = $request->validate([
            'folder' => ['sometimes', Rule::in(['inbox', 'archived', 'trash'])],
            'read' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('read', $data)) {
            $message->read_at = $data['read'] ? ($message->read_at ?? now()) : null;
        }
        $message->fill(array_intersect_key($data, ['folder' => 1]))->save();

        return new JsonResource($message);
    }

    public function destroy(Message $message): JsonResponse
    {
        $message->delete();

        return response()->json(null, 204);
    }
}
