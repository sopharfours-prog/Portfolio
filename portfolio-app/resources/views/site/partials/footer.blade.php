{{--
    Slim footer: "<footer text> <name> 💻 © <year> <live clock>" and a small log-in pill for guests.
    Footer text: Admin → Website → Contact Information. The clock is updated by site/js/clock.js.
--}}
@php
    $owner = \Illuminate\Support\Str::title(mb_strtolower($settings['general']['site_name'] ?? 'SOPHAR'));
    $signedIn = auth('member')->check() || auth('web')->check();
@endphp
<footer class="site-footer">
    <div class="container-xl footer-bar">
        <p class="footer-bar__line">
            <span>{{ tr($settings['contact']['footer_text'] ?? null) ?: __('site.footer.madeBy') }}</span>
            <a class="footer-bar__name" href="{{ route('home') }}">{{ $owner }}</a>
            <span aria-hidden="true">💻</span>
            <span>© {{ now()->year }}</span>
            <time class="footer-bar__clock" data-clock datetime="{{ now()->toIso8601String() }}">{{ now()->format('g:i:s A') }}</time>
            @unless ($signedIn)
                <a class="footer-bar__login" href="{{ route('member.login') }}">{{ icon('box-arrow-in-right') }}{{ __('site.auth.login') }}</a>
            @endunless
        </p>
    </div>
</footer>

<button type="button" class="back-to-top" data-back-to-top aria-label="{{ __('site.nav.backToTop') }}" title="{{ __('site.nav.backToTop') }}">
    {{ icon('arrow-up') }}
</button>
