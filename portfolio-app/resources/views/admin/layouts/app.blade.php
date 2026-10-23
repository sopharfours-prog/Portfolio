@extends('admin.layouts.base')

@php
    $user = auth()->user();
    $is = fn (string ...$patterns) => request()->routeIs(...$patterns) ? 'is-active' : '';
    $logo = media_url(setting('general.logo'));
    $siteName = setting('general.site_name', 'SOPHAR');
    $groups = [
        'main' => [__('Main'), [
            ['admin.dashboard', 'grid-1x2', __('Dashboard'), ['admin.dashboard'], null, false],
        ]],
        'content' => [__('Content'), [
            ['admin.website.index', 'window', __('Website'), ['admin.website.*'], null, true],
            ['admin.cv.edit', 'file-earmark-person', __('CV'), ['admin.cv.*'], null, true],
            ['admin.projects.index', 'collection', __('Projects'), ['admin.projects.*'], null, false],
            ['admin.services.index', 'briefcase', __('Services'), ['admin.services.*'], null, false],
            ['admin.skills.index', 'lightning-charge', __('Skills'), ['admin.skills.*'], null, false],
            ['admin.experience.index', 'signpost-split', __('Experience'), ['admin.experience.*'], null, false],
            ['admin.education.index', 'mortarboard', __('Education'), ['admin.education.*'], null, false],
            ['admin.certificates.index', 'patch-check', __('Certificates'), ['admin.certificates.*'], null, false],
            ['admin.testimonials.index', 'chat-quote', __('Testimonials'), ['admin.testimonials.*'], null, false],
        ]],
        'communication' => [__('Communication'), [
            ['admin.messages.index', 'envelope', __('Messages'), ['admin.messages.*'], $unreadMessages ?: null, false],
            ['admin.members.index', 'person-badge', __('Members'), ['admin.members.*'], null, true],
        ]],
        'system' => [__('System'), [
            ['admin.media.index', 'images', __('Media Library'), ['admin.media.*'], null, false],
            ['admin.users.index', 'people', __('Users'), ['admin.users.*'], null, true],
            ['admin.activity.index', 'clock-history', __('Activity Log'), ['admin.activity.*'], null, true],
            ['admin.settings.edit', 'gear', __('Settings'), ['admin.settings.*'], null, true],
        ]],
    ];
@endphp

