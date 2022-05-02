/**
 * 
 * @param {string} walletAddress 
 * @param {string} tokenAddress 
 * @param {string} protocol 
 */
async function loadNftsFromWallet(walletAddress, tokenAddress, protocol) {
  if (!walletAddress || !tokenAddress || !protocol) {
    return [];
  }
  var data = {
		action: 'load_nft_info',
		nftInfo: { walletAddress, tokenAddress, protocol }
	};
	jQuery.post('/wp-admin/admin-ajax.php', data, function(response) {
		console.log('Got this from the server: ' + response);
	});
}

/**
 * @param {{img:string; title:string; description:string; tokenId:number; address:string;}[]} nfts 
 */
function showNftColumns(nfts = []) {
  const $ = jQuery;
  const nftColumnsDiv = $('#nft-columns');

  if (nfts.length === 0) {
    nftColumnsDiv.html('No NFTs found yet.');
    return;
  }
  nftColumnsDiv.html('');

  for (let i = 0; i < nfts.length; ++i) {
    let title = nfts[i]['title'];
    let address = nfts[i]['address'];
    let description = nfts[i]['description'];
    const url = nfts[i]['url'];
    if (title.length > 40) {
      title = title.slice(0, 40) + '...';
    }
    if (address.length > 30) {
      address = address.slice(0, 30) + '...';
    }
    if (description.length > 100) {
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
          <span><strong>Address: </strong><a href="${url}" target="_blank">${address} <i class="fa fa-external-link"></i></a></span>
          <span class="description">${description}</span>
        </p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:column -->
    `;
    nftColumnsDiv.append(nftColumn);
  }
  // console.log(nftColumnsDiv);
}
