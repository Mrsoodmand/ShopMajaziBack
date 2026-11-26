<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One of the shop's own cards support pays outside services with (admin › کارت‌های خارجی). Number and expiry are
 * encrypted at rest; no CVV is ever stored. Orders point here with orders.external_card_id.
 */
class ExternalCard extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['number' => 'encrypted', 'expiry' => 'encrypted', 'active' => 'boolean'];
    protected $hidden = ['number', 'expiry'];

    /** "Mellat Visa •••• 4821": what pickers and the order page show. */
    public function label(): string
    {
        return $this->title . ' •••• ' . $this->last4;
    }
}
