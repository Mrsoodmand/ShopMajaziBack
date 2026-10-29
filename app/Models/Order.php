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
}
