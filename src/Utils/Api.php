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
const ERC721_TOKENSOFOWNER_ABI = [array(
	'inputs' => [array('name' => 'owner', 'type' => 'address'), array('name' => 'startIndex', 'type' => 'uint256')],
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
	'inputs' => [array('name' => 'account', 'type' => 'address'), array('name' => 'id', 'type' => 'uint256')],
	'name' => 'tokensOfOwner',
	'outputs' => [array('name' => '', 'type' => 'uint256')],
	'stateMutability' => 'view',
	'type' => 'function',
)];


class Api
{
	static function get_cryptum_url($environment)
	{
		return $environment == 'production' ? 'https://api.cryptum.io' : 'https://api-dev.cryptum.io';
	}
	static function get_cryptum_store_url($environment)
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

	static function get_nft_info_from_wallet($walletAddress, $tokenAddress, $protocol, $id = 0, $isErc721 = true)
	{
		$options = get_option('cryptum_nft');
		$url = Api::get_cryptum_url($options['environment']);
		$balanceResponse = Api::request("{$url}/tx/call-method?protocol={$protocol}", array(
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
				'method' => 'balanceOf',
				'params' => $isErc721 ? [$walletAddress] : [$walletAddress, $id],
				'contractAbi' => $isErc721 ? ERC721_BALANCEOF_ABI : ERC1155_BALANCEOF_ABI,
			]),
		));
		if (isset($balanceResponse['error'])) {
			return $balanceResponse;
		}

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
				'params' => $isErc721 ? [$walletAddress, 0] : [$walletAddress, $id],
				'contractAbi' => $isErc721 ? ERC721_TOKENSOFOWNER_ABI : ERC1155_TOKENSOFOWNER_ABI,
			]),
		));
		if (isset($tokensResponse['error'])) {
			return $tokensResponse;
		}
		return $tokensResponse;
	}
}
