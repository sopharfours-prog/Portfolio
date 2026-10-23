{{--
    Search + filter + sort bar used on every management page.
    $search: placeholder · $selects: [param => [label, [value => text]]] · $sorts: [value => text] · $tabs: [param, [value => [text, count]]]
--}}
<form class="toolbar" method="GET" action="{{ url()->current() }}" data-filters data-no-lock>
    @isset($tabs)
        @php [$tabParam, $tabItems] = $tabs; @endphp
        <div class="tabs">
            @foreach ($tabItems as $value => [$text, $count])
                <a class="tab {{ (string) request($tabParam, '') === (string) $value ? 'is-active' : '' }}"
                   href="{{ request()->fullUrlWithQuery([$tabParam => $value ?: null, 'page' => null]) }}">{{ $text }}@if (! is_null($count)) <small>{{ $count }}</small>@endif</a>
            @endforeach
        </div>
        @if (request()->filled($tabParam))<input type="hidden" name="{{ $tabParam }}" value="{{ request($tabParam) }}">@endif
    @endisset

    <div class="search toolbar__grow">
        {{ icon('search') }}
        <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="{{ $search }}" aria-label="{{ $search }}">
    </div>

    @foreach ($selects ?? [] as $param => [$label, $options])
        <select class="select select--auto" name="{{ $param }}" aria-label="{{ $label }}">
            <option value="">{{ $label }}</option>
            @foreach ($options as $value => $text)
                <option value="{{ $value }}" @selected(request($param) === (string) $value)>{{ $text }}</option>
            @endforeach
        </select>
    @endforeach

    @isset($sorts)
        <select class="select select--auto" name="sort" aria-label="{{ __('Sort') }}">
            @foreach ($sorts as $value => $text)
                <option value="{{ $value }}" @selected($sort === $value)>{{ __('Sort') }}: {{ $text }}</option>
            @endforeach
        </select>
    @endisset

    @if (request()->hasAny(['search', ...array_keys($selects ?? [])]) && collect(request()->only(['search', ...array_keys($selects ?? [])]))->filter()->isNotEmpty())
        <a class="btn btn--ghost btn--sm" href="{{ url()->current() }}">{{ __('Reset') }}</a>
    @endif
    <noscript><button class="btn btn--secondary btn--sm">{{ __('Apply') }}</button></noscript>
</form>
