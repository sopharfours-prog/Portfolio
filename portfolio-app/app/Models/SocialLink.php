<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    /** platform => [label, Bootstrap icon] */
    public const PLATFORMS = [
        'facebook' => ['Facebook', 'facebook'],
        'instagram' => ['Instagram', 'instagram'],
        'github' => ['GitHub', 'github'],
        'linkedin' => ['LinkedIn', 'linkedin'],
        'whatsapp' => ['WhatsApp', 'whatsapp'],
        'telegram' => ['Telegram', 'telegram'],
    ];

    protected $fillable = ['platform', 'url', 'enabled', 'sort_order'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('enabled', true)->whereNotNull('url')->where('url', '!=', '')->orderBy('sort_order');
    }

    public function label(): string
    {
        return self::PLATFORMS[$this->platform][0] ?? ucfirst($this->platform);
    }

    public function icon(): string
    {
        return self::PLATFORMS[$this->platform][1] ?? 'link-45deg';
    }
}
