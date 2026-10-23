<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    public const STATUSES = ['published', 'draft', 'hidden'];

    protected $fillable = ['client_name', 'position', 'company', 'photo', 'message', 'rating', 'sort_order', 'status'];

    protected function casts(): array
    {
        return ['position' => 'array', 'message' => 'array', 'rating' => 'integer'];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    public function initials(): string
    {
        return mb_strtoupper(collect(preg_split('/\s+/', trim($this->client_name)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''));
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? media_url($this->photo) : null;
    }
}
