<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// One payment for a whole cart. `items`: [{kind, payload, label, price, order_id, result, message}].
class CartCheckout extends Model
{
    protected $fillable = ['user_id', 'method', 'total', 'payment_id', 'status', 'items'];
    protected $casts = ['items' => 'encrypted:array', 'total' => 'integer'];
}
