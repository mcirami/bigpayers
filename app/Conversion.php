<?php

namespace App;

use Database\Factories\ConversionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Conversion
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $click_id
 * @property string $timestamp
 * @property float $paid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversion whereClickId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversion wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversion whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversion whereUserId($value)
 */
class Conversion extends Model
{
	use HasFactory;
	    protected $table = 'conversions';
	    public $timestamps = false;

	/**
	 * Create a new factory instance for the model.
	 */
	protected static function newFactory(): Factory
	{
		return ConversionFactory::new();
	}

	public function scopeCountryConversionsByOfferInGeo(
		Builder $query,
		string $startDate,
		string $endDate,
		?string $geoCode = null
	): Builder {
		$geoCountryCode = Click::GEO_COUNTRY_CODE_SQL;

		return $query
			->whereBetween('conversions.timestamp', [$startDate, $endDate])
			->join('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
			->leftJoin('click_geo_cache as geo', 'geo.ip_address', '=', 'clicks.ip_address')
			->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
			->selectRaw("
				offer.offer_name,
				clicks.offer_idoffer AS offer_id,
				{$geoCountryCode} AS country_code,
				COUNT(conversions.id) AS total_conversions
			")
			->when($geoCode, fn (Builder $builder) => $builder->whereRaw("{$geoCountryCode} = ?", [$geoCode]))
			->groupBy('clicks.offer_idoffer', DB::raw($geoCountryCode));
	}

	public function scopeCountryConversionsByIpInGeo(
		Builder $query,
		string $startDate,
		string $endDate,
		?int $userId = null,
		?int $offerId = null
	): Builder {
		$geoCountryCode = Click::GEO_COUNTRY_CODE_SQL;

		return $query
			->whereBetween('timestamp', [$startDate, $endDate])
			->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
			->leftJoin('click_geo_cache as geo', 'geo.ip_address', '=', 'clicks.ip_address')
			->when(!is_null($userId), fn (Builder $builder) => $builder->where('user_id', '=', $userId))
			->when(!is_null($offerId), fn (Builder $builder) => $builder->where('clicks.offer_idoffer', '=', $offerId))
			->select(
				'clicks.ip_address',
				DB::raw($geoCountryCode . ' as country_code'),
				DB::raw('COUNT(conversions.id) as conversions')
			)
			->groupBy('clicks.ip_address', DB::raw($geoCountryCode));
	}

}
