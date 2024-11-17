<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOffer extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'q_from', 'q_to', 'price_unit'];

    // تعريف العلاقة مع المنتجات
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
