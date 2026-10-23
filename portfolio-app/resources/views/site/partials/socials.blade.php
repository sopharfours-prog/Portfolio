@foreach ($socials as $social)
    <li><a class="social-link" href="{{ $social->url }}" target="_blank" rel="noopener me" aria-label="{{ $social->label() }}">{{ icon($social->icon()) }}<span>{{ $social->label() }}</span></a></li>
@endforeach
