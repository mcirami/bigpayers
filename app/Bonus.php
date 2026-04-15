<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Bonus
 *
 * @property int $id
 * @property string $name
 * @property int $sales_required
 * @property float $payout
 * @property int $author
 * @property int $is_active
 * @property int $timestamp
 * @property int $inheritable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bonus whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bonus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bonus whereInheritable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bonus whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bonus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bonus wherePayout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bonus whereSalesRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bonus whereTimestamp($value)
 * @mixin \Eloquent
 */
class Bonus extends Model
{
    protected $table = 'bonus';
    public $timestamps = false;
}
