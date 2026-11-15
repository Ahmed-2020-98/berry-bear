<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Validates + normalises a schema form submission:
 *   data[field]                (scalars, repeater rows)
 *   files[field] / files[field][i][sub]   (uploads)
 */
class ContentSaver
{
    public const IMAGE_RULE = ['file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:6144'];

    public static function save(Request $request, string $key): array
    {
        $fields = ContentSchema::fields($key);
        $request->validate(static::rules($fields), [], static::attributes($fields));

        $input = (array) $request->input('data', []);
        $out = [];
        foreach ($fields as $field) {
            $out[$field['name']] = static::value($field, $input[$field['name']] ?? null, $request, 'files.'.$field['name']);
        }

        return $out;
    }

    protected static function value(array $field, mixed $raw, Request $request, string $fileKey): mixed
    {
        switch ($field['type']) {
            case 'repeater':
                $rows = [];
                foreach (array_values(is_array($raw) ? $raw : []) as $i => $row) {
                    $clean = [];
                    foreach ($field['fields'] as $sub) {
                        $clean[$sub['name']] = static::value($sub, $row[$sub['name']] ?? null, $request, "$fileKey.$i.{$sub['name']}");
                    }
                    // keep any extra keys the schema doesn't edit (e.g. legacy "media")
                    $clean += Arr::except((array) $row, array_keys($clean));
                    if (collect($clean)->filter(fn ($v) => trim((string) $v) !== '')->isNotEmpty()) {
                        $rows[] = $clean;
                    }
                }

                return $rows;

            case 'image':
            case 'file':
                $upload = $request->file($fileKey);
                if ($upload instanceof UploadedFile) {
                    return Media::store($upload, 'content');
                }

                return trim((string) $raw);

            case 'toggle':
                return in_array($raw, ['1', 1, true, 'on'], true) ? '1' : '0';

            case 'color':
                $raw = trim((string) $raw);
                if ($raw !== '' && ! preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $raw)) {
                    throw ValidationException::withMessages([str_replace('files.', 'data.', $fileKey) => 'اللون يجب أن يكون بصيغة ‎#RRGGBB.']);
                }

                return $raw;

            case 'select':
                $raw = (string) $raw;

                return array_key_exists($raw, $field['options']) ? $raw : (string) array_key_first($field['options']);

            default:
                return is_scalar($raw) ? trim((string) $raw) : '';
        }
    }

    protected static function rules(array $fields): array
    {
        $rules = ['data' => ['array'], 'files' => ['array']];
        foreach ($fields as $f) {
            if ($f['type'] === 'repeater') {
                $rules["data.{$f['name']}"] = ['nullable', 'array', 'max:60'];
                foreach ($f['fields'] as $sub) {
                    if (in_array($sub['type'], ['image', 'file'], true)) {
                        $rules["files.{$f['name']}.*.{$sub['name']}"] = static::uploadRule($sub);
                    } elseif ($sub['type'] === 'number') {
                        $rules["data.{$f['name']}.*.{$sub['name']}"] = ['nullable', 'numeric'];
                    } else {
                        $rules["data.{$f['name']}.*.{$sub['name']}"] = ['nullable', 'string', 'max:5000'];
                    }
                }
            } elseif (in_array($f['type'], ['image', 'file'], true)) {
                $rules["files.{$f['name']}"] = static::uploadRule($f);
            } elseif ($f['type'] === 'number') {
                $rules["data.{$f['name']}"] = ['nullable', 'numeric'];
            } elseif ($f['type'] !== 'toggle') {
                $rules["data.{$f['name']}"] = ['nullable', 'string', 'max:20000'];
            }
        }

        return $rules;
    }

    protected static function uploadRule(array $f): array
    {
        if ($f['type'] === 'image') {
            return array_merge(['nullable'], static::IMAGE_RULE);
        }

        return ['nullable', 'file', 'mimes:'.($f['accept'] ?? 'pdf,mp4,webm'), 'max:30720'];
    }

    protected static function attributes(array $fields): array
    {
        $attrs = [];
        foreach ($fields as $f) {
            $attrs["data.{$f['name']}"] = $f['label'];
            $attrs["files.{$f['name']}"] = $f['label'];
            foreach ($f['fields'] ?? [] as $sub) {
                $attrs["data.{$f['name']}.*.{$sub['name']}"] = $sub['label'];
                $attrs["files.{$f['name']}.*.{$sub['name']}"] = $sub['label'];
            }
        }

        return $attrs;
    }
}
