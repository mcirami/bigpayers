<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Privilege
 *
 * @mixin \Eloquent
 * @property int $idprivileges
 * @property int $rep_idrep
 * @property int|null $is_god
 * @property int|null $is_manager
 * @property int|null $is_admin
 * @property int|null $is_rep
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Privilege whereIdprivileges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Privilege whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Privilege whereIsGod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Privilege whereIsManager($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Privilege whereIsRep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Privilege whereRepIdrep($value)
 * @property-read \App\User $user
 */
class Privilege extends Model
{

    protected $primaryKey = 'idprivileges';
    public $timestamps = false;



    const ROLE_GOD = 0;
    const ROLE_ADMIN = 1;
    const ROLE_MANAGER = 2;
    const ROLE_AFFILIATE = 3;
    const ROLE_UNKNOWN = -1;


    public function user()
    {
        return $this->belongsTo(User::class, 'rep_idrep');
    }

}
