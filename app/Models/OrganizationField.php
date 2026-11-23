<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One input or question on the organisation-account form (App\Services\Organizations). */
class OrganizationField extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['required' => 'boolean', 'enabled' => 'boolean', 'builtin' => 'boolean', 'options' => 'array', 'accept' => 'array'];
}
