<?php

namespace Cryptum\NFT\Utils;

// @codeCoverageIgnoreStart
defined('ABSPATH') or exit;
// @codeCoverageIgnoreEnd

const ERC721_BALANCEOF_ABI = [array(
	'inputs' => [array('name' => 'account', 'type' => 'address')],
	'name' => 'balanceOf',
	'outputs' => [array('name' => '', 'type' => 'uint256')],
	'stateMutability' => 'view',
	'type' => 'function',
)];
const ERC1155_BALANCEOF_ABI = [array(
	'inputs' => [array('name' => 'account', 'type' => 'address'), array('name' => 'id', 'type' => 'uint256')],
	'name' => 'balanceOf',
	'outputs' => [array('name' => '', 'type' => 'uint256')],
	'stateMutability' => 'view',
	'type' => 'function',
)];
const ERC721_URI_ABI = [array(
	'inputs' => [array('name' => 'id', 'type' => 'uint256')],
	'name' => 'tokenURI',
	'outputs' => [array('name' => '', 'type' => 'string')],
	'stateMutability' => 'view',
	'type' => 'function',
)];
const ERC1155_URI_ABI = [array(
	'inputs' => [array('name' => 'id', 'type' => 'uint256')],
	'name' => 'uri',
	'outputs' => [array('name' => '', 'type' => 'string')],
	'stateMutability' => 'view',
	'type' => 'function',
)];
const ERC721_TOKENSOFOWNER_ABI = [array(
	'inputs' => [array('name' => 'owner', 'type' => 'address'), array('name' => 'startIndex', 'type' => 'uint256'), array('name' => 'endIndex', 'type' => 'uint256')],
	'name' => 'tokensOfOwner',
	'outputs' => [array(
		"components" => [
			array(
				"name" => "id",
				"type" => "uint256"
			),
			array(
				"name" => "uri",
				"type" => "string"
			)
		],
		"internalType" => "struct TokenERC721.Token[]",
		"name" => "",
		"type" => "tuple[]"
	)],
	'stateMutability' => 'view',
	'type' => 'function',
)];
const ERC1155_TOKENSOFOWNER_ABI = [array(
	'inputs' => [array('name' => 'account', 'type' => 'address'), array('name' => 'ids', 'type' => 'uint256[]')],
	'name' => 'tokensOfOwner',
	'outputs' => [array(
		"components" => [
			array(
				"name" => "id",
				"type" => "uint256"
			),
			array(
				"name" => "amount",
				"type" => "uint256"
			),
			array(
				"name" => "uri",
				"type" => "string"
			)
		],
		"internalType" => "struct TokenERC1155.Token[]",
		"name" => "",
		"type" => "tuple[]"
	)],
	'stateMutability' => 'view',
	'type' => 'function',
)];

class Api
{
	static function get_cryptum_url($environment)
	{
		return $environment == 'production' ? 'https://api.cryptum.io' : 'https://api-hml.cryptum.io';
	}
	static function get_cryptum_store_url($environment)
	{
		return Api::get_cryptum_url($environment) . '/plugins';
	}

