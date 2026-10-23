<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\MessageReplyMail;
use App\Models\ActivityLog;
use App\Models\Message;
use App\Support\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $view = in_array($request->query('view'), Message::VIEWS, true) ? $request->query('view') : 'inbox';
        $messages = Listing::for(Message::query()->view($view), $request)
            ->search(['name', 'email', 'subject', 'body'])
            ->sorts(['newest' => ['created_at', 'desc'], 'oldest' => ['created_at', 'asc']], 'newest')
            ->paginate(15);

        $selected = $request->filled('message') ? Message::with('replies.user')->find($request->integer('message')) : null;
        if ($selected?->isUnread()) {
            $selected->update(['read_at' => now()]);
        }

        return view('admin.messages.index', [
            'messages' => $messages,
            'selected' => $selected,
            'view' => $view,
            'counts' => collect(Message::VIEWS)->mapWithKeys(fn ($v) => [$v => Message::view($v)->count()]),
        ]);
    }

    /** Folder moves and read state: action = read | unread | archive | trash | restore */
    /** Sends an email reply from the website itself and keeps it under the message. */
    public function reply(Request $request, Message $message): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        try {
            Mail::to($message->email, $message->name)->send(new MessageReplyMail($message, $data['subject'], $data['body']));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('toast', ['error', __('The email could not be sent. Check the mail settings and try again.')]);
        }

        $message->replies()->create([...$data, 'user_id' => $request->user()->id]);
        $message->update(['replied_at' => now(), 'read_at' => $message->read_at ?? now()]);
        ActivityLog::record('replied', null, [], target: $message->email);

        return redirect()->route('admin.messages.index', ['message' => $message->id])->with('toast', ['success', __('Reply sent to :email', ['email' => $message->email])]);
    }

    public function update(Request $request, Message $message): RedirectResponse
    {
        $action = $request->validate(['action' => ['required', Rule::in(['read', 'unread', 'archive', 'trash', 'restore', 'replied'])]])['action'];

        match ($action) {
            'read' => $message->update(['read_at' => now()]),
            'unread' => $message->update(['read_at' => null]),
            'archive' => $message->update(['folder' => 'archived']),
            'trash' => $message->update(['folder' => 'trash']),
            'restore' => $message->update(['folder' => 'inbox']),
            'replied' => $message->update(['replied_at' => now(), 'read_at' => $message->read_at ?? now()]),
        };
        ActivityLog::record($action === 'trash' ? 'trashed' : $action, $message);

        $toast = [
            'archive' => __('Message archived'), 'trash' => __('Message moved to trash'), 'restore' => __('Message restored'),
            'read' => __('Marked as read'), 'unread' => __('Marked as unread'), 'replied' => __('Marked as replied'),
        ][$action];
        $stay = in_array($action, ['read', 'unread', 'replied'], true);

        return redirect()->route('admin.messages.index', array_filter([
            'view' => $request->input('view'),
            'message' => $stay ? $message->id : null,
        ]))->with('toast', ['success', $toast]);
    }

    public function destroy(Request $request, Message $message): RedirectResponse
    {
        $message->delete();
        ActivityLog::record('deleted', $message);

        return redirect()->route('admin.messages.index', ['view' => $request->input('view', 'trash')])
            ->with('toast', ['success', __('Message deleted permanently')]);
    }

    public function emptyTrash(): RedirectResponse
    {
        $count = Message::where('folder', 'trash')->delete();
        ActivityLog::record('emptied_trash', null, ['count' => $count], target: __('Messages'));

        return redirect()->route('admin.messages.index', ['view' => 'trash'])->with('toast', ['success', __('Trash emptied')]);
    }
}
