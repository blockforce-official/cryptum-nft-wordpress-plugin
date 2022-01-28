<?php
namespace Cryptum\NFT\Utils;

// @codeCoverageIgnoreStart
defined('ABSPATH') or exit;
// @codeCoverageIgnoreEnd

class Api
{
	static function get_cryptum_url($environment)
	{
		return $environment == 'production' ? 'https://api.cryptum.io/plugins' : 'https://api-dev.cryptum.io/plugins';
	}

	static function request($url, $args = array())
	{
		$response = wp_safe_remote_request($url, $args);
		if (is_wp_error($response)) {
			Log::error(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			return [
				'error' => 'Error',
				'message' => $response->get_error_message()
			];
		}
		$responseBody = json_decode($response['body'], true);
		if (isset($responseBody['error'])) {
			$error_message = isset($responseBody['error']['message']) ? $responseBody['error']['message'] : $responseBody['message'];
			Log::error(json_encode($responseBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			return [
				'error' => 'Error',
				'message' => $error_message
			];
		}
		return $responseBody;
	}
}
