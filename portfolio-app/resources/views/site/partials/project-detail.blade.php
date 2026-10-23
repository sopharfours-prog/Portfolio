@php
    $gallery = $project->galleryImages();
    $link = fn ($url) => filled($url) && $url !== '#';
    $sub = ($heading ?? 'h2') === 'h1' ? 'h2' : 'h3'; // keep heading levels sequential
@endphp
<header class="pm-header">
    <p class="eyebrow"><span class="eyebrow__num">{{ sprintf('%02d', $number) }}</span>{{ __('site.projects.'.$project->category) }}</p>
    <{{ $heading ?? 'h2' }} class="pm-title" id="project-modal-title">{{ $project->title }}</{{ $heading ?? 'h2' }}>
    <p class="pm-lead">{{ $project->tr('description') ?? $project->tr('summary') }}</p>
</header>

@if ($cover = $project->coverUrl())
    <figure class="pm-hero"><img src="{{ $cover }}" width="1600" height="1200" alt="{{ $project->title }} — {{ $project->tr('summary') }}"></figure>
@endif

<dl class="pm-facts">
    @if ($project->client)<div><dt>{{ __('site.projects.client') }}</dt><dd>{{ $project->client }}</dd></div>@endif
    @if ($role = $project->tr('role'))<div><dt>{{ __('site.projects.role') }}</dt><dd>{{ $role }}</dd></div>@endif
    @if ($project->project_date)<div><dt>{{ __('site.projects.year') }}</dt><dd>{{ $project->project_date->year }}</dd></div>@endif
    <div><dt>{{ __('site.projects.category') }}</dt><dd>{{ __('site.projects.'.$project->category) }}</dd></div>
</dl>

<div class="pm-body row g-4 g-lg-5">
    @if ($challenge = $project->tr('challenge'))
        <div class="col-lg-6"><{{ $sub }} class="pm-subtitle">{{ __('site.projects.challenge') }}</{{ $sub }}><p>{{ $challenge }}</p></div>
    @endif
    @if ($solution = $project->tr('solution'))
        <div class="col-lg-6"><{{ $sub }} class="pm-subtitle">{{ __('site.projects.solution') }}</{{ $sub }}><p>{{ $solution }}</p></div>
    @endif
    @if ($features = $project->featureList())
        <div class="col-lg-6">
            <{{ $sub }} class="pm-subtitle">{{ __('site.projects.features') }}</{{ $sub }}>
            <ul class="pm-features">@foreach ($features as $feature)<li>{{ icon('check2') }}<span>{{ $feature }}</span></li>@endforeach</ul>
        </div>
    @endif
    @if ($project->technologies)
        <div class="col-lg-6">
            <{{ $sub }} class="pm-subtitle">{{ __('site.projects.technology') }}</{{ $sub }}>
            <ul class="tags tags--lg">@foreach ($project->technologies as $tech)<li>{{ $tech }}</li>@endforeach</ul>
        </div>
    @endif
</div>

@if ($gallery->isNotEmpty())
    <{{ $sub }} class="pm-subtitle">{{ __('site.projects.gallery') }}</{{ $sub }}>
    <div class="pm-gallery">
        @foreach ($gallery as $image)
            <figure><img src="{{ $image->url() }}" width="{{ $image->width ?: 1200 }}" height="{{ $image->height ?: 900 }}" loading="lazy" decoding="async" alt="{{ $project->title }} — {{ __('site.projects.gallery') }} {{ $loop->iteration }}"></figure>
        @endforeach
    </div>
@endif

@if ($link($project->url) || $link($project->github_url))
    <div class="pm-actions">
        @if ($link($project->url))<a class="btn-x btn-x--solid" href="{{ $project->url }}" target="_blank" rel="noopener">{{ __('site.projects.visit') }} {{ icon('arrow-up-right') }}</a>@endif
        @if ($link($project->github_url))<a class="btn-x btn-x--outline" href="{{ $project->github_url }}" target="_blank" rel="noopener">{{ icon('github') }} {{ __('site.projects.github') }}</a>@endif
    </div>
@endif

@if ($prev->id !== $project->id)
<nav class="pm-pager" aria-label="{{ __('site.projects.label') }}">
    <a class="pm-pager__btn" href="{{ route('projects.show', $prev) }}" data-project-open>
        {{ icon('arrow-left') }}<span><small>{{ __('site.projects.prev') }}</small>{{ $prev->title }}</span>
    </a>
    <a class="pm-pager__btn pm-pager__btn--next" href="{{ route('projects.show', $next) }}" data-project-open>
        <span><small>{{ __('site.projects.next') }}</small>{{ $next->title }}</span>{{ icon('arrow-right') }}
    </a>
</nav>
@endif
