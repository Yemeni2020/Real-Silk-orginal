<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureAdmin extends Model
{
    use HasFactory;
    protected $table = 'signatures_admin';
    protected $fillable = [
        'signature_path',
        'code_change',
    ];
}
