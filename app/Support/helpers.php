<?php

use App\Support\Content;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

if (! function_exists('content')) {
    /** Page section field with fallback (berry_field / berry_home_field). */
    function content(string $page, string $field, mixed $default = null): mixed
    {
        return Content::get($page, $field, $default);
    }
}

if (! function_exists('setting')) {
    /** Site setting with fallback (berry_option). */
    function setting(string $field, mixed $default = null): mixed
    {
        return Content::get('settings', $field, $default);
    }
}

if (! function_exists('theme_img')) {
    function theme_img(string $file): string
    {
        return asset('theme/img/'.ltrim($file, '/'));
    }
}

if (! function_exists('media_url')) {
    /**
     * Resolve a stored media value into a URL:
     *  - "uploads/..."   → public storage
     *  - absolute / "/…" → as-is
     *  - bare filename   → theme asset (berry_image_src behaviour)
     */
    function media_url(mixed $val, string $fallback = ''): string
    {
        if (is_array($val)) {
            $val = $val['url'] ?? '';
        }
        $val = trim((string) $val);
        if ($val === '') {
            return $fallback ? theme_img($fallback) : '';
        }
        if (preg_match('#^(https?:)?//#', $val) || str_starts_with($val, '/') || str_starts_with($val, 'data:')) {
            return $val;
        }
        if (str_starts_with($val, 'uploads/')) {
            return asset('storage/'.$val);
        }

        return theme_img($val);
    }
}

if (! function_exists('site_url')) {
    /** Resolve a stored link: relative "/about" → absolute; others untouched. */
    function site_url(?string $url, string $default = '/'): string
    {
        $url = trim((string) $url) ?: $default;
        if (str_starts_with($url, '#') || preg_match('#^([a-z]+:|//)#i', $url)) {
            return $url;
        }

        return url($url);
    }
}

if (! function_exists('ar_digits')) {
    function ar_digits(mixed $str): string
    {
        return str_replace(range(0, 9), ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'], (string) $str);
    }
}

if (! function_exists('ar_date')) {
    function ar_date(?Carbon $date): string
    {
        if (! $date) {
            return '';
        }
        $months = [1 => 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];

        return ar_digits($date->day).' '.$months[$date->month].' '.ar_digits($date->year);
    }
}

if (! function_exists('delay_class')) {
    /** Reveal delay class for the Nth card (berry_delay). */
    function delay_class(int $i, int $mod = 3): string
    {
        return ['', 'd1', 'd2', 'd3'][$i % $mod];
    }
}

if (! function_exists('safe_html')) {
    /** Sanitised HTML for "HTML allowed" fields and rich bodies (wp_kses_post). */
    function safe_html(mixed $html): HtmlString
    {
        return new HtmlString(clean((string) $html));
    }
}

if (! function_exists('flatten_text')) {
    /** Repeater rows of {key} or plain strings → list of non-empty strings. */
    function flatten_text(mixed $rows, string $key = 'text'): array
    {
        $out = [];
        foreach ((array) $rows as $r) {
            $v = is_array($r) ? ($r[$key] ?? '') : $r;
            if (trim((string) $v) !== '') {
                $out[] = $v;
            }
        }

        return $out;
    }
}

if (! function_exists('text_lines')) {
    function text_lines(?string $text): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", (string) $text))));
    }
}

if (! function_exists('hex_color')) {
    function hex_color(?string $color, string $default = '#E51B6E'): string
    {
        return preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', (string) $color) ? $color : $default;
    }
}

if (! function_exists('unique_slug')) {
    /** URL slug that keeps Arabic letters (like WordPress) and is unique for the model. */
    function unique_slug(string $model, string $source, ?int $ignoreId = null): string
    {
        $base = trim(preg_replace('/[^\p{L}\p{M}\p{N}]+/u', '-', mb_strtolower(trim($source))), '-') ?: 'item';
        $base = mb_substr($base, 0, 150);
        $slug = $base;
        $i = 2;
        while ($model::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}

if (! function_exists('reserved_slugs')) {
    /** First URL segments taken by fixed routes. */
    function reserved_slugs(): array
    {
        return ['admin', 'about', 'products', 'product', 'collection', 'faqs', 'contact', 'blog', 'category', 'search', 'storage', 'theme', 'build', 'up', 'wp-admin', 'wp-login.php'];
    }
}
