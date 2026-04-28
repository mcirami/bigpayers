<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Offer
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $affiliates
 * @mixin \Eloquent
 * @property int $idoffer
 * @property int $created_by
 * @property string|null $offer_name
 * @property string|null $description
 * @property string $url
 * @property int $offer_type
 * @property int|null $is_public
 * @property float|null $payout
 * @property float|null $affiliate_payout
 * @property float|null $manager_payout
 * @property float|null $admin_payout
 * @property int|null $status
 * @property string|null $offer_timestamp
 * @property int $campaign_id
 * @property int|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereCampaignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereIdoffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereOfferName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereOfferTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereOfferType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer wherePayout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereUrl($value)
 * @property-read \App\Campaign $campaign
 */
class Offer extends Model
{

	const TYPE_PPS = 0;
	const TYPE_PPC = 1;
	const TYPE_BLACKLISTED = 2;
	const TYPE_PPL = 3;
	const TYPE_DATING = 4;
	const TYPE_CAMS = 5;
	const TYPE_SWEEPS = 6;
	const TYPE_NUTRA = 7;

    CONST VISIBILITY_PRIVATE = 0;
    const VISIBILITY_PUBLIC = 1;
    CONST VISIBILITY_REQUESTABLE = 2;

    protected $fillable = [
        'offer_name',
        'description',
        'url',
        'offer_type',
        'payout',
        'affiliate_payout',
        'manager_payout',
        'admin_payout',
        'status',
        'offer_timestamp',
        'is_public',
        'campaign_id',
        'parent',
    ];

    protected $table = 'offer';

    public $timestamps = false;

    protected $primaryKey = 'idoffer';

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }


    public function affiliates()
    {
        return $this->belongsToMany(User::class, 'rep_has_offer', 'offer_idoffer', 'rep_idrep');
    }

    public function getRolePayoutForRole(int $role): ?float
    {
        return match ($role) {
            Privilege::ROLE_ADMIN => $this->admin_payout,
            Privilege::ROLE_MANAGER => $this->manager_payout,
            Privilege::ROLE_AFFILIATE => $this->affiliate_payout,
            default => null,
        };
    }

    public function resolveDefaultPayoutForRole(int $role): float
    {
        return (float) ($this->getRolePayoutForRole($role) ?? $this->payout ?? 0);
    }

}
