<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    /** Switched off while seeding so demo data doesn't flood the log. */
    public static bool $enabled = true;

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'target', 'ip', 'status', 'meta'];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?Model $subject = null, array $meta = [], string $status = 'success', ?int $userId = null, ?string $target = null): ?self
    {
        if (! static::$enabled) {
            return null;
        }

        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'target' => $target ?? ($subject && method_exists($subject, 'activityLabel') ? $subject->activityLabel() : null),
            'ip' => request()?->ip(),
            'status' => $status,
            'meta' => $meta ?: null,
        ]);
    }

    /** "Updated Project", "Deleted Skill", "Login" ... */
    public function description(): string
    {
        $action = __(ucfirst(str_replace('_', ' ', $this->action)));

        return $this->subject_type ? $action.' '.__($this->subject_type) : $action;
    }
}
