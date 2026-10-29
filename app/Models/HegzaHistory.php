<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HegzaHistory extends Model
{
    use HasFactory;
    protected $table = 'hegza_history';

    public function Order()
    {
        return $this->hasMany(HegzaOrder::class);
    }
}
