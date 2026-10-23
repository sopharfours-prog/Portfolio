<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Notification dropdown actions. JSON for the dropdown; redirect fallback without JS. */
class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->adminNotifications()->limit(15)->get();

        return response()->json([
            'unread' => $request->user()->adminNotifications()->unread()->count(),
            'items' => $items->map(fn (AdminNotification $n) => [
                'id' => $n->id, 'type' => $n->type, 'title' => __($n->title), 'body' => $n->body,
                'url' => $n->url, 'read' => (bool) $n->read_at, 'time' => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function read(Request $request, AdminNotification $notification): JsonResponse|RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);
        $notification->update(['read_at' => $notification->read_at ?? now()]);

        return $this->respond($request, $notification->url);
    }

    public function readAll(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->adminNotifications()->unread()->update(['read_at' => now()]);

        return $this->respond($request);
    }

    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->adminNotifications()->delete();

        return $this->respond($request);
    }

    private function respond(Request $request, ?string $url = null): JsonResponse|RedirectResponse
    {
        return $request->expectsJson()
            ? response()->json(['ok' => true, 'url' => $url])
            : ($url ? redirect($url) : back());
    }
}
