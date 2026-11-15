<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// One open cart per signed-in user (user_id) or guest session (session_key). See Api\StorefrontCartController.
class Cart extends Model
{
    protected $fillable = ['user_id', 'session_key'];

    public function items()
    {
        return $this->hasMany(CartItem::class)->orderBy('id');
    }
}
