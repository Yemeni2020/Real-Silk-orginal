<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeService extends Model
{
    use HasFactory;
    protected $fillable = ['office', 'service', 'status'];
    protected $table = "office_service";

    // تعريف العلاقة لجلب بيانات المكتب بالكامل
    // علاقة المكتب بالخدمات (كل مكتب يمكن أن يكون لديه عدة خدمات)
    public function office()
    {
        return $this->belongsTo(Seller::class, 'office', 'id');
    }

    // علاقة الخدمة بالمكاتب (كل خدمة يمكن أن يكون لديها عدة مكاتب)
    public function service()
    {
        return $this->belongsTo(Product::class, 'service', 'id');
    }
}
