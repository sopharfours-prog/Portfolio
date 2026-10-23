<?php

namespace App\Models\Concerns;

/**
 * Bilingual JSON attributes: {"lo": "...", "en": "..."}.
 * Declare them in `protected array $translatable` and cast them to 'array'.
 */
trait HasTranslations
{
    /** Value of a translatable attribute in the given (or current) locale, falling back to the other language. */
    public function tr(string $attribute, ?string $locale = null): mixed
    {
        $value = $this->getAttribute($attribute);
        if (! is_array($value)) {
            return $value;
        }
        $locale ??= app()->getLocale();

        return $value[$locale] ?? $value[config('app.fallback_locale')] ?? reset($value) ?: null;
    }
}
