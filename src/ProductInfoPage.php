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
				<div id="_cryptum_nft_info_title" style="display: flex;">
					<span id="_cryptum_nft_nft_info">
						<i class="fa fa-link"></i>
					</span>
					<p style="flex-grow:1;"><?= __('This product is linked by an NFT', 'cryptum-nft-domain') ?></p>
				</div>
				<?php if (isset($nft)) :
					$uri = Api::get_nft_uri($nft['tokenAddress'], $nft['protocol'], $nft['tokenId']);

					wc_enqueue_js(<<<JS
						jQuery(function() {
							jQuery.ajax({
								url: formatIpfsUri("{$uri}"),
								method: 'GET',
								success: (data) => {
									jQuery('.wp-post-image').attr('src', formatIpfsUri(data.image));
								},
								error: (xhr, status, error) => { console.error(error); },
							});
						});
					JS);
				?>
					<hr>
					<p style="font-size: 14px;"><?= __('Token Address', 'cryptum-nft-domain') ?>: <?php echo $nft['tokenAddress'] ?></p>
					<p style="font-size: 14px;"><?= __('Token Id', 'cryptum-nft-domain') ?> : <?php echo $nft['tokenId'] ?></p>
					<p style="font-size: 14px;">Blockchain: <?php echo $nft['protocol'] ?></p>
					<p style="font-size: 14px;">
						<a href="<?php echo Blockchain::get_explorer_url($nft['protocol'], $nft['tokenAddress'], $nft['tokenId']) ?>" target="_blank">
							<?php _e('View in explorer', 'cryptum-nft-domain') ?>
						</a>
					</p>
				<?php endif; ?>
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
