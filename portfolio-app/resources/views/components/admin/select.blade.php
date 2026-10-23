@props(['label', 'name', 'options' => [], 'value' => null, 'required' => false, 'hint' => null, 'full' => false])
<x-admin.field :label="$label" :name="$name" :required="$required" :hint="$hint" :full="$full">
    <select class="select" id="f-{{ $name }}" name="{{ $name }}" @if ($required) required @endif {{ $attributes }}>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</x-admin.field>
