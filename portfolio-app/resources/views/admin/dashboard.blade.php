@extends('admin.layouts.app')
@section('title', __('Dashboard'))

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? __('Good morning') : ($hour < 18 ? __('Good afternoon') : __('Good evening'));
    $delta = fn (?float $v) => is_null($v) ? null : ($v > 0 ? '+' : '').$v.'%';
@endphp

@section('content')
    <div class="page-head greeting">
        <div>
            <h1>{{ $greeting }}, {{ auth()->user()->name }} 👋</h1>
            <p>{{ __("Here's what's happening with your portfolio today.") }}</p>
        </div>
        <div class="page-head__actions">
            <a class="btn btn--primary" href="{{ route('admin.projects.create') }}">{{ icon('plus-lg') }}{{ __('Add Project') }}</a>
        </div>
    </div>

    <div class="grid grid--stats">
        <div class="card stat">
            <p class="stat__label">{{ __('Total Projects') }}</p>
            <div class="stat__row"><span class="stat__value">{{ sprintf('%02d', $stats['projects']) }}</span><span class="stat__icon">{{ icon('collection') }}</span></div>
            <p class="stat__meta {{ ($stats['projectsGrowth'] ?? 0) > 0 ? 'is-up' : '' }}">
                @if (! is_null($stats['projectsGrowth'])){{ $delta($stats['projectsGrowth']) }} · @endif{{ __(':count published', ['count' => $stats['projectsPublished']]) }}
            </p>
        </div>
        <div class="card stat">
            <p class="stat__label">{{ __('Services') }}</p>
            <div class="stat__row"><span class="stat__value">{{ sprintf('%02d', $stats['services']) }}</span><span class="stat__icon">{{ icon('briefcase') }}</span></div>
            <p class="stat__meta is-up">{{ __(':count active', ['count' => $stats['servicesActive']]) }}</p>
        </div>
        <div class="card stat">
            <p class="stat__label">{{ __('Messages') }}</p>
            <div class="stat__row"><span class="stat__value">{{ sprintf('%02d', $stats['messages']) }}</span><span class="stat__icon">{{ icon('envelope') }}</span></div>
            <p class="stat__meta {{ $stats['messagesNew'] ? 'is-up' : '' }}">{{ __(':count new', ['count' => $stats['messagesNew']]) }}</p>
        </div>
        <div class="card stat">
            <p class="stat__label">{{ __('Website Views') }} <span class="muted" style="font-weight:400;letter-spacing:0;text-transform:none">· {{ __('30 days') }}</span></p>
            <div class="stat__row">
                <span class="stat__value">{{ number_format($stats['views']) }}</span>
                <canvas class="stat__spark" width="96" height="40" data-spark="{{ json_encode($stats['viewsSpark']) }}" aria-hidden="true"></canvas>
            </div>
            <p class="stat__meta {{ ($stats['viewsGrowth'] ?? 0) > 0 ? 'is-up' : (($stats['viewsGrowth'] ?? 0) < 0 ? 'is-down' : '') }}">{{ $delta($stats['viewsGrowth']) ?? '—' }} {{ __('vs previous 30 days') }}</p>
        </div>
    </div>

    <div class="grid grid--main mt">
        <section class="card" data-overview="{{ route('admin.analytics') }}">
            <div class="card__head">
                <div><h2>{{ __('Website Overview') }}</h2><p>{{ __('Traffic on your public portfolio') }}</p></div>
                <div class="tabs" role="group" aria-label="{{ __('Date range') }}">
                    @foreach ([7 => __('7 Days'), 30 => __('30 Days'), 90 => __('90 Days'), 365 => __('1 Year')] as $days => $text)
                        <button type="button" class="tab {{ $days === 7 ? 'is-active' : '' }}" data-range="{{ $days }}">{{ $text }}</button>
                    @endforeach
                </div>
            </div>
            <div class="card__body">
                <div class="chart-legend">
                    @foreach (['visitors' => __('Visitors'), 'page_views' => __('Page Views'), 'project_views' => __('Projects Viewed'), 'contacts' => __('Contact Requests')] as $key => $text)
                        <button type="button" class="metric {{ $loop->first ? 'is-active' : '' }}" data-metric="{{ $key }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                            <span class="metric__label">{{ $text }}</span>
                            <span class="metric__value" style="display:block" data-value>—</span>
                            <span class="metric__delta" data-delta></span>
                        </button>
                    @endforeach
                </div>
                <div class="chart-box"><canvas role="img" aria-label="{{ __('Website Overview') }}"></canvas></div>
            </div>
        </section>

        <section class="card card--flush">
            <div class="card__head" style="padding-bottom:16px">
                <h2>{{ __('Recent Messages') }}</h2>
                <a class="btn btn--ghost btn--sm" href="{{ route('admin.messages.index') }}">{{ __('View all') }}</a>
            </div>
            <div class="card__body">
                <div class="list">
                    @forelse ($recentMessages as $message)
                        <a class="list__item {{ $message->isUnread() ? 'is-unread' : '' }}" href="{{ route('admin.messages.index', ['message' => $message->id]) }}">
                            <span class="avatar avatar--soft">{{ $message->initials() }}</span>
                            <span class="list__main">
                                <span class="list__title truncate" style="display:block">{{ $message->name }}</span>
                                <span class="list__sub truncate" style="display:block">{{ $message->subject }}</span>
                            </span>
                            <span class="list__aside">{{ $message->created_at->diffForHumans(short: true) }}</span>
                        </a>
                    @empty
                        <div class="empty">{{ icon('envelope-open') }}<p>{{ __('No messages yet') }}</p></div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <div class="grid grid--main mt">
        <section class="card card--flush">
            <div class="card__head" style="padding-bottom:16px">
                <h2>{{ __('Recent Projects') }}</h2>
                <a class="btn btn--secondary btn--sm" href="{{ route('admin.projects.index') }}">{{ __('View All Projects') }}</a>
            </div>
            <div class="card__body table-wrap">
                <table class="table">
                    <thead><tr><th>{{ __('Project') }}</th><th>{{ __('Category') }}</th><th>{{ __('Status') }}</th><th>{{ __('Views') }}</th></tr></thead>
                    <tbody>
                        @forelse ($recentProjects as $project)
                            <tr>
                                <td data-primary>
                                    <a class="cell-media" href="{{ route('admin.projects.edit', $project) }}">
                                        @if ($project->coverUrl())<img class="thumb" src="{{ $project->coverUrl() }}" alt="" loading="lazy">@else<span class="thumb"></span>@endif
                                        <span class="table__title">{{ $project->title }}</span>
                                    </a>
                                </td>
                                <td data-label="{{ __('Category') }}">{{ __('taxonomy.'.$project->category) }}</td>
                                <td data-label="{{ __('Status') }}"><x-admin.badge :status="$project->status" /></td>
                                <td data-label="{{ __('Views') }}" class="num">{{ number_format($project->views) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty">{{ icon('collection') }}<p>{{ __('No projects yet') }}</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card card--flush">
            <div class="card__head" style="padding-bottom:16px"><div><h2>{{ __('Top Projects') }}</h2><p>{{ __('Most viewed in the last 30 days') }}</p></div></div>
            <div class="card__body">
                <div class="list">
                    @forelse ($topProjects as $row)
                        <a class="list__item" href="{{ route('admin.projects.edit', $row['project']) }}">
                            <span class="list__aside num" style="width:18px">{{ $loop->iteration }}</span>
                            <span class="list__main"><span class="list__title truncate" style="display:block">{{ $row['project']->title }}</span></span>
                            <span class="list__aside num">{{ number_format($row['views']) }} {{ __('views') }}</span>
                        </a>
                    @empty
                        <div class="empty"><p>{{ __('No data yet') }}</p></div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
