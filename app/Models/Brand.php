<?php

namespace App\Models;

use App\Traits\SettingsTrait;
use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $name
 * @property string $image
 * @property int $status
 */
class Brand extends Model
{
    use StorageTrait;
    protected $fillable = [
        'name',
        'image',
        'image_storage_type',
        'image_alt_text',
        'status',
        'seller_id'
    ];

    protected $casts = [
        'name' => 'string',
        'image' => 'string',
        'image_storage_type' => 'string',
        'image_alt_text' => 'string',
        'status' => 'integer',
        'brand_products_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive(): mixed
    {
        return $this->where('status',1);
    }

    public function brandProducts(): HasMany
    {
        return $this->hasMany(Product::class)->select('id', 'added_by','user_id','name','slug','product_type','category_ids','category_id','sub_category_id','sub_sub_category_id','brand_id','unit','min_qty','production_period','refundable','digital_product_type','digital_file_ready','digital_file_ready_storage_type','images','color_image','thumbnail','thumbnail_storage_type','preview_file','preview_file_storage_type','featured','flash_deal','video_provider','video_url','published','unit_price','purchase_price','tax','tax_type','tax_model','discount','discount_type','current_stock','minimum_order_qty','free_shipping','attachment','created_at','updated_at','status','featured_status','request_status','denied_note','shipping_cost','multiply_qty','temp_shipping_cost','is_shipping_cost_updated','code')->active();
    }

    public function brandAllProducts(): HasMany
    {
        return $this->hasMany(Product::class)->select('id', 'added_by','user_id','name','slug','product_type','category_ids','category_id','sub_category_id','sub_sub_category_id','brand_id','unit','min_qty','production_period','refundable','digital_product_type','digital_file_ready','digital_file_ready_storage_type','images','color_image','thumbnail','thumbnail_storage_type','preview_file','preview_file_storage_type','featured','flash_deal','video_provider','video_url','published','unit_price','purchase_price','tax','tax_type','tax_model','discount','discount_type','current_stock','minimum_order_qty','free_shipping','attachment','created_at','updated_at','status','featured_status','request_status','denied_note','shipping_cost','multiply_qty','temp_shipping_cost','is_shipping_cost_updated','code');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }

    public function getNameAttribute($name): string|null
    {
        // if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
        //     return $name;
        // }
        $curnnet_lang = getDefaultLanguage();
        $translation = $this->translations->where('locale', $curnnet_lang)->first();
        return $translation->value ?? $name; // إرجاع الترجمة إذا كانت موجودة، وإلا يتم عرض الاسم الأصلي

        // return $this->translations[0]->value??$name;
    }

    public function getDefaultNameAttribute(): string|null
    {
        // return $this->translations[0]->value ?? $this->name;
        return $this->name;
    }
    public function storage():MorphMany
    {
        return $this->morphMany(Storage::class, 'data');
    }
    public function getImageFullUrlAttribute():array
    {
        $value = $this->image;
        return $this->storageLink('brand',$value,$this->image_storage_type ??'public');
    }
    protected $appends = ['image_full_url'];
    // protected static function boot(): void
    // {
    //     parent::boot();
    //     static::addGlobalScope('translate', function (Builder $builder) {
    //         $builder->with(['translations' => function ($query) {
    //             if (strpos(url()->current(), '/api')){
    //                 return $query->where('locale', App::getLocale());
    //             }else{
    //                 return $query->where('locale', getDefaultLanguage());
    //             }
    //         }]);
    //     });
    // }
}
