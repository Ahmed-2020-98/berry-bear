<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title', 'slug', 'category', 'excerpt', 'body', 'cover', 'reading_time', 'status', 'published_at', 'wp_id',
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeLatestFirst(Builder $q): Builder
    {
        return $q->orderByDesc('published_at')->orderByDesc('id');
    }

    public function url(): string
    {
        return route('posts.show', $this->slug);
    }

    public function coverUrl(string $fallback = 'life-white.jpeg'): string
    {
        return media_url($this->cover, $fallback);
    }

    public function readingMinutes(): int
    {
        if ($this->reading_time) {
            return (int) $this->reading_time;
        }
        $words = count(array_filter(preg_split('/\s+/u', trim(strip_tags((string) $this->body)))));

        return max(1, (int) ceil($words / 200));
    }

    public function metaLine(): string
    {
        return ar_date($this->published_at ?? $this->created_at).' · '.ar_digits($this->readingMinutes()).' دقائق قراءة';
    }

    public function excerptText(): string
    {
        return $this->excerpt ?: \Illuminate\Support\Str::limit(trim(strip_tags((string) $this->body)), 160);
    }
}
