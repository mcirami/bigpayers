<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PredefinedOfferRule extends Model
{
    protected $table = 'predefined_offer_rules';

    protected $fillable = [
        'type',
        'name',
        'rule_name',
        'redirect_offer',
        'deny',
        'is_active',
        'cap_amount',
        'cap_status',
        'items_json',
    ];

    protected $casts = [
        'redirect_offer' => 'integer',
        'deny' => 'boolean',
        'is_active' => 'boolean',
        'cap_amount' => 'integer',
        'cap_status' => 'boolean',
    ];

    public function getItemsAttribute(): array
    {
        $decoded = json_decode($this->items_json ?? '[]', true);

        return is_array($decoded) ? $decoded : [];
    }
}
