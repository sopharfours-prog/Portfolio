{{--
    Skills: technical skills as icon + bar rows, professional skills (category "soft") as percentage rings.
    Bars and rings fill when the card scrolls into view ([data-animate] → .is-visible, see animations.css).
--}}
@php
    [$professional, $technical] = $skills->partition(fn ($skill) => $skill->isProfessional());
    // Brand colours for common Bootstrap Icons names; anything else uses the accent colour
    $brand = [
        'filetype-html' => '#e34f26', 'filetype-css' => '#1572b6', 'filetype-js' => '#d4b106', 'filetype-jsx' => '#61dafb',
        'filetype-tsx' => '#3178c6', 'filetype-php' => '#777bb4', 'filetype-py' => '#3776ab', 'bootstrap' => '#7952b3',
        'bootstrap-fill' => '#7952b3', 'layers' => '#ff2d20', 'database' => '#00758f', 'git' => '#f05032',
        'filetype-psd' => '#31a8ff', 'filetype-ai' => '#ff9a00', 'bezier2' => '#a259ff', 'palette' => '#e5498b',
        'android2' => '#3ddc84', 'apple' => 'currentColor', 'github' => 'currentColor', 'robot' => '#10a37f',
    ];
@endphp
@if ($skills->isNotEmpty())
<section class="section section--soft skills" id="skills" data-section>
    <div class="container-xl">
        <div class="skills__head">
            <h2 class="section-title skills__title" data-animate="fade-up">{{ __('site.skills.title') }} <span class="skills__title-accent">{{ __('site.skills.titleAccent') }}</span></h2>
            @if ($summary = tr($settings['about']['skills_summary'] ?? null))<p class="text-soft section-sub skills__sub" data-animate="fade-up">{{ $summary }}</p>@endif
        </div>

        <div class="skills__grid {{ $technical->isEmpty() || $professional->isEmpty() ? 'skills__grid--single' : '' }}">
            @if ($technical->isNotEmpty())
                <article class="skills-panel" data-animate="fade-up">
                    <h3 class="skills-panel__title">{{ __('site.skills.technical') }}</h3>
                    <ul class="skill-bars">
                        @foreach ($technical as $skill)
                            <li class="skill-bar" style="--level: {{ ($skill->level ?? 100) / 100 }}; --i: {{ $loop->index }}; --brand: {{ $brand[$skill->icon] ?? 'var(--accent)' }}">
                                <span class="skill-bar__name">{{ icon($skill->icon, 'skill-bar__icon') }}<span>{{ $skill->displayName() }}</span></span>
                                <span class="skill-bar__track" @if (! is_null($skill->level)) role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $skill->level }}" aria-label="{{ $skill->displayName() }}" @endif>
                                    <span class="skill-bar__fill"></span>
                                </span>
                                @if (! is_null($skill->level))<span class="skill-bar__value">{{ $skill->level }}%</span>@endif
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endif

            @if ($professional->isNotEmpty())
                <article class="skills-panel" data-animate="fade-up" style="--delay: 120ms">
                    <h3 class="skills-panel__title">{{ __('site.skills.professional') }}</h3>
                    <ul class="skill-rings">
                        @foreach ($professional as $skill)
                            <li class="skill-ring" style="--level: {{ $skill->level ?? 100 }}; --i: {{ $loop->index }}">
                                <span class="skill-ring__chart" @if (! is_null($skill->level)) role="img" aria-label="{{ $skill->displayName() }}: {{ $skill->level }}%" @endif>
                                    <svg viewBox="0 0 120 120" aria-hidden="true">
                                        <circle class="skill-ring__track" cx="60" cy="60" r="52" pathLength="100" />
                                        <circle class="skill-ring__fill" cx="60" cy="60" r="52" pathLength="100" />
                                    </svg>
                                    <span class="skill-ring__value" aria-hidden="true">{{ is_null($skill->level) ? '' : $skill->level.'%' }}</span>
                                </span>
                                <span class="skill-ring__name">{{ $skill->displayName() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endif
        </div>
    </div>
</section>
@endif
