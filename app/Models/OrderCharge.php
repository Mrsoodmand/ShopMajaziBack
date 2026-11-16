<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/* Money an order asks the customer for: an admin-made order's price (kind "order") or an extra amount (kind "extra"). See App\Services\OrderCharges. */
class OrderCharge extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['paid_at' => 'datetime'];

    public function Order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
