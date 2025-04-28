<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
