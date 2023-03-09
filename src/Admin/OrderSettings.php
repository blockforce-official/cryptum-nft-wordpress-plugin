<?php

namespace Cryptum\NFT\Admin;

use Cryptum\NFT\Utils\Api;
use Cryptum\NFT\Utils\Blockchain;
use Cryptum\NFT\Utils\Log;
use Cryptum\NFT\Utils\Misc;

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
		$postType = Misc::get_post_type_from_querystring($_SERVER['QUERY_STRING']);
		if (is_admin() && strcmp($postType, 'shop_order') === 0) {
			// Log::info($postType);
			add_action('wp_enqueue_scripts', function () {
				wp_enqueue_style('admin', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/admin.css');
			});
			$title = get_transient('order_settings_error.title');
			$message = get_transient('order_settings_error.message');
			if (!empty($title) or !empty($message)) {
				add_action('admin_notices', function () use ($title, $message) { ?>
					<div class="error notice notice-error">
						<p class="cryptum_nft_title"><?php esc_html_e($title) ?></p>
						<p><?php esc_html_e($message) ?></p>
					</div>
		<?php
				});
				delete_transient('order_settings_error.title');
				delete_transient('order_settings_error.message');
			}
		}
	}

	function set_admin_notices_error($title = '', $message = '')
	{
		set_transient('order_settings_error.title', $title);
		set_transient('order_settings_error.message', $message);
	}

	public function on_order_status_changed($order_id, $old_status, $new_status)
	{
		// Log::info($old_status . ' -> ' . $new_status);
		if ($new_status == 'processing') {
			$order = wc_get_order($order_id);

			$user = $order->get_user();
			$options = get_option('cryptum_nft');
			$store_id = $options['storeId'];

			$items = $order->get_items();
			$products = [];
			$cryptum_product_ids = [];
			foreach ($items as $orderItem) {
				$product = wc_get_product($orderItem->get_product_id());
				$cryptum_nft_enabled = $product->get_meta('_cryptum_nft_options_nft_enable');
				if (isset($cryptum_nft_enabled) and $cryptum_nft_enabled == 'yes') {
					array_push($cryptum_product_ids, $product->get_meta('_cryptum_nft_options_product_id'));
					$products[] = [
						'id' => trim($product->get_meta('_cryptum_nft_options_product_id')),
						'name' => $product->get_name(),
						'value' => $product->get_price(),
						'quantity' => $orderItem->get_quantity()
					];
				}
			}
			if (count($cryptum_product_ids) == 0) {
				return;
			}

			$res = Api::get_products_by_ids($cryptum_product_ids);
			if (isset($res['error'])) {
				$this->set_admin_notices_error(
					__("Error on connecting to Cryptum APIs", 'cryptum-nft-domain'),
					$res['message']
				);
				Log::error($res);
				return;
			}
			$not_linked_product_names = [];
			$not_available_product_names = [];
			foreach ($res['products'] as $p) {
				// Log::info($p);
				if ($p['status'] === 'null' or !isset($p['nft'])) {
					array_push($not_linked_product_names, "<li>{$p['name']}</li>");
				} elseif ($p['status'] === 'transferred' or $p['nft']['amount'] === 0) {
					array_push($not_available_product_names, "<li>{$p['name']}</li>");
				}
			}
			if (count($not_linked_product_names) > 0) {
				$not_linked_product_names = join('', $not_linked_product_names);
				$this->set_admin_notices_error(
					__('Error', 'cryptum-nft-domain'),
					'<br>' . __('The following products are not linked to NFTs:', 'cryptum-nft-domain') .
						"<ul>{$not_linked_product_names}</ul>"
				);
				return;
			}
			if (count($not_available_product_names) > 0) {
				$not_available_product_names = join('', $not_available_product_names);
				$this->set_admin_notices_error(
					__('', 'cryptum-nft-domain'),
					'<br>' . __('The following products have no more linked NFTs:', 'cryptum-nft-domain') .
						"<ul>{$not_available_product_names}</ul>"
				);
				return;
			}

			$email_address = !empty($order->get_billing_email()) ? $order->get_billing_email() : $user->get('email');

			$response = Api::create_nft_order($order, $store_id, $products, $email_address);
			if (isset($response['error'])) {
				$message = $response['message'];
				$this->set_admin_notices_error(
					__("Error in configuring Cryptum NFT Plugin", 'cryptum-nft-domain'),
					$message
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

			if (!empty($order->get_meta('user_eth_wallet_address')) || !empty($order->get_meta('user_hathor_wallet_address'))) {
				add_meta_box(
					'cryptum_nft_transactions_info',
					__('Cryptum NFT Transactions Info', 'cryptum-nft-domain'),
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
				echo '<p style="font-size:12px;">' . esc_html($message)  . '</p>';
			}
			$transactions = json_decode($order->get_meta('_cryptum_nft_order_transactions'));
			if (isset($transactions) and count($transactions) > 0) {
				echo '<h4>' . __('NFT transactions hashes', 'cryptum-nft-domain') . '</h4>';
				foreach ($transactions as $transaction) {
					echo '<p><strong>' . esc_html($transaction->protocol) . ': </strong> '
						. '<a href="' . esc_url(Blockchain::get_tx_explorer_url($transaction->protocol, $transaction->hash)) . '" target="_blank">'
						. esc_html($transaction->hash)
						. '</a></p>';
				}
			} else {
				echo '<p>' . __('No NFTs have been transferred yet.', 'cryptum-nft-domain') . '</p>';
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
			$apikey = filter_input(INPUT_SERVER, 'HTTP_X_API_KEY', FILTER_SANITIZE_SPECIAL_CHARS);
			$options = get_option('cryptum_nft');
			if (strcmp($apikey, $options['apikey']) !== 0) {
				wp_send_json_error(array('message' => 'Unauthorized request'), 401);
			}

			$raw_post = file_get_contents('php://input');
			$decoded  = json_decode($raw_post);
			if (!isset($decoded)) {
				wp_send_json_error(array('message' => 'JSON body payload is null or invalid'), 400);
			}
			$ecommerceOrderId = intval($decoded->ecommerceOrderId);
			$storeId = $decoded->storeId;
			$message = $decoded->message;
			$transactions = $decoded->transactions;
			$updatedProducts = $decoded->updatedProducts;
			Log::info($updatedProducts);

			if (strcmp($options['storeId'], $storeId) !== 0) {
				wp_send_json_error(array('message' => 'Incorrect store id'), 400);
			}
			$order = wc_get_order($ecommerceOrderId);
			if (!isset($order)) {
				wp_send_json_error(array('message' => 'Incorrect order id'), 400);
			}

			foreach ($order->get_items() as $item) {
				$cryptum_productId = get_post_meta($item->get_product_id(), '_cryptum_nft_options_product_id', true);
				$products_columns = array_column($updatedProducts, '_id');
				$found_product = array_search($cryptum_productId, $products_columns);
				if ($found_product) {
					$product = wc_get_product($item->get_product_id());
					$product->set_manage_stock(true);
					$product->set_stock_quantity($found_product['nft']['amount']);
					$product->save();
				}
			}

			$order->update_meta_data('_cryptum_nft_order_transactions', json_encode($transactions));
			$order->update_meta_data('_cryptum_nft_order_transactions_message', $message);
			$order->save();
		}
	}
}
