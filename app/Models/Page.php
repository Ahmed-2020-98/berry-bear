<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'body', 'is_published', 'wp_id'];

    protected $casts = ['is_published' => 'boolean'];
}
