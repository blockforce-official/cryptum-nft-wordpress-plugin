(function ($) {

  function showNftColumns() {
    const nfts = [{
      'img': 'https://blockforce.mypinata.cloud/ipfs/bafkreiatf4viunwlrdy625zsdafg5nvr76ucyn7xezqjzuw5ng7xrt2jfq',
      'title': 'NFT 1 title bla bla bla bbbbbbbb wwww',
      'description': 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      'tokenId': 18337,
      'address': '0x88663cedfe505c144b19295504760de075d20335'
    },{
      'img': 'https://blockforce.mypinata.cloud/ipfs/bafkreiatf4viunwlrdy625zsdafg5nvr76ucyn7xezqjzuw5ng7xrt2jfq',
      'title': 'NFT 2 title',
      'description': 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      'tokenId': 29,
      'address': '0x88663cedfe505c144b19295504760de075d20335'
    },{
      'img': 'https://blockforce.mypinata.cloud/ipfs/bafkreiatf4viunwlrdy625zsdafg5nvr76ucyn7xezqjzuw5ng7xrt2jfq',
      'title': 'NFT 3 title',
      'description': 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      'tokenId': 455,
      'address': '0x88663cedfe505c144b19295504760de075d20335'
    },{
      'img': 'https://blockforce.mypinata.cloud/ipfs/bafkreiatf4viunwlrdy625zsdafg5nvr76ucyn7xezqjzuw5ng7xrt2jfq',
      'title': 'NFT 3 title',
      'description': 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      'tokenId': 455,
      'address': '0x88663cedfe505c144b19295504760de075d20335'
    },{
      'img': 'https://blockforce.mypinata.cloud/ipfs/bafkreiatf4viunwlrdy625zsdafg5nvr76ucyn7xezqjzuw5ng7xrt2jfq',
      'title': 'NFT 3 title',
      'description': 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      'tokenId': 455,
      'address': '0x88663cedfe505c144b19295504760de075d20335'
    }];
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
      if (title.length > 40) {
        title = title.slice(0, 40) + '...';
      }
      if (address.length > 33) {
        address = address.slice(0, 33) + '...';
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
            <span><strong>Address:</strong> ${address}</span><br>
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

  showNftColumns();
})(jQuery);
