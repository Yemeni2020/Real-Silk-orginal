<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'item',
        'item_name',
        'item_type',
        'item_order',
        'is_required',
        'item_length',
        'default_value',
        'select_options',
    ];


    protected $casts = [
        'select_options' => 'array', // لتعامل السلسلة النصية مع خيارات التحديد كـ JSON
    ];
}
