<?php

namespace Cryptum\NFT\Admin;

use Cryptum\NFT\Utils\Api;
use Cryptum\NFT\Utils\Log;
use Cryptum\NFT\Utils\Misc;

class ProductEditPage
{
	private static $instance = null;
	public static function instance()
	{
		if (self::$instance == null) {
			self::$instance = new ProductEditPage();
		}
		return self::$instance;
	}
	private function __construct()
	{
		$postType = Misc::get_post_type_from_querystring($_SERVER['QUERY_STRING']);
		if (is_admin() && strcmp($postType, 'product') === 0) {
			// Log::info($postType);
			add_action('wp_enqueue_scripts', function () {
				wp_enqueue_style('admin', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/admin.css');
			});
			add_action('admin_notices', function () {
				$title = get_transient('product_edit_page_error.title');
				$message = get_transient('product_edit_page_error.message');
				if (!empty($title) or !empty($message)) { ?>
					<div class="error notice notice-error">
						<p class="cryptum_nft_title"><?php esc_html_e($title) ?></p>
						<p><?php esc_html_e($message) ?></p>
					</div>
		<?php
					delete_transient('product_edit_page_error.title');
					delete_transient('product_edit_page_error.message');
				}
			});
		}
	}

	function set_admin_notices_error($title = '', $message = '')
	{
		set_transient('product_edit_page_error.title', $title);
		set_transient('product_edit_page_error.message', $message);
	}

	public function show_product_data_tab($tabs)
	{
		$tabs['cryptum_nft_options'] = [
			'label' => __('Cryptum NFT Options', 'cryptum-nft-domain'),
			'target' => 'cryptum_nft_options'
		];
		return $tabs;
	}

	public function show_product_data_tab_panel()
	{
		?>
		<div id="cryptum_nft_options" class="panel woocommerce_options_panel hidden">
			<?php woocommerce_wp_checkbox(
				array(
					'id' => '_cryptum_nft_options_nft_enable',
					'placeholder' => '',
					'label' => __('Enable NFT link', 'cryptum-nft-domain'),
					'description' => __('Enable/Disable link between this product and NFT', 'cryptum-nft-domain'),
					'desc_tip' => 'true'
				)
			); ?>
			<hr>

			<div id="cryptum_nft_options_div">
				<p><?php _e('After updating this product, go to Cryptum Dashboard to mint and link the NFT to this product SKU', 'cryptum-nft-domain') ?></p>
				<p id="cryptum_nft_options_product_error_message" class="error-message hidden"></p>
			</div>
		</div>
<?php
	}
	public function skuify($product)
	{
		$id = mb_strtoupper(bin2hex(random_bytes(7)));
		return $product->get_id() . '-' . $id;
	}

	public function on_process_product_metadata($post_id)
	{
		$product = wc_get_product($post_id);
		$old_nft_enabled = $product->get_meta('_cryptum_nft_options_nft_enable', true);
		$nft_enabled = filter_input(INPUT_POST, '_cryptum_nft_options_nft_enable');
		$old_sku = get_post_meta($post_id, '_sku', true);
		$sku = filter_input(INPUT_POST, '_sku', FILTER_SANITIZE_SPECIAL_CHARS);

		// Log::info($product->get_meta_data());
		// Log::info('$old_nft_enabled: ' . (!empty($old_nft_enabled) ? 'true' : 'false') . ' -> ' . gettype($old_nft_enabled));
		// Log::info('$nft_enabled: ' . (!empty($nft_enabled) ? 'true' : 'false'));
		// Log::info('$old_sku: ' . $old_sku);
		// Log::info('$sku: ' . $sku);
		// Log::info('!empty($nft_enabled) and empty($old_nft_enabled): ' . (!empty($nft_enabled) and empty($old_nft_enabled) ? 'true' : 'false'));
		// Log::info('!empty($old_nft_enabled) and !empty($nft_enabled) and $old_nft_enabled == $nft_enabled: ' . (!empty($old_nft_enabled) and !empty($nft_enabled) and $old_nft_enabled == $nft_enabled ? 'true' : 'false'));

		if (!empty($nft_enabled) and empty($old_nft_enabled)) {

			if (empty($old_sku) and empty($sku)) {
				$sku = $this->skuify($product);
				// add new product
				$response = Api::call_product_request('POST', array('name' => $product->get_name(), 'sku' => $sku));
				if ($this->has_error_response($response)) {
					return false;
				}
				$product->update_meta_data('_cryptum_nft_options_product_id', $response[0]['id']);
			} elseif (empty($old_sku) and !empty($sku)) {
				$response = Api::call_product_request('POST', array('name' => $product->get_name(), 'sku' => $sku));
				if ($this->has_error_response($response)) {
					return false;
				}
				$product->update_meta_data('_cryptum_nft_options_product_id', $response[0]['id']);
			} elseif (!empty($old_sku) and !empty($sku)) {
				if ($old_sku != $sku) {
					$price = $product->get_price();
					$response = Api::call_product_request('POST', array(
						'name' => $product->get_name(),
						'sku' => $sku,
						'value' => $price,
						'currency' => get_woocommerce_currency()
					));
					if ($this->has_error_response($response)) {
						return false;
					}
					$product->update_meta_data('_cryptum_nft_options_product_id', $response[0]['id']);
					$product->update_meta_data('_cryptum_nft_product_price', $price);
				} else {
					$response = Api::call_product_request('GET', array('sku' => $sku), false);
					if ($this->has_error_response($response)) {
						// no product yet, add it
						$price = $product->get_price();
						$response = Api::call_product_request(
							'POST',
							array(
								'name' => $product->get_name(),
								'sku' => $sku,
								'value' => $price,
								'currency' => get_woocommerce_currency()
							)
						);
						if ($this->has_error_response($response)) {
							return false;
						}
						$product->update_meta_data('_cryptum_nft_options_product_id', $response[0]['id']);
						$product->update_meta_data('_cryptum_nft_product_price', $price);
					} else {
						$this->set_admin_notices_error(
							__("Error in configuring product on Cryptum NFT Plugin", 'cryptum-nft-domain'),
							__('Product SKU is duplicate, try to set another SKU value.', 'cryptum-nft-domain')
						);
						return false;
					}
				}
			} elseif (!empty($old_sku) and empty($sku)) {
				// $response = Api::call_product_request('DELETE', array('cryptum_product_id' => $product->get_meta('_cryptum_nft_options_product_id', true)));
				// if ($this->has_error_response($response)) {
				// 	return false;
				// }
				$product->update_meta_data('_cryptum_nft_options_product_id', '');
			}
		} elseif (!empty($old_nft_enabled) and empty($nft_enabled)) {
			// deselecting checkbox for link nft
			$product_id = $product->get_meta('_cryptum_nft_options_product_id', true);
			Log::info('Product id: ' . $product_id . ', type: ' . gettype($product_id));
			if (!empty($product_id)) {
				$response = Api::call_product_request('DELETE', array('cryptum_product_id' => $product_id));
				if ($this->has_error_response($response)) {
					return false;
				}
				$product->update_meta_data('_cryptum_nft_options_product_id', '');
				$product->update_meta_data('_cryptum_nft_product_price', '');
			}
		} elseif (!empty($old_nft_enabled) and !empty($nft_enabled) and $old_nft_enabled == $nft_enabled) {
			if ($old_sku != $sku) {
				$response = Api::call_product_request('POST', array('name' => $product->get_name(), 'sku' => $sku));
				if ($this->has_error_response($response)) {
					return false;
				}
				$product->update_meta_data('_cryptum_nft_options_product_id', $response[0]['id']);
			}
		}

		$product->update_meta_data('_cryptum_nft_options_nft_enable', $nft_enabled);
		$product->update_meta_data('_cryptum_nft_sku', $sku);
		$product->save();
	}

	public function on_update_product($post_id)
	{
		$product = wc_get_product($post_id);
		$sku = $product->get_meta('_cryptum_nft_sku', true);

		$updated_price = $product->get_price();
		$price = $product->get_meta('_cryptum_nft_product_price', true);
		$product_id = $product->get_meta('_cryptum_nft_options_product_id');
		static $pass_count = 0;
		$pass_count++;
		if ($updated_price != $price and $pass_count <= 2) {
			Log::info('on_update_product: Product value: ' . $price . ' => ' . $updated_price);

			if (!empty($product_id)) {
				$response = Api::call_product_request('PUT', array(
					'cryptum_product_id' => $product_id,
					'value' => $updated_price,
					'currency' => get_woocommerce_currency()
				), true);
				if ($this->has_error_response($response)) {
					Log::error('Error updating product price');
				}
			}

			$product->update_meta_data('_cryptum_nft_product_price', $updated_price);
			$product->save();
		}

		update_post_meta($post_id, '_sku', $sku);
	}

	private function has_error_response($response)
	{
		if (isset($response['error'])) {
			$message = $response['message'];
			$this->set_admin_notices_error(
				__("Error in configuring product on Cryptum NFT Plugin", 'cryptum-nft-domain'),
				$message
			);
			return true;
		}
		return false;
	}
}
