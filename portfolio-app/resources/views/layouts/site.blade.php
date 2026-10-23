@php
    $locale = app()->getLocale();
    $title = trim($__env->yieldContent('title')) ?: tr($settings['seo']['meta_title'] ?? null) ?: config('app.name');
    $description = trim($__env->yieldContent('description')) ?: tr($settings['seo']['meta_description'] ?? null);
    $ogImage = media_url(trim($__env->yieldContent('og_image')) ?: ($settings['seo']['og_image'] ?? null));
    $theme = $settings['appearance']['default_theme'] ?? 'system';
    $siteName = $settings['general']['site_name'] ?? 'SOPHAR';
@endphp
<!doctype html>
<html lang="{{ $locale }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    @if (! empty($settings['seo']['keywords']))<meta name="keywords" content="{{ $settings['seo']['keywords'] }}">@endif
    <meta name="author" content="{{ $siteName }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#080b12" media="(prefers-color-scheme: dark)">
    <link rel="manifest" href="{{ route('manifest') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="color-scheme" content="light dark">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ $locale === 'lo' ? 'lo_LA' : 'en_US' }}">
    @if ($ogImage)<meta property="og:image" content="{{ url($ogImage) }}">@endif
    <meta name="twitter:card" content="summary_large_image">

    {{-- Custom favicon: shown with rounded corners (RoundedFavicon); otherwise the default rounded "S." icon --}}
    @php $favicon = \App\Support\RoundedFavicon::url() ?? media_url($settings['general']['favicon'] ?? null); @endphp
    <link rel="icon" href="{{ $favicon ?: asset('favicon.ico') }}" sizes="any" @if ($favicon && str_contains($favicon, 'favicon-rounded')) type="image/png" @endif>
    @unless ($favicon)<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">@endunless
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    {{-- Theme before first paint (no flash): saved choice → site default → OS --}}
    <script>
        (function () {
            var d = document.documentElement, fallback = @json($theme);
            d.classList.add('js');
            try {
                var t = localStorage.getItem('theme');
                if (t !== 'light' && t !== 'dark') t = fallback !== 'system' ? fallback : (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                d.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>

    @include('site.partials.fonts', ['signature' => true])

    @vite('resources/site/js/main.js')
    @if ($buttonColors = \App\Support\ButtonColors::css())<style>{!! $buttonColors !!}</style>@endif
    @stack('head')
</head>
<body id="top" data-page="{{ $page ?? 'home' }}">
    @include('site.partials.header')

    @if (session('member_status'))
        <div class="toast-x" role="status" data-toast>{{ icon('check2-circle') }}<span>{{ session('member_status') }}</span></div>
    @endif

    <main id="main" tabindex="-1">
        @yield('content')
    </main>

    @include('site.partials.footer')

    {{-- Strings the site JavaScript needs, in the current language --}}
    @php $i18n = ['nav' => __('site.nav'), 'contact' => __('site.contact'), 'testimonials' => __('site.testimonials'), 'projects' => __('site.projects')]; @endphp
    <script id="i18n" type="application/json">{!! json_encode($i18n, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>
</body>
</html>
