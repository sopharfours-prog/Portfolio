@props(['label' => null, 'name' => null, 'required' => false, 'hint' => null, 'full' => false])
<div {{ $attributes->class(['field', 'field--full' => $full]) }}>
    @if ($label)<label class="label" @if ($name) for="f-{{ str_replace(['[', ']', '.'], ['-', '', '-'], $name) }}" @endif>{{ $label }}@if ($required) <span class="req">*</span>@endif</label>@endif
    {{ $slot }}
    @if ($hint)<p class="hint">{{ $hint }}</p>@endif
    @if ($name) @error(str_replace(['[', ']'], ['.', ''], $name))<p class="error">{{ $message }}</p>@enderror @endif
</div>
