<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'collection_id', 'title', 'slug', 'excerpt', 'body', 'image', 'carton_image',
        'flavor_label', 'flavor_color', 'anchor_id', 'sort', 'is_published', 'wp_id',
    ];

    protected $casts = ['is_published' => 'boolean'];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort')->orderBy('id');
    }

    public function url(): string
    {
        return route('products.show', $this->slug);
    }

    public function imageUrl(): string
    {
        return media_url($this->image, 'bear-fruit.jpeg');
    }

    public function cartonUrl(): string
    {
        return media_url($this->carton_image);
    }
}
