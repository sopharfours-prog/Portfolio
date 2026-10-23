@if ($testimonials->isNotEmpty())
<section class="section" id="testimonials" data-section>
    <div class="container-xl">
        <div class="row section-head">
            <div class="col-12"><h2 class="section-title" data-animate="fade-up">{{ __('site.testimonials.heading') }}</h2></div>
        </div>

        <section class="carousel-x" data-carousel aria-roledescription="carousel" aria-label="{{ __('site.testimonials.region') }}" data-animate="fade-up">
            <div class="carousel-x__viewport">
                <div class="carousel-x__track" data-carousel-track aria-live="polite">
                    @foreach ($testimonials as $item)
                        <div class="t-slide" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} / {{ $loop->count }}">
                            {{ icon('quote', 't-slide__mark') }}
                            <blockquote class="t-slide__quote"><p>{{ $item->tr('message') }}</p></blockquote>
                            <p class="t-slide__author">
                                @if ($photo = $item->photoUrl())
                                    <img class="t-slide__avatar" src="{{ $photo }}" width="52" height="52" loading="lazy" alt="">
                                @else
                                    <span class="t-slide__avatar" aria-hidden="true">{{ $item->initials() }}</span>
                                @endif
                                <span><strong>{{ $item->client_name }}</strong><span>{{ collect([$item->tr('position'), $item->company])->filter()->implode(', ') }}</span></span>
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
            @if ($testimonials->count() > 1)
                <div class="carousel-x__controls">
                    <div class="carousel-x__dots" data-carousel-dots></div>
                    <div class="carousel-x__arrows">
                        <button type="button" class="icon-btn icon-btn--outline" data-carousel-prev aria-label="{{ __('site.testimonials.prev') }}">{{ icon('arrow-left') }}</button>
                        <button type="button" class="icon-btn icon-btn--outline" data-carousel-next aria-label="{{ __('site.testimonials.next') }}">{{ icon('arrow-right') }}</button>
                    </div>
                </div>
            @else
                <div class="visually-hidden" data-carousel-dots></div>
            @endif
        </section>
    </div>
</section>
@endif
