<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LeadMax\TrackYourStats\System\Session;

/**
 * App\User
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ban[] $bans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\EmailPool[] $emailPools
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Offer[] $offers
 * @property-read \App\Privilege $role
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SMSClient[] $smsClients
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User myUsers()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User withRole($role)
 * @property int $idrep
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $cell_phone
 * @property string|null $email
 * @property string|null $user_name
 * @property string|null $password
 * @property int|null $status
 * @property int $referrer_repid
 * @property string|null $rep_timestamp
 * @property int|null $lft
 * @property int|null $rgt
 * @property string|null $skype
 * @property string $company_name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCellPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIdrep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereReferrerRepid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRepTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRgt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereSkype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserName($value)
 * @property-read \App\Salary $salary
 * @property-read \App\User $referrer
 */
class User extends Authenticatable
{

    protected $primaryKey = 'idrep';

    protected $table = 'rep';

    public $timestamps = false;

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function scopeMyUsers(Builder $query)
    {
        return $query->where('lft', '>', Session::user()->lft)->where('rgt', '<', Session::user()->rgt);
    }

    public function scopeWithRole(Builder $query, $role)
    {
        $query->join('privileges', 'privileges.rep_idrep', '=', 'rep.idrep');
        switch ($role) {
            case Privilege::ROLE_GOD:
                $roleAsString = 'god';
                break;
            case Privilege::ROLE_ADMIN:
                $roleAsString = 'admin';
                break;
            case Privilege::ROLE_MANAGER:
                $roleAsString = 'manager';
                break;
            case Privilege::ROLE_AFFILIATE: // the special case here..
            default :
                $roleAsString = 'rep';
                break;

        }
        $query->where("privileges.is_" . $roleAsString, '=', 1)->groupBy('rep.idrep', 'privileges.rep_idrep');

        return $query;
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_repid', 'idrep');
    }

    public function smsClients()
    {
        return $this->hasMany(SMSClient::class, 'user_id');
    }

    public function users()
    {
        return User::where('lft', '>', $this->lft)->where('rgt', '<', $this->rgt);
    }


    // GOT TO LOVE LEGACY CODE AMIRITE
    public function getRole()
    {
        $type = Privilege::ROLE_UNKNOWN;
        $role = $this->role;

        if (is_null($role)) {
            return $type;
        }

        if ($role->is_god == 1) {
            return Privilege::ROLE_GOD;
        }

        if ($role->is_admin == 1) {
            return Privilege::ROLE_ADMIN;
        }

        if ($role->is_manager == 1) {
            return Privilege::ROLE_MANAGER;
        }

        if ($role->is_rep == 1) {
            return Privilege::ROLE_AFFILIATE;
        }

        return $type;
    }

    public function role()
    {
        return $this->hasOne(Privilege::class, 'rep_idrep', 'idrep');
    }

    /**
     * @return Builder|BelongsToMany
     */
    public function offers(): Builder|BelongsToMany {
        switch ($this->getRole()) {
            case Privilege::ROLE_GOD:
            case Privilege::ROLE_ADMIN:
                return Offer::query();

            // I mean, it's the best i could do m8
            case Privilege::ROLE_MANAGER:
                return Offer::join('rep', function (JoinClause $join) {
                    $join->where([['rep.lft', '>', $this->lft], ['rep.rgt', '<', $this->rgt]]);
                })->join('rep_has_offer', 'rep_has_offer.rep_idrep', '=', 'rep.idrep')
                    ->whereRaw('(rep_has_offer.offer_idoffer = offer.idoffer OR offer.created_by = ' . $this->idrep . ')')
                    ->groupBy(['idoffer']);

            case Privilege::ROLE_AFFILIATE:
                return $this->belongsToMany(Offer::class, 'rep_has_offer', 'rep_idrep', 'offer_idoffer')
                    ->withPivot('payout');
        }
    }


    public function emailPools()
    {
        return $this->hasManyThrough(EmailPool::class, AffiliateEmailPool::class, 'user_id', 'id', 'idrep',
            'email_pool_id');
    }

    public function bans()
    {
        return $this->hasMany(Ban::class, 'user_id', 'idrep');
    }

    public function isBanned()
    {
        return $this->bans()->active()->count() > 0;
    }


    public function salary()
    {
        return $this->hasOne(Salary::class, 'user_id', 'idrep');
    }

	public function smsOrders()
	{
		return $this->hasMany(\App\SmsOrder::class, 'rep_id', 'idrep');
	}

}
