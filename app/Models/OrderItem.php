<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    public function Product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function VirtualNumber()
    {
        return $this->belongsTo(Virtual_Products::class,'product_id');
    }

    public function Order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }
}
