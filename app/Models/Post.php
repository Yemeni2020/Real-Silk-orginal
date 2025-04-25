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
    protected $table = 'post';
    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable')
        ;
    }
    public function admins(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryPost::class, 'category_id');
    }
    public function getTitleAttribute($title): string|null
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $title;
        }
        $curnnet_lang = getDefaultLanguage();

        $translation = $this->translations->where('locale', $curnnet_lang)->first();
        return $translation->value ?? $title; // إرجاع الترجمة إذا كانت موجودة، وإلا يتم عرض الاسم الأصلي
    }
    public function getNameAttribute(){
        return $this->getTitleAttribute($this->title);
    }
}
