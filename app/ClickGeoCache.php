<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClickGeoCache extends Model
{
	use HasFactory;

	protected $table = 'click_geo_cache';

	protected $fillable = [
		'ip_address',
		'country_code',
		'subDivision',
		'city',
		'postal',
		'latitude',
		'longitude',
		'source',
		'resolved_at',
	];
}
