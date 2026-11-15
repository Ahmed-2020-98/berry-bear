<?php

namespace App\Support;

/**
 * Reads config/content.php and normalises field specs
 * ([name, label, type, extra]) into associative arrays.
 */
class ContentSchema
{
    public const TYPES = ['text', 'textarea', 'html', 'url', 'image', 'file', 'color', 'select', 'toggle', 'number', 'repeater'];

    /** @return array<string,string> page key => title */
    public static function pages(): array
    {
        return collect(config('content.pages'))->map(fn ($p) => $p['title'])->all();
    }

    public static function has(string $key): bool
    {
        return $key === 'settings' || array_key_exists($key, config('content.pages', []));
    }

    /** Group definition (title + normalised tabs) for a page key or 'settings'. */
    public static function group(string $key): array
    {
        $raw = $key === 'settings' ? config('content.settings') : config("content.pages.$key");
        abort_if(! $raw, 404);

        $tabs = [];
        foreach ($raw['tabs'] as $tab => $fields) {
            $tabs[$tab] = array_map([self::class, 'field'], $fields);
        }

        return ['key' => $key, 'title' => $raw['title'], 'tabs' => $tabs];
    }

    /** @return array<int,array> flat list of top-level fields */
    public static function fields(string $key): array
    {
        return array_merge(...array_values(self::group($key)['tabs']));
    }

    public static function field(array $spec): array
    {
        [$name, $label] = $spec;
        $type = $spec[2] ?? 'text';
        $extra = $spec[3] ?? [];

        $field = array_merge(['name' => $name, 'label' => $label, 'type' => $type, 'help' => null], $extra);

        if ($type === 'select' && ($field['options'] ?? null) === 'social') {
            $field['options'] = Social::platforms();
        }
        if ($type === 'repeater') {
            $field['fields'] = array_map([self::class, 'field'], $extra['fields'] ?? []);
            $field['button'] ??= 'إضافة عنصر';
        }

        return $field;
    }
}
