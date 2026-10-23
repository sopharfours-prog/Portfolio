@extends('admin.layouts.app')
@section('title', __('Members'))

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ __('Members') }}</h1>
            <p>{{ __('People who signed up on the website. :total in total, :new in the last 7 days.', ['total' => $total, 'new' => $newThisWeek]) }}</p>
        </div>
    </div>
    <div class="card card--flush">
        @include('admin.partials.filters', [
            'search' => __('Search by name or email...'),
            'selects' => ['method' => [__('Signed up with'), ['email' => __('Email'), 'google' => 'Google', 'apple' => 'Apple']]],
            'sorts' => ['newest' => __('Newest'), 'oldest' => __('Oldest'), 'name' => __('Name'), 'last_login' => __('Last login')],
        ])
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>{{ __('Member') }}</th><th>{{ __('Signed up with') }}</th><th>{{ __('Language') }}</th><th>{{ __('Last login') }}</th><th>{{ __('Joined') }}</th><th class="col-actions">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse ($members as $m)
                        <tr>
                            <td data-primary>
                                <span class="cell-media">
                                    <span class="avatar avatar--lg avatar--soft">{{ $m->initials() }}</span>
                                    <span><span class="table__title">{{ $m->name }}</span><a class="table__sub" style="display:block" href="mailto:{{ $m->email }}">{{ $m->email }}</a></span>
                                </span>
                            </td>
                            <td data-label="{{ __('Signed up with') }}">
                                @if ($m->google_id)<span class="badge badge--plain">{{ icon('google') }} Google</span>@endif
                                @if ($m->apple_id)<span class="badge badge--plain">{{ icon('apple') }} Apple</span>@endif
                                @if (! $m->google_id && ! $m->apple_id)<span class="badge badge--plain">{{ icon('envelope') }} {{ __('Email') }}</span>@endif
                            </td>
                            <td data-label="{{ __('Language') }}" class="muted">{{ $m->locale ? __('site.languages.'.$m->locale) : '—' }}</td>
                            <td data-label="{{ __('Last login') }}" class="muted">{{ $m->last_login_at?->diffForHumans() ?? '—' }}</td>
                            <td data-label="{{ __('Joined') }}" class="muted nowrap" title="{{ $m->created_at->format('Y-m-d H:i') }}">{{ $m->created_at->translatedFormat('j M Y') }}</td>
                            <td class="col-actions">
                                <form method="POST" action="{{ route('admin.members.destroy', $m) }}"
                                      data-confirm="{{ __('Delete Member?') }}" data-confirm-text="{{ __(':name will no longer be able to sign in.', ['name' => $m->name]) }}" data-confirm-button="{{ __('Delete') }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn--danger-ghost btn--sm">{{ icon('trash') }}<span>{{ __('Delete') }}</span></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">{{ icon('person-badge') }}<h3>{{ __('No members yet') }}</h3><p>{{ __('People who sign up on the website appear here.') }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $members->links() }}
    </div>
@endsection
