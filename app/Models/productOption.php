<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class productOption extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type', 'product_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function details()
    {
        return $this->hasMany(optionProductDetails::class);
    }
}
