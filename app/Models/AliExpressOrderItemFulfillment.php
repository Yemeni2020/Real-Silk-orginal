<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AliExpressOrderItemFulfillment extends Model
{
    protected $table = 'aliexpress_order_item_fulfillments';

    protected $fillable = [
        'order_id',
        'order_detail_id',
        'status',
        'supplier_order_id',
        'supplier_line_id',
        'supplier_order_url',
        'supplier_paid_amount',
        'supplier_currency',
        'carrier',
        'supplier_carrier',
        'tracking_number',
        'supplier_tracking_number',
        'note',
        'notes',
        'last_error',
        'updated_by_admin_id',
        'placed_by_admin_id',
        'placed_at',
        'tracking_synced_at',
    ];

    protected $casts = [
        'supplier_paid_amount' => 'float',
        'placed_by_admin_id' => 'integer',
        'updated_by_admin_id' => 'integer',
        'placed_at' => 'datetime',
        'tracking_synced_at' => 'datetime',
    ];
}
