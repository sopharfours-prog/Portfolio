<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

if (! function_exists('media_url')) {
    /** Public URL for a file on the public disk (absolute URLs pass through). */
    function media_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('icon')) {
    /** Inline Bootstrap Icon SVG (decorative). Unknown names render nothing. */
    function icon(string $name, string $class = ''): HtmlString
    {
        static $cache = [];
        $name = preg_replace('/[^a-z0-9-]/', '', strtolower($name));
        $key = $name.'|'.$class;

        if (! isset($cache[$key])) {
            $file = resource_path("icons/{$name}.svg");
            $svg = is_file($file) ? file_get_contents($file) : '';
            $cache[$key] = $svg ? preg_replace(
                ['/\s(width|height|class)="[^"]*"/', '/<svg/', '/\n\s*/'],
                ['', '<svg class="icon'.($class ? ' '.e($class) : '').'" aria-hidden="true" focusable="false"', ''],
                $svg
            ) : '';
        }

        return new HtmlString($cache[$key]);
    }
}

if (! function_exists('locales')) {
    /** Supported site languages: code => short label */
    function locales(): array
    {
        return ['lo' => 'LA', 'en' => 'EN'];
    }
}

if (! function_exists('setting')) {
    /** Website setting by dot key, e.g. setting('contact.email') */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('setting_tr')) {
    /** Bilingual website setting in the current locale, e.g. setting_tr('hero.greeting') */
    function setting_tr(string $key, ?string $fallback = null): ?string
    {
        return Setting::tr($key) ?? $fallback;
    }
}

if (! function_exists('tr')) {
    /** Picks the current language from a {"lo": .., "en": ..} array (plain values pass through). */
    function tr(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        return $value[app()->getLocale()] ?? $value[config('app.fallback_locale')] ?? reset($value) ?: null;
    }
}
