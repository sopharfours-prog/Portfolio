{{--
    Language picker with flags.
      variant "dropdown" (header): current flag + code; click to open the list (site/js/dropdowns.js)
      variant "pills"    (mobile menu): every language visible as a flag pill
    Each option is a plain link to /lang/{code}, so it also works without JavaScript.
--}}
@php
    $enabled = $settings['language']['enabled'] ?? array_keys(locales());
    $current = app()->getLocale();
    $variant = $variant ?? 'dropdown';
    $menuId = 'lang-menu-'.\Illuminate\Support\Str::random(4);
@endphp
@if (count($enabled) > 1)
    @if ($variant === 'pills')
        <div class="lang-switch lang-pills {{ $class ?? '' }}" role="group" aria-label="{{ __('site.nav.language') }}">
            @foreach (locales() as $code => $label)
                @continue(! in_array($code, $enabled, true))
                <a class="lang-pills__btn" href="{{ route('language', $code) }}" lang="{{ $code }}" hreflang="{{ $code }}"
                   @if ($current === $code) aria-current="true" @endif>
                    @include('site.partials.flag', ['code' => $code])<span>{{ __('site.languages.'.$code) }}</span>
                </a>
            @endforeach
        </div>
    @else
        <div class="lang-switch dropdown-x lang-menu {{ $class ?? '' }}" data-dropdown>
            <button type="button" class="lang-menu__toggle" data-dropdown-toggle aria-expanded="false" aria-controls="{{ $menuId }}"
                    aria-label="{{ __('site.nav.language') }}: {{ __('site.languages.'.$current) }}" title="{{ __('site.nav.language') }}">
                @include('site.partials.flag', ['code' => $current])
                <span class="lang-menu__code">{{ locales()[$current] ?? strtoupper($current) }}</span>
                {{ icon('chevron-down', 'dropdown-x__chevron') }}
            </button>
            <ul class="dropdown-x__panel" id="{{ $menuId }}" data-dropdown-panel>
                @foreach (locales() as $code => $label)
                    @continue(! in_array($code, $enabled, true))
                    <li>
                        <a class="dropdown-x__item" href="{{ route('language', $code) }}" lang="{{ $code }}" hreflang="{{ $code }}"
                           @if ($current === $code) aria-current="true" @endif>
                            @include('site.partials.flag', ['code' => $code])
                            <span class="dropdown-x__label">{{ __('site.languages.'.$code) }}<small>{{ $label }}</small></span>
                            {{ icon('check2', 'dropdown-x__check') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endif
