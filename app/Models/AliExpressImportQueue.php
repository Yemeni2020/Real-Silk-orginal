<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AliExpressImportQueue extends Model
{
    protected $table = 'aliexpress_import_queues';

    protected $fillable = [
        'batch_id',
        'source_input',
        'ali_express_product_id',
        'status',
        'attempts',
        'message',
        'error_message',
        'store_product_id',
        'queued_by_admin_id',
        'created_by',
        'started_at',
        'finished_at',
        'cancelled_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'store_product_id' => 'integer',
        'queued_by_admin_id' => 'integer',
        'created_by' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
}
