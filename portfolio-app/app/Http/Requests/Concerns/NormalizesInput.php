<?php

namespace App\Http\Requests\Concerns;

use App\Support\InputNormalizer;

/** Form-request shortcuts for InputNormalizer, plus shared bilingual validation rules. */
trait NormalizesInput
{
    protected function bilingual(string $key): ?array
    {
        return InputNormalizer::bilingual($this->input($key));
    }

    protected function bilingualLines(string $key): ?array
    {
        return InputNormalizer::bilingualLines($this->input($key));
    }

    protected function tags(string $key): array
    {
        return InputNormalizer::tags($this->input($key));
    }

    /** Lao is the primary language (required when the field is required); English optional. */
    protected static function bilingualRules(string $key, bool $required = true, int $max = 2000): array
    {
        return [
            $key => ['array'],
            "{$key}.lo" => [$required ? 'required' : 'nullable', 'string', "max:{$max}"],
            "{$key}.en" => ['nullable', 'string', "max:{$max}"],
        ];
    }
}
