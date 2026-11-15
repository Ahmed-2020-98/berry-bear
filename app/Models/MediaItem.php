<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** One file in the central media library (images only). */
class MediaItem extends Model
{
    protected $table = 'media';

    protected $fillable = ['original_name', 'filename', 'path', 'mime_type', 'size', 'width', 'height', 'alt', 'hash', 'uploaded_by'];

    protected $casts = ['size' => 'integer', 'width' => 'integer', 'height' => 'integer'];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }
        $like = '%'.$term.'%';

        return $q->where(fn ($w) => $w->where('original_name', 'like', $like)
            ->orWhere('filename', 'like', $like)
            ->orWhere('alt', 'like', $like));
    }

    public function url(): string
    {
        return media_url($this->path);
    }

    public function sizeLabel(): string
    {
        $kb = $this->size / 1024;

        return $kb >= 1024 ? number_format($kb / 1024, 1).' MB' : number_format(max($kb, 0.1), 0).' KB';
    }

    /**
     * Where this file is referenced (products, posts, pages, page content, settings).
     * Values are stored as the relative path, so a plain LIKE finds every use.
     *
     * @return array<int, array{label:string, url:?string}>
     */
    public function usages(): array
    {
        $like = '%'.$this->path.'%'; // "_" may over-match one char; harmless here
        $out = [];

        foreach (Product::query()->where(fn ($q) => $q->where('image', $this->path)->orWhere('carton_image', $this->path)->orWhere('body', 'like', $like))->get(['id', 'title']) as $p) {
            $out[] = ['label' => 'منتج: '.$p->title, 'url' => route('admin.products.edit', $p)];
        }
        foreach (Post::query()->where(fn ($q) => $q->where('cover', $this->path)->orWhere('body', 'like', $like))->get(['id', 'title']) as $p) {
            $out[] = ['label' => 'مقال: '.$p->title, 'url' => route('admin.posts.edit', $p)];
        }
        foreach (Page::query()->where('body', 'like', $like)->get(['id', 'title']) as $p) {
            $out[] = ['label' => 'صفحة: '.$p->title, 'url' => route('admin.pages.edit', $p)];
        }
        // Page content / settings are a handful of JSON rows: check them in PHP (portable across DBs).
        $pages = \App\Support\ContentSchema::pages();
        foreach (PageContent::query()->get(['key', 'data']) as $row) {
            if (! str_contains(json_encode($row->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $this->path)) {
                continue;
            }
            $out[] = $row->key === 'settings'
                ? ['label' => 'إعدادات الموقع', 'url' => route('admin.settings')]
                : ['label' => 'محتوى صفحة: '.($pages[$row->key] ?? $row->key), 'url' => route('admin.content.edit', $row->key)];
        }

        return $out;
    }

    public function toPickerArray(): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'url' => $this->url(),
            'src' => '/storage/'.$this->path, // host-independent, used inside rich-text bodies
            'name' => $this->original_name,
            'filename' => $this->filename,
            'mime' => $this->mime_type,
            'type' => strtoupper(pathinfo($this->filename, PATHINFO_EXTENSION)),
            'size' => $this->sizeLabel(),
            'width' => $this->width,
            'height' => $this->height,
            'alt' => (string) $this->alt,
            'date' => $this->created_at?->format('Y-m-d H:i'),
            'date_label' => $this->created_at ? ar_date($this->created_at) : '',
        ];
    }

    protected static function booted(): void
    {
        // Removing the record removes the file, unless another record points at it.
        static::deleted(function (MediaItem $m) {
            if (! static::query()->where('path', $m->path)->exists()) {
                Storage::disk('public')->delete($m->path);
                \App\Support\ImageVariants::purge($m->path);
            }
        });
    }
}
