@php
    // Editorial rhythm: rows alternate wide/narrow, then narrow/wide (JS recomputes after filtering)
    $wide = ($index % 2 === 0) === (intdiv($index, 2) % 2 === 0);
    $cover = $project->coverUrl();
    $summary = $project->tr('summary');
@endphp
<article class="project-card {{ $wide ? 'project-card--wide' : 'project-card--narrow' }} {{ $index % 2 ? 'project-card--offset' : '' }}"
         data-category="{{ $project->category }}" @if ($hidden ?? false) hidden @endif>
    <div class="project-card__media">
        @if ($cover)
            <img src="{{ $cover }}" width="1600" height="1200" loading="lazy" decoding="async" alt="{{ $project->title }} — {{ $summary }}">
        @endif
        <span class="project-card__overlay" aria-hidden="true"><span class="project-card__circle">{{ icon('arrow-up-right') }}</span></span>
    </div>
    <div class="project-card__body">
        <p class="project-card__meta"><span>{{ __('site.projects.'.$project->category) }}</span>@if ($project->project_date)<span>{{ $project->project_date->year }}</span>@endif</p>
        <{{ $cardTag ?? 'h3' }} class="project-card__title">
            <a class="project-card__link" href="{{ route('projects.show', $project) }}" data-project-open>{{ $project->title }}</a>
        </{{ $cardTag ?? 'h3' }}>
        <p class="project-card__desc">{{ $summary }}</p>
        @if ($project->technologies)
            <p class="visually-hidden">{{ __('site.projects.technology') }}:</p>
            <ul class="tags">@foreach ($project->technologies as $tech)<li>{{ $tech }}</li>@endforeach</ul>
        @endif
        <span class="link-arrow" aria-hidden="true">{{ __('site.projects.view') }} {{ icon('arrow-right') }}</span>
    </div>
</article>
