<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SmsPoolService {
	protected string $baseUrl;
	protected string $apiKey;

	public function __construct()
	{
		$this->baseUrl = rtrim(config('services.smspool.base_url'), '/');
		$this->apiKey = config('services.smspool.key');
	}

	protected function post(string $endpoint, array $data = []): array
	{
		$response = Http::asForm()->post("{$this->baseUrl}/{$endpoint}", array_merge([
			'key' => $this->apiKey,
		], $data));

		return $this->handleResponse($response, $endpoint);
	}

	protected function handleResponse(Response $response, string $endpoint): array
	{
		$json = $response->json();

		if (! $response->successful()) {
			$message = is_array($json)
				? ($json['message'] ?? $json['error'] ?? "SMSPool request failed for endpoint [{$endpoint}]")
				: "SMSPool request failed for endpoint [{$endpoint}]";

			throw new RuntimeException($message);
		}

		if (is_array($json)) {
			if (
				(isset($json['success']) && $json['success'] === 0) ||
				(isset($json['status']) && in_array(strtolower((string) $json['status']), ['error', 'failed'], true))
			) {
				$message = $json['message'] ?? $json['error'] ?? "SMSPool returned an error for endpoint [{$endpoint}]";
				throw new RuntimeException($message);
			}

			return $json;
		}

		throw new RuntimeException("Unexpected SMSPool response for endpoint [{$endpoint}]");
	}

	public function orderSms(string $country, ?string $service = null, ?string $pool = null): array
	{
		$payload = [
			'country' => $country,
			'service' =>  $service ?? "Instagram / Threads",
		];

		if ($pool) {
			$payload['pool'] = $pool;
		}

		return $this->post('purchase/sms', $payload);
	}

	public function checkSms(string $orderId): array
	{
		return $this->post('sms/check', [
			'orderid' => $orderId,
		]);
	}

	public function getActiveOrders(): array
	{
		return $this->post('request/active');
	}
}