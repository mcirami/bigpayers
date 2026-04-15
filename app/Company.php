<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Company
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $shortHand
 * @property string $subDomain
 * @property string $companyName
 * @property string $city
 * @property string $state
 * @property string $address
 * @property string $zip
 * @property string $telephone
 * @property string $email
 * @property string $skype
 * @property string $colors
 * @property string $uid
 * @property float $db_version
 * @property string $login_url
 * @property string $landing_page
 * @property string $login_theme
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereColors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereDbVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereLandingPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereLoginUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereShortHand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereSkype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereSubDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company whereZip($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OfferURL[] $offerUrls
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Company instance()
 */
class Company extends Model
{
    protected $table = 'company';

    protected $connection = 'master';

    public $timestamps = false;

    public static function getInstance(): Company
    {
        return static::where('subDomain', \LeadMax\TrackYourStats\System\Company::getCustomSub())->first();
    }

    // This stuff should really be refactored but whatever
    public function scopeInstance(Builder $query)
    {
        return $query->where('subDomain', \LeadMax\TrackYourStats\System\Company::getCustomSub());
    }

    public function offerUrls()
    {
        return $this->hasMany(OfferURL::class);
    }

    public function colors(): array
    {
        return explode(';', $this->colors);
    }

}
