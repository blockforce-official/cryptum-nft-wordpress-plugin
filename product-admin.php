<?php

require_once 'utils.php';

function show_cryptum_nft_product_data_tab($tabs)
{
	$tabs['cryptum_nft_options'] = [
		'label' => __('Cryptum NFT Options', 'txtdomain'),
		'target' => 'cryptum_nft_options'
	];
	return $tabs;
}
function show_cryptum_nft_product_data_tab_panel()
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
			<p><?php _e('Input the product id with NFT link from Cryptum Dashboard') ?></p>
			<?php woocommerce_wp_text_input(
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
			); ?>
			<p id="cryptum_nft_options_product_error_message" class="error-message hidden"></p>
			<div class="cryptum_nft_options_toolbar">
				<button id="cryptum_nft_options_button_save" type="button" class="button button-primary"><?php _e('Save') ?></button>
			</div>
		</div>
		<?php wp_enqueue_style('product-data', plugins_url('css/product-data.css', __FILE__)); ?>
		<?php wp_enqueue_script('product-data', plugins_url('js/product-data.js', __FILE__)); ?>
		<script>
			<?php
			$options = get_option('cryptum_nft');
			global $post;
			?>

			function is_blocked($node) {
				return $node.is(".processing") || $node.parents(".processing").length;
			};

			function block($node) {
				if (!is_blocked($node)) {
					$node.addClass("processing").block({
						message: null,
						overlayCSS: {
							background: "#fff",
							opacity: 0.6,
						},
					});
				}
			};

			function unblock($node) {
				$node.removeClass("processing").unblock();
			};

			function updateProductMetadata(productData) {
				jQuery.ajax({
					url: "/wp-admin/admin-ajax.php",
					type: "post",
					data: {
						action: "process_product_metadata",
						productData
					},
					error: (xhr, textStatus, error) => {
						console.error(textStatus, error);
						unblock(jQuery('#woocommerce-product-data'));
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
				block(jQuery('#woocommerce-product-data'));

				jQuery.ajax({
					method: 'get',
					headers: {
						'x-api-key': "<?php echo $options['apikey'] ?>",
						'content-type': 'application/json'
					},
					url: '<?php echo get_cryptum_url($options['environment']) . '/products/' ?>' + productId.val(),
					success: (data, textStatus) => {
						console.log('Updated product id', data);
						jQuery('#cryptum_nft_options_product_error_message').addClass('hidden');
						// update product metadata
						updateProductMetadata({
							post_id: <?php echo $post->ID ?>,
							_cryptum_nft_options_product_id: productId.val(),
							_cryptum_nft_options_nft_enable: 'yes',
							_cryptum_nft_options_nft_contract_address: data.nft.contractAddress,
							_cryptum_nft_options_nft_blockchain: data.nft.protocol,
						});
						setTimeout(() => {
							unblock(jQuery('#woocommerce-product-data'));
						}, 1000);
					},
					error: (xhr, textStatus, error) => {
						console.error(textStatus, error);
						unblock(jQuery('#woocommerce-product-data'));
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

function on_process_product_metadata($post_id)
{
	$product = wc_get_product($post_id);
	$product->update_meta_data('_cryptum_nft_options_nft_enable', $_POST['_cryptum_nft_options_nft_enable']);
	$nft_enabled = $_POST['_cryptum_nft_options_nft_enable'];
	if (!isset($nft_enabled)) {
		$product->update_meta_data('_cryptum_nft_options_nft_blockchain', '');
		$product->update_meta_data('_cryptum_nft_options_nft_contract_address', '');
		$product->update_meta_data('_cryptum_nft_options_product_id', '');
	}
	$product->save();
	_log("-------------------------\nSaving product custom fields " . $post_id . json_encode($product->get_meta_data()));
}
function process_product_metadata()
{
	_log("-------------------------\n" . json_encode($_REQUEST['productData']));
	$product_data = $_REQUEST['productData'];
	$product = wc_get_product($product_data['post_id']);
	$product->update_meta_data('_cryptum_nft_options_nft_blockchain', $product_data['_cryptum_nft_options_nft_blockchain']);
	$product->update_meta_data('_cryptum_nft_options_nft_contract_address', $product_data['_cryptum_nft_options_nft_contract_address']);
	$product->update_meta_data('_cryptum_nft_options_product_id', $product_data['_cryptum_nft_options_product_id']);
	$product->update_meta_data('_cryptum_nft_options_nft_enable', $product_data['_cryptum_nft_options_nft_enable']);
	$product->save();
	_log("-------------------------\nAjax Saving product custom fields " . $product->get_id() . json_encode($product->get_meta_data()));
}
