<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AliExpressProduct extends Model
{
    protected $table = 'aliexpress_products';

    protected $fillable = [
        'ali_express_product_id',
        'local_product_id',
        'title',
        'description',
        'supplier_price',
        'supplier_shipping_price',
        'selling_price',
        'margin',
        'stock',
        'currency',
        'supplier_currency',
        'images',
        'variants',
        'variant_mappings',
        'supplier_url',
        'supplier_product_url',
        'is_active',
        'is_available',
        'last_synced_at',
        'sync_status',
        'sync_error',
        'supplier_stock_status',
        'source_updated_at',
        'block_reason',
        'warning_flags',
        'raw_payload',
    ];

    protected $casts = [
        'local_product_id' => 'integer',
        'supplier_price' => 'float',
        'supplier_shipping_price' => 'float',
        'selling_price' => 'float',
        'margin' => 'float',
        'stock' => 'integer',
        'images' => 'array',
        'variants' => 'array',
        'variant_mappings' => 'array',
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'last_synced_at' => 'datetime',
        'source_updated_at' => 'datetime',
        'warning_flags' => 'array',
        'raw_payload' => 'array',
    ];
}
