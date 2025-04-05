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
    public function getPriceUnitAttribute($value): float
    {
        // لا تغيّر السعر في لوحة التحكم
        if (
            strpos(url()->current(), '/admin') !== false ||
            strpos(url()->current(), '/vendor') !== false ||
            strpos(url()->current(), '/seller') !== false
        ) {
            return (float) $value;
        }

        // استخدام optional لتجنب الأخطاء إن لم تكن العلاقات محملة
        if (optional($this->product)->added_by === 'seller') {
            $commission = optional($this->product->seller)->sales_commission_percentage;

            $multiplier = $commission
                ? ($commission / 100) + 1
                : ((getWebConfig('sales_commission') ?? 0) / 100) + 1;

            return round((float) $value * $multiplier, 2);
        }

        return (float) $value;
    }
}
