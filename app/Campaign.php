<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Campaign
 *
 * @property int $id
 * @property string $name
 * @property int $timestamp
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Offer[] $offers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereTimestamp($value)
 * @mixin \Eloquent
 */
class Campaign extends Model
{

    public $timestamps = false;

    public function offers()
    {
        return $this->hasMany(Offer::class, 'campaign_id');
    }

}
