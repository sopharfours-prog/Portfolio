{{ $replyBody }}

—
{{ \App\Models\Setting::get('general.site_name', config('app.name')) }}
{{ url('/') }}

----------------------------------------
{{ $original->name }} · {{ $original->created_at->format('Y-m-d H:i') }}
{{ $original->subject }}

{{ $original->body }}
