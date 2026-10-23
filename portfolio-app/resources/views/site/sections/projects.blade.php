@php $categories = collect(['all' => $projectTotal])->merge($categoryCounts)->filter(); @endphp
<section class="section {{ $class ?? '' }}" id="projects" data-section>
    <div class="container-xl">
        <div class="row section-head">
            <div class="col-12"><{{ $titleTag ?? 'h2' }} class="section-title" @if (($titleTag ?? 'h2') !== 'h1') data-animate="fade-up" @endif>{{ __($heading ?? 'site.projects.heading') }}</{{ $titleTag ?? 'h2' }}></div>
        </div>

        @if ($categories->count() > 2)
            <div class="project-filters" role="group" aria-label="{{ __('site.projects.filterLabel') }}" data-project-filters>
                @foreach ($categories as $category => $count)
                    <button type="button" class="filter-btn {{ $loop->first ? 'is-active' : '' }}" data-filter="{{ $category }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ __('site.projects.'.$category) }}<sup>{{ $count }}</sup></button>
                @endforeach
            </div>
        @endif

        <div class="projects-grid" data-projects-grid data-limit="{{ $limit ?? 0 }}" aria-live="polite">
            @foreach ($projects as $project)
                @include('site.partials.project-card', ['index' => $loop->index, 'cardTag' => ($titleTag ?? 'h2') === 'h1' ? 'h2' : 'h3'])
            @endforeach
            <p class="projects-empty" @if ($projects->isNotEmpty()) hidden @endif>{{ __('site.projects.empty') }}</p>
        </div>

        @unless ($all ?? false)
            <div class="projects__more">
                <a class="btn-x btn-x--outline" href="{{ route('projects.index') }}"><span>{{ __('site.projects.viewAll') }}</span>{{ icon('arrow-right') }}</a>
            </div>
        @endunless
    </div>
</section>

<dialog class="project-modal" data-project-modal aria-labelledby="project-modal-title">
    <div class="project-modal__bar">
        <button type="button" class="icon-btn project-modal__close" data-project-close aria-label="{{ __('site.projects.close') }}" title="{{ __('site.projects.close') }}">{{ icon('x-lg') }}</button>
    </div>
    <div class="project-modal__content container-xl" data-project-modal-content></div>
</dialog>
