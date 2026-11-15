<?php

namespace App\Support;

use App\Models\PageContent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Editable content store (page sections + site settings).
 * Mirrors berry_field(): an empty value always falls back to the default.
 */
class Content
{
    protected static array $loaded = [];

    public static function data(string $key): array
    {
        if (! array_key_exists($key, static::$loaded)) {
            static::$loaded[$key] = Cache::rememberForever(static::cacheKey($key), function () use ($key) {
                if (! Schema::hasTable('page_contents')) {
                    return [];
                }

                return PageContent::where('key', $key)->value('data') ?? [];
            });
        }

        return static::$loaded[$key];
    }

    /**
     * Stored value, else the explicit default, else the schema default from
     * config/content_defaults.php (the original theme copy).
     */
    public static function get(string $key, string $field, mixed $default = null): mixed
    {
        $val = static::data($key)[$field] ?? null;
        if (! static::isEmpty($val)) {
            return $val;
        }

        return $default ?? static::default($key, $field);
    }

    public static function default(string $key, string $field): mixed
    {
        return config("content_defaults.$key.$field", '');
    }

    /** Stored values merged over defaults (used to prefill admin forms). */
    public static function withDefaults(string $key): array
    {
        $out = config("content_defaults.$key", []);
        foreach (static::data($key) as $k => $v) {
            if (! static::isEmpty($v) || ! array_key_exists($k, $out)) {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    public static function put(string $key, array $data): void
    {
        PageContent::updateOrCreate(['key' => $key], ['data' => $data]);
        static::forget($key);
    }

    public static function merge(string $key, array $data): void
    {
        static::put($key, array_merge(PageContent::where('key', $key)->value('data') ?? [], $data));
    }

    public static function forget(string $key): void
    {
        Cache::forget(static::cacheKey($key));
        unset(static::$loaded[$key]);
    }

    /** Drop the per-request memo (tests / long-running workers). */
    public static function flushMemo(): void
    {
        static::$loaded = [];
    }

    public static function isEmpty(mixed $val): bool
    {
        return $val === null || $val === '' || $val === false || (is_array($val) && empty($val));
    }

    protected static function cacheKey(string $key): string
    {
        return 'content.'.$key;
    }
}
