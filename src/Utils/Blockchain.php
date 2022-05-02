<?php
namespace Cryptum\NFT\Utils;

// @codeCoverageIgnoreStart
defined('ABSPATH') or exit;
// @codeCoverageIgnoreEnd

class Blockchain
{
	static function get_nft_product_info($product_id)
	{
		$options = get_option('cryptum_nft');
		$url = Api::get_cryptum_store_url($options['environment']);
		$res = Api::request($url . '/products/' . $product_id, array('method' => 'GET'));
		return $res;
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
	static function get_tx_explorer_url($protocol, $hash)
	{
		$options = get_option("cryptum_nft");
		switch ($protocol)
		{
			case 'CELO':
				$middle = $options['environment'] == "production" ? 'explorer.celo' : 'alfajores-blockscout.celo-testnet';
				return "https://$middle.org/tx/$hash";
			case 'ETHEREUM':
				$middle = $options['environment'] == "production" ? 'etherscan' : 'rinkeby.etherscan';
				return "https://$middle.io/tx/$hash";
			case 'BSC':
				$middle = $options['environment'] == "production" ? 'bscscan' : 'testnet.bscscan';
				return "https://$middle.com/tx/$hash";
			case 'AVAXCCHAIN':
				$middle = $options['environment'] == "production" ? 'snowtrace' : 'testnet.snowtrace';
				return "https://$middle.io/tx/$hash";
			default:
				return "";
		}
	}
}
