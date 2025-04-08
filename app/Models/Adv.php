<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\StorageTrait;

class Adv extends Model
{
    use HasFactory;
    use StorageTrait;

    protected $table="_adv";

    protected $casts = [
        'title' => 'string',
        'category' => 'string',
        'image' => 'string',
        'link' => 'string',
        'status' => 'boolean',
        'priority' => 'integer',
    ];
    
    protected $fillable = [
        'title',
        'category' ,
        'image' ,
        'link' ,
        'status' ,
        'priority',
    ];

    protected $appends = ['icon_full_url'];
    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }

    public function AdvCategory():BelongsTo{
        return $this->belongsTo(AdvCategory::class,"category");
    }

    public function getIconFullUrlAttribute():array
    {
        $value = $this->image;
        return $this->storageLink('Adv',$value,'public');
    }
}
