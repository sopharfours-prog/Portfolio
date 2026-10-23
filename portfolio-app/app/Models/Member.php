<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** A visitor account on the public website (guard "member"). Has no admin access. */
class Member extends Authenticatable
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = ['name', 'email', 'password', 'locale', 'google_id', 'apple_id'];

    protected $hidden = ['password', 'remember_token', 'google_id', 'apple_id'];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Every admin is notified when someone signs up on the website. */
    protected static function booted(): void
    {
        static::created(function (Member $member) {
            AdminNotification::notifyAll(
                'member',
                __('New member signed up'),
                $member->name.' — '.$member->email,
                route('admin.members.index', ['search' => $member->email])
            );
        });
    }

    public function initials(): string
    {
        return mb_strtoupper(collect(preg_split('/\s+/', trim($this->name)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''));
    }
}
