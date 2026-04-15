<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\AggregateReport
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $clicks
 * @property int $unique_clicks
 * @property int $free_sign_ups
 * @property int $pending_conversions
 * @property int $conversions
 * @property int $revenue
 * @property int $deductions
 * @property string $aggregate_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport whereAggregateDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport whereClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport whereConversions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport whereDeductions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport whereFreeSignUps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport wherePendingConversions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport whereRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport whereUniqueClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AggregateReport whereUserId($value)
 */
class AggregateReport extends Model
{
    protected $guarded = [];
}
