<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Deduction
 *
 * @property int $id
 * @property int $conversion_id
 * @property string $deduction_timestamp
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deduction whereConversionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deduction whereDeductionTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deduction whereId($value)
 * @mixin \Eloquent
 */
class Deduction extends Model
{
    //

    public $timestamps = false;

}
