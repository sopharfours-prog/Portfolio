@extends('admin.layouts.app')
@section('title', __('Messages'))

@php
    $labels = ['inbox' => __('Inbox'), 'unread' => __('Unread'), 'read' => __('Read'), 'archived' => __('Archived'), 'trash' => __('Trash')];
    $keep = fn (array $extra = []) => array_filter([...request()->only(['view', 'search', 'sort', 'page']), ...$extra]);
@endphp

@section('content')
    <div class="page-head">
        <div><h1>{{ __('Messages') }}</h1><p>{{ __('Enquiries sent from the contact form on your website.') }}</p></div>
        @if ($view === 'trash' && $counts['trash'] > 0)
            <form method="POST" action="{{ route('admin.messages.empty-trash') }}" data-confirm="{{ __('Empty trash?') }}" data-confirm-text="{{ __(':count messages will be deleted permanently.', ['count' => $counts['trash']]) }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn--secondary">{{ icon('trash3') }}{{ __('Empty trash') }}</button>
            </form>
        @endif
    </div>

    <div class="card card--flush">
        @include('admin.partials.filters', [
            'search' => __('Search messages...'),
            'tabs' => ['view', collect($labels)->mapWithKeys(fn ($text, $key) => [$key === 'inbox' ? '' : $key => [$text, $counts[$key]]])->all()],
            'sorts' => ['newest' => __('Newest'), 'oldest' => __('Oldest')],
            'sort' => request('sort', 'newest'),
        ])

        <div class="mail {{ $selected ? 'mail--open' : '' }}">
            <div class="mail__list">
                @forelse ($messages as $message)
                    <a class="mail__item {{ $message->isUnread() ? 'is-unread' : '' }} {{ $selected?->id === $message->id ? 'is-selected' : '' }}"
                       href="{{ route('admin.messages.index', $keep(['message' => $message->id])) }}" @if ($selected?->id === $message->id) aria-current="true" @endif>
                        <span class="avatar avatar--soft">{{ $message->initials() }}</span>
                        <span class="mail__main">
                            <span class="mail__top"><span class="mail__name truncate">{{ $message->name }}</span><span class="mail__time">{{ $message->created_at->diffForHumans(short: true) }}</span></span>
                            <span class="mail__subject truncate" style="display:block">{{ $message->subject }}</span>
                            <span class="mail__excerpt truncate" style="display:block">{{ Str::limit($message->body, 70) }}</span>
                        </span>
                    </a>
                @empty
                    <div class="empty">{{ icon('envelope-open') }}<h3>{{ __('No messages here') }}</h3></div>
                @endforelse
                {{ $messages->links() }}
            </div>

            <div class="mail__view">
                @if ($selected)
                    <a class="btn btn--ghost btn--sm mail__back" href="{{ route('admin.messages.index', $keep()) }}">{{ icon('arrow-left') }}{{ __('Back') }}</a>
                    <div class="mail__head">
                        <div class="row" style="align-items:flex-start;min-width:0">
                            <span class="avatar avatar--lg">{{ $selected->initials() }}</span>
                            <div style="min-width:0">
                                <h2 style="font-size:18px;font-weight:600;letter-spacing:-.01em">{{ $selected->subject }}</h2>
                                <p><strong>{{ $selected->name }}</strong> · <a href="mailto:{{ $selected->email }}" style="text-decoration:underline">{{ $selected->email }}</a>@if ($selected->phone) · <a href="tel:{{ preg_replace('/[^\d+]/', '', $selected->phone) }}" style="text-decoration:underline">{{ $selected->phone }}</a>@endif</p>
                                <p class="muted" style="font-size:12.5px">{{ $selected->created_at->translatedFormat('j M Y, H:i') }}
                                    @if ($selected->replied_at) · <span class="badge badge--success">{{ __('Replied') }}</span>@endif</p>
                            </div>
                        </div>
                    </div>

                    <div class="mail__body">{{ $selected->body }}</div>

                    {{-- Replies sent from here (newest last) --}}
                    @foreach ($selected->replies as $reply)
                        <div class="mail__reply">
                            <p class="mail__reply-meta">{{ icon('reply-fill') }}<strong>{{ $reply->user?->name ?? __('Admin') }}</strong> → {{ $selected->email }} · {{ $reply->created_at->translatedFormat('j M Y, H:i') }}</p>
                            <p class="mail__reply-subject">{{ $reply->subject }}</p>
                            <div class="mail__reply-body">{{ $reply->body }}</div>
                        </div>
                    @endforeach

                    @if ($selected->folder !== 'trash')
                        <form class="mail__compose" method="POST" action="{{ route('admin.messages.reply', $selected) }}">
                            @csrf
                            <h3 class="mail__compose-title">{{ icon('reply') }}{{ __('Reply by email') }} <span class="muted">→ {{ $selected->email }}</span></h3>
                            @if (\App\Support\MailSettings::isTestMode())
                                <p class="mail__notice">{{ icon('exclamation-triangle') }}<span>{{ __('Email is in test mode: replies are saved to the log instead of being delivered.') }} @can('super-admin')<a href="{{ route('admin.settings.edit', ['tab' => 'mail']) }}" style="text-decoration:underline;font-weight:600">{{ __('Set up email') }}</a>@endcan</span></p>
                            @endif
                            <label class="label" for="reply-subject">{{ __('Subject') }}</label>
                            <input class="input" id="reply-subject" name="subject" maxlength="200" required value="{{ old('subject', 'Re: '.$selected->subject) }}">
                            @error('subject')<p class="error">{{ $message }}</p>@enderror
                            <label class="label" for="reply-body" style="margin-top:12px">{{ __('Message') }}</label>
                            <textarea class="textarea" id="reply-body" name="body" rows="6" maxlength="10000" required placeholder="{{ __('Write your reply…') }}">{{ old('body') }}</textarea>
                            @error('body')<p class="error">{{ $message }}</p>@enderror
                            <div class="row" style="margin-top:12px"><button type="submit" class="btn btn--primary">{{ icon('send') }}{{ __('Send reply') }}</button></div>
                        </form>
                    @endif

                    <div class="row row--wrap" style="padding-top:20px;border-top:1px solid var(--border)">
                        @php
                            $actions = match ($selected->folder) {
                                'trash' => [['restore', 'arrow-counterclockwise', __('Restore')]],
                                'archived' => [['restore', 'inbox', __('Move to inbox')], ['trash', 'trash3', __('Trash')]],
                                default => [['replied', 'check2', __('Mark as replied')], ['unread', 'envelope', __('Mark as unread')], ['archive', 'archive', __('Archive')], ['trash', 'trash3', __('Trash')]],
                            };
                        @endphp
                        @foreach ($actions as [$action, $iconName, $text])
                            <form method="POST" action="{{ route('admin.messages.update', $selected) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="{{ $action }}"><input type="hidden" name="view" value="{{ $view === 'inbox' ? '' : $view }}">
                                <button type="submit" class="btn btn--secondary">{{ icon($iconName) }}{{ $text }}</button>
                            </form>
                        @endforeach
                        @if ($selected->folder === 'trash')
                            <form method="POST" action="{{ route('admin.messages.destroy', $selected) }}" data-confirm="{{ __('Delete Message?') }}" data-confirm-text="{{ __('Are you sure you want to delete ":name"?', ['name' => $selected->subject]) }}">
                                @csrf @method('DELETE')<input type="hidden" name="view" value="trash">
                                <button type="submit" class="btn btn--danger-ghost">{{ icon('trash3') }}{{ __('Delete permanently') }}</button>
                            </form>
                        @endif
                    </div>
                @else
                    <div class="empty" style="padding-top:120px">{{ icon('envelope') }}<h3>{{ __('Select a message') }}</h3><p>{{ __('Choose a message from the list to read it.') }}</p></div>
                @endif
            </div>
        </div>
    </div>
@endsection
