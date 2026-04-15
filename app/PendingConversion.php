<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PendingConversion
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $click_id
 * @property float $payout
 * @property int $converted
 * @property string $timestamp
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PendingConversion whereClickId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PendingConversion whereConverted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PendingConversion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PendingConversion wherePayout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PendingConversion whereTimestamp($value)
 */
class PendingConversion extends Model
{
    protected $table = "pending_conversions";
    public $timestamps = false;
}
