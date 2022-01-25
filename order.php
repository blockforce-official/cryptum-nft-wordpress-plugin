<?php

function cryptum_nft_checkout_page($checkout)
{
	$cart = WC()->cart->get_cart();
	$has_nft_enabled = false;
	foreach ($cart as $cart_item) {
		$product = wc_get_product($cart_item['product_id']);
		$cryptum_nft_enabled = $product->get_meta('_cryptum_nft_options_nft_enable');
		if (isset($cryptum_nft_enabled) and $cryptum_nft_enabled == 'yes') {
			$has_nft_enabled = true;
			break;
		}
	}
	if ($has_nft_enabled) {
		woocommerce_form_field(
			'user_wallet_address',
			array(
				'type' => 'text',
				'class' => array(
					'my-field-class form-row-wide'
				),
				'label' => __('User wallet address'),
				'placeholder' => '',
				'required' => true
			),
			$checkout->get_value('user_wallet_address')
		);
	}
}
function cryptum_nft_checkout_validation_process()
{
	$cart = WC()->cart->get_cart();
	$has_nft_enabled = false;
	foreach ($cart as $cart_item) {
		$product = wc_get_product($cart_item['product_id']);
		$cryptum_nft_enabled = $product->get_meta('_cryptum_nft_options_nft_enable');
		if (isset($cryptum_nft_enabled) and $cryptum_nft_enabled == 'yes') {
			$has_nft_enabled = true;
			break;
		}
	}
	if ($has_nft_enabled) {
		if (empty($_POST['user_wallet_address'])) {
			wc_add_notice(__('Please enter user wallet address!'), 'error');
		}
	}
}
function cryptum_nft_checkout_field_update_order_meta($order_id)
{
	if (!empty($_POST['user_wallet_address'])) {
		update_post_meta($order_id, 'user_wallet_address', sanitize_text_field($_POST['user_wallet_address']));
	}
}
