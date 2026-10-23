<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory, HasTranslations, LogsActivity;

    public const STATUSES = ['published', 'draft'];

    protected $fillable = ['title', 'issuer', 'issued_on', 'image', 'sort_order', 'status'];

    protected function casts(): array
    {
        return ['title' => 'array', 'issued_on' => 'date'];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    public function imageUrl(): ?string
    {
        return media_url($this->image);
    }
}
