<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_EDITOR = 'editor';

    public const ROLES = [self::ROLE_SUPER_ADMIN, self::ROLE_EDITOR];

    protected $fillable = ['name', 'email', 'password', 'role', 'avatar', 'locale'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function roleLabel(): string
    {
        return $this->isSuperAdmin() ? __('Super Admin') : __('Editor');
    }

    public function initials(): string
    {
        return mb_strtoupper(collect(preg_split('/\s+/', trim($this->name)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''));
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar ? media_url($this->avatar) : null;
    }

    /**
     * Ends every other login for this user: rotates the "remember me" token and deletes their
     * stored sessions except $keepSessionId. Used after a password change and on deletion.
     */
    public function endOtherSessions(?string $keepSessionId = null): void
    {
        $this->forceFill(['remember_token' => Str::random(60)])->saveQuietly();

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $this->id)
                ->when($keepSessionId, fn ($query) => $query->where('id', '!=', $keepSessionId))
                ->delete();
        }
    }

    public function adminNotifications(): HasMany
    {
        return $this->hasMany(AdminNotification::class)->latest();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
