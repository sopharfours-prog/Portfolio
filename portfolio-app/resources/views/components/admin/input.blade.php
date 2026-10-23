@props(['label', 'name', 'value' => null, 'type' => 'text', 'required' => false, 'hint' => null, 'full' => false])
@php $key = str_replace(['[', ']'], ['.', ''], $name); @endphp
<x-admin.field :label="$label" :name="$name" :required="$required" :hint="$hint" :full="$full">
    <input class="input" id="f-{{ str_replace('.', '-', $key) }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($key, $value) }}"
           @if ($required) required @endif @error($key) aria-invalid="true" @enderror {{ $attributes }}>
</x-admin.field>
