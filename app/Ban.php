<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Ban
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban active()
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property string $reason
 * @property string $timestamp
 * @property string|null $expires
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ban whereUserId($value)
 */
class Ban extends Model
{

    protected $table = 'banned_users';

    public $timestamps = false;


    public function scopeActive(Builder $query)
    {
        return $query->where('status', '=', '1')
            ->where('expires', '>', Carbon::now()->format('Y-m-d H:i:s'));
    }

}
