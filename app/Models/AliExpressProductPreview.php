<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AliExpressProductPreview extends Model
{
    protected $table = 'aliexpress_product_previews';

    protected $fillable = [
        'source_input',
        'ali_express_product_id',
        'title',
        'normalized_title',
        'supplier_price',
        'supplier_shipping_price',
        'final_price',
        'estimated_profit',
        'currency',
        'images',
        'variants',
        'supplier_url',
        'availability_status',
        'warnings',
        'block_reasons',
        'policy_status',
        'pricing_payload',
        'normalized_payload',
        'raw_payload',
        'status',
        'category_id',
        'created_by',
        'local_product_id',
        'message',
        'imported_at',
        'published_at',
        'skipped_at',
    ];

    protected $casts = [
        'supplier_price' => 'float',
        'supplier_shipping_price' => 'float',
        'final_price' => 'float',
        'estimated_profit' => 'float',
        'images' => 'array',
        'variants' => 'array',
        'warnings' => 'array',
        'block_reasons' => 'array',
        'pricing_payload' => 'array',
        'normalized_payload' => 'array',
        'raw_payload' => 'array',
        'category_id' => 'integer',
        'created_by' => 'integer',
        'local_product_id' => 'integer',
        'imported_at' => 'datetime',
        'published_at' => 'datetime',
        'skipped_at' => 'datetime',
    ];
}
