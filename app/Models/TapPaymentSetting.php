<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TapPaymentSetting extends Model
{
    use HasFactory;
    protected $fillable = ['key', 'value','Type','url_back','method','logo'];

}
