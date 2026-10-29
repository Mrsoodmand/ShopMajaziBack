<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Virtual_Country extends Model
{
    use HasFactory;

    public function Products()
    {
        return $this->hasOne(Virtual_Products::class,'service_id');
    }
}
