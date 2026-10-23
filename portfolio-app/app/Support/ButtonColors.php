<?php

namespace App\Support;

use App\Models\Setting;

/**
 * The website's button and accent colours (Admin → Settings → Appearance).
 * Every other shade (hover, soft tints, borders, glows, shadows) is derived from these three in
 * resources/site/css/style.css with color-mix(), so changing them recolours the whole site.
 */
class ButtonColors
{
    /** CSS variable => [setting key, default] */
    public const COLORS = [
        '--accent' => ['primary', '#00afc4'],     // solid buttons, links, icons, back-to-top
        '--accent-2' => ['secondary', '#4f6bff'], // middle of the gradient, focus rings, borders
        '--accent-3' => ['tertiary', '#9b5cff'],  // end of the gradient
    ];

    public const PATTERN = '/^#[0-9a-fA-F]{6}$/';

    /** @return array<string, string> setting key => colour (saved value or default) */
    public static function current(): array
    {
        $saved = Setting::get('appearance.colors', []);

        return collect(self::COLORS)->mapWithKeys(fn ($item) => [
            $item[0] => preg_match(self::PATTERN, (string) ($saved[$item[0]] ?? '')) ? strtolower($saved[$item[0]]) : $item[1],
        ])->all();
    }

    /** Inline CSS overriding the defaults, or '' when the defaults are in use. */
    public static function css(): string
    {
        $current = self::current();
        $rules = collect(self::COLORS)
            ->filter(fn ($item) => $current[$item[0]] !== $item[1])
            ->map(fn ($item, $var) => $var.': '.$current[$item[0]].';')
            ->implode(' ');

        return $rules === '' ? '' : ':root { '.$rules.' }';
    }
}
