<?php

namespace App\Support;

/**
 * Turns form input into the shapes stored in the database.
 * Accepts both form strings and JSON arrays, so admin forms and the API behave the same.
 */
class InputNormalizer
{
    /** {"lo": "...", "en": "..."} with trimmed values; empty languages dropped, null if all empty. */
    public static function bilingual(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }
        $clean = collect(array_keys(locales()))
            ->mapWithKeys(fn ($locale) => [$locale => trim((string) ($value[$locale] ?? ''))])
            ->filter(fn ($text) => $text !== '');

        return $clean->isEmpty() ? null : $clean->all();
    }

    /** Bilingual lists: each language is a textarea with one item per line (or an array). */
    public static function bilingualLines(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }
        $clean = collect(array_keys(locales()))
            ->mapWithKeys(fn ($locale) => [$locale => self::list($value[$locale] ?? '', "\n")])
            ->filter(fn ($items) => $items !== []);

        return $clean->isEmpty() ? null : $clean->all();
    }

    /** "Laravel, MySQL" (or ["Laravel", "MySQL"]) → ["Laravel", "MySQL"] */
    public static function tags(mixed $value): array
    {
        return self::list($value ?? '', ',');
    }

    private static function list(mixed $value, string $separator): array
    {
        $items = is_array($value) ? $value : preg_split('/'.preg_quote($separator, '/').'|\r?\n/', (string) $value);

        return array_values(array_unique(array_filter(
            array_map(fn ($item) => trim(strip_tags((string) $item)), $items),
            fn ($item) => $item !== ''
        )));
    }
}
