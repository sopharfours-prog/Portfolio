<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    public const STATUSES = ['published', 'draft'];

    protected $fillable = [
        'company', 'position', 'location', 'start_date', 'end_date', 'is_current',
        'description', 'technologies', 'image', 'sort_order', 'status',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'array',
            'description' => 'array',
            'technologies' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function imageUrl(): ?string
    {
        return media_url($this->image);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    /** e.g. "2025 — Present" */
    public function period(): string
    {
        $start = $this->start_date->format('Y');
        $end = $this->is_current ? __('site.present') : ($this->end_date?->format('Y') ?? '');

        // Same-year entries read "2022", not "2022 — 2022"
        return $end === '' || $end === $start ? $start : $start.' — '.$end;
    }

    public function activityLabel(): string
    {
        return ($this->position['en'] ?? '').' @ '.$this->company;
    }
}
