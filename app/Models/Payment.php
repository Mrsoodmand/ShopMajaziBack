<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public function User()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function Order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }
}
