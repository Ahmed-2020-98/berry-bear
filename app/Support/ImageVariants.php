<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Throwable;

/**
 * Resized WebP copies of uploaded images, so each section downloads an image
 * close to the size it is actually shown at (srcset + sizes).
 *
 * Variants live on the public disk under cache/img/{width}/{original path}.webp
 * and are generated lazily (first render), on upload, or via `berry:image-variants`.
 */
class ImageVariants
{
    public const WIDTHS = [160, 320, 640, 960, 1280, 1600];

    private const DIR = 'cache/img';

    private const QUALITY = 80;

    /** Skip absurdly large sources to protect memory on shared hosting. */
    private const MAX_PIXELS = 40_000_000;

    /** @var array<string, array{0:int,1:int}|false> */
    private static array $dims = [];

    /** Map a URL (or stored path) of an uploaded image to its public-disk path. */
    public static function pathFrom(string $urlOrPath): ?string
    {
        $v = trim($urlOrPath);
        if ($v === '') {
            return null;
        }
        if (str_starts_with($v, 'uploads/')) {
            $path = $v;
        } elseif (($pos = strpos($v, '/storage/uploads/')) !== false) {
            $path = substr($v, $pos + strlen('/storage/'));
        } else {
            return null;
        }
        $path = rawurldecode(strtok($path, '?#'));

        return str_contains($path, '..') ? null : $path;
    }

    public static function supported(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    /** ` srcset="…" sizes="…"` attributes for an <img>, or an empty string. */
    public static function attributes(string $urlOrPath, string $sizes): HtmlString
    {
        $path = self::pathFrom($urlOrPath);
        if (! $path || ! self::supported() || ! self::resizable($path)) {
            return new HtmlString('');
        }
        [$w] = self::dimensions($path) ?: [0];
        if ($w < 200) {
            return new HtmlString('');
        }

        $set = [];
        foreach (self::WIDTHS as $width) {
            if ($width >= $w * 0.9) {
                break;
            }
            if ($url = self::url($path, $width)) {
                $set[] = $url.' '.$width.'w';
            }
        }
        if (! $set) {
            return new HtmlString('');
        }
        $set[] = Storage::disk('public')->url($path).' '.$w.'w';

        return new HtmlString(' srcset="'.e(implode(', ', $set)).'" sizes="'.e($sizes).'"');
    }

    /** Public URL of a variant, generating it if needed (null on failure). */
    public static function url(string $path, int $width): ?string
    {
        $variant = self::variantPath($path, $width);
        $disk = Storage::disk('public');
        if (! $disk->exists($variant) && ! self::generate($path, $width)) {
            return null;
        }

        return $disk->url(implode('/', array_map('rawurlencode', explode('/', $variant))));
    }

    /** Generate every variant for one image. Returns how many exist afterwards. */
    public static function warm(string $path): int
    {
        if (! self::supported() || ! self::resizable($path)) {
            return 0;
        }
        [$w] = self::dimensions($path) ?: [0];
        $n = 0;
        foreach (self::WIDTHS as $width) {
            if ($width >= $w * 0.9) {
                break;
            }
            $variant = self::variantPath($path, $width);
            if (Storage::disk('public')->exists($variant) || self::generate($path, $width)) {
                $n++;
            }
        }

        return $n;
    }

    public static function purge(string $path): void
    {
        $disk = Storage::disk('public');
        foreach (self::WIDTHS as $width) {
            $disk->delete(self::variantPath($path, $width));
        }
    }

    public static function variantPath(string $path, int $width): string
    {
        return self::DIR.'/'.$width.'/'.preg_replace('/\.[A-Za-z0-9]+$/', '', $path).'.webp';
    }

    private static function resizable(string $path): bool
    {
        // GIFs may be animated — resizing would freeze them.
        return (bool) preg_match('/\.(jpe?g|png|webp)$/i', $path)
            && Storage::disk('public')->exists($path);
    }

    /** @return array{0:int,1:int}|false */
    private static function dimensions(string $path): array|false
    {
        if (! array_key_exists($path, self::$dims)) {
            $info = @getimagesize(Storage::disk('public')->path($path));
            self::$dims[$path] = $info ? [(int) $info[0], (int) $info[1]] : false;
        }

        return self::$dims[$path];
    }

    private static function generate(string $path, int $width): bool
    {
        $src = null;
        $dst = null;
        try {
            $dims = self::dimensions($path);
            if (! $dims || $dims[0] * $dims[1] > self::MAX_PIXELS || $width >= $dims[0]) {
                return false;
            }
            $disk = Storage::disk('public');
            $src = @imagecreatefromstring((string) $disk->get($path));
            if (! $src) {
                return false;
            }
            if (! imageistruecolor($src)) {
                imagepalettetotruecolor($src);
            }
            $height = max(1, (int) round($dims[1] * $width / $dims[0]));
            $dst = imagecreatetruecolor($width, $height);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $dims[0], $dims[1]);

            ob_start();
            $ok = imagewebp($dst, null, self::QUALITY);
            $data = ob_get_clean();

            return $ok && $data !== '' && $disk->put(self::variantPath($path, $width), $data);
        } catch (Throwable $e) {
            report($e);

            return false;
        } finally {
            if ($src) {
                imagedestroy($src);
            }
            if ($dst) {
                imagedestroy($dst);
            }
        }
    }
}
