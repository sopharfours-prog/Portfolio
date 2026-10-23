{{--
    Self-hosted web fonts (SIL Open Font License), shared by the website and the admin.
    One variable file per family covers every weight (400–700), so a page loads at most three fonts.
    The fonts the page needs are preloaded so they arrive before the first paint; each extra font
    that arrives later would make the browser lay out the whole page again (slow for Lao text).
    Usage: @include('site.partials.fonts', ['signature' => true])
--}}
@php $signature = $signature ?? false; @endphp
<link rel="preload" href="{{ asset('fonts/inter-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
@if (app()->getLocale() === 'lo')<link rel="preload" href="{{ asset('fonts/noto-sans-lao.woff2') }}" as="font" type="font/woff2" crossorigin>@endif
@if ($signature)<link rel="preload" href="{{ asset('fonts/mrs-saint-delafield.woff2') }}" as="font" type="font/woff2" crossorigin>@endif
<style>
    @font-face { font-family: 'Inter'; font-style: normal; font-weight: 400 700; font-display: swap; src: url('{{ asset('fonts/inter-latin.woff2') }}') format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD; }
    @font-face { font-family: 'Inter'; font-style: normal; font-weight: 400 700; font-display: swap; src: url('{{ asset('fonts/inter-latin-ext.woff2') }}') format('woff2');
        unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C4, U+2113, U+2C60-2C7F, U+A720-A7FF; }
    @font-face { font-family: 'Noto Sans Lao'; font-style: normal; font-weight: 400 700; font-display: swap; src: url('{{ asset('fonts/noto-sans-lao.woff2') }}') format('woff2');
        unicode-range: U+0E81-0EDF, U+200C-200D, U+25CC; }
@if ($signature)
    @font-face { font-family: 'Mrs Saint Delafield'; font-style: normal; font-weight: 400; font-display: swap; src: url('{{ asset('fonts/mrs-saint-delafield.woff2') }}') format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD; }
@endif
</style>
