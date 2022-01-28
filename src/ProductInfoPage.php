<?php

namespace Cryptum\NFT;

use Cryptum\NFT\Utils\Blockchain;

class ProductInfoPage
{

	private static $instance = null;
	public static function instance()
	{
		if (self::$instance == null) {
			self::$instance = new ProductInfoPage();
		}
		return self::$instance;
	}
	private function __construct()
	{
	}

	public function show_product_nft_blockchain_info()
	{
		global $post;
		$product = wc_get_product($post->ID);
		$nft_enabled = $product->get_meta('_cryptum_nft_options_nft_enable');
		if (isset($nft_enabled) and $nft_enabled == 'yes') {
			$contractAddress = $product->get_meta('_cryptum_nft_options_token_address');
			$tokenId = $product->get_meta('_cryptum_nft_options_token_id');
			$blockchain = $product->get_meta('_cryptum_nft_options_nft_blockchain');
			$explorerUrl = Blockchain::get_explorer_url($blockchain, $contractAddress, $tokenId);

?>
			<div id="_cryptum_nft_info" style="background-color: #75757526; padding: 10px;">
				<div style="display: flex;">
					<h4 style="flex-grow:1;"><?= __('Chain info') ?></h4>
					<span id="_cryptum_nft_nft_info" title="<?= __('When you buy this product, you will receive a non-fungible token from the ' . $blockchain . ' network. The redemption instructions will be sent by email.') ?>">
						<i class="fa fa-info-circle dashicons dashicons-info"></i>
					</span>
				</div>
				<hr style="margin: 5px 0;">
				<p style="font-size: 14px;"><?= __('Contract Address') ?>: <?php echo $contractAddress ?></p>
				<p style="font-size: 14px;"><?= __('Token Id') ?> : <?php echo $tokenId ?></p>
				<p style="font-size: 14px;">Blockchain: <?php echo $blockchain ?></p>
				<p style="font-size: 14px;"><a href="<?php echo $explorerUrl ?>" target="_blank"><?= __('View in explorer') ?></a></p>
			</div>
<?php
			wc_enqueue_js('
			jQuery(function(){
				jQuery("#_cryptum_nft_nft_info").tooltip({
					position: { my: "left+15 center", at: "right center" }
				});
			});
		');
		}
	}
}
