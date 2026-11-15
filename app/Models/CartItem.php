<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'kind', 'payload', 'label', 'price'];
    protected $casts = ['payload' => 'encrypted:array', 'label' => 'array', 'price' => 'integer'];
}
