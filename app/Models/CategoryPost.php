<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CategoryPost extends Model
{
    use HasFactory;
    protected $table = 'category_post';
    protected $fillable = [
        'name',
    ];
    
    protected $casts = [
        'name' => 'string',
    ];
    
    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }

    public function getNameAttribute($name): string|null
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $name;
        }
        $curnnet_lang = getDefaultLanguage();

        $translation = $this->translations->where('locale', $curnnet_lang)->first();
        return $translation->value ?? $name; // إرجاع الترجمة إذا كانت موجودة، وإلا يتم عرض الاسم الأصلي

        // return $this->translations[0]->value ?? $name;
    }

    public function getDefaultNameAttribute(): string|null
    {
        $curnnet_lang = getDefaultLanguage();

        $translation = $this->translations->where('locale', $curnnet_lang)->first();
        return $translation->value ?? $this->name;
        // return  $this->name;
    }
}
