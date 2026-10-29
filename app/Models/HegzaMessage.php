<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HegzaMessage extends Model
{
    use HasFactory;
    protected $table = 'hegza_messages';

    public function User()
    {
        return $this->belongsTo(HegzaUser::class,'user_id');
    }

}
