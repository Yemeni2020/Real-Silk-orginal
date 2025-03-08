<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $icon
 * @property int $parent_id
 * @property int $position
 * @property int $home_status
 * @property int $priority
 */
class Category extends Model
{
    use StorageTrait;
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'icon_storage_type',
        'image_ad',
        'parent_id',
        'position',
        'home_status',
        'priority',
        "menu",
        "brands",
    ];

    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
        'icon' => 'string',
        'icon_storage_type' => 'string',
        'image_ad' => 'string',
        'parent_id' => 'integer',
        'position' => 'integer',
        'home_status' => 'integer',
        'priority' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id')->orderBy('priority', 'desc');
    }

    public function childes(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('priority', 'asc');
    }

    public function product(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    // Old Relation: sub_category_product
    public function subCategoryProduct(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_category_id', 'id');
    }

    // Old Relation: sub_sub_category_product
    public function subSubCategoryProduct(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_sub_category_id', 'id');
    }

    public function getNameAttribute($name): string|null
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $name;
        }
        $curnnet_lang = session()->get("local");

        $translation = $this->translations->where('locale', $curnnet_lang)->first();
        return $translation->value ?? $name; // إرجاع الترجمة إذا كانت موجودة، وإلا يتم عرض الاسم الأصلي

        // return $this->translations[0]->value ?? $name;
    }

    public function getDefaultNameAttribute(): string|null
    {
        return $this->translations[0]->value ?? $this->name;
    }


    public function scopePriority($query): mixed
    {
        return $query->orderBy('priority', 'asc');
    }

    public function getIconFullUrlAttribute():array
    {
        $value = $this->icon;
        return $this->storageLink('category',$value,$this->icon_storage_type ?? 'public');
    }
    public function getAdvFullUrlAttribute():array
    {
        $value = $this->image_ad;
        return $this->storageLink('category/image_ad',$value,$this->icon_storage_type ?? 'public');
    }
    protected $appends = ['icon_full_url','adv_full_url'];

    // protected static function boot(): void
    // {
    //     parent::boot();
    //     static::addGlobalScope('translate', function (Builder $builder) {
    //         $builder->with(['translations' => function ($query) {
    //             if (strpos(url()->current(), '/api')) {
    //                 return $query->where('locale', App::getLocale());
    //             } else {
    //                 return $query->where('locale', getDefaultLanguage());
    //             }
    //         }]);
    //     });
    // }
}
