{{-- About: portrait left, heading / subtitle / introduction / statistics / button right. Content: Admin → Website → About.
     Used at the top of the About page (h1) and as the About section of the home page (h2). --}}
@php
    $about = $settings['about'] ?? [];
    $image = media_url($about['image'] ?? ($settings['hero']['image'] ?? null));
    $title = tr($about['page_title'] ?? null) ?: __('site.about.label');
    $accent = tr($about['page_title_accent'] ?? null);
    $cta = tr($about['cta_label'] ?? null);
    $ctaUrl = $about['cta_url'] ?? '#contact';
    $headingTag = $headingTag ?? 'h1';
    // Decorative network: node coordinates and the pairs joined by a line
    $nodes = [[60, 90], [190, 40], [330, 110], [470, 50], [560, 170], [120, 230], [270, 250], [420, 220], [540, 330], [70, 380], [210, 400], [360, 370], [480, 460], [140, 520], [300, 540], [440, 570]];
    $links = [[0, 1], [1, 2], [2, 3], [3, 4], [0, 5], [1, 6], [2, 6], [2, 7], [4, 7], [5, 6], [6, 7], [7, 8], [5, 9], [6, 10], [6, 11], [8, 11], [9, 10], [10, 11], [11, 12], [8, 12], [9, 13], [10, 14], [13, 14], [14, 15], [12, 15], [11, 14]];
@endphp
<section class="about-hero {{ $class ?? '' }}" id="about" data-section>
    <svg class="about-hero__pattern" viewBox="0 0 620 620" aria-hidden="true" focusable="false">
        <g fill="none" stroke="currentColor" stroke-width="1.2">
            @foreach ($links as [$a, $b])<line x1="{{ $nodes[$a][0] }}" y1="{{ $nodes[$a][1] }}" x2="{{ $nodes[$b][0] }}" y2="{{ $nodes[$b][1] }}"/>@endforeach
            @foreach ([2, 6, 11] as $hex)<circle cx="{{ $nodes[$hex][0] }}" cy="{{ $nodes[$hex][1] }}" r="18"/>@endforeach
        </g>
        <g fill="currentColor">@foreach ($nodes as $i => [$x, $y])<circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $i % 3 === 0 ? 6 : 4 }}"/>@endforeach</g>
    </svg>

    <div class="about-hero__inner">
        <figure class="about-hero__frame">
            @if ($image)
                <img src="{{ $image }}" width="760" height="1400" @if ($headingTag === 'h1') fetchpriority="high" @else loading="lazy" @endif decoding="async" alt="{{ __('site.hero.imageAlt') }}">
            @else
                {{ icon('person') }}
            @endif
        </figure>

        <div class="about-hero__content">
            <{{ $headingTag }} class="about-hero__title"><span>{{ $title }}</span>@if ($accent)@if (app()->getLocale() !== 'lo') @endif<span class="about-hero__accent">{{ $accent }}</span>@endif</{{ $headingTag }}>
            @if ($subtitle = tr($about['subtitle'] ?? null))<p class="about-hero__subtitle">{{ $subtitle }}</p>@endif
            <div class="about-hero__text">
                <p>{{ tr($about['introduction'] ?? null) }}</p>
                @if ($bio = tr($about['biography'] ?? null))<p>{{ $bio }}</p>@endif
            </div>

            @if (! empty($about['stats']))
                <dl class="about-hero__stats">
                    @foreach ($about['stats'] as $stat)
                        <div class="about-stat reveal" style="--delay: {{ 450 + $loop->index * 90 }}ms">
                            <dt>{{ tr($stat['label']) }}</dt>
                            <dd><span data-count="{{ (int) $stat['value'] }}">{{ (int) $stat['value'] }}</span>{{ $stat['suffix'] ?? '' }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif

            @if ($cta)
                <p class="about-hero__actions"><a class="btn-cyan" href="{{ $ctaUrl }}" aria-label="{{ $cta }}">{{ $cta }}</a></p>
            @endif
        </div>
    </div>
</section>
