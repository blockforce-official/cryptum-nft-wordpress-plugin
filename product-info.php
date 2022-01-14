<?php

function show_product_nft_blockchain_info()
{
	global $post;
	$product = wc_get_product($post->ID);
	$nft_enabled = $product->get_meta('_cryptum_nft_options_nft_enable');
	if (isset($nft_enabled) and $nft_enabled == 'yes') {
		$contractAddress = $product->get_meta('_cryptum_nft_options_nft_contract_address');
		$blockchain = $product->get_meta('_cryptum_nft_options_nft_blockchain');

		echo ('
			<div id="_cryptum_nft_info" style="background-color: #75757526; padding: 10px;">
				<div style="display: flex;">
					<h4 style="flex-grow:1;">' . __('Chain info') . '</h4>
					<span id="_cryptum_nft_nft_info" title="' . __('When you buy this product, you will receive a non-fungible token from the ' . $blockchain . ' network. The redemption instructions will be sent by email.') . '">
						<i class="fa fa-info-circle dashicons dashicons-info"></i>
					</span>
				</div>
				<hr style="margin: 5px 0;">
				<p style="font-size: 14px;">' . __('Contract Address') . ': ' . $contractAddress . '</p>
				<p style="font-size: 14px;">Blockchain: ' . $blockchain . '</p>
			</div>
		');
		wc_enqueue_js('
			jQuery(function(){
				jQuery("#_cryptum_nft_nft_info").tooltip({
					position: { my: "left+15 center", at: "right center" }
				});
			});
		');
	}
}
