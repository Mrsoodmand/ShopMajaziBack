<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HegzaUser extends Model
{
    use HasFactory;

    protected $table = 'hegza_user';

    public function referrals()
    {
        return $this->hasMany(HegzaUser::class, 'parent');
    }

    public function Orders()
    {
        return $this->hasMany(HegzaOrder::class);
    }

    public function Payment()
    {
        return $this->belongsTo(HegzaPayment::class, 'user_id');
    }
}
