<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderService extends Model
{
    use HasFactory;
    protected $table = 'order_service';

    protected $fillable = [
        'item',
        'customer',
    ];

    // العلاقة مع DetailsOrderService
    public function details()
    {
        return $this->hasMany(DetailsOrderService::class, 'order_id', 'id');
    }
    // العلاقة مع Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'item', 'id');
    }
}
