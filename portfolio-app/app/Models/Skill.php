<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    public const CATEGORIES = ['frontend', 'backend', 'database', 'design', 'tools', 'other', 'soft'];

    /** Category for professional (soft) skills, shown as rings instead of bars on the website. */
    public const PROFESSIONAL = 'soft';

    public const STATUSES = ['published', 'draft'];

    protected $fillable = ['name', 'label', 'category', 'icon', 'level', 'sort_order', 'status'];

    protected function casts(): array
    {
        return ['level' => 'integer', 'label' => 'array'];
    }

    /** Name in the current language when a translated label is set, otherwise the plain name. */
    public function displayName(): string
    {
        return $this->tr('label') ?: $this->name;
    }

    public function isProfessional(): bool
    {
        return $this->category === self::PROFESSIONAL;
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }
}
