@extends('admin.layouts.app')
@php $editing = $item->exists; @endphp
@section('title', $editing ? __('Edit Testimonial') : __('Add Testimonial'))

@section('content')
    <x-admin.resource-form :title="$editing ? __('Edit Testimonial') : __('Add Testimonial')" :index="route('admin.testimonials.index')" :index-label="__('Testimonials')"
        :action="$editing ? route('admin.testimonials.update', $item) : route('admin.testimonials.store')" :editing="$editing" :current="$item->client_name" multipart>
        <div class="fields">
            <x-admin.input :label="__('Client Name')" name="client_name" :value="$item->client_name" required maxlength="120" />
            <x-admin.input :label="__('Company')" name="company" :value="$item->company" maxlength="150" />
            <x-admin.bilingual :label="__('Position')" name="position" :value="$item->position" :max="150" />
            <x-admin.bilingual :label="__('Message')" name="message" :value="$item->message" textarea :rows="5" required :max="1500" />
        </div>
        <x-slot:aside>
            <x-admin.field :label="__('Photo')" name="photo" :hint="__('Optional. JPG, PNG, WebP or SVG, up to :size MB.', ['size' => \App\Support\ImageUploader::MAX_KB / 1024])">
                <div class="row">
                    @if ($item->photoUrl())<img class="avatar avatar--lg" src="{{ $item->photoUrl() }}" alt="">@else<span class="avatar avatar--lg avatar--soft">{{ $item->exists ? $item->initials() : '?' }}</span>@endif
                    <input class="input" id="f-photo" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>
                @if ($item->photo)<label class="check" style="margin-top:8px"><input type="checkbox" name="remove_photo" value="1"> {{ __('Remove photo') }}</label>@endif
            </x-admin.field>
            <x-admin.select :label="__('Rating')" name="rating" :value="$item->rating ?? 5" required :options="[5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★']" />
            <x-admin.input :label="__('Order')" name="sort_order" type="number" min="0" :value="$item->sort_order ?? 0" />
            <x-admin.status-select :value="$item->status" :statuses="['published', 'draft', 'hidden']" />
        </x-slot:aside>
    </x-admin.resource-form>
@endsection
