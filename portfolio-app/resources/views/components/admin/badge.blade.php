@props(['status'])
@php
    $map = [
        'published' => ['success', __('Published')], 'draft' => ['warning', __('Draft')], 'archived' => ['', __('Archived')],
        'hidden' => ['', __('Hidden')], 'success' => ['success', __('Success')], 'failed' => ['danger', __('Failed')],
        'unread' => ['warning', __('Unread')], 'read' => ['', __('Read')],
    ];
    [$tone, $text] = $map[$status] ?? ['', ucfirst((string) $status)];
@endphp
<span {{ $attributes->class(['badge', "badge--{$tone}" => $tone]) }}>{{ $text }}</span>
