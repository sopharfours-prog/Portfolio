<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'body', 'url', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    /** Sends the same notification to every admin user. */
    public static function notifyAll(string $type, string $title, ?string $body = null, ?string $url = null): void
    {
        $now = now();
        $rows = User::pluck('id')->map(fn ($id) => [
            'user_id' => $id, 'type' => $type, 'title' => $title, 'body' => $body, 'url' => $url,
            'created_at' => $now, 'updated_at' => $now,
        ])->all();

        if ($rows) {
            static::insert($rows);
        }
    }
}
