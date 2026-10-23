{{--
    Site header, shared by every page (included once from layouts/site.blade.php).
    The items follow the order of the sections on the home page, so the
    active indicator steps through them while scrolling (see site/js/navigation.js).
--}}
@php
    $siteName = $settings['general']['site_name'] ?? 'SOPHAR';
    $home = route('home');
    $nav = ['home', 'about', 'skills', 'services', 'projects', 'experience', 'education', 'contact'];
@endphp
<a class="skip-link" href="#main">{{ __('site.nav.skip') }}</a>
<div class="scroll-progress" aria-hidden="true"><span class="scroll-progress__bar" data-scroll-progress></span></div>

<header class="site-header" data-header>
    <div class="container">
        <a class="brand brand--signature" href="{{ $home }}" aria-label="{{ __('site.nav.homeLink') }}">@if ($logo = media_url($settings['general']['logo'] ?? null))<img class="brand__logo" src="{{ $logo }}" width="40" height="40" alt="" decoding="async">@endif<span class="brand__sign">{{ \Illuminate\Support\Str::title(mb_strtolower($siteName)) }}</span><span class="brand__dot">.</span></a>

        <nav class="main-navigation" aria-label="{{ __('site.nav.primary') }}">
            @foreach ($nav as $key)
                <a class="nav-link" href="{{ $home }}#{{ $key }}" data-nav="{{ $key }}">{{ __('site.nav.'.$key) }}</a>
            @endforeach
        </nav>

        <div class="nav-actions">
            @include('site.partials.lang-switch', ['class' => 'lang-switch--nav'])
            <button type="button" class="icon-btn theme-toggle" data-theme-toggle aria-label="{{ __('site.nav.theme') }}" title="{{ __('site.nav.theme') }}">
                {{ icon('sun', 'theme-toggle__sun') }}{{ icon('moon-stars', 'theme-toggle__moon') }}
            </button>
            @include('site.partials.account')
            <button type="button" class="icon-btn menu-toggle" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" aria-label="{{ __('site.nav.openMenu') }}">
                <span class="menu-toggle__bars" aria-hidden="true"><span></span><span></span></span>
            </button>
        </div>
    </div>
</header>

<div class="mobile-menu" id="mobile-menu" data-mobile-menu inert>
    <div class="mobile-menu__inner container-xl">
        <nav aria-label="{{ __('site.nav.mobile') }}">
            <ul class="mobile-menu__links">
                @foreach ($nav as $key)
                    <li><a href="{{ $home }}#{{ $key }}" data-nav="{{ $key }}">{{ __('site.nav.'.$key) }}</a></li>
                @endforeach
            </ul>
        </nav>
        <div class="mobile-menu__footer">
            @include('site.partials.account', ['variant' => 'mobile'])
            @include('site.partials.lang-switch', ['variant' => 'pills'])
            <ul class="social-list social-list--icons">@include('site.partials.socials')</ul>
        </div>
    </div>
</div>
