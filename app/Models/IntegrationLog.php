<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    protected $fillable = [
        'provider',
        'action',
        'external_id',
        'status',
        'message',
        'payload',
        'request_id',
        'created_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_by' => 'integer',
    ];
}
