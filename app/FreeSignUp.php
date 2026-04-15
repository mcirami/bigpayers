<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\FreeSignUp
 *
 * @property int $id
 * @property int $click_id
 * @property int $user_id
 * @property string $timestamp
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FreeSignUp whereClickId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FreeSignUp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FreeSignUp whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FreeSignUp whereUserId($value)
 * @mixin \Eloquent
 */
class FreeSignUp extends Model
{
    protected $table = 'free_sign_ups';
    public $timestamps = false;
}
