{{--
    Education: a large photo on the left (Website → About → "Education photo", falling back to the
    profile image) and one card per entry on the right with a round logo, title, place | period and text.
--}}
@php
    $about = $settings['about'] ?? [];
    $photo = media_url($about['education_image'] ?? null) ?: media_url($about['image'] ?? null);
@endphp
@if ($education->isNotEmpty())
<section class="section section--soft edu" id="education" data-section>
    <div class="container-xl">
        <div class="edu__head">
            <h2 class="section-title edu__title" data-animate="fade-up">{{ __('site.education.title') }}</h2>
        </div>

        <div class="edu__grid {{ $photo ? '' : 'edu__grid--single' }}">
            @if ($photo)
                <figure class="edu__photo" data-animate="fade-right">
                    <img src="{{ $photo }}" loading="lazy" decoding="async" alt="{{ __('site.education.photoAlt') }}">
                </figure>
            @endif

            <ol class="edu__list">
                @foreach ($education as $item)
                    <li class="entry-card" data-animate="fade-up" style="--delay: {{ min($loop->index, 4) * 80 }}ms">
                        <span class="entry-card__logo" aria-hidden="true">
                            @if ($image = $item->imageUrl())
                                <img src="{{ $image }}" loading="lazy" decoding="async" alt="">
                            @else
                                {{ icon('mortarboard') }}
                            @endif
                        </span>
                        <div class="entry-card__body">
                            <h3 class="entry-card__title">{{ $item->tr('degree') }} <span class="entry-card__sep">-</span> {{ $item->institution }}</h3>
                            <p class="entry-card__meta">
                                @if ($item->location)<span>{{ $item->location }}</span><span class="entry-card__bar" aria-hidden="true">|</span>@endif
                                <span>{{ $item->period() }}</span>
                            </p>
                            @if ($field = $item->tr('field'))<p class="entry-card__field">{{ $field }}</p>@endif
                            @if ($desc = $item->tr('description'))<p class="entry-card__desc">{{ $desc }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
@endif
