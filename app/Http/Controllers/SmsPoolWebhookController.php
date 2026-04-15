<?php

namespace App\Http\Controllers;

use App\SmsOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsPoolWebhookController extends Controller
{
	public function handle(Request $request): JsonResponse
	{
		$payload = $request->all();

		$orderId = $payload['orderid'] ?? null;
		$sms = $payload['sms'] ?? null;
		$fullSms = $payload['full_sms'] ?? null;

		if (! $orderId) {
			return response()->json([
				'ok' => false,
				'message' => 'Missing orderid',
			], 422);
		}

		$order = SmsOrder::where('smspool_order_id', (string) $orderId)->first();

		if (! $order) {
			return response()->json([
				'ok' => false,
				'message' => 'Order not found',
			], 404);
		}

		$order->raw_webhook_payload = $payload;

		if (! empty($sms)) {
			$order->status = 'received';
			$order->code = (string) $sms;
			$order->full_sms = $fullSms;
			$order->received_at = now();
		}

		$order->save();

		return response()->json([
			'ok' => true,
		]);
	}
}
