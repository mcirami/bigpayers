<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

/**
 * App\OfferURL
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $url
 * @property int $status
 * @property int $company_id
 * @property string $timestamp
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferURL whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferURL whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferURL whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferURL whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferURL whereUrl($value)
 */
class OfferURL extends Model
{
    protected $table = 'offer_urls';
    protected $connection = 'master';

}
