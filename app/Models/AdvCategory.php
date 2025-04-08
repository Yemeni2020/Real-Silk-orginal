<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\StorageTrait;

class AdvCategory extends Model
{
    use HasFactory;
    use StorageTrait;
    protected $table="adv_category";

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'icon' => 'string',
        'priority' => 'integer',
    ];

    protected $fillable = [
        'name',
        'icon',
        'priority',
    ];

    protected $appends = ['icon_full_url'];
    
    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }
    public function Adv():HasMany{
        return $this->hasMany(Adv::class,"category");
    }

    public function getIconFullUrlAttribute():array
    {
        $value = $this->icon;
        return $this->storageLink('Advcategory',$value,'public');
    }
}
