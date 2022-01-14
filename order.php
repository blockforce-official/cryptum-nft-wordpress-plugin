<?php

function on_order_status_changed($order_id, $old_status, $new_status)
{
	_log($old_status . ' -> ' . $new_status);
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
		$url = get_cryptum_url($options['environment']);
		$response = request($url . '/nft/checkout', [
			'body' => [
				'storeId' => $storeId,
				'emailAddress' => $emailAddress,
				'products' => $products
			],
			'headers' => ['x-api-key' => $options['apikey']],
			'data_format' => 'body',
			'method' => 'POST',
			'timeout' => 60
		]);
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
