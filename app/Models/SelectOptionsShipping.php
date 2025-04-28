<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelectOptionsShipping extends Model
{
    use HasFactory;
    protected $table="select_option_shipping";

    protected $fillable = [
        'id',
        'name',
        'option_shipping',
    ];
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'option_shipping' => 'integer',
    ];
}
