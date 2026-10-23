@if ($paginator->total() > 0)
<nav class="pagination" aria-label="{{ __('Page navigation') }}">
    <span>{{ __('Showing :from–:to of :total', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}</span>
    @if ($paginator->hasPages())
        <div class="pagination__pages">
            <a class="page-link {{ $paginator->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $paginator->previousPageUrl() ?? '#' }}" aria-label="{{ __('Previous') }}" @if ($paginator->onFirstPage()) aria-disabled="true" tabindex="-1" @endif>{{ icon('chevron-left') }}</a>
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page-link is-disabled">…</span>
                @else
                    @foreach ($element as $page => $url)
                        <a class="page-link {{ $page == $paginator->currentPage() ? 'is-active' : '' }}" href="{{ $url }}" @if ($page == $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</a>
                    @endforeach
                @endif
            @endforeach
            <a class="page-link {{ $paginator->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $paginator->nextPageUrl() ?? '#' }}" aria-label="{{ __('Next') }}" @if (! $paginator->hasMorePages()) aria-disabled="true" tabindex="-1" @endif>{{ icon('chevron-right') }}</a>
        </div>
    @endif
</nav>
@endif
