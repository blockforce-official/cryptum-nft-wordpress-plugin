<?php

namespace Cryptum\NFT;

use Cryptum\NFT\Utils\Api;
use Cryptum\NFT\Utils\Blockchain;
use Cryptum\NFT\Utils\Log;

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
		wp_enqueue_style('product-info', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/product-info.css');

		global $post;
		$product = wc_get_product($post->ID);
		$nft_enabled = $product->get_meta('_cryptum_nft_options_nft_enable');
		if (isset($nft_enabled) and $nft_enabled == 'yes') {
			$nft = $this->get_product_nft_info($product->get_meta('_cryptum_nft_options_product_id'));
?>
			<div id="_cryptum_nft_info">
				<?php if (isset($nft)) { ?>
					<div id="_cryptum_nft_info_title" style="display: flex;">
						<span id="_cryptum_nft_nft_info">
							<i class="fa fa-link"></i>
						</span>
						<p style="flex-grow:1;"><?php _e('This product is linked by an NFT', 'cryptum-nft-domain') ?></p>
					</div>
					<hr>
					<p style="font-size: 14px;"><?php _e('Token Address', 'cryptum-nft-domain') ?>: <?php esc_html_e($nft['tokenAddress']) ?></p>
					<?php if (!empty($nft['tokenId'])) : ?>
						<p style="font-size: 14px;"><?php _e('Token Id', 'cryptum-nft-domain') ?> : <?php esc_html_e($nft['tokenId']) ?></p>
					<?php endif; ?>

					<p style="font-size: 14px;">Blockchain: <?php esc_html_e($nft['protocol']) ?></p>
					<p style="font-size: 14px;">
						<a href="<?php echo Blockchain::get_explorer_url($nft['protocol'], $nft['tokenAddress'], $nft['tokenId']) ?>" target="_blank">
							<?php _e('View in explorer', 'cryptum-nft-domain') ?>
						</a>
					</p>
				<?php } else { ?>
					<div id="_cryptum_nft_info_title" style="display: flex;">
						<span id="_cryptum_nft_nft_info">
							<i class="fa fa-link-slash"></i>
						</span>
						<p style="flex-grow:1;"><?php _e('This product is not linked by an NFT', 'cryptum-nft-domain') ?></p>
					</div>
				<?php } ?>
			</div>
<?php
		}
	}

	private function get_product_nft_info($cryptumProductId)
	{
		$product = Api::get_product_nft_info($cryptumProductId);
		return $product['nft'];
	}
}
