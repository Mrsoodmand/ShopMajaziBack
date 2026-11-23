<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A bulk SMS send (admin › پیامک). See the sms_campaigns migration and App\Jobs\SendSmsCampaign. */
class SmsCampaign extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'mobiles' => 'array',
        'pack_ids' => 'array',
        'cost' => 'float',
        'finished_at' => 'datetime',
    ];

    protected $hidden = ['mobiles'];
}
