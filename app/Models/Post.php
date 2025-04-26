<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\StorageTrait;

class Post extends Model
{
    use HasFactory;
    use StorageTrait;

    protected $fillable = [
        'id',
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
        'id' => 'integer',
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
        'meta_keywords' => 'string',
        'video_provider' => 'array',
        'video_url' => 'array',
    ];
    protected $table = 'post';
    protected $appends = [ 'thumbnail_full_url', 'meta_image_full_url', 'images_full_url'];

    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
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

    public function getDetailsAttribute($detail): string|null
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $detail;
        }
        $curnnet_lang = getDefaultLanguage();
        $translation = $this->translations->where('locale', $curnnet_lang)->where('key', 'description')->first();
        return $translation->value ?? $detail; 
        // return $this->translations[1]->value ?? $detail;
    }

    public function getNameAttribute(){
        return $this->getTitleAttribute($this->title);
    }

    public function seoInfo(): HasOne
    {
        return $this->hasOne(PostSeo::class, 'post_id', 'id');
        // post_id هو المفتاح الموجود بجدول post_seo
        // id هو المفتاح الأساسي بجدول posts
    }
    public function getThumbnailFullUrlAttribute(): string|null|array
    {
        $value = $this->thumbnail;
        return $this->storageLink('post/thumbnail', $value, $this->thumbnail_storage_type ?? 'public');
    }
    public function getMetaImageFullUrlAttribute(): array
    {
        $value = $this->meta_image;
        return $this->storageLink('post/meta', $value, 'public');
    }
    public function getImagesFullUrlAttribute(): array
    {
        $images = [];
        $value = json_decode($this->images);
         if ($value){
             foreach ($value as $item){
                 $item = isset($item->image_name) ? (array)$item : ['image_name' => $item, 'storage' => 'public'];
                 $images[] =  $this->storageLink('post',$item['image_name'],$item['storage'] ?? 'public');
             }
         }
        return $images;
    }
}
