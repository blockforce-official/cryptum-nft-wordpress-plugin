<?php

namespace Cryptum\NFT\Admin;

use Cryptum\NFT\Utils\Api;
use Cryptum\NFT\Utils\Blockchain;
use Cryptum\NFT\Utils\Log;

class OrderSettings
{
	private static $instance = null;
	public static function instance()
	{
		if (self::$instance == null) {
			self::$instance = new OrderSettings();
		}
		return self::$instance;
	}
	private function __construct()
	{
	}

	public function on_order_status_changed($order_id, $old_status, $new_status)
	{
		// Log::info($old_status . ' -> ' . $new_status);
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
			$url = Api::get_cryptum_url($options['environment']);
			$response = Api::request($url . '/nft/checkout', [
				'body' => json_encode([
					'store' => $storeId,
					'email' => $emailAddress,
					'products' => $products,
					'ecommerceType' => 'wordpress',
					'ecommerceOrderId' => $order_id,
					'clientWallet' => $order->get_meta('user_wallet_address'),
					'callbackUrl' => WC()->api_request_url('cryptum_nft_order_status_changed_callback')
				]),
				'headers' => array(
					'x-api-key' => $options['apikey'],
					'Content-Type' => 'application/json; charset=utf-8',
					'x-version' => '1.0.0'
				),
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

	public function show_transactions_info_panel()
	{
		global $pagenow, $post;
		$post_type = get_post_type($post);
		if (is_admin() and $post_type == 'shop_order' and $pagenow == 'post.php') {
			$order = wc_get_order($post);

			if (!empty($order->get_meta('user_wallet_address'))) {
				add_meta_box(
					'cryptum_nft_transactions_info',
					__('Cryptum NFT Transactions Info'),
					[$this, 'show_transactions_info'],
					'shop_order',
					'normal'
				);
			}
		}
	}

	public function show_transactions_info()
	{ ?>
		<div class="cryptum_nft_transactions_infro_panel_data">
			<?php
			global $post;
			$order = wc_get_order($post);

			$message = $order->get_meta('_cryptum_nft_order_transactions_message');
			if (!empty($message)) {
				echo '<p style="font-size:12px;">' . __($message)  . '</p>';
			}
			$transactions = json_decode($order->get_meta('_cryptum_nft_order_transactions'));
			if (isset($transactions) and count($transactions) > 0) {
				echo '<h4>' . __('NFT transactions hashes') . '</h4>';
				foreach ($transactions as $transaction) {
					echo '<p><strong>' . $transaction->protocol . ': </strong> '
						. '<a href="' . Blockchain::get_tx_explorer_url($transaction->protocol, $transaction->hash) . '" target="_blank">'
						. $transaction->hash
						. '</a></p>';
				}
			} else {
				echo '<p>' . __('No NFTs have been transferred yet.') . '</p>';
			} ?>
		</div>
<?php
	}

	public function nft_order_status_changed_callback()
	{
		if ('OPTIONS' == $_SERVER['REQUEST_METHOD']) {
			status_header(200);
			exit();
		} elseif ('POST' == $_SERVER['REQUEST_METHOD']) {
			$apikey = $_SERVER['HTTP_X_API_KEY'];
			$options = get_option('cryptum_nft');
			if ($apikey != $options['apikey']) {
				wp_send_json_error(array('message' => 'Unauthorized'), 401);
			}

			$raw_post = file_get_contents('php://input');
			$decoded  = json_decode($raw_post);
			$orderId = $decoded->orderId;
			$storeId = $decoded->storeId;
			$message = $decoded->message;
			$transactions = $decoded->transactions;

			if (!isset($storeId) or $options['storeId'] != $storeId) {
				wp_send_json_error(array('message' => 'Incorrect store id'), 400);
			}
			$order = wc_get_order($orderId);
			if (!isset($order)) {
				wp_send_json_error(array('message' => 'Incorrect order id'), 400);
			}
			$order->update_meta_data('_cryptum_nft_order_transactions', json_encode($transactions));
			$order->update_meta_data('_cryptum_nft_order_transactions_message', $message);
			$order->save();
		}
	}
}
