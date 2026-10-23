<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    public const STATUSES = ['published', 'draft'];

    protected $fillable = ['title', 'description', 'features', 'icon', 'sort_order', 'status'];

    protected function casts(): array
    {
        return ['title' => 'array', 'description' => 'array', 'features' => 'array'];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    public function featureList(?string $locale = null): array
    {
        return (array) ($this->tr('features', $locale) ?? []);
    }
}
