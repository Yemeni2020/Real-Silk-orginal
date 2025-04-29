<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingCountry extends Model
{
    use HasFactory;
    protected $table="shipping_country";

    protected $fillable = [
        'id',
        'name',
        'shipping_method',
    ];
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'shipping_method' => 'integer',
    ];
    public function Countries():BelongsTo{
        return $this->belongsTo(ShippingMethod::class,"shipping_method","id");
    }
}
