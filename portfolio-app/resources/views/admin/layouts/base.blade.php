{{-- Shared <head>: theme is applied before first paint (saved choice → system) --}}
<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', __('Dashboard')) · {{ setting('general.site_name', 'SOPHAR') }} {{ __('Admin') }}</title>
    @php $adminFavicon = \App\Support\RoundedFavicon::url(); @endphp
    <link rel="icon" href="{{ $adminFavicon ?: asset('favicon.ico') }}" sizes="any" @if ($adminFavicon) type="image/png" @endif>
    <script>
        (function () {
            try {
                var mode = localStorage.getItem('admin-theme') || 'system';
                var dark = mode === 'dark' || (mode === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme-mode', mode);
            } catch (e) {}
        })();
    </script>
    @include('site.partials.fonts')
    @vite('resources/admin/js/admin.js')
</head>
<body class="@yield('body-class')">
    @yield('body')

    <div class="toasts" data-toasts aria-live="polite"
         @if (session('toast')) data-flash="{{ json_encode(['type' => session('toast')[0], 'message' => session('toast')[1]]) }}" @endif></div>
</body>
</html>
