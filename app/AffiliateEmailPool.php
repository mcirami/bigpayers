<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\AffiliateEmailPool
 *
 * @property int $id
 * @property int $user_id
 * @property int $email_pool_id
 * @property string $timestamp
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AffiliateEmailPool whereEmailPoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AffiliateEmailPool whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AffiliateEmailPool whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AffiliateEmailPool whereUserId($value)
 * @mixin \Eloquent
 */
class AffiliateEmailPool extends Model
{
    public $timestamps = false;
    protected $table = "affiliate_email_pools";
}
