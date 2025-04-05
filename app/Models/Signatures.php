<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signatures extends Model
{
    use HasFactory;
    protected $fillable = ['seller', 'signature_path', 'contract_path'];

    // ✅ علاقة مع التاجر (Seller)
    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller', 'id');
    }
}
