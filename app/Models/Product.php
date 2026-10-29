<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function OrderItem()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ServiceCategory()
    {
        return $this->belongsTo(Category::class,'country_id');
    }

    public function Service()
    {
        return $this->belongsTo(Virtual_Service::class,'service_id');
    }

}
