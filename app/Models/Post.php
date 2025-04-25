<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'details',
        'category_id',
        'images',
        'thumbnail',
        'thumbnail_storage_type',
        'video_provider',
        'video_url',
        'meta_title',
        'meta_description',
        'meta_image',
        'meta_keywords',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'user_id' => 'integer',
        'title' => 'string',
        'slug' => 'string',
        'category_id' => 'integer',
        'details' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'thumbnail' => 'string',
        'meta_title' => 'string',
        'meta_description' => 'string',
        'meta_image' => 'string',
        'meta_keywords' => 'integer',
        'video_provider' => 'array',
        'video_url' => 'array',
    ];

    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable')
        ;
    }
}
