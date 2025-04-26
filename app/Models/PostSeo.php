<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\StorageTrait;

class PostSeo extends Model
{
    use HasFactory, StorageTrait;
    protected $fillable = [
        'post_id',
        'title',
        'description',
        'meta_keywords',
        'index',
        'no_follow',
        'no_image_index',
        'no_archive',
        'no_snippet',
        'max_snippet',
        'max_snippet_value',
        'max_video_preview',
        'max_video_preview_value',
        'max_image_preview',
        'max_image_preview_value',
        'image',
    ];

    protected $casts = [
        'post_id' => 'integer',
        'title' => 'string',
        'description' => 'string',
        'meta_keywords' => 'string',
        'index' => 'string',
        'no_follow' => 'string',
        'no_image_index' => 'string',
        'no_archive' => 'string',
        'no_snippet' => 'string',
        'max_snippet' => 'string',
        'max_snippet_value' => 'string',
        'max_video_preview' => 'string',
        'max_video_preview_value' => 'string',
        'max_image_preview' => 'string',
        'max_image_preview_value' => 'string',
        'image' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $table="post_seo";
    public function getImageFullUrlAttribute(): array
    {
        $value = $this->image;
        if (count($this->storage) > 0 ) {
            $storage = $this->storage->where('key','image')->first();
        }
        return $this->storageLink('post/meta', $value, $storage['value'] ?? 'public');
    }

    protected $appends = ['image_full_url'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
        // post_id هو المفتاح الأجنبي الموجود بجدول post_seo
        // id هو المفتاح الأساسي بجدول posts
    }
    protected static function boot(): void
    {
        parent::boot();
        static::saved(function ($model) {
            if ($model->isDirty('image')) {
                $storage = config('filesystems.disks.default') ?? 'public';
                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'image',
                ], [
                    'value' => $storage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
