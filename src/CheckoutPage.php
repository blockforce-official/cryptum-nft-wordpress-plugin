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
		wp_enqueue_script('walletconnect', 'https://unpkg.com/@walletconnect/web3-provider@1.7.8/dist/umd/index.min.js', [], false, false);
		wp_enqueue_script('walletconnection', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/walletconnect.js', ['jquery', 'walletconnect'], true, false);
		wp_localize_script('walletconnection', 'walletconnection_wpScriptObject', array(
			'nonce' => wp_generate_uuid4(),
			'signMessage'  => esc_html("Sign this message to prove you have access to this wallet and we'll log you in. This won't cost you anything. To stop hackers using your wallet, here's a unique message ID they can't guess "),
		));
		wp_enqueue_script('checkout', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/checkout.js', ['jquery'], true, true);
		wp_localize_script('checkout', 'checkout_wpScriptObject', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'action' => 'save_user_meta',
			'security' => wp_create_nonce('save_user_meta'),
			'save'  => esc_html('Save'),
			'cancel' => esc_html('Cancel'),
		));

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
					'label' => __('User wallet address', 'cryptum-nft-domain'),
					'placeholder' => '',
					'required' => true
				),
				$wallet_address
			);
?>
			<div id="user-wallet-block">
				<div id="user-wallet-connection-block">
					<p class="user-wallet-label">
						<?php echo __('Click the button to connect your wallet', 'cryptum-nft-domain') ?>:
					</p>
					<div class="loading-icon" style="display:none;">
						<div class="">
							<i class="fa fa-spinner fa-spin" style="--fa-animation-duration:2s;"></i>
							Connecting...
						</div>
					</div>
					<button id="user-wallet-connection-button" class="button alt">
						<div id="user-wallet-connection-img-div">
							<img src="https://docs.walletconnect.com/img/walletconnect-logo.svg" alt="" />
						</div>
						<div>&nbsp;&nbsp;<?php echo __('Connect to WalletConnect', 'cryptum-nft-domain') ?></div>
					</button>
					<p id="user-walletconnect-error" style="color:red;"></p>
				</div>
				<div id="user-wallet-generator-block">
					<p class="user-wallet-label">
						<?php echo __('If you don\'t have a wallet yet or would like to generate a new one, click below', 'cryptum-nft-domain') ?>:
					</p>
					<button id="user-wallet-generator-button" class="button alt">
						<?php echo __('Generate new wallet', 'cryptum-nft-domain') ?>
					</button>
					<div id="user-wallet-generator-modal" style="display:none;" title="<?php echo __('New Wallet', 'cryptum-nft-domain') ?>">
						<p><strong><?php echo __('Address', 'cryptum-nft-domain') ?>:</strong> <span id="user-wallet-modal-address"></span></p>
						<p><strong><?php echo __('Private Key', 'cryptum-nft-domain') ?>:</strong> <span id="user-wallet-modal-privateKey"></span></p>
						<p style="color:red;">
							<strong>
								<?php echo __('Obs: Copy this private key and save it somewhere safe. For security reasons, we cannot show it to you again', 'cryptum-nft-domain') ?>
							</strong>
						</p>
						<p id="user-wallet-modal-error" style="color:red; display:none;"></p>
					</div>
				</div>
			</div>
<?php
		}
	}

	public function save_user_meta()
	{
		check_ajax_referer('save_user_meta', 'security');
		$address = $_POST['address'];
		// Log::info($address);
		$user = wp_get_current_user();
		if (!empty($user)) {
			update_user_meta($user->ID, '_cryptum_nft_user_wallet', '{"address":"' . $address . '"}');
		}

		wp_die();
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
				wc_add_notice(__('Please enter a valid user wallet address!', 'cryptum-nft-domain'), 'error');
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
