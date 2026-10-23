<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Key → JSON value store for website content and configuration.
 * Keys: general, seo, hero, about, contact, appearance, language.
 *
 *   Setting::get('hero.name')           // dot notation into the JSON
 *   Setting::put('hero', [...])
 */
class Setting extends Model
{
    private const CACHE_KEY = 'settings.all';

    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::pluck('value', 'key')
            ->map(fn ($value) => is_string($value) ? json_decode($value, true) : $value)
            ->all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Arr::get(static::allCached(), $key, $default);
    }

    public static function put(string $key, array $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    /** Bilingual setting field, e.g. Setting::tr('hero.greeting') */
    public static function tr(string $key, ?string $locale = null): ?string
    {
        $value = static::get($key);
        if (! is_array($value)) {
            return $value;
        }
        $locale ??= app()->getLocale();

        return $value[$locale] ?? $value[config('app.fallback_locale')] ?? null;
    }
}
