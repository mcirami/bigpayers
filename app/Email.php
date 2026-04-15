<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Email
 *
 * @property-read \App\EmailPool $pool
 * @mixin \Eloquent
 * @property int $id
 * @property int $email_pool_id
 * @property string $email
 * @property string $timestamp
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Email whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Email whereEmailPoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Email whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Email whereTimestamp($value)
 */
class Email extends Model
{

    public $timestamps = false;

    public function save(array $options = [])
    {
        if (!isset($this->email_pool_id)) {
            $this->email_pool_id = EmailPool::getOpenPool()->id;
        }

        return parent::save($options);
    }


    public function pool()
    {
        return $this->belongsTo(EmailPool::class);
    }
}
