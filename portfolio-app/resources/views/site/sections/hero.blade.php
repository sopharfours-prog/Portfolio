@php
    $hero = $settings['hero'] ?? [];
    $image = media_url($hero['image'] ?? null);
    $url = fn ($u) => str_starts_with((string) $u, '#') ? route('home').$u : $u;
@endphp
<section class="hero" id="home" data-section>
    <div class="container-xl hero__grid">
        <div class="hero__content">
            @if ($label = tr($hero['label'] ?? null))
                <p class="eyebrow hero__label" data-hero-item style="--i: 0"><span class="status-dot" aria-hidden="true"></span><span>{{ $label }}</span></p>
            @endif
            <h1 class="hero__title">
                <span class="hero__greeting" data-hero-item style="--i: 1">{{ tr($hero['greeting'] ?? null) }}</span>
                <span class="hero__name" data-hero-item style="--i: 2">{{ $hero['name'] ?? 'SOPHAR' }}</span>
                <span class="hero__role" data-hero-item style="--i: 3">
                    <span>{{ tr($hero['title'] ?? null) }}</span>
                    @if ($subtitle = tr($hero['subtitle'] ?? null))<span class="hero__role-alt">{{ $subtitle }}</span>@endif
                </span>
            </h1>
            <p class="hero__intro" data-hero-item style="--i: 4">{{ tr($hero['description'] ?? null) }}</p>
            <div class="hero__actions" data-hero-item style="--i: 5">
                @if ($primary = tr($hero['primary_label'] ?? null))
                    <a class="btn-x btn-x--solid" href="{{ $url($hero['primary_url'] ?? '#projects') }}"><span>{{ $primary }}</span>{{ icon('arrow-right') }}</a>
                @endif
                @if ($secondary = tr($hero['secondary_label'] ?? null))
                    <a class="btn-x btn-x--outline" href="{{ $url($hero['secondary_url'] ?? '#contact') }}"><span>{{ $secondary }}</span></a>
                @endif
                @if (\App\Support\CvFile::availableOnSite())
                    <a class="btn-x btn-x--outline" href="{{ route('cv.download', app()->getLocale()) }}" download>{{ icon('download') }}<span>{{ __('site.hero.downloadCv') }}</span></a>
                @endif
            </div>
        </div>

        <div class="hero__media" data-hero-item style="--i: 6">
            <figure class="hero__portrait {{ $image ? '' : 'hero__portrait--empty' }}" data-parallax>
                @if ($image)
                    <img src="{{ $image }}" width="1000" height="1250" fetchpriority="high" decoding="async" alt="{{ __('site.hero.imageAlt') }}">
                @else
                    {{ icon('person') }}
                @endif
            </figure>
            @if ($badge = tr($hero['badge'] ?? null))<p class="hero__badge"><span>{{ $badge }}</span></p>@endif
            @if ($location = tr($hero['location'] ?? null))
                <p class="hero__caption"><span class="hero__caption-label">{{ __('site.hero.based') }}</span><span class="hero__caption-value">{{ $location }}</span></p>
            @endif
        </div>
    </div>

</section>
