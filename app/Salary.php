<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Salary
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $salary
 * @property int $timestamp
 * @property int $last_update
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Salary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Salary whereLastUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Salary whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Salary whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Salary whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Salary whereUserId($value)
 * @property-read \App\User $user
 */
class Salary extends Model
{
    protected $table = 'salary';

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'idrep');
    }


}
