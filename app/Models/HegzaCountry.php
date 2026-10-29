<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HegzaCountry extends Model
{
    use HasFactory;
    protected $table = 'hegza_country';

    public function Order()
    {
        return $this->hasMany(HegzaOrder::class);
    }
}
