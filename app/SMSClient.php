<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\SMSClient
 *
 * @property-read \App\User $user
 * @mixin \Eloquent
 * @property int $id
 * @property int|null $client_id
 * @property string|null $client_secret
 * @property int $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int $sms_user_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SMSClient whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SMSClient whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SMSClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SMSClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SMSClient whereSmsUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SMSClient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SMSClient whereUserId($value)
 */
class SMSClient extends Model
{

    protected $table = "sms_clients";

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
