{{-- Social links with per-platform on/off switch. Posts socials[platform][url|enabled]. --}}
<section class="card">
    <div class="card__head"><div><h2>{{ __('Social Links') }}</h2><p>{{ __('Switch a platform off to hide it on the website.') }}</p></div></div>
    <div class="card__body">
        @foreach ($socials as $social)
            <div class="social-row">
                <span class="social-row__name">{{ icon($social->icon()) }}{{ $social->label() }}</span>
                <div>
                    <input class="input" type="url" name="socials[{{ $social->platform }}][url]" value="{{ old("socials.{$social->platform}.url", $social->url) }}" placeholder="https://" aria-label="{{ $social->label() }} URL"
                           @error("socials.{$social->platform}.url") aria-invalid="true" @enderror>
                    @error("socials.{$social->platform}.url")<p class="error" style="margin-top:4px">{{ $message }}</p>@enderror
                </div>
                <label class="switch" title="{{ __('Show on website') }}">
                    <input type="hidden" name="socials[{{ $social->platform }}][enabled]" value="0">
                    <input type="checkbox" name="socials[{{ $social->platform }}][enabled]" value="1" @checked(old("socials.{$social->platform}.enabled", $social->enabled))>
                    <span class="switch__track"></span><span class="sr-only">{{ __('Show on website') }}</span>
                </label>
            </div>
        @endforeach
    </div>
</section>
