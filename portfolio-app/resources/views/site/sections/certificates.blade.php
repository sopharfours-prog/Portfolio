{{--
    Certificates gallery (Admin → Certificates). Hovering or focusing a certificate darkens it and shows
    its title with an "open" icon; clicking opens the full image in a new tab.
--}}
@if (($certificates ?? collect())->isNotEmpty())
<section class="section certs" id="certificates" data-section>
    <div class="container-xl">
        <div class="edu__head">
            <h2 class="section-title edu__title" data-animate="fade-up">{{ __('site.certificates.title') }}</h2>
        </div>
        <ul class="certs__grid">
            @foreach ($certificates as $certificate)
                @php $title = $certificate->tr('title'); @endphp
                <li data-animate="fade-up" style="--delay: {{ ($loop->index % 4) * 70 }}ms">
                    <a class="cert" href="{{ $certificate->imageUrl() }}" target="_blank" rel="noopener"
                       aria-label="{{ $title }}@if ($certificate->issuer) — {{ $certificate->issuer }}@endif ({{ __('site.certificates.open') }})">
                        <img src="{{ $certificate->imageUrl() }}" loading="lazy" decoding="async" alt="{{ $title }}">
                        <span class="cert__overlay" aria-hidden="true">
                            <span class="cert__title">{{ $title }}</span>
                            @if ($certificate->issuer || $certificate->issued_on)
                                <span class="cert__meta">{{ collect([$certificate->issuer, $certificate->issued_on?->year])->filter()->implode(' · ') }}</span>
                            @endif
                            {{ icon('box-arrow-up-right', 'cert__open') }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
