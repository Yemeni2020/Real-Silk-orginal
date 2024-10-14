<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $f_name
 * @property string $l_name
 * @property string $phone
 * @property string $image
 * @property string $email
 * @property string $password
 * @property string $status
 * @property string $bank_name
 * @property string $branch
 * @property string $account_no
 * @property string $holder_name
 * @property string $auth_token
 * @property float $sales_commission_percentage
 * @property float $gst
 * @property string $cm_firebase_token
 * @property string $pos_status
 * @property float $minimum_order_amount
 * @property string $free_delivery_status
 * @property float $free_delivery_over_amount
 * @property string $app_language
 */
class factory extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'free_delivery_over_amount',
    ];
    protected $casts = [
        'id' => 'integer',
        'orders_count' => 'integer',
        'product_count' => 'integer',
        'pos+status' => 'integer'
    ];

    public function scopeApproved($query)
    {
        return $query->where(['status'=>'approved']);
    }

    public function shop():HasOne
    {
        return $this->hasOne(Shop::class, 'factory_id');
    }

    public function shops():HasMany
    {
        return $this->hasMany(Shop::class, 'factory_id');
    }

    public function orders():HasMany
    {
        return $this->hasMany(Order::class, 'factory_id');
    }

    public function product():HasMany
    {
        return $this->hasMany(Product::class, 'user_id')->where(['added_by'=>'factories']);
    }

    public function wallet():HasOne
    {
        return $this->hasOne(factoryWallet::class);
    }

    public function coupon():HasMany
    {
        return $this->hasMany(Coupon::class, 'factory_id')
            ->where(['coupon_bearer'=>'factory', 'status'=>1])
            ->whereDate('start_date','<=',date('Y-m-d'))
            ->whereDate('expire_date','>=',date('Y-m-d'));
    }

}
