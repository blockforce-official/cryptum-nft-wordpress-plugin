<?php

function get_cryptum_url($environment)
{
	return $environment == 'production' ? 'https://api.cryptum.io/plugins' : 'https://api-dev.cryptum.io/plugins';
}

function get_nft_product_info($product_id)
{
	$options = get_option('cryptum_nft');
	$res = request(get_cryptum_url($options['environment']) . '/products/' . $product_id, array('method' => 'GET'));
	return $res;
}

function request($url, $args = array())
{
	$response = wp_safe_remote_request($url, $args);
	if (is_wp_error($response)) {
		_log(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		return [
			'error' => 'Error',
			'message' => $response->get_error_message()
		];
	}
	$responseBody = json_decode($response['body'], true);
	if (isset($responseBody['error'])) {
		$error_message = $responseBody['error']['message'];
		if (!isset($error_message)) {
			$error_message = $responseBody['message'];
		}
		_log(json_encode($responseBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		return [
			'error' => 'Error',
			'message' => $error_message
		];
	}
	return $responseBody;
}
