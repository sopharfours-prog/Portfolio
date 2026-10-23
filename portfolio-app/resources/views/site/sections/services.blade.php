{{--
    Services: centred icon, title, short description and a "Read more" button that unfolds
    the full description and the feature list (site/js/services.js; without JS everything is shown).
--}}
@if ($services->isNotEmpty())
<section class="section" id="services" data-section>
    <div class="container-xl">
        <div class="edu__head">
            <h2 class="section-title services__title" data-animate="fade-up">{{ __('site.services.title') }}</h2>
        </div>
        <div class="services-grid">
            @foreach ($services as $service)
                @php $features = $service->featureList(); $moreId = 'service-more-'.$service->id; @endphp
                <article class="service-card" data-animate="fade-up" data-service style="--delay: {{ ($loop->index % 4) * 80 }}ms">
                    <span class="service-card__icon">{{ icon($service->icon) }}</span>
                    <h3 class="service-card__title">{{ $service->tr('title') }}</h3>
                    <div class="service-card__more" id="{{ $moreId }}" data-service-more>
                        <p class="service-card__desc">{{ $service->tr('description') }}</p>
                        @if ($features)
                            <ul class="service-card__list">@foreach ($features as $feature)<li>{{ icon('check2') }}<span>{{ $feature }}</span></li>@endforeach</ul>
                        @endif
                    </div>
                    <button type="button" class="service-card__btn" data-service-toggle aria-expanded="false" aria-controls="{{ $moreId }}" hidden>
                        <span data-label-more>{{ __('site.services.readMore') }}</span><span data-label-less hidden>{{ __('site.services.readLess') }}</span>
                    </button>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
