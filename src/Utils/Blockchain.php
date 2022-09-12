<?php

namespace Cryptum\NFT\Utils;

// @codeCoverageIgnoreStart
defined('ABSPATH') or exit;
// @codeCoverageIgnoreEnd

define('CRYPTUM_IPFS_GATEWAY', 'https://blockforce.mypinata.cloud/ipfs/');

class Blockchain
{
	static function get_explorer_url($protocol, $tokenAddress, $tokenId)
	{
		$options = get_option("cryptum_nft");
		switch ($protocol) {
			case 'CELO':
				$middle = $options['environment'] == "production" ? 'explorer.celo' : 'alfajores-blockscout.celo-testnet';
				return "https://$middle.org/token/$tokenAddress/instance/$tokenId";
			case 'ETHEREUM':
				$middle = $options['environment'] == "production" ? 'etherscan' : 'rinkeby.etherscan';
				return "https://$middle.io/token/$tokenAddress?a=$tokenId";
			case 'POLYGON':
				$middle = $options['environment'] == "production" ? 'polygonscan' : 'mumbai.polygonscan';
				return "https://$middle.com/token/$tokenAddress?a=$tokenId";
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
		switch ($protocol) {
			case 'CELO':
				$middle = $options['environment'] == "production" ? 'explorer.celo' : 'alfajores-blockscout.celo-testnet';
				return "https://$middle.org/tx/$hash";
			case 'ETHEREUM':
				$middle = $options['environment'] == "production" ? 'etherscan' : 'rinkeby.etherscan';
				return "https://$middle.io/tx/$hash";
			case 'BSC':
				$middle = $options['environment'] == "production" ? 'bscscan' : 'testnet.bscscan';
				return "https://$middle.com/tx/$hash";
			case 'POLYGON':
				$middle = $options['environment'] == "production" ? 'polygonscan' : 'mumbai.polygonscan';
				return "https://$middle.com/tx/$hash";
			case 'AVAXCCHAIN':
				$middle = $options['environment'] == "production" ? 'snowtrace' : 'testnet.snowtrace';
				return "https://$middle.io/tx/$hash";
			default:
				return "";
		}
	}
	static function get_formatted_uri($uri)
	{
		if (str_starts_with($uri, 'ipfs://')) {
			return str_replace('ipfs://', CRYPTUM_IPFS_GATEWAY, $uri);
		}
		return $uri;
	}
}
