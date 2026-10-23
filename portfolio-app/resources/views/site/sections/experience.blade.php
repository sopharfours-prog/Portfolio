{{-- Work experience: one card per job with a round company logo (Admin → Experience → Logo). --}}
@if ($experiences->isNotEmpty())
<section class="section section--soft" id="experience" data-section>
    <div class="container-xl">
        <div class="edu__head">
            <h2 class="section-title edu__title" data-animate="fade-up">{{ __('site.experience.title') }}</h2>
        </div>
        <ol class="edu__list exp__list">
            @foreach ($experiences as $item)
                <li class="entry-card" data-animate="fade-up" style="--delay: {{ min($loop->index, 4) * 80 }}ms">
                    <span class="entry-card__logo" aria-hidden="true">
                        @if ($image = $item->imageUrl())
                            <img src="{{ $image }}" loading="lazy" decoding="async" alt="">
                        @else
                            {{ icon('briefcase') }}
                        @endif
                    </span>
                    <div class="entry-card__body">
                        <h3 class="entry-card__title">{{ $item->tr('position') }} <span class="entry-card__sep">-</span> {{ $item->company }}</h3>
                        <p class="entry-card__meta">
                            @if ($item->location)<span>{{ $item->location }}</span><span class="entry-card__bar" aria-hidden="true">|</span>@endif
                            <span>{{ $item->period() }}</span>
                        </p>
                        @if ($desc = $item->tr('description'))<p class="entry-card__desc">{{ $desc }}</p>@endif
                        @if ($item->technologies)
                            <ul class="tags entry-card__tags">@foreach ($item->technologies as $tech)<li>{{ $tech }}</li>@endforeach</ul>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
@endif
