{{-- One labelled input for the visitor login / register forms (same markup as the contact form fields). --}}
@php
    $id = 'auth-'.$name;
    $error = $errors->first($name);
    $describedBy = trim(($hint ?? false ? $id.'-hint ' : '').$id.'-error');
@endphp
<div class="field">
    <label class="field__label" for="{{ $id }}">{{ __('site.auth.'.($label ?? $name)) }}</label>
    <div class="field__control">
        <input class="field__input" id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" required maxlength="{{ $max }}"
               @if ($type !== 'password') value="{{ old($name) }}" @endif @if ($autofocus ?? false) autofocus @endif
               aria-describedby="{{ $describedBy }}" @if ($error) aria-invalid="true" @endif>
        @if ($type === 'password')
            <button type="button" class="field__reveal" data-password-toggle aria-pressed="false" aria-label="{{ __('site.auth.showPassword') }}" title="{{ __('site.auth.showPassword') }}">
                {{ icon('eye', 'field__reveal-show') }}{{ icon('eye-slash', 'field__reveal-hide') }}
            </button>
        @endif
    </div>
    @if ($hint ?? false)<p class="field__hint" id="{{ $id }}-hint">{{ $hint }}</p>@endif
    <p class="field__error" id="{{ $id }}-error">{{ $error }}</p>
</div>
