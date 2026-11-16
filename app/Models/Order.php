<?php

namespace App\Models;

use App\Models\Payment;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function OrderItem()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function Payment()
    {
        return $this->hasMany(Payment::class);
    }

    public function Charges()
    {
        return $this->hasMany(OrderCharge::class, 'order_id');
    }

    /*
     * The checkout's "available for a call 21:00–07:00?" answer (night_call=1|0), saved on every order a storefront
     * buy creates. Cart checkouts build their own requests, so the cart sets it on its orders itself.
     */
    protected static function booted()
    {
        static::creating(function (Order $Order) {
            if ($Order->night_call === null && app()->bound('request')) {
                $Answer = request()->input('night_call');
                if ($Answer !== null && $Answer !== '') {
                    $Order->night_call = filter_var($Answer, FILTER_VALIDATE_BOOLEAN);
                }
            }
        });
    }
}
