<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HegzaOrder extends Model
{
    use HasFactory;
    protected $table = 'hegza_orders';

    public function User()
    {
        return $this->belongsTo(HegzaUser::class,'user_id');
    }

    public function Country()
    {
        return $this->belongsTo(HegzaCountry::class,'country_id');
    }

    public function Service()
    {
        return $this->belongsTo(HegzaProduct::class,'service_id');
    }

    public function Payment()
    {
        return $this->hasMany(HegzaPayment::class,'order_id');
    }

}
