<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AliExpressProduct extends Model
{
    protected $table = 'aliexpress_products';

    protected $fillable = [
        'ali_express_product_id',
        'title',
        'description',
        'supplier_price',
        'selling_price',
        'margin',
        'stock',
        'currency',
        'images',
        'variants',
        'supplier_url',
        'is_active',
        'raw_payload',
    ];

    protected $casts = [
        'supplier_price' => 'float',
        'selling_price' => 'float',
        'margin' => 'float',
        'stock' => 'integer',
        'images' => 'array',
        'variants' => 'array',
        'is_active' => 'boolean',
        'raw_payload' => 'array',
    ];
}
