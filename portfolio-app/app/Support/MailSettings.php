<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * Outgoing email settings from Admin → Settings → Email, applied over config/mail.php at start-up,
 * so replies, password resets and notifications use the account entered in the admin.
 * The SMTP password is stored encrypted with APP_KEY and never sent back to the browser.
 */
class MailSettings
{
    public const ENCRYPTIONS = ['tls', 'ssl', 'none'];

    /** Applies the saved settings, if any. Safe to call before the database exists (e.g. during install). */
    public static function apply(): void
    {
        try {
            $mail = Setting::get('mail', []);
        } catch (Throwable) {
            return;
        }

        if (($mail['mode'] ?? null) !== 'smtp' || blank($mail['host'] ?? null)) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $mail['host'],
            'mail.mailers.smtp.port' => (int) ($mail['port'] ?? 587),
            'mail.mailers.smtp.scheme' => ($mail['encryption'] ?? 'tls') === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.username' => $mail['username'] ?? null,
            'mail.mailers.smtp.password' => self::password(),
            'mail.from.address' => ($mail['from_address'] ?? null) ?: config('mail.from.address'),
            'mail.from.name' => ($mail['from_name'] ?? null) ?: config('mail.from.name'),
        ]);
    }

    /** True while emails are only written to the log (nothing is delivered). */
    public static function isTestMode(): bool
    {
        return in_array(config('mail.default'), ['log', 'array'], true);
    }

    public static function hasPassword(): bool
    {
        return filled(Setting::get('mail.password'));
    }

    public static function encrypt(string $password): string
    {
        return Crypt::encryptString($password);
    }

    private static function password(): ?string
    {
        $stored = Setting::get('mail.password');
        if (blank($stored)) {
            return null;
        }
        try {
            return Crypt::decryptString($stored);
        } catch (DecryptException) {
            return null;   // APP_KEY changed: the password must be entered again
        }
    }
}
