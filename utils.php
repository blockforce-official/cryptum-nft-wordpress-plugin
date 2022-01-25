<?php

class CryptumNFTUtils
{
	static function get_cryptum_url($environment)
	{
		// return 'https://httpbin.org/post';
		return $environment == 'production' ? 'https://api.cryptum.io/plugins' : 'https://api-dev.cryptum.io/plugins';
	}

	static function get_nft_product_info($product_id)
	{
		$options = get_option('cryptum_nft');
		$res = CryptumNFTUtils::request(CryptumNFTUtils::get_cryptum_url($options['environment']) . '/products/' . $product_id, array('method' => 'GET'));
		return $res;
	}

	static function request($url, $args = array())
	{
		$response = wp_safe_remote_request($url, $args);
		if (is_wp_error($response)) {
			cryptum_nft__log(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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
			cryptum_nft__log(json_encode($responseBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			return [
				'error' => 'Error',
				'message' => $error_message
			];
		}
		return $responseBody;
	}

	static function get_explorer_url($protocol, $tokenAddress, $tokenId)
	{
		$options = get_option("cryptum_nft");
		switch ($protocol)
		{
			case 'CELO':
				$middle = $options['environment'] == "production" ? 'explorer.celo' : 'alfajores-blockscout.celo-testnet';
				return "https://$middle.org/token/$tokenAddress/instance/$tokenId/token-transfers";
			case 'ETHEREUM':
				$middle = $options['environment'] == "production" ? 'etherscan' : 'rinkeby.etherscan';
				return "https://$middle.io/token/$tokenAddress?a=$tokenId";
			case 'BSC':
				$middle = $options['environment'] == "production" ? 'bscscan' : 'testnet.bscscan';
				return "https://$middle.com/token/$tokenAddress?a=$tokenId";
			case 'AVAXCCHAIN':
				$middle = $options['environment'] == "production" ? 'snowtrace' : 'testnet.snowtrace';
				return "https://$middle.io/token/$tokenAddress?a=$tokenId";
			default:
				return "";
		}
	}
}
