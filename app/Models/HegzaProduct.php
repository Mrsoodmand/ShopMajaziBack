<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HegzaProduct extends Model
{
    use HasFactory;
    protected $table = 'hegza_products';

    public function Order()
    {
        return $this->hasMany(HegzaOrder::class);
    }
}
