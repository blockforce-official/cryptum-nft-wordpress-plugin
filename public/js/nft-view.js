/**
 * 
 * @param {string} walletAddress 
 * @param {string} tokenAddress 
 * @param {string} protocol 
 */
async function loadNftsFromWallet(walletAddress, protocol, tokenAddress, tokenId = null) {
  if (!walletAddress || !tokenAddress || !protocol) {
    return [];
  }
  return new Promise((resolve, reject) => {
    const data = {
      action: wpScriptObject.action,
      security: wpScriptObject.security,
      nftInfo: { walletAddress, tokenAddress, tokenId, protocol }
    };
    console.log(data);
    jQuery.ajax({
      method: 'POST',
      url: wpScriptObject.ajaxUrl,
      data,
      success: (data) => {
        console.log('Got this from the server: ', data);
        resolve(data.result);
      },
      error: (xhr, textStatus, error) => {
        console.error(error);
        jQuery('#nft-columns').html('Error loading NFTs. Try again later!');
        reject(error);
      }
    });
  });
}
/**
 * @param {any[][]} data
 * @returns {{img:string; title:string; description:string; tokenId:number; address:string;}[]}
 */
async function formatNftData(tokenAddress, environment, protocol, data) {
  const nfts = [];
  for (let i = 0; i < data.length; ++i) {
    let id, uri, amount;
    console.log(data[i]);
    if (data[i] && data[i].length === 3) {
      ([id, amount, uri] = data[i]);
      if (amount && Number(amount) === 0) {
        continue;
      }
    } else {
      ([id, uri] = data[i]);
    }

    const json = await new Promise((resolve, reject) => {
      jQuery.ajax({
        url: formatIpfsUri(uri),
        method: 'GET',
        success: (data) => { resolve(data); },
        error: (xhr, status, error) => { console.error(error); resolve({}); },
      });
    });
    console.log('push')
    nfts.push({
      tokenId: id,
      img: json.image ? formatIpfsUri(json.image) : '',
      title: json.name,
      description: json.description,
      address: tokenAddress,
      amount,
      url: getTokenExplorerUrl(id, tokenAddress, environment, protocol)
    });
  }
  return nfts;
}

/**
 * @param {{img:string; title:string; description:string; tokenId:number; address:string;}[]} nfts 
 */
function showNftColumns(nfts = []) {
  const $ = jQuery;
  const nftColumnsDiv = $('#nft-columns');

  for (let i = 0; i < nfts.length; ++i) {
    let title = nfts[i]['title'];
    let address = nfts[i]['address'];
    let description = nfts[i]['description'];
    const url = nfts[i]['url'];
    if (title && title.length > 40) {
      title = title.slice(0, 40) + '...';
    }
    if (address && address.length > 30) {
      address = address.slice(0, 30) + '...';
    }
    if (description && description.length > 100) {
      description = description.slice(0, 100) + '...';
    }
    const nftColumn = `
      <!-- wp:column -->
      <div class="wp-block-columnq nft-column">
        <!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
        <figure class="wp-block-image size-large"><img src="${nfts[i]['img']}" alt="" /></figure>
        <!-- /wp:image -->

        <!-- wp:paragraph {"style":{"typography":{"fontSize":"14px"}}} -->
        <strong class="title">${title}</strong><br>
        <p class="text">
          <span><strong>ID:</strong> ${nfts[i]['tokenId']}</span><br>
          ${nfts[i]['amount'] ? `<span><strong>Amount:</strong> ${nfts[i]['amount']}</span><br>` : ''}
          <span><strong>Address: </strong><a href="${url}" target="_blank">${address} <i class="fa fa-external-link"></i></a></span>
          <span class="description">${description}</span>
        </p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:column -->
    `;
    nftColumnsDiv.append(nftColumn);
  }
  console.log(nftColumnsDiv);
}

// function formatIpfsUri(uri) {
//   let url = uri;
//   if (uri.startsWith('ipfs')) {
//     url = `https://blockforce.mypinata.cloud/ipfs/${uri.slice(7)}`;
//   }
//   return url;
// }

// function getTokenExplorerUrl(tokenId, tokenAddress, environment, protocol) {
//   let middle = '';
//   switch (protocol) {
//     case 'CELO':
//       middle = environment == `production` ? 'explorer.celo' : 'alfajores-blockscout.celo-testnet';
//       return `https://${middle}.org/token/${tokenAddress}/instance/${tokenId}/token-transfers`;
//     case 'ETHEREUM':
//       middle = environment == `production` ? 'etherscan' : 'rinkeby.etherscan';
//       return `https://${middle}.io/token/${tokenAddress}?a=${tokenId}`;
//     case 'BSC':
//       middle = environment == `production` ? 'bscscan' : 'testnet.bscscan';
//       return `https://${middle}.com/token/${tokenAddress}?a=${tokenId}`;
//     case 'AVAXCCHAIN':
//       middle = environment == `production` ? 'snowtrace' : 'testnet.snowtrace';
//       return `https://${middle}.io/token/${tokenAddress}?a=${tokenId}`;
//     default:
//       return ``;
//   }
// }

jQuery(function () {
  const walletAddress = wpScriptObject.walletAddress;
  const tokenAddresses = wpScriptObject.tokenAddresses;
  const environment = wpScriptObject.environment;

  jQuery('#user-wallet-address-title').css('display', 'block');
  jQuery('#user-wallet-address').text(walletAddress);
  console.log(wpScriptObject);

  jQuery('#nft-columns').html('');
  for (const tokenAddress of tokenAddresses) {
    const [protocol, address, tokenId] = tokenAddress.split('#');
    console.log([protocol, address, tokenId]);
    loadNftsFromWallet(walletAddress, protocol, address, tokenId)
      .then(data => formatNftData(address, environment, protocol, data))
      .then(nfts => showNftColumns(nfts));
  }
});