{{--
    Round national flag for a site language (inline SVG, so it looks the same on every OS —
    Windows does not draw emoji flags). Usage: @include('site.partials.flag', ['code' => 'lo'])
--}}
@php $uid = 'flag'.\Illuminate\Support\Str::random(6); @endphp
@if ($code === 'lo')
    <svg class="flag" viewBox="0 0 30 20" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
        <rect width="30" height="20" fill="#ce1126"/>
        <rect y="5" width="30" height="10" fill="#002868"/>
        <circle cx="15" cy="10" r="4" fill="#fff"/>
    </svg>
@elseif ($code === 'en')
    <svg class="flag" viewBox="0 0 60 30" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
        <clipPath id="{{ $uid }}"><path d="M30 15h30v15zv15H0zH0V0zV0h30z"/></clipPath>
        <rect width="60" height="30" fill="#012169"/>
        <path d="M0 0l60 30m0-30L0 30" stroke="#fff" stroke-width="6"/>
        <path d="M0 0l60 30m0-30L0 30" clip-path="url(#{{ $uid }})" stroke="#c8102e" stroke-width="4"/>
        <path d="M30 0v30M0 15h60" stroke="#fff" stroke-width="10"/>
        <path d="M30 0v30M0 15h60" stroke="#c8102e" stroke-width="6"/>
    </svg>
@endif
