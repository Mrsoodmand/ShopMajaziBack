<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HegzaIp extends Model
{
    use HasFactory;
    protected $table = 'hegza_ip';

    public function Server()
    {
        return $this->belongsTo(HegzaIp::class,'parent_id');
    }
}
