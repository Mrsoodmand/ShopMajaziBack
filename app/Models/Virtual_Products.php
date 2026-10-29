<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Virtual_Products extends Model
{
    use HasFactory;

    public function Service()
    {
        return $this->belongsTo(Virtual_Service::class,'service_id');
    }

    public function Country()
    {
        return $this->belongsTo(Virtual_Country::class,'country_id');
    }

    public function OrderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

}
