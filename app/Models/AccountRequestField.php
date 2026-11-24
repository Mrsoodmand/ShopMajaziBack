<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One input on the «درخواست اکانت» form (App\Services\AccountRequests). */
class AccountRequestField extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['required' => 'boolean', 'enabled' => 'boolean', 'builtin' => 'boolean', 'options' => 'array', 'accept' => 'array'];
}
