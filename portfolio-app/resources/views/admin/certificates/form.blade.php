@extends('admin.layouts.app')
@php $editing = $item->exists; @endphp
@section('title', $editing ? __('Edit Certificate') : __('Add Certificate'))

@section('content')
    <x-admin.resource-form :title="$editing ? __('Edit Certificate') : __('Add Certificate')" :index="route('admin.certificates.index')" :index-label="__('Certificates')"
        :action="$editing ? route('admin.certificates.update', $item) : route('admin.certificates.store')" :editing="$editing" :current="$item->tr('title')">
        <div class="fields">
            <x-admin.bilingual :label="__('Title')" name="title" :value="$item->title" required :max="150" :hint="__('e.g. the course or award name. Shown when hovering over the certificate.')" />
            <x-admin.input :label="__('Issued by')" name="issuer" :value="$item->issuer" maxlength="150" />
            <x-admin.input :label="__('Date')" name="issued_on" type="date" :value="$item->issued_on?->format('Y-m-d')" />
            <x-admin.image-input :label="__('Certificate image')" name="image" :value="$item->image" full
                :hint="__('A scan or photo of the certificate. JPG, PNG or WebP; landscape works best.')" />
        </div>
        <x-slot:aside>
            <x-admin.input :label="__('Order')" name="sort_order" type="number" min="0" :value="$item->sort_order ?? 0" />
            <x-admin.status-select :value="$item->status" />
        </x-slot:aside>
    </x-admin.resource-form>
@endsection