	static function request($url, $args = array())
	{
		$response = wp_remote_request($url, $args);
		if (is_wp_error($response)) {
			Log::error(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			return [
				'error' => 'Error',
				'message' => $response->get_error_message()
			];
		}

		$responseObj = $response['response'];
		$responseBody = json_decode($response['body'], true);
		if (isset($responseBody['error']) || (isset($responseObj) && $responseObj['code'] >= 400)) {
			$error_message = isset($responseBody['error']['message']) ? $responseBody['error']['message'] : $responseBody['message'];
			Log::error(json_encode($responseBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			return [
				'error' => 'Error',
				'message' => $error_message
			];
		}
		return $responseBody;
	}

	/**
	 * @param string $walletAddress
	 * @param string $tokenAddress
	 * @param string $protocol
	 * @param string $id
	 */
	static function get_nft_info_from_wallet($walletAddress, $tokenAddress, $protocol, $id)
	{
		$options = get_option('cryptum_nft');
		$url = Api::get_cryptum_url($options['environment']);

		$isERC1155 = !is_null($id) && $id != '';
		$tokensResponse = Api::request("{$url}/tx/call-method?protocol={$protocol}", array(
			'method' => 'POST',
			'headers' => array(
				'x-api-key' => $options['apikey'],
				'Content-type' => 'application/json'
			),
			'data_format' => 'body',
			'timeout' => 60,
			'body' => json_encode([
				'from' => $walletAddress,
				'contractAddress' => $tokenAddress,
				'method' => 'tokensOfOwner',
				'params' => !$isERC1155 ? [$walletAddress, 0, 100] : [$walletAddress, [$id]],
				'contractAbi' => !$isERC1155 ? ERC721_TOKENSOFOWNER_ABI : ERC1155_TOKENSOFOWNER_ABI,
			]),
		));
		if (isset($tokensResponse['error'])) {
			Log::info([
				'url' => "{$url}/tx/call-method?protocol={$protocol}",
				'method' => 'POST',
				'request' => array(
					'from' => $walletAddress,
					'contractAddress' => $tokenAddress,
					'method' => 'tokensOfOwner',
					'params' => !$isERC1155 ? [$walletAddress, 0, 100] : [$walletAddress, [$id]],
					'isERC1155' => $isERC1155,
					'id' => $id
				),
				'response' => $tokensResponse
			]);
		}
		return $tokensResponse;
	}
	static function get_nft_uri($tokenAddress, $protocol, $id)
	{
		$options = get_option('cryptum_nft');
		$url = Api::get_cryptum_url($options['environment']);

		$isERC1155 = !is_null($id) && $id != '';
		$tokensResponse = Api::request("{$url}/tx/call-method?protocol={$protocol}", array(
			'method' => 'POST',
			'headers' => array(
				'x-api-key' => $options['apikey'],
				'Content-type' => 'application/json'
			),
			'data_format' => 'body',
			'timeout' => 60,
			'body' => json_encode([
				'contractAddress' => $tokenAddress,
				'method' => !$isERC1155 ? 'tokenURI' : 'uri',
				'params' => !$isERC1155 ? [$id] : [$id],
				'contractAbi' => !$isERC1155 ? ERC721_URI_ABI : ERC1155_URI_ABI,
			]),
		));
		if (isset($tokensResponse['error'])) {
			Log::info([
				'url' => "{$url}/tx/call-method?protocol={$protocol}",
				'method' => 'POST',
				'request' => array(
					'contractAddress' => $tokenAddress,
					'method' => !$isERC1155 ? 'tokenURI' : 'uri',
					'params' => !$isERC1155 ? [$id] : [$id],
					'contractAbi' => !$isERC1155 ? ERC721_URI_ABI : ERC1155_URI_ABI,
				),
				'response' => $tokensResponse
			]);
		}
		if (isset($tokensResponse['result'])) {
			return Blockchain::get_formatted_uri($tokensResponse['result']);
		}
		return '';
	}

	static function get_product_nft_info($productId)
	{
		$options = get_option('cryptum_nft');
		$url = Api::get_cryptum_store_url($options['environment']);

		$res = Api::request("{$url}/products/{$productId}", array(
			'method' => 'GET',
			'headers' => array(
				'x-api-key' => $options['apikey'],
				'Content-type' => 'application/json'
			),
			'timeout' => 60
		));
		if (isset($res['error'])) {
			Log::info([
				'url' => "{$url}/products/{$productId}",
				'method' => 'GET',
				'request' => array(
					'productId' => $productId,
				),
				'response' => $res
			]);
		}
		return $res;
	}
	static function get_products_by_ids(array $cryptum_product_ids)
	{
		$options = get_option('cryptum_nft');
		$url = Api::get_cryptum_store_url($options['environment']);

		$qs = [];
		foreach ($cryptum_product_ids as $id) {
			array_push($qs, "ids[]={$id}");
		}
		$ids = join("&", $qs);
		$res = Api::request("{$url}/products?storeId={$options['storeId']}&{$ids}", array(
			'method' => 'GET',
			'headers' => array(
				'x-api-key' => $options['apikey'],
				'Content-type' => 'application/json'
			),
			'timeout' => 60
		));
		if (isset($res['error'])) {
			Log::info([
				'url' => "{$url}/products?storeId={$options['storeId']}&{$ids}",
				'method' => 'GET',
				'request' => array(
					'ids' => $ids,
				),
				'response' => $res
			]);
		}
		return $res;
	}
	/**
	 * @param \WC_Order $order
	 * @param string $store_id
	 * @param mixed $products
	 * @param string $email_address
	 */
	static function create_nft_order($order, $store_id, $products, $email_address)
	{
		$options = get_option('cryptum_nft');
		$url = Api::get_cryptum_store_url($options['environment']);
		$client_wallets = [];
		$client_wallets['ETHEREUM'] = $order->get_meta('user_eth_wallet_address');
		$client_wallets['HATHOR'] = $order->get_meta('user_hathor_wallet_address');

		$response = Api::request($url . '/nft/checkout', [
			'body' => json_encode([
				'store' => $store_id,
				'email' => $email_address,
				'products' => $products,
				'ecommerceType' => 'wordpress',
				'ecommerceOrderId' => $order->get_id(),
				'clientWallets' => $client_wallets,
				'callbackUrl' => WC()->api_request_url('cryptum_nft_order_status_changed_callback'),
				'orderTotal' => $order->get_total(),
				'orderCurrency' => $order->get_currency()
			]),
			'headers' => array(
				'x-api-key' => $options['apikey'],
				'Content-Type' => 'application/json; charset=utf-8',
				'x-version' => '1.0.0'
			),
			'data_format' => 'body',
			'method' => 'POST',
			'timeout' => 60
		]);
		return $response;
	}
	/**
	 * @param string $apikey
	 * @param string $store_id
	 * @param string $environment
	 */
	static function verify_store_credentials($apikey, $store_id, $environment)
	{
		$url = Api::get_cryptum_store_url($environment);
		$response = Api::request("{$url}/stores/verification", array(
			'body' => json_encode(array(
				'storeId' => $store_id,
				'plugin' => 'nft',
				'ecommerceType' => 'wordpress'
			)),
			'headers' => array(
				'x-api-key' => $apikey,
				'Content-type' => 'application/json'
			),
			'data_format' => 'body',
			'method' => 'POST',
			'timeout' => 60
		));
		return $response;
	}

	static function call_product_request($method, $request_body, $show_admin_notice = true)
	{
		$body = $request_body;
		$options = get_option('cryptum_nft');
		if ($method == 'POST') {
			$url = Api::get_cryptum_store_url($options['environment']) . '/products';
			$request_body['store'] = $options['storeId'];
			$body = [$request_body];
		} elseif ($method == 'PUT') {
			$url = Api::get_cryptum_store_url($options['environment']) . '/products/' . $request_body['cryptum_product_id'];
			$body = ['value' => $request_body['value'], 'currency' => $request_body['currency']];
		} elseif ($method == 'DELETE') {
			$url = Api::get_cryptum_store_url($options['environment']) . '/products/' . $request_body['cryptum_product_id'];
		} elseif ($method == 'GET') {
			$url = Api::get_cryptum_store_url($options['environment']) . '/products/sku/' . $request_body['sku'] . '?store=' . $options['storeId'];
			$body = null;
		}
		Log::info($method . ' ' . $url);
		return Api::request($url, array(
			'headers' => array(
				'x-credential-identifier' => 'b0f9d288-351e-4b51-baae-f77afc8af4ad',
				'x-api-key' => $options['apikey'],
				'content-type' => 'application/json'
			),
			'data_format' => 'body',
			'method' => $method,
			'timeout' => 60,
			'body' => json_encode($body)
		));
	}
}
