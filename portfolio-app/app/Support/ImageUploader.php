<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Stores an uploaded image in the Media Library.
 * - Validation rules live in rules() so every upload endpoint shares them.
 * - JPEG/PNG are resized (max 2400px) and re-encoded as WebP to save bandwidth.
 * - SVGs are sanitised (scripts, event handlers, external references removed).
 */
class ImageUploader
{
    public const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

    public const MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];

    public const MAX_KB = 102400; // 100 MB

    private const MAX_DIMENSION = 2400;

    /** Laravel validation rules for a single image field. */
    public static function rules(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'max:'.self::MAX_KB,
            'extensions:'.implode(',', self::EXTENSIONS),
            'mimetypes:'.implode(',', self::MIME_TYPES),
        ];
    }

    public function store(UploadedFile $file, string $folder = 'uploads', ?int $userId = null): Media
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $disk = Storage::disk('public');
        $base = $folder.'/'.now()->format('Y/m').'/'.Str::lower(Str::ulid());

        if ($extension === 'svg' || $mime === 'image/svg+xml') {
            $svg = SvgSanitizer::clean(file_get_contents($file->getRealPath()));
            if ($svg === null) {
                throw ValidationException::withMessages(['file' => __('The SVG file is not valid or contains unsafe content.')]);
            }
            $path = "{$base}.svg";
            $disk->put($path, $svg);
            [$width, $height] = SvgSanitizer::dimensions($svg);
            $mime = 'image/svg+xml';
        } else {
            [$binary, $width, $height, $mime, $ext] = $this->optimise($file);
            $path = "{$base}.{$ext}";
            $disk->put($path, $binary);
        }

        return Media::create([
            'name' => Str::limit(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 120, ''),
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $mime,
            'size' => $disk->size($path),
            'width' => $width,
            'height' => $height,
            'uploaded_by' => $userId ?? auth()->id(),
        ]);
    }

    /** @return array{0: string, 1: int, 2: int, 3: string, 4: string} */
    private function optimise(UploadedFile $file): array
    {
        $raw = file_get_contents($file->getRealPath());
        $info = @getimagesizefromstring($raw);
        if (! $info) {
            throw ValidationException::withMessages(['file' => __('The file is not a valid image.')]);
        }
        [$width, $height] = $info;

        // Decoding needs ~5 bytes per pixel (+ the resized copy). Raise the limit for this request if needed;
        // if a huge photo would still not fit, keep the original file instead of failing the upload.
        if (! self::ensureMemoryFor($width, $height)) {
            return [$raw, $width, $height, $info['mime'], strtolower($file->getClientOriginalExtension())];
        }

        $image = function_exists('imagecreatefromstring') ? @imagecreatefromstring($raw) : false;
        if (! $image || ! function_exists('imagewebp')) {
            // GD unavailable: keep the original bytes (already validated as an image)
            return [$raw, $width, $height, $info['mime'], $file->getClientOriginalExtension()];
        }

        $scale = min(1, self::MAX_DIMENSION / max($width, $height));
        if ($scale < 1) {
            $resized = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));
            imagedestroy($image);
            $image = $resized;
            [$width, $height] = [imagesx($image), imagesy($image)];
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        ob_start();
        imagewebp($image, null, 82);
        $webp = ob_get_clean();
        imagedestroy($image);

        return [$webp, $width, $height, 'image/webp', 'webp'];
    }

    /** Makes room to decode a $width × $height image (max 1 GB for one upload); false when that is not possible. */
    private static function ensureMemoryFor(int $width, int $height): bool
    {
        $needed = memory_get_usage() + (int) ($width * $height * 5 * 1.3) + 64 * 1024 * 1024;
        $limit = self::bytes((string) ini_get('memory_limit'));
        if ($limit <= 0 || $needed <= $limit) {
            return true;   // unlimited, or already enough
        }
        if ($needed > 1024 * 1024 * 1024) {
            return false;
        }

        return @ini_set('memory_limit', (string) $needed) !== false;
    }

    private static function bytes(string $value): int
    {
        $number = (int) $value;

        return match (strtoupper(substr(trim($value), -1))) {
            'G' => $number * 1024 ** 3,
            'M' => $number * 1024 ** 2,
            'K' => $number * 1024,
            default => $number,
        };
    }
}
