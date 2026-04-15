<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PayoutLog
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property float $revenue
 * @property float $deductions
 * @property float $bonuses
 * @property float $referrals
 * @property string|null $start_of_week
 * @property string|null $end_of_week
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereBonuses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereDeductions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereEndOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereReferrals($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereStartOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereUserId($value)
 */
class PayoutLog extends Model
{


    protected $guarded = [];


}
