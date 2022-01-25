<?php

function cryptum_nft_on_order_status_changed($order_id, $old_status, $new_status)
{
	cryptum_nft__log($old_status . ' -> ' . $new_status);
	if ($new_status == 'processing') {
		$order = wc_get_order($order_id);

		$user = $order->get_user();
		$options = get_option('cryptum_nft');
		$storeId = $options['storeId'];

		$items = $order->get_items();
		$products = [];
		foreach ($items as $orderItem) {
			$product = wc_get_product($orderItem->get_product_id());
			$cryptum_nft_enabled = $product->get_meta('_cryptum_nft_options_nft_enable');
			if (isset($cryptum_nft_enabled) and $cryptum_nft_enabled == 'yes') {
				$products[] = [
					'id' => trim($product->get_meta('_cryptum_nft_options_product_id')),
					'name' => $product->get_name(),
					'value' => $product->get_price(),
					'quantity' => $orderItem->get_quantity()
				];
			}
		}

		if (count($products) == 0) {
			return;
		}

		$emailAddress = !empty($order->get_billing_email()) ? $order->get_billing_email() : $user->get('email');
		$url = CryptumNFTUtils::get_cryptum_url($options['environment']);
		$response = CryptumNFTUtils::request($url . '/nft/checkout', [
			'body' => json_encode([
				'store' => $storeId,
				'email' => $emailAddress,
				'products' => $products,
				'ecommerceType' => 'wordpress',
				'ecommerceOrderId' => $order_id,
				'clientWallet' => $order->get_meta('user_wallet_address'),
				'callbackUrl' => ''
			]),
			'headers' => array(
				'x-api-key' => $options['apikey'],
				'Content-Type' => 'application/json; charset=utf-8'
			),
			'data_format' => 'body',
			'method' => 'POST',
			'timeout' => 60
		]);
		cryptum_nft__log(json_encode($response));
		if (isset($response['error'])) {
			$error_message = $response['message'];
			add_settings_error(
				'cryptum_nft',
				'Processing error',
				__($error_message, 'cryptum_nft'),
				'error'
			);
			return;
		}
	}
}
