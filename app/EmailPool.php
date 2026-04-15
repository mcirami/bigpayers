<?php

namespace App;

use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;

/**
 * App\EmailPool
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $affiliate
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Email[] $emails
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmailPool availablePools()
 * @mixin \Eloquent
 * @property int $id
 * @property string $timestamp
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmailPool whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmailPool whereTimestamp($value)
 */
class EmailPool extends Model
{
    protected $table = "email_pools";

    public $timestamps = false;

    public static $POOL_COUNT = 100;

    public function scopeAvailablePools($query)
    {
        $assignedPools = AffiliateEmailPool::pluck('email_pool_id')->all();

        return $query->whereNotIn('id', $assignedPools);
    }

    public function affiliate()
    {
        return $this->belongsToMany(User::class, 'affiliate_email_pools', 'email_pool_id', 'user_id', 'id');
    }

    public function canAffiliateClaimPool($affiliateId)
    {
        $affiliate = User::find($affiliateId);


        if($affiliate->emailPools()->get()->isEmpty()){
            return true;
        }

        $lastPoolDate = Carbon::createFromTimeString($affiliate->emailPools()->latest('timestamp')->first()->timestamp);

        $instancesTimestamp = Carbon::createFromTimeString($this->timestamp);

        if ($instancesTimestamp->isToday() && $lastPoolDate->isToday()) {
            return false;
        }

        return true;
    }

    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    public static function getOpenPool()
    {
        $pool = EmailPool::all()->last();
        if (is_null($pool) || $pool->emails()->count() > static::$POOL_COUNT) {
            $pool = new EmailPool();
            $pool->save();
        }

        return $pool;
    }

}
