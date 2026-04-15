<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use LeadMax\TrackYourStats\Table\Date;

/**
 * App\BonusOffer
 *
 * @property-read \App\Offer $offer
 * @mixin \Eloquent
 * @property int $id
 * @property int $required_sales
 * @property int $active
 * @property int $offer_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BonusOffer whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BonusOffer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BonusOffer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BonusOffer whereOfferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BonusOffer whereRequiredSales($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BonusOffer whereUpdatedAt($value)
 */
class BonusOffer extends Model
{

    public function canAffiliateUseOffer($affiliateId)
    {
        $salesWeek = Date::getSalesWeek();
        $sales = Conversion::where('user_id', '=', $affiliateId)->whereBetween('timestamp',
            [$salesWeek['start'], $salesWeek['end']])->count();

        return $sales >= $this->required_sales;
    }

    public function offer()
    {
        return $this->hasOne(Offer::class, 'idoffer', 'offer_id');
    }


}
