<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    /** Inbox views shown in the admin. unread/read are subsets of the inbox. */
    public const VIEWS = ['inbox', 'unread', 'read', 'archived', 'trash'];

    protected $fillable = ['name', 'email', 'phone', 'subject', 'body', 'folder', 'read_at', 'replied_at', 'ip', 'user_agent'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime', 'replied_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::created(function (Message $message) {
            AdminNotification::notifyAll(
                'message',
                __('New contact message'),
                $message->name.' — '.$message->subject,
                route('admin.messages.index', ['message' => $message->id])
            );
        });
    }

    public function replies(): HasMany
    {
        return $this->hasMany(MessageReply::class)->oldest();
    }

    public function scopeView(Builder $query, string $view): void
    {
        match ($view) {
            'unread' => $query->where('folder', 'inbox')->whereNull('read_at'),
            'read' => $query->where('folder', 'inbox')->whereNotNull('read_at'),
            'archived', 'trash' => $query->where('folder', $view),
            default => $query->where('folder', 'inbox'),
        };
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function initials(): string
    {
        return mb_strtoupper(collect(preg_split('/\s+/', trim($this->name)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''));
    }

    public function activityLabel(): string
    {
        return $this->name.' — '.$this->subject;
    }
}
