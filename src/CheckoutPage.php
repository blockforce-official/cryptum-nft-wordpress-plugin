<?php

namespace Cryptum\NFT;

use Cryptum\NFT\Utils\AddressValidator;
use Cryptum\NFT\Utils\Log;

class CheckoutPage
{
	private static $instance = null;
	public static function instance()
	{
		if (self::$instance == null) {
			self::$instance = new CheckoutPage();
		}
		return self::$instance;
	}
	private function __construct()
	{
	}

	public function show_checkout_page($checkout)
	{
		wp_enqueue_style('checkout', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/checkout.css');
		wp_enqueue_script('web3', 'https://unpkg.com/web3@latest/dist/web3.min.js', [], false, false);
		wp_enqueue_script('checkout', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/checkout.js', ['jquery'], true, true);

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
		// if ($has_nft_enabled) {
			$current_user = wp_get_current_user();
			$wallet_address = '';
			$user_wallet = json_decode(get_user_meta($current_user->ID, '_cryptum_nft_user_wallet', true));
			if (isset($user_wallet)) {
				$wallet_address = $user_wallet->address;
			}
			woocommerce_form_field(
				'user_wallet_address',
				array(
					'type' => 'text',
					'class' => array(
						'my-field-class form-row-wide user-wallet-form-field'
					),
					'label' => __('User wallet address'),
					'placeholder' => '',
					'required' => true
				),
				$wallet_address
			);
			?>
			<p class="user-wallet-generator-label"><?php echo __('Click below to generate a new wallet') ?>:</p>
			<button class="button alt user-wallet-generator-button">
				<?php echo __('Generate new wallet') ?>
			</button>
			<div id="user-wallet-generator-modal" style="display:none;" title="<?php echo __('New Wallet') ?>">
				<p><strong><?php echo __('Address') ?>:</strong> <span id="user-wallet-modal-address"></span></p>
				<p><strong><?php echo __('Private Key') ?>:</strong> <span id="user-wallet-modal-privateKey"></span></p>
			</div>
			<?php
		// }
	}

	public function checkout_validation_process()
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
			if (empty($_POST['user_wallet_address']) or !AddressValidator::isETHAddress($_POST['user_wallet_address'])) {
				wc_add_notice(__('Please enter a valid user wallet address!'), 'error');
			}
		}
	}

	public function checkout_field_update_order_meta($order_id)
	{
		if (!empty($_POST['user_wallet_address'])) {
			update_post_meta($order_id, 'user_wallet_address', sanitize_text_field($_POST['user_wallet_address']));
		}
	}
}
