@extends('admin.layouts.app')
@section('title', __('Users'))

@section('content')
    <div class="page-head">
        <div><h1>{{ __('Users') }}</h1><p>{{ __('People who can sign in to this admin.') }}</p></div>
        <div class="page-head__actions"><a class="btn btn--primary" href="{{ route('admin.users.create') }}">{{ icon('plus-lg') }}{{ __('Add User') }}</a></div>
    </div>
    <div class="card card--flush">
        @include('admin.partials.filters', [
            'search' => __('Search users...'),
            'selects' => ['role' => [__('Role'), ['super_admin' => __('Super Admin'), 'editor' => __('Editor')]]],
            'sorts' => ['name' => __('Name'), 'newest' => __('Newest'), 'last_login' => __('Last login')],
        ])
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>{{ __('User') }}</th><th>{{ __('Role') }}</th><th>{{ __('Last login') }}</th><th>{{ __('Created') }}</th><th class="col-actions">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td data-primary>
                                <a class="cell-media" href="{{ route('admin.users.edit', $u) }}">
                                    @if ($u->avatarUrl())<img class="avatar avatar--lg" src="{{ $u->avatarUrl() }}" alt="">@else<span class="avatar avatar--lg avatar--soft">{{ $u->initials() }}</span>@endif
                                    <span><span class="table__title">{{ $u->name }}@if ($u->is(auth()->user())) <span class="badge badge--plain">{{ __('You') }}</span>@endif</span><span class="table__sub" style="display:block">{{ $u->email }}</span></span>
                                </a>
                            </td>
                            <td data-label="{{ __('Role') }}"><span class="badge badge--plain">{{ $u->roleLabel() }}</span></td>
                            <td data-label="{{ __('Last login') }}" class="muted">{{ $u->last_login_at?->diffForHumans() ?? '—' }}</td>
                            <td data-label="{{ __('Created') }}" class="muted nowrap">{{ $u->created_at->translatedFormat('j M Y') }}</td>
                            <td class="col-actions">
                                @if ($u->is(auth()->user()))
                                    <a class="btn btn--ghost btn--sm" href="{{ route('admin.users.edit', $u) }}">{{ icon('pencil') }}<span>{{ __('Edit') }}</span></a>
                                @else
                                    @include('admin.partials.row-actions', ['edit' => route('admin.users.edit', $u), 'delete' => route('admin.users.destroy', $u), 'confirmTitle' => __('Delete User?'), 'name' => $u->name])
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty">{{ icon('people') }}<h3>{{ __('No users found') }}</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>
@endsection
