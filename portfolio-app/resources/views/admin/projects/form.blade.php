@extends('admin.layouts.app')
@php $editing = $project->exists; @endphp
@section('title', $editing ? __('Edit Project') : __('Add New Project'))

@section('content')
    <div class="page-head">
        <div>
            <nav class="breadcrumb"><a href="{{ route('admin.projects.index') }}">{{ __('Projects') }}</a>{{ icon('chevron-right') }}<span>{{ $editing ? $project->title : __('New') }}</span></nav>
            <h1>{{ $editing ? __('Edit Project') : __('Add New Project') }}</h1>
        </div>
        @if ($editing && $project->status === 'published')
            <div class="page-head__actions"><a class="btn btn--secondary" href="{{ route('projects.show', $project) }}" target="_blank" rel="noopener">{{ icon('eye') }}{{ __('View') }}</a></div>
        @endif
    </div>

    @if ($errors->any())
        <div class="alert alert--danger" role="alert" style="margin-bottom:20px">
            {{ icon('exclamation-circle') }}<div><strong>{{ __('Please fix the following:') }}</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        </div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.projects.update', $project) : route('admin.projects.store') }}" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid grid--form">
            <div class="stack">
                <section class="card">
                    <div class="card__head"><h2>{{ __('Details') }}</h2></div>
                    <div class="card__body">
                        <div class="fields">
                            <x-admin.input :label="__('Project Name')" name="title" :value="$project->title" required maxlength="150" data-slug-source />
                            <x-admin.input :label="__('Slug')" name="slug" :value="$project->slug" maxlength="160" data-slug :hint="__('Used in the URL. Leave empty to generate from the name.')" />
                            <x-admin.bilingual :label="__('Short Description')" name="summary" :value="$project->summary" required :max="300" :hint="__('One sentence shown on the project card.')" />
                            <x-admin.bilingual :label="__('Full Description')" name="description" :value="$project->description" textarea :rows="4" />
                            <x-admin.bilingual :label="__('Challenge')" name="challenge" :value="$project->challenge" textarea :rows="3" />
                            <x-admin.bilingual :label="__('Solution')" name="solution" :value="$project->solution" textarea :rows="3" />
                            <x-admin.bilingual :label="__('Key Features')" name="features" :value="$project->features" textarea :rows="4" :hint="__('One feature per line.')" />
                        </div>
                    </div>
                </section>

                <section class="card" data-gallery data-upload="{{ route('admin.media.store') }}" data-items="{{ json_encode($gallery) }}">
                    <div class="card__head">
                        <div><h2>{{ __('Images') }}</h2><p>{{ __('Drag to reorder. The cover image is shown on the project card.') }}</p></div>
                        <button type="button" class="btn btn--secondary btn--sm" data-gallery-library>{{ icon('images') }}{{ __('Choose from library') }}</button>
                    </div>
                    <div class="card__body">
                        <input type="hidden" name="gallery_present" value="1">
                        <input type="hidden" name="cover" data-gallery-cover>
                        <div class="dropzone" data-gallery-drop role="button" tabindex="0">
                            {{ icon('cloud-arrow-up') }}
                            <p><strong>{{ __('Drag & drop images here') }}</strong> {{ __('or click to upload') }}</p>
                            <p class="hint">JPG, PNG, WebP, SVG · {{ __('max :size MB each', ['size' => \App\Support\ImageUploader::MAX_KB / 1024]) }}</p>
                            <input type="file" accept=".jpg,.jpeg,.png,.webp,.svg" multiple hidden data-gallery-file>
                        </div>
                        <div class="gallery" data-gallery-list></div>
                    </div>
                </section>
            </div>

            <aside class="stack">
                <section class="card">
                    <div class="card__head"><h2>{{ __('Publishing') }}</h2></div>
                    <div class="card__body">
                        <x-admin.select :label="__('Status')" name="status" :value="$project->status" :options="['published' => __('Published'), 'draft' => __('Draft'), 'archived' => __('Archived')]" />
                        <div class="field">
                            <label class="switch"><input type="hidden" name="featured" value="0"><input type="checkbox" name="featured" value="1" @checked(old('featured', $project->featured))><span class="switch__track"></span><span>{{ __('Featured on home page') }}</span></label>
                        </div>
                        @if ($editing)
                            <dl class="meta-list" style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                                <dt>{{ __('Created Date') }}</dt><dd>{{ $project->created_at->translatedFormat('j M Y') }}</dd>
                                <dt>{{ __('Last Updated') }}</dt><dd>{{ $project->updated_at->diffForHumans() }}</dd>
                                <dt>{{ __('Project Views') }}</dt><dd class="num">{{ number_format($project->views) }}</dd>
                            </dl>
                        @endif
                    </div>
                </section>

                <section class="card">
                    <div class="card__head"><h2>{{ __('Project info') }}</h2></div>
                    <div class="card__body">
                        <x-admin.select :label="__('Category')" name="category" :value="$project->category" required
                            :options="collect(\App\Models\Project::CATEGORIES)->mapWithKeys(fn ($c) => [$c => __('taxonomy.'.$c)])->all()" />
                        <x-admin.input :label="__('Technologies')" name="technologies" :value="implode(', ', (array) old('technologies', $project->technologies ?? []))" :hint="__('Separate with commas.')" placeholder="Laravel, Bootstrap, JavaScript" />
                        <x-admin.input :label="__('Client')" name="client" :value="$project->client" maxlength="150" />
                        <x-admin.bilingual :label="__('My role')" name="role" :value="$project->role" :full="false" />
                        <x-admin.input :label="__('Date')" name="project_date" type="date" :value="$project->project_date?->format('Y-m-d')" />
                        <x-admin.input :label="__('Project URL')" name="url" type="url" :value="$project->url" placeholder="https://" />
                        <x-admin.input :label="__('GitHub URL')" name="github_url" type="url" :value="$project->github_url" placeholder="https://github.com/..." />
                    </div>
                </section>
            </aside>
        </div>

        <div class="form-actions">
            <a class="btn btn--ghost" href="{{ route('admin.projects.index') }}">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn--secondary" name="action" value="draft">{{ __('Save Draft') }}</button>
            @if ($editing)<button type="submit" class="btn btn--secondary" name="action" value="save">{{ __('Save Changes') }}</button>@endif
            <button type="submit" class="btn btn--primary" name="action" value="publish">{{ __('Publish Project') }}</button>
        </div>
    </form>
@endsection
