<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserOffer
 *
 * @mixin \Eloquent
 * @property int $idrep_has_offer
 * @property int $rep_idrep
 * @property int $offer_idoffer
 * @property float $payout
 * @property string|null $postback_url
 * @property string $deduction_postback
 * @property string $free_sign_up_postback
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserOffer whereDeductionPostback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserOffer whereFreeSignUpPostback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserOffer whereIdrepHasOffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserOffer whereOfferIdoffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserOffer wherePayout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserOffer wherePostbackUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserOffer whereRepIdrep($value)
 */
class UserOffer extends Model
{

    protected $primaryKey = 'idrep_has_offer';

    protected $table = 'rep_has_offer';


    public $timestamps = false;
}
