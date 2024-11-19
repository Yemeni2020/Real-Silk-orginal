<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class optionProductDetails extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'option_id'];

    public function option()
    {
        return $this->belongsTo(ProductOption::class);
    }
}
