<?php

namespace Cryptum\NFT\Admin;

use Cryptum\NFT\Utils\Api;
use Cryptum\NFT\Utils\Log;

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
	}

	public function show_product_data_tab($tabs)
	{
		$tabs['cryptum_nft_options'] = [
			'label' => __('Cryptum NFT Options', 'txtdomain'),
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
					'label' => __('Enable NFT link'),
					'description' => __('Enable/Disable link between this product and NFT'),
					'desc_tip' => 'true'
				)
			); ?>
			<hr>

			<div id="cryptum_nft_options_div">
				<p><?php _e('After updating this product, go to Cryptum Dashboard to mint and link the NFT to this product SKU') ?></p>
				<?php /*woocommerce_wp_text_input(
					array(
						'id' => '_cryptum_nft_options_product_id',
						'placeholder' => '',
						'label' => __('Product id', 'woocommerce'),
						'description' => __('Product id with NFT link from Cryptum Dashboard'),
						'desc_tip' => 'true',
						'custom_attributes' => array(
							'required' => 'required'
						)
					)
				);*/ ?>
				<p id="cryptum_nft_options_product_error_message" class="error-message hidden"></p>
				<!-- <div class="cryptum_nft_options_toolbar">
					<button id="cryptum_nft_options_button_save" type="button" class="button button-primary"><?php // _e('Save') 
																												?></button>
				</div> -->
			</div>
			<?php wp_enqueue_style('product-data', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/product-data.css'); ?>
			<?php wp_enqueue_script('product-data', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/product-data.js'); ?>
			<script>
				<?php
				$options = get_option('cryptum_nft');
				global $post;
				?>

				function cryptum_nft_is_blocked($node) {
					return $node.is(".processing") || $node.parents(".processing").length;
				};

				function cryptum_nft_block($node) {
					if (!cryptum_nft_is_blocked($node)) {
						$node.addClass("processing").block({
							message: null,
							overlayCSS: {
								background: "#fff",
								opacity: 0.6,
							},
						});
					}
				};

				function cryptum_nft_unblock($node) {
					$node.removeClass("processing").unblock();
				};

				function cryptum_nft_updateProductMetadata(productData) {
					jQuery.ajax({
						url: "/wp-admin/admin-ajax.php",
						type: "post",
						data: {
							action: "process_product_metadata",
							productData
						},
						error: (xhr, textStatus, error) => {
							console.error(textStatus, error);
							cryptum_nft_unblock(jQuery('#woocommerce-product-data'));
							jQuery('#cryptum_nft_options_product_error_message').text('<?php _e('Error saving product data') ?>');
							jQuery('#cryptum_nft_options_product_error_message').removeClass('hidden');
							setTimeout(() => {
								jQuery('#_cryptum_nft_options_product_id').val('');
							}, 3000);
						}
					});
				}
				jQuery('#cryptum_nft_options_button_save').click(() => {
					var productId = jQuery('#_cryptum_nft_options_product_id');
					if (!productId.val()) {
						productId.focus();
						return false;
					}
					cryptum_nft_block(jQuery('#woocommerce-product-data'));

					jQuery.ajax({
						method: 'get',
						headers: {
							'x-api-key': "<?php echo $options['apikey'] ?>",
							'content-type': 'application/json'
						},
						url: '<?php echo Api::get_cryptum_url($options['environment']) . '/products/' ?>' + productId.val(),
						success: (data, textStatus) => {
							console.log('Updated product id', data);
							jQuery('#cryptum_nft_options_product_error_message').addClass('hidden');
							// update product metadata
							cryptum_nft_updateProductMetadata({
								post_id: <?php echo $post->ID ?>,
								_cryptum_nft_options_product_id: productId.val(),
								_cryptum_nft_options_nft_enable: 'yes',
								_cryptum_nft_options_token_address: data.nft.tokenAddress,
								_cryptum_nft_options_token_id: data.nft.tokenId,
								_cryptum_nft_options_token_amount: data.nft.amount,
								_cryptum_nft_options_nft_blockchain: data.nft.protocol,
							});
							setTimeout(() => {
								cryptum_nft_unblock(jQuery('#woocommerce-product-data'));
							}, 1000);
						},
						error: (xhr, textStatus, error) => {
							console.error(textStatus, error);
							cryptum_nft_unblock(jQuery('#woocommerce-product-data'));
							jQuery('#cryptum_nft_options_product_error_message').text(error || '<?php _e('Invalid product id') ?>');
							jQuery('#cryptum_nft_options_product_error_message').removeClass('hidden');
							setTimeout(() => {
								jQuery('#_cryptum_nft_options_product_id').val('');
							}, 2000);
						}
					});
				});
			</script>
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
		Log::info('on_process_product_metadata');
		$product = wc_get_product($post_id);
		$old_nft_enabled = $product->get_meta('_cryptum_nft_options_nft_enable', true);
		$nft_enabled = $_POST['_cryptum_nft_options_nft_enable'];
		$old_sku = get_post_meta($post_id, '_sku', true);
		$sku = $_POST['_sku'];

		Log::info($product->get_meta_data());
		Log::info('$old_nft_enabled: ' . (!empty($old_nft_enabled) ? 'true' : 'false') . ' -> ' . gettype($old_nft_enabled));
		Log::info('$nft_enabled: ' . (!empty($nft_enabled) ? 'true' : 'false'));
		Log::info('$old_sku: ' . $old_sku);
		Log::info('$sku: ' . $sku);
		Log::info('!empty($nft_enabled) and empty($old_nft_enabled): ' . (!empty($nft_enabled) and empty($old_nft_enabled) ? 'true' : 'false'));
		Log::info('!empty($old_nft_enabled) and !empty($nft_enabled) and $old_nft_enabled == $nft_enabled: ' . (!empty($old_nft_enabled) and !empty($nft_enabled) and $old_nft_enabled == $nft_enabled ? 'true' : 'false'));

		if (!empty($nft_enabled) and empty($old_nft_enabled)) {

			if (empty($old_sku) and empty($sku)) {
				$sku = $this->skuify($product);
				// add new product
				$response = $this->call_product_request('POST', array('name' => $product->get_name(), 'sku' => $sku));
				if (!$response) {
					return false;
				}
				$product->update_meta_data('_cryptum_nft_options_product_id', $response[0]['id']);
			} elseif (empty($old_sku) and !empty($sku)) {
				$response = $this->call_product_request('POST', array('name' => $product->get_name(), 'sku' => $sku));
				if (!$response) {
					return false;
				}
				$product->update_meta_data('_cryptum_nft_options_product_id', $response[0]['id']);
			} elseif (!empty($old_sku) and !empty($sku)) {
				if ($old_sku != $sku) {
					$id = $product->get_meta('_cryptum_nft_options_product_id', true);
					$response = $this->call_product_request('PUT', array('cryptum_product_id' => $id, 'name' => $product->get_name(), 'sku' => $sku));
					if (!$response) {
						return false;
					}
				} else {
					$response = $this->call_product_request('GET', array('sku' => $sku));
					if (!$response) {
						// no product yet, add it
						$response = $this->call_product_request('POST', array('name' => $product->get_name(), 'sku' => $sku));
						if (!$response) {
							return false;
						}
						$product->update_meta_data('_cryptum_nft_options_product_id', $response[0]['id']);
					} else {
						$product->update_meta_data('_cryptum_nft_options_product_id', $response['id']);
					}
				}
			} elseif (!empty($old_sku) and empty($sku)) {
				$response = $this->call_product_request('DELETE', array('cryptum_product_id' => $product->get_meta('_cryptum_nft_options_product_id', true)));
				if (!$response) {
					return false;
				}
				$product->update_meta_data('_cryptum_nft_options_product_id', '');
			}
		} elseif (!empty($old_nft_enabled) and empty($nft_enabled)) {
			// deselecting checkbox for link nft
			$response = $this->call_product_request('DELETE', array('cryptum_product_id' => $product->get_meta('_cryptum_nft_options_product_id', true)));
			if (!$response) {
				return false;
			}
			$product->update_meta_data('_cryptum_nft_options_product_id', '');
		} elseif (!empty($old_nft_enabled) and !empty($nft_enabled) and $old_nft_enabled == $nft_enabled) {
			if ($old_sku != $sku) {
				$id = $product->get_meta('_cryptum_nft_options_product_id', true);
				$response = $this->call_product_request('PUT', array('cryptum_product_id' => $id, 'name' => $product->get_name(), 'sku' => $sku));
				if (!$response) {
					return false;
				}
			}
		}
		$product->update_meta_data('_cryptum_nft_options_nft_enable', $_POST['_cryptum_nft_options_nft_enable']);
		$product->update_meta_data('_cryptum_nft_sku', $sku);
		$product->save();
	}

	public function on_update_product($post_id)
	{
		$product = wc_get_product($post_id);
		$sku = $product->get_meta('_cryptum_nft_sku', true);
		update_post_meta($post_id, '_sku', $sku);
	}

	// public function process_product_metadata()
	// {
	// 	Log::info("-------------------------\n" . json_encode($_REQUEST['productData']));
	// 	$product_data = $_REQUEST['productData'];
	// 	$product = wc_get_product($product_data['post_id']);
	// 	$product->update_meta_data('_cryptum_nft_options_nft_blockchain', $product_data['_cryptum_nft_options_nft_blockchain']);
	// 	$product->update_meta_data('_cryptum_nft_options_token_address', $product_data['_cryptum_nft_options_token_address']);
	// 	$product->update_meta_data('_cryptum_nft_options_token_id', $product_data['_cryptum_nft_options_token_id']);
	// 	$product->update_meta_data('_cryptum_nft_options_token_amount', $product_data['_cryptum_nft_options_token_amount']);
	// 	$product->update_meta_data('_cryptum_nft_options_product_id', $product_data['_cryptum_nft_options_product_id']);
	// 	$product->update_meta_data('_cryptum_nft_options_nft_enable', $product_data['_cryptum_nft_options_nft_enable']);
	// 	$product->save();
	// 	Log::info("-------------------------\nAjax Saving product custom fields " . $product->get_id() . json_encode($product->get_meta_data()));
	// }

	function call_product_request($method, $request_body)
	{
		$body = $request_body;
		$options = get_option('cryptum_nft');
		if ($method == 'POST') {
			$url = Api::get_cryptum_url($options['environment']) . '/products';
			$request_body['store'] = $options['storeId'];
			$body = [$request_body];
		} elseif ($method == 'PUT') {
			$url = Api::get_cryptum_url($options['environment']) . '/products/' . $request_body['cryptum_product_id'];
		} elseif ($method == 'DELETE') {
			$url = Api::get_cryptum_url($options['environment']) . '/products/' . $request_body['cryptum_product_id'];
		} elseif ($method == 'GET') {
			$url = Api::get_cryptum_url($options['environment']) . '/products/sku/' . $request_body['sku'];
			$body = null;
		}
		Log::info($method . ' ' . $url);
		$response = Api::request($url, array(
			'headers' => array('x-api-key' => $options['apikey'], 'content-type' => 'application/json'),
			'data_format' => 'body',
			'method' => $method,
			'timeout' => 60,
			'body' => json_encode($body)
		));
		if (isset($response['error'])) {
			Log::error($response);
			$message = $response['message'];
			add_action('admin_notices', function () use ($message) {
				ob_start();
				Log::error('Showing: ' . $message); ?>
				<div class="error notice notice-error">
					<p><?php echo __("Error in configuring product on Cryptum NFT Plugin") ?></p>
					<p><?php echo __($message) ?></p>
				</div>
<?php
				echo ob_get_clean();
			});
			return false;
		}
		return $response;
	}
}
