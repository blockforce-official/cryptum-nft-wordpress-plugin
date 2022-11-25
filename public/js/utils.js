function formatIpfsUri(uri) {
	let url = uri;
	if (uri.startsWith('ipfs')) {
		url = `https://blockforce.mypinata.cloud/ipfs/${uri.slice(7)}`;
	}
	return url;
}

function getTokenExplorerUrl(tokenId, tokenAddress, environment, protocol) {
	let middle = '';
	switch (protocol) {
		case 'CELO':
			middle = environment == `production` ? 'mainnet' : 'alfajores';
			return `https://explorer.celo.org/${middle}/token/${tokenAddress}/instance/${tokenId}/token-transfers`;
		case 'ETHEREUM':
			middle = environment == `production` ? 'etherscan' : 'rinkeby.etherscan';
			return `https://${middle}.io/token/${tokenAddress}?a=${tokenId}`;
		case 'POLYGON':
			middle = environment == `production` ? 'polygonscan' : 'mumbai.polygonscan';
			return `https://${middle}.io/token/${tokenAddress}?a=${tokenId}`;
		case 'BSC':
			middle = environment == `production` ? 'bscscan' : 'testnet.bscscan';
			return `https://${middle}.com/token/${tokenAddress}?a=${tokenId}`;
		case 'AVAXCCHAIN':
			middle = environment == `production` ? 'snowtrace' : 'testnet.snowtrace';
			return `https://${middle}.io/token/${tokenAddress}?a=${tokenId}`;
		default:
			return ``;
	}
}