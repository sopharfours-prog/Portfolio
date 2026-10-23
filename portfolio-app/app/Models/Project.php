<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    public const CATEGORIES = ['web', 'mobile', 'uiux', 'branding', 'system', 'other'];

    public const STATUSES = ['published', 'draft', 'archived'];

    protected $fillable = [
        'slug', 'title', 'category', 'summary', 'description', 'challenge', 'solution', 'features', 'role',
        'technologies', 'client', 'project_date', 'url', 'github_url', 'featured', 'status', 'sort_order', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'summary' => 'array',
            'description' => 'array',
            'challenge' => 'array',
            'solution' => 'array',
            'features' => 'array',
            'role' => 'array',
            'technologies' => 'array',
            'project_date' => 'date',
            'featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order');
    }

    public function cover(): HasOne
    {
        return $this->hasOne(ProjectImage::class)->where('is_cover', true);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('project_date')->orderByDesc('id');
    }

    public function coverUrl(): ?string
    {
        $cover = $this->relationLoaded('cover') ? $this->cover : $this->cover()->first();
        $cover ??= $this->relationLoaded('images') ? $this->images->first() : $this->images()->first();

        return $cover ? media_url($cover->path) : null;
    }

    /** Gallery = every image except the cover. */
    public function galleryImages()
    {
        return $this->images->reject(fn ($image) => $image->is_cover)->values();
    }

    public function featureList(?string $locale = null): array
    {
        return (array) ($this->tr('features', $locale) ?? []);
    }
}