@section('body')
<div class="app" data-app>
    <aside class="sidebar" id="sidebar" aria-label="{{ __('Main navigation') }}">
        <a class="sidebar__brand" href="{{ route('admin.dashboard') }}">
            <span class="sidebar__logo">@if ($logo)<img src="{{ $logo }}" alt="">@else{{ mb_substr($siteName, 0, 1) }}@endif</span>
            <span class="sidebar__name"><strong>{{ $siteName }}</strong><span>ADMIN</span></span>
        </a>

        <nav class="sidebar__nav">
            {{-- Each group can be folded (admin/js/ui.js remembers it); the group of the current page always opens --}}
            @foreach ($groups as $key => [$label, $items])
                @php
                    $visible = collect($items)->filter(fn ($item) => ! $item[5] || $user->isSuperAdmin());
                    $hasActive = $visible->contains(fn ($item) => $is(...$item[3]));
                    $groupCount = $visible->sum(fn ($item) => (int) $item[4]);
                @endphp
                @continue($visible->isEmpty())
                <div class="nav-group" data-nav-group="{{ $key }}" @if ($hasActive) data-has-active @endif>
                    <button type="button" class="nav-group__label nav-group__toggle" data-nav-group-toggle aria-expanded="true" aria-controls="nav-group-{{ $key }}">
                        <span>{{ $label }}</span>
                        @if ($groupCount)<span class="nav-group__count">{{ $groupCount }}</span>@endif
                        {{ icon('chevron-down', 'nav-group__chevron') }}
                    </button>
                    <div class="nav-group__items" id="nav-group-{{ $key }}">
                        <div class="nav-group__inner">
                            @foreach ($visible as [$route, $icon, $text, $active, $count])
                                <a class="nav-item {{ $is(...$active) }}" href="{{ route($route) }}" title="{{ $text }}" @if ($is(...$active)) aria-current="page" @endif>
                                    {{ icon($icon) }}<span class="nav-item__label">{{ $text }}</span>
                                    @if ($count)<span class="nav-item__count">{{ $count }}</span>@endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="sidebar__foot">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="nav-item" title="{{ __('Logout') }}">{{ icon('box-arrow-right') }}<span class="nav-item__label">{{ __('Logout') }}</span></button>
            </form>
        </div>
    </aside>
    <div class="backdrop" data-sidebar-close></div>

    <div class="app__main">
        <header class="topbar">
            <button type="button" class="icon-btn" data-sidebar-toggle aria-controls="sidebar" aria-expanded="false" aria-label="{{ __('Toggle menu') }}">{{ icon('list') }}</button>
            <span class="topbar__title">@yield('title', __('Dashboard'))</span>
            <span class="topbar__spacer"></span>

            <div class="topbar__actions">
                <a class="icon-btn" href="{{ route('home') }}" target="_blank" rel="noopener" aria-label="{{ __('View website') }}" data-tip="{{ __('View website') }}" data-tip-pos="left">{{ icon('box-arrow-up-right') }}</a>

                <button type="button" class="icon-btn" data-theme-cycle aria-label="{{ __('Change theme') }}">
                    <span data-theme-icon="light" hidden>{{ icon('sun') }}</span>
                    <span data-theme-icon="dark" hidden>{{ icon('moon-stars') }}</span>
                    <span data-theme-icon="system">{{ icon('circle-half') }}</span>
                </button>

                <div class="dropdown" data-dropdown data-notifications data-url="{{ route('admin.notifications.index') }}">
                    <button type="button" class="icon-btn" data-dropdown-toggle aria-haspopup="true" aria-expanded="false" aria-label="{{ __('Notifications') }}">
                        {{ icon('bell') }}
                        <span class="icon-btn__dot" data-notif-dot @if (! $unreadNotifications) hidden @endif></span>
                    </button>
                    <div class="dropdown__menu notif">
                        <div class="notif__head">
                            <h3>{{ __('Notifications') }}</h3>
                            <button type="button" class="btn btn--ghost btn--sm" data-notif-action="{{ route('admin.notifications.read-all') }}">{{ __('Mark all as read') }}</button>
                        </div>
                        <div class="notif__list" data-notif-list>
                            @forelse ($notifications as $n)
                                <button type="button" class="notif__item {{ $n->read_at ? 'is-read' : '' }}" data-notif-read="{{ route('admin.notifications.read', $n) }}" data-url="{{ $n->url }}">
                                    <span class="notif__dot"></span>
                                    <span>
                                        <span class="notif__title">{{ __($n->title) }}</span>
                                        @if ($n->body)<span class="notif__body truncate" style="display:block">{{ $n->body }}</span>@endif
                                        <span class="notif__time" style="display:block">{{ $n->created_at->diffForHumans() }}</span>
                                    </span>
                                </button>
                            @empty
                                <p class="empty" style="padding:32px 16px">{{ __('No notifications') }}</p>
                            @endforelse
                        </div>
                        <div class="notif__foot">
                            <span></span>
                            <button type="button" class="btn btn--ghost btn--sm" data-notif-action="{{ route('admin.notifications.clear') }}" data-notif-clear>{{ __('Clear') }}</button>
                        </div>
                    </div>
                </div>

                {{-- Same flag picker as the public website header (site/partials/lang-switch) --}}
                @php $currentLocale = app()->getLocale(); @endphp
                <div class="dropdown lang-menu" data-dropdown>
                    <button type="button" class="lang-menu__toggle" data-dropdown-toggle aria-haspopup="true" aria-expanded="false"
                            aria-label="{{ __('Language') }}: {{ __('site.languages.'.$currentLocale) }}" data-tip="{{ __('Language') }}">
                        @include('site.partials.flag', ['code' => $currentLocale])
                        <span class="lang-menu__code">{{ locales()[$currentLocale] ?? strtoupper($currentLocale) }}</span>
                        {{ icon('chevron-down', 'lang-menu__chevron') }}
                    </button>
                    <div class="dropdown__menu lang-menu__list">
                        @foreach (locales() as $code => $label)
                            <form method="POST" action="{{ route('admin.locale', $code) }}">@csrf
                                <button type="submit" class="dropdown__item lang-menu__item" lang="{{ $code }}" @if ($currentLocale === $code) aria-current="true" @endif>
                                    @include('site.partials.flag', ['code' => $code])
                                    <span class="lang-menu__label">{{ __('site.languages.'.$code) }}<small>{{ $label }}</small></span>
                                    {{ icon('check2', 'lang-menu__check') }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

                <div class="dropdown" data-dropdown>
                    <button type="button" class="profile-btn" data-dropdown-toggle aria-haspopup="true" aria-expanded="false">
                        @if ($user->avatarUrl())<img class="avatar" src="{{ $user->avatarUrl() }}" alt="">@else<span class="avatar">{{ $user->initials() }}</span>@endif
                        <span class="profile-btn__text"><strong>{{ $user->name }}</strong><span>{{ $user->roleLabel() }}</span></span>
                        {{ icon('chevron-down') }}
                    </button>
                    <div class="dropdown__menu">
                        <a class="dropdown__item" href="{{ route('admin.profile.edit') }}">{{ icon('person') }}{{ __('Profile') }}</a>
                        <a class="dropdown__item" href="{{ route('admin.profile.edit') }}#password">{{ icon('shield-lock') }}{{ __('Account Settings') }}</a>
                        <div class="dropdown__sep"></div>
                        <form method="POST" action="{{ route('admin.logout') }}">@csrf
                            <button type="submit" class="dropdown__item dropdown__item--danger">{{ icon('box-arrow-right') }}{{ __('Logout') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="content" id="main">
            @yield('content')
        </main>
    </div>
</div>

{{-- Shared confirmation dialog, driven by [data-confirm] forms --}}
<dialog class="modal" data-confirm-modal aria-labelledby="confirm-title">
    <div class="modal__head"><h2 id="confirm-title" data-confirm-title></h2></div>
    <div class="modal__body"><p data-confirm-text></p><p style="margin-top:6px">{{ __('This action cannot be undone.') }}</p></div>
    <div class="modal__foot">
        <button type="button" class="btn btn--secondary" data-confirm-cancel>{{ __('Cancel') }}</button>
        <button type="button" class="btn btn--danger" data-confirm-ok>{{ __('Delete') }}</button>
    </div>
</dialog>

@include('admin.partials.media-picker')
@stack('modals')

<script id="admin-i18n" type="application/json">{!! json_encode([
    'error' => __('Something went wrong'), 'copied' => __('URL copied'), 'orderSaved' => __('Order saved'),
    'cover' => __('Cover'), 'setCover' => __('Set as cover'), 'remove' => __('Remove'), 'uploading' => __('Uploading…'),
    'noNotifications' => __('No notifications'), 'noResults' => __('No results'), 'loadMore' => __('Load more'),
], JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
