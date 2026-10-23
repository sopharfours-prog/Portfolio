<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The downloadable CV: one PDF per site language, kept on the private "local" disk
 * (storage/app/private/cv) so it is only reachable through the download routes.
 * Metadata lives in the "cv" setting: {"lo": {...}, "en": {...}, "show_on_site": bool}.
 */
class CvFile
{
    public const MAX_KB = 102400; // 100 MB

    private const DIRECTORY = 'cv';

    /** Largest CV the server will actually accept: the app limit, or PHP's upload limits if lower. */
    public static function maxBytes(): int
    {
        $limits = [self::MAX_KB * 1024];
        foreach (['upload_max_filesize', 'post_max_size'] as $setting) {
            $bytes = self::iniBytes((string) ini_get($setting));
            if ($bytes > 0) {
                $limits[] = $bytes;
            }
        }

        return min($limits);
    }

    /** "8M" / "512K" / "1G" → bytes (0 = unlimited). */
    private static function iniBytes(string $value): int
    {
        $value = trim($value);
        $number = (int) $value;

        return match (strtoupper(substr($value, -1))) {
            'G' => $number * 1024 ** 3,
            'M' => $number * 1024 ** 2,
            'K' => $number * 1024,
            default => $number,
        };
    }

    /** @return array{path: string, name: string, size: int, uploaded_at: string}|null */
    public static function get(string $locale): ?array
    {
        $file = Setting::get("cv.{$locale}");

        return is_array($file) && Storage::disk('local')->exists($file['path'] ?? '') ? $file : null;
    }

    public static function isPublic(): bool
    {
        return (bool) Setting::get('cv.show_on_site', true);
    }

    /** The CV for the visitor's language, falling back to any other uploaded language. */
    public static function forLocale(string $locale): ?array
    {
        foreach ([$locale, ...array_keys(locales())] as $code) {
            if ($file = self::get($code)) {
                return $file;
            }
        }

        return null;
    }

    /** True when the website should show a "Download CV" button. */
    public static function availableOnSite(): bool
    {
        return self::isPublic() && self::forLocale(app()->getLocale()) !== null;
    }

    /** Stores a new PDF for $locale, replacing (and deleting) the previous one. */
    public static function store(string $locale, UploadedFile $upload): void
    {
        $disk = Storage::disk('local');
        $path = $disk->putFileAs(self::DIRECTORY, $upload, "cv-{$locale}-".Str::random(8).'.pdf');

        self::deleteFile($locale);
        self::save([$locale => [
            'path' => $path,
            'name' => Str::limit(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME), 80, '').'.pdf',
            'size' => $upload->getSize(),
            'uploaded_at' => now()->toIso8601String(),
        ]]);
    }

    public static function remove(string $locale): void
    {
        self::deleteFile($locale);
        self::save([$locale => null]);
    }

    public static function setPublic(bool $visible): void
    {
        self::save(['show_on_site' => $visible]);
    }

    /** Download response with a readable file name, e.g. "Sophar-CV-EN.pdf". */
    public static function download(array $file, string $locale): StreamedResponse
    {
        $name = Str::slug(Setting::get('general.site_name', config('app.name'))).'-CV-'.strtoupper($locale).'.pdf';

        return Storage::disk('local')->download($file['path'], $name, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private static function deleteFile(string $locale): void
    {
        if ($old = Setting::get("cv.{$locale}.path")) {
            Storage::disk('local')->delete($old);
        }
    }

    private static function save(array $changes): void
    {
        Setting::put('cv', [...Setting::get('cv', []), ...$changes]);
    }
}
