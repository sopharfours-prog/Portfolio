@extends('admin.layouts.base')

@section('body')
    <main class="auth">
        <div class="card auth__card">
            <h1 class="auth__brand">
                <strong>{{ setting('general.site_name', 'SOPHAR') }}</strong>
                <span>ADMIN</span>
            </h1>
            @yield('content')
        </div>
    </main>
@endsection
