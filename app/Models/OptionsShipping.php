<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionsShipping extends Model
{
    use HasFactory;
    protected $table="options_shipping";
    protected $fillable = [
        'id',
        'name',
        'shipping_method',
    ];
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'shipping_method' => 'string',
    ];

    public function shippingMethod():BelongsTo{
        return $this->belongsTo(ShippingMethod::class, "shipping_method", "id");
    }
    public function options():HasMany{
        return $this->hasMany(SelectOptionsShipping::class,"option_shipping","id");
    }
    
}
