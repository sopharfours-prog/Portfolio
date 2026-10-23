<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasTranslations, LogsActivity;

    public const STATUSES = ['published', 'draft'];

    protected $table = 'education';

    protected $fillable = [
        'institution', 'degree', 'field', 'location', 'start_date', 'end_date', 'is_current',
        'description', 'image', 'sort_order', 'status',
    ];

    protected function casts(): array
    {
        return [
            'degree' => 'array',
            'field' => 'array',
            'description' => 'array',
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

    /** e.g. "2017 — 2021" or "2023 — Present" */
    public function period(): string
    {
        $start = $this->start_date->format('Y');
        $end = $this->is_current ? __('site.present') : ($this->end_date?->format('Y') ?? '');

        // Same-year entries read "2022", not "2022 — 2022"
        return $end === '' || $end === $start ? $start : $start.' — '.$end;
    }

    public function activityLabel(): string
    {
        return ($this->degree['en'] ?? $this->degree['lo'] ?? '').' @ '.$this->institution;
    }
}
