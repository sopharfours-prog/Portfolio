@extends('admin.layouts.app')
@section('title', __('Profile'))

@section('content')
    <div class="page-head"><div><h1>{{ __('Profile') }}</h1><p>{{ __('Your account details.') }}</p></div></div>

    <div class="grid grid--form">
        <div class="stack">
            <section class="card">
                <div class="card__head"><h2>{{ __('Personal information') }}</h2></div>
                <div class="card__body">
                    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" novalidate>
                        @csrf @method('PUT')
                        <div class="row" style="margin-bottom:20px">
                            @if ($user->avatarUrl())<img class="avatar avatar--xl" src="{{ $user->avatarUrl() }}" alt="">@else<span class="avatar avatar--xl">{{ $user->initials() }}</span>@endif
                            <div style="min-width:0">
                                <label class="label" for="f-avatar">{{ __('Profile Photo') }}</label>
                                <input class="input" id="f-avatar" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" style="margin-top:6px">
                                @error('avatar')<p class="error">{{ $message }}</p>@enderror
                                @if ($user->avatar)<label class="check" style="margin-top:8px"><input type="checkbox" name="remove_avatar" value="1"> {{ __('Remove photo') }}</label>@endif
                            </div>
                        </div>
                        <div class="fields">
                            <x-admin.input :label="__('Name')" name="name" :value="$user->name" required maxlength="100" autocomplete="name" />
                            <x-admin.input :label="__('Email')" name="email" type="email" :value="$user->email" required autocomplete="email" />
                            <x-admin.select :label="__('Admin language')" name="locale" :value="$user->locale" :options="['lo' => __('Lao'), 'en' => __('English')]" />
                            <x-admin.field :label="__('Role')"><input class="input" type="text" value="{{ $user->roleLabel() }}" disabled></x-admin.field>
                        </div>
                        <div class="row" style="justify-content:flex-end;margin-top:20px"><button type="submit" class="btn btn--primary">{{ __('Save Changes') }}</button></div>
                    </form>
                </div>
            </section>

            <section class="card" id="password">
                <div class="card__head"><div><h2>{{ __('Password') }}</h2><p>{{ __('At least 10 characters, with upper and lower case letters and a number.') }}</p></div></div>
                <div class="card__body">
                    <form method="POST" action="{{ route('admin.profile.password') }}" novalidate>
                        @csrf @method('PUT')
                        @php $bag = $errors->password; @endphp
                        <div class="fields">
                            @foreach ([['current_password', __('Current password'), 'current-password', true], ['password', __('New password'), 'new-password', false], ['password_confirmation', __('Confirm password'), 'new-password', false]] as [$name, $label, $auto, $full])
                                <div class="field {{ $full ? 'field--full' : '' }}">
                                    <label class="label" for="pw-{{ $name }}">{{ $label }}</label>
                                    <input class="input" id="pw-{{ $name }}" type="password" name="{{ $name }}" required autocomplete="{{ $auto }}" @if ($bag->has($name)) aria-invalid="true" @endif>
                                    @if ($bag->has($name))<p class="error">{{ $bag->first($name) }}</p>@endif
                                </div>
                            @endforeach
                        </div>
                        <div class="row" style="justify-content:flex-end;margin-top:20px"><button type="submit" class="btn btn--secondary">{{ __('Change password') }}</button></div>
                    </form>
                </div>
            </section>
        </div>

        <aside class="stack">
            <section class="card"><div class="card__body" style="text-align:center">
                @if ($user->avatarUrl())<img class="avatar avatar--xl" src="{{ $user->avatarUrl() }}" alt="" style="margin:0 auto 12px">@else<span class="avatar avatar--xl" style="margin:0 auto 12px">{{ $user->initials() }}</span>@endif
                <h2 style="font-size:17px;font-weight:600">{{ $user->name }}</h2>
                <p class="muted">{{ $user->email }}</p>
                <p style="margin-top:10px"><span class="badge badge--plain">{{ $user->roleLabel() }}</span></p>
                <dl class="meta-list" style="margin-top:18px;padding-top:16px;border-top:1px solid var(--border);text-align:left">
                    <dt>{{ __('Last login') }}</dt><dd>{{ $user->last_login_at?->diffForHumans() ?? '—' }}</dd>
                    <dt>{{ __('Member since') }}</dt><dd>{{ $user->created_at->translatedFormat('j M Y') }}</dd>
                </dl>
            </div></section>
            <section class="card card--flush">
                <div class="card__head" style="padding-bottom:14px"><h2>{{ __('Recent activity') }}</h2></div>
                <div class="list">
                    @forelse ($recentActivity as $log)
                        <div class="list__item"><span class="list__main"><span class="list__title" style="font-weight:500">{{ $log->description() }}</span><span class="list__sub truncate" style="display:block">{{ $log->target }}</span></span><span class="list__aside">{{ $log->created_at->diffForHumans(short: true) }}</span></div>
                    @empty
                        <div class="empty" style="padding:28px"><p>{{ __('No activity yet') }}</p></div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
@endsection
