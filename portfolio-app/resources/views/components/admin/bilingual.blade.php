{{-- One field, two languages (LA | EN tabs). Posts name[lo] and name[en]. --}}
@props(['label', 'name', 'value' => [], 'textarea' => false, 'rows' => 4, 'required' => false, 'hint' => null, 'full' => true, 'max' => null])
@php $value = is_array($value) ? $value : []; @endphp
<div {{ $attributes->class(['field', 'field--full' => $full]) }} data-bilingual>
    <div class="bilingual__head">
        <label class="label" for="f-{{ $name }}-lo">{{ $label }}@if ($required) <span class="req">*</span>@endif</label>
        <div class="lang-tabs" role="tablist" aria-label="{{ __('Language') }}">
            @foreach (locales() as $code => $short)
                <button type="button" role="tab" data-lang="{{ $code }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        class="{{ $loop->first ? 'is-active' : '' }} {{ $errors->has("$name.$code") ? 'has-error' : '' }}">{{ $short }}</button>
            @endforeach
        </div>
    </div>
    @foreach (locales() as $code => $short)
        @php $current = old("$name.$code", $value[$code] ?? ''); if (is_array($current)) { $current = implode("\n", $current); } @endphp
        <div data-lang-panel="{{ $code }}" @if (! $loop->first) hidden @endif>
            @if ($textarea)
                <textarea class="textarea" id="f-{{ $name }}-{{ $code }}" name="{{ $name }}[{{ $code }}]" rows="{{ $rows }}" lang="{{ $code }}"
                          @if ($max) maxlength="{{ $max }}" @endif @if ($required && $code === 'lo') required @endif @error("$name.$code") aria-invalid="true" @enderror>{{ $current }}</textarea>
            @else
                <input class="input" id="f-{{ $name }}-{{ $code }}" type="text" name="{{ $name }}[{{ $code }}]" value="{{ $current }}" lang="{{ $code }}"
                       @if ($max) maxlength="{{ $max }}" @endif @if ($required && $code === 'lo') required @endif @error("$name.$code") aria-invalid="true" @enderror>
            @endif
            @error("$name.$code")<p class="error" style="margin-top:6px">{{ $message }}</p>@enderror
        </div>
    @endforeach
    @if ($hint)<p class="hint">{{ $hint }}</p>@endif
</div>
