@extends('admin.layouts.app')
@section('title', __('Activity Log'))

@section('content')
    <div class="page-head"><div><h1>{{ __('Activity Log') }}</h1><p>{{ __('Who did what, and when.') }}</p></div></div>

    <div class="card card--flush">
        @include('admin.partials.filters', [
            'search' => __('Search activity...'),
            'selects' => [
                'user' => [__('Admin'), $users->pluck('name', 'id')->all()],
                'action' => [__('Action'), $actions->mapWithKeys(fn ($a) => [$a => __(ucfirst(str_replace('_', ' ', $a)))])->all()],
                'status' => [__('Status'), ['success' => __('Success'), 'failed' => __('Failed')]],
            ],
            'sorts' => ['newest' => __('Newest'), 'oldest' => __('Oldest')],
        ])
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>{{ __('Admin') }}</th><th>{{ __('Action') }}</th><th>{{ __('Target') }}</th><th>{{ __('Date') }}</th><th>{{ __('IP') }}</th><th>{{ __('Status') }}</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td data-primary>
                                <span class="cell-media"><span class="avatar avatar--soft">{{ $log->user?->initials() ?? '–' }}</span><span class="table__title">{{ $log->user?->name ?? __('Guest') }}</span></span>
                            </td>
                            <td data-label="{{ __('Action') }}">{{ $log->description() }}</td>
                            <td data-label="{{ __('Target') }}"><span class="truncate" style="display:block;max-width:280px">{{ $log->target ?? '—' }}</span></td>
                            <td data-label="{{ __('Date') }}" class="nowrap muted" title="{{ $log->created_at }}">{{ $log->created_at->translatedFormat('j M Y, H:i') }}</td>
                            <td data-label="{{ __('IP') }}" class="num muted">{{ $log->ip ?? '—' }}</td>
                            <td data-label="{{ __('Status') }}"><x-admin.badge :status="$log->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">{{ icon('clock-history') }}<h3>{{ __('No activity found') }}</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
@endsection
