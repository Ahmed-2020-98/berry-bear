<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = ['parent_id', 'label', 'url', 'color', 'open_new_tab', 'sort'];

    protected $casts = ['open_new_tab' => 'boolean'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort')->orderBy('id');
    }

    /** Resolve "/about" style relative URLs against the site root. */
    public function href(): string
    {
        $url = (string) $this->url;
        if ($url === '' || str_starts_with($url, '#') || preg_match('#^(https?:|mailto:|tel:)#', $url)) {
            return $url ?: '#';
        }

        return url($url);
    }
}
