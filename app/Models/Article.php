<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title', 'description', 'content','url','published_at', 'source_id', 'category_id', 'author_id'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}