<?php

namespace App\Support;

use App\Models\MediaItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media
{
    /** Formats accepted by the media library (no SVG: it can carry scripts). */
    public const IMAGE_MIMES = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public const MAX_KB = 6144;

    /**
     * Store an upload on the public disk; returns "uploads/{dir}/YYYY/MM/name.ext".
     * Images are also registered in the media library so they can be reused.
     */
    public static function store(UploadedFile $file, string $dir = 'media'): string
    {
        if (str_starts_with((string) $file->getMimeType(), 'image/')) {
            return static::library($file)->path;
        }

        return $file->storeAs('uploads/'.$dir.'/'.date('Y/m'), static::filename($file), 'public');
    }

    /**
     * Add an image to the library. Identical contents already in the library
     * return the existing record instead of writing a second copy.
     */
    public static function library(UploadedFile $file, ?int $userId = null): MediaItem
    {
        $hash = sha1_file($file->getRealPath());
        $existing = MediaItem::query()->where('hash', $hash)->first();
        if ($existing && Storage::disk('public')->exists($existing->path)) {
            return $existing;
        }

        $name = static::filename($file);
        $path = $file->storeAs('uploads/library/'.date('Y/m'), $name, 'public');
        [$w, $h] = static::dimensions($file->getRealPath());

        return MediaItem::create([
            'original_name' => Str::limit($file->getClientOriginalName(), 240, ''),
            'filename' => $name,
            'path' => $path,
            'mime_type' => (string) $file->getMimeType(),
            'size' => (int) $file->getSize(),
            'width' => $w,
            'height' => $h,
            'hash' => $hash,
            'uploaded_by' => $userId ?? auth()->id(),
        ]);
    }

    /** Register a file that already sits on the public disk (imports / sync). */
    public static function register(string $path): ?MediaItem
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($path) || MediaItem::query()->where('path', $path)->exists()) {
            return null;
        }
        $abs = $disk->path($path);
        $mime = (string) (mime_content_type($abs) ?: '');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! str_starts_with($mime, 'image/') || ! in_array($ext, static::IMAGE_MIMES, true)) {
            return null;
        }
        [$w, $h] = static::dimensions($abs);

        return MediaItem::create([
            'original_name' => basename($path),
            'filename' => basename($path),
            'path' => $path,
            'mime_type' => $mime,
            'size' => (int) filesize($abs),
            'width' => $w,
            'height' => $h,
            'hash' => sha1_file($abs),
        ]);
    }

    protected static function filename(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';

        return Str::limit($base, 50, '').'-'.Str::lower(Str::random(6)).'.'.$ext;
    }

    /** @return array{0:?int,1:?int} */
    protected static function dimensions(string $abs): array
    {
        $info = @getimagesize($abs);

        return $info ? [(int) $info[0], (int) $info[1]] : [null, null];
    }
}
