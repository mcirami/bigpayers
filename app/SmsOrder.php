<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsOrder extends Model
{

	protected $fillable = [
		'rep_id',
		'client_reference',
		'smspool_order_id',
		'phone_number',
		'service',
		'country',
		'pool',
		'status',
		'code',
		'full_sms',
		'received_at',
		'last_checked_at',
		'raw_order_response',
		'raw_last_check_response',
		'raw_webhook_payload',
		'expires_at',
	];

	protected $casts = [
		'received_at' => 'datetime',
		'last_checked_at' => 'datetime',
		'expires_at' => 'datetime',
		'raw_order_response' => 'array',
		'raw_last_check_response' => 'array',
		'raw_webhook_payload' => 'array',
	];

	public function rep()
	{
		return $this->belongsTo(\App\User::class, 'rep_id', 'idrep');
	}
}
