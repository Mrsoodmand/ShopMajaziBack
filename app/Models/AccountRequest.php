<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A customer's request for a subscription the site doesn't sell (App\Services\AccountRequests). */
class AccountRequest extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['answers' => 'array', 'reviewed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
