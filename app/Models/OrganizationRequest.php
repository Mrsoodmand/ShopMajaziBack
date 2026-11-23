<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A customer's request to turn their account into an organisation account (App\Services\Organizations). */
class OrganizationRequest extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['answers' => 'array', 'reviewed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
