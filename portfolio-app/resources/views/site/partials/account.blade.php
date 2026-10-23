{{--
    Account controls in the site header. Shows whoever is signed in: a visitor account (guard "member")
    or an administrator (guard "web"), who also gets a link to the dashboard.
      variant "header": avatar menu when signed in, "Log in" + "Sign up" for guests
      variant "mobile": full-width buttons for the mobile menu
--}}
@php
    $member = auth('member')->user();
    $admin = auth('web')->user();
    $account = $member ?? $admin;
    // The admin Dashboard link is only for an administrator; while a member is signed in the menu is theirs alone
    $showDashboard = $admin && ! $member;
    $logoutRoute = $member ? route('member.logout') : ($admin ? route('admin.logout') : null);
    $variant = $variant ?? 'header';
@endphp
@if ($variant === 'mobile')
    <div class="account-mobile">
        @if ($account)
            <p class="account-mobile__who"><span class="avatar-x" aria-hidden="true">{{ $account->initials() }}</span><span><small>{{ __('site.auth.signedInAs') }}</small>{{ $account->name }}</span></p>
            @if ($member)
                <a class="btn-x btn-x--outline" href="{{ route('member.account') }}">{{ icon('person-circle') }}{{ __('site.account.title') }}</a>
            @endif
            @if ($showDashboard)
                <a class="btn-x btn-x--solid" href="{{ route('admin.dashboard') }}">{{ icon('speedometer2') }}{{ __('site.auth.dashboard') }}</a>
            @endif
            <form method="POST" action="{{ $logoutRoute }}">
                @csrf
                <input type="hidden" name="to" value="site">
                <button type="submit" class="btn-x btn-x--outline btn-x--block">{{ icon('box-arrow-right') }}{{ __('site.auth.logout') }}</button>
            </form>
        @else
            <a class="btn-x btn-x--outline" href="{{ route('member.login') }}">{{ __('site.auth.login') }}</a>
            <a class="btn-x btn-x--solid" href="{{ route('member.register') }}">{{ __('site.auth.register') }}</a>
        @endif
    </div>
@elseif ($account)
    <div class="dropdown-x account-menu" data-dropdown>
        <button type="button" class="account-menu__toggle" data-dropdown-toggle aria-expanded="false" aria-controls="account-menu"
                aria-label="{{ __('site.auth.account') }}" title="{{ $account->name }}">
            <span class="avatar-x" aria-hidden="true">{{ $account->initials() }}</span>
            {{ icon('chevron-down', 'dropdown-x__chevron') }}
        </button>
        <div class="dropdown-x__panel dropdown-x__panel--account" id="account-menu" data-dropdown-panel>
            <p class="account-menu__who">
                <small>{{ __('site.auth.signedInAs') }}</small><strong>{{ $account->name }}</strong>
                <span>{{ $account->email }}@if (! $member && $admin) · {{ $admin->roleLabel() }}@endif</span>
            </p>
            @if ($member)
                <a class="dropdown-x__item" href="{{ route('member.account') }}">{{ icon('person-circle') }}<span class="dropdown-x__label">{{ __('site.account.title') }}</span></a>
            @endif
            @if ($showDashboard)
                <a class="dropdown-x__item" href="{{ route('admin.dashboard') }}">{{ icon('speedometer2') }}<span class="dropdown-x__label">{{ __('site.auth.dashboard') }}</span></a>
            @endif
            <form method="POST" action="{{ $logoutRoute }}">
                @csrf
                <input type="hidden" name="to" value="site">
                <button type="submit" class="dropdown-x__item">{{ icon('box-arrow-right') }}<span class="dropdown-x__label">{{ __('site.auth.logout') }}</span></button>
            </form>
        </div>
    </div>
@else
    <div class="nav-auth">
        <a class="nav-auth__login" href="{{ route('member.login') }}">{{ icon('person') }}<span>{{ __('site.auth.login') }}</span></a>
        <a class="nav-auth__register" href="{{ route('member.register') }}">{{ __('site.auth.register') }}</a>
    </div>
@endif
