{{-- Shown by the service worker (public/sw.js) when a page can't load. Self-contained: no external CSS, JS or fonts. --}}
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="color-scheme" content="light dark">
    <title>{{ __('site.offline.title') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <style>
        :root { --bg: #fbfcfe; --surface: #fff; --text: #0f172a; --muted: #5b6577; --border: #e3e8ef; }
        @media (prefers-color-scheme: dark) { :root { --bg: #080b12; --surface: #111827; --text: #f1f5f9; --muted: #94a3b8; --border: #1d2535; } }
        * { box-sizing: border-box; }
        body {
            display: grid; place-items: center; min-height: 100vh; margin: 0; padding: 1.5rem;
            font-family: system-ui, -apple-system, 'Segoe UI', 'Noto Sans Lao', sans-serif; line-height: 1.6;
            color: var(--text);
            background: radial-gradient(50% 60% at 15% 10%, rgba(0, 175, 196, .16), transparent 70%),
                        radial-gradient(50% 60% at 85% 90%, rgba(155, 92, 255, .14), transparent 70%), var(--bg);
        }
        main { max-width: 420px; padding: 2.5rem 2rem; border: 1px solid var(--border); border-radius: 28px; background: var(--surface); text-align: center; box-shadow: 0 30px 60px -30px rgba(15, 23, 42, .35); }
        .icon { display: inline-grid; place-items: center; width: 64px; height: 64px; margin-bottom: 1.25rem; border-radius: 20px; color: #fff; background: linear-gradient(120deg, #00afc4, #4f6bff 55%, #9b5cff); }
        h1 { margin: 0 0 .5rem; font-size: 1.5rem; line-height: 1.3; }
        p { margin: 0 0 1.75rem; color: var(--muted); }
        button { padding: .8rem 1.75rem; border: 0; border-radius: 999px; font: 600 1rem/1 inherit; color: #fff; cursor: pointer; background: linear-gradient(120deg, #00afc4, #4f6bff 55%, #9b5cff); }
    </style>
</head>
<body>
    <main>
        <span class="icon" aria-hidden="true">
            <svg width="30" height="30" viewBox="0 0 16 16" fill="currentColor"><path d="M10.706 3.294A12.6 12.6 0 0 0 8 3C5.259 3 2.723 3.882.663 5.379a.485.485 0 0 0-.048.736.52.52 0 0 0 .668.05A11.45 11.45 0 0 1 8 4q.946 0 1.852.148zM8 6c-1.905 0-3.68.56-5.166 1.526a.48.48 0 0 0-.063.745.525.525 0 0 0 .652.065 8.45 8.45 0 0 1 3.51-1.27zm2.596 1.404.785-.785q.947.362 1.785.907a.48.48 0 0 1 .063.745.525.525 0 0 1-.652.065 8.5 8.5 0 0 0-1.98-.932zM8 10l.933-.933a6.5 6.5 0 0 1 2.013.637c.285.145.326.524.1.75l-.015.015a.53.53 0 0 1-.611.09A5.5 5.5 0 0 0 8 10m4.905-4.905.747-.747q.886.451 1.685 1.03a.485.485 0 0 1 .047.737.52.52 0 0 1-.668.05 11.5 11.5 0 0 0-1.811-1.07M9.02 11.78c.238.14.236.464.04.66l-.707.706a.5.5 0 0 1-.707 0l-.707-.707c-.195-.195-.197-.518.04-.66A2 2 0 0 1 8 11.5c.374 0 .723.102 1.021.28zm4.355-9.905a.53.53 0 0 1 .75.75l-10.75 10.75a.53.53 0 0 1-.75-.75z"/></svg>
        </span>
        <h1>{{ __('site.offline.title') }}</h1>
        <p>{{ __('site.offline.text') }}</p>
        <button type="button" onclick="location.reload()">{{ __('site.offline.retry') }}</button>
    </main>
    <script>window.addEventListener('online', () => location.reload());</script>
</body>
</html>
