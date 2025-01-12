<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class DetailsOrderService extends Model
{
    use HasFactory;
    protected $table = 'details_order_service';

    protected $fillable = [
        'order_id',
        'faild_id',
        'name_faild',
        'type_faild',
        'value',
    ];

    // العلاقة مع OrderService
    public function order()
    {
        return $this->belongsTo(OrderService::class, 'order_id', 'id');
    }
}
