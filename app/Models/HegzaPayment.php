<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HegzaPayment extends Model
{
    use HasFactory;

    protected $table = 'hegza_payment';

    public function User()
    {
        return $this->belongsTo(HegzaUser::class);
    }

    public function Order()
    {
        return $this->belongsTo(HegzaOrder::class);
    }
}
