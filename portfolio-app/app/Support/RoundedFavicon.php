<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Browser-tab icon with rounded corners, made from the favicon uploaded in Settings → General.
 * Tab icons are drawn as-is (CSS cannot round them), so the image itself gets transparent corners.
 * The PNG is generated once per uploaded file and cached on the public disk.
 */
class RoundedFavicon
{
    private const SIZE = 128;      // final icon size in px

    private const RADIUS = 0.24;   // corner radius as a share of the size

    private const SCALE = 4;       // draw at 4× and scale down for smooth (anti-aliased) corners

    /** URL of the rounded icon, or null when no raster favicon is set (the default icon is used then). */
    public static function url(): ?string
    {
        $path = self::source();

        return $path ? route('favicon.rounded', ['v' => substr(self::key($path), 0, 10)]) : null;
    }

    /** Raw PNG bytes of the rounded icon (generated on first use). */
    public static function png(): ?string
    {
        $path = self::source();
        if (! $path) {
            return null;
        }
        $disk = Storage::disk('public');
        $cached = 'favicons/rounded-'.self::key($path).'.png';
        if (! $disk->exists($cached)) {
            $png = self::render($disk->get($path));
            if ($png === null) {
                return null;
            }
            $disk->put($cached, $png);
        }

        return $disk->get($cached);
    }

    /** Uploaded favicon path on the public disk, if it is a raster image GD can read. */
    private static function source(): ?string
    {
        $path = Setting::get('general.favicon');

        return is_string($path) && preg_match('/\.(png|jpe?g|webp|gif)$/i', $path) && Storage::disk('public')->exists($path) ? $path : null;
    }

    /** Changes whenever a different or updated file is uploaded. */
    private static function key(string $path): string
    {
        return sha1($path.'|'.Storage::disk('public')->lastModified($path));
    }

    private static function render(string $bytes): ?string
    {
        $src = @imagecreatefromstring($bytes);
        if (! $src) {
            return null;
        }

        // Centre-crop to a square, drawn large
        $big = self::SIZE * self::SCALE;
        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $canvas = imagecreatetruecolor($big, $big);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $src, 0, 0, intdiv($w - $side, 2), intdiv($h - $side, 2), $big, $big, $side, $side);

        // Clear everything outside the rounded corners
        $r = (int) round($big * self::RADIUS);
        $clear = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        foreach ([[$r, $r], [$big - $r - 1, $r], [$r, $big - $r - 1], [$big - $r - 1, $big - $r - 1]] as [$cx, $cy]) {
            $x0 = $cx < $big / 2 ? 0 : $cx;
            $y0 = $cy < $big / 2 ? 0 : $cy;
            for ($x = $x0; $x <= $x0 + $r; $x++) {
                for ($y = $y0; $y <= $y0 + $r; $y++) {
                    if (($x - $cx) ** 2 + ($y - $cy) ** 2 > $r ** 2) {
                        imagesetpixel($canvas, $x, $y, $clear);
                    }
                }
            }
        }

        // Scale down: the resampling smooths the corner edges
        $icon = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagealphablending($icon, false);
        imagesavealpha($icon, true);
        imagefill($icon, 0, 0, imagecolorallocatealpha($icon, 0, 0, 0, 127));
        imagecopyresampled($icon, $canvas, 0, 0, 0, 0, self::SIZE, self::SIZE, $big, $big);

        ob_start();
        imagepng($icon, null, 9);

        return ob_get_clean() ?: null;
    }
}
