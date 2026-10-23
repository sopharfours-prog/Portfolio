@props(['value' => 'published', 'statuses' => ['published', 'draft']])
<x-admin.select :label="__('Status')" name="status" :value="$value" required
    :options="collect($statuses)->mapWithKeys(fn ($s) => [$s => __(ucfirst($s))])->all()" />
