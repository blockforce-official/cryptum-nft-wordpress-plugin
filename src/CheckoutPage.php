<?php

namespace Cryptum\NFT;

use Cryptum\NFT\Utils\AddressValidator;
use Cryptum\NFT\Utils\Api;
use Cryptum\NFT\Utils\Blockchain;
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

	public function show_wallet_connection_form()
	{
		wp_enqueue_style('wp-jquery-ui-dialog');
		wp_enqueue_style('checkout', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/checkout.css');
		wp_enqueue_script('web3', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/vendor/web3@1.7.5.min.js', [], false, false);
		wp_enqueue_script('web3modal', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/vendor/web3modal@1.9.7.js', [], false, false);
		wp_enqueue_script('walletconnect', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/vendor/walletconnect-web3provider@1.7.8.min.js', [], false, false);
		wp_enqueue_script('walletconnection', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/walletconnect.js', ['jquery', 'web3modal', 'walletconnect'], false, false);
		wp_localize_script('walletconnection', 'walletconnection_wpScriptObject', array(
			'nonce' => wp_generate_uuid4(),
			'signMessage'  => esc_html__("Sign this message to prove you have access to this wallet and we'll log you in. This won't cost you anything. To stop hackers using your wallet, here's a unique message ID they can't guess "),
		));
		wp_enqueue_script(
			'checkout',
			CRYPTUM_NFT_PLUGIN_DIR . 'public/js/checkout.js',
			['jquery', 'jquery-ui-core', 'jquery-ui-dialog', 'walletconnection'],
			false,
			false
		);
		wp_localize_script('checkout', 'checkout_wpScriptObject', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'action' => 'save_user_meta',
			'security' => wp_create_nonce('save_user_meta'),
			'sign'  => esc_html__('Sign', 'cryptum-nft-domain'),
			'save'  => esc_html__('Save', 'cryptum-nft-domain'),
			'cancel' => esc_html__('Cancel', 'cryptum-nft-domain'),
			'walletConnectedMessage' => esc_html__('Wallet connected successfully', 'cryptum-nft-domain'),
		));

		$cart = WC()->cart->get_cart();
		$has_nft_enabled = false;
		$cryptum_product_ids = [];
		foreach ($cart as $cart_item) {
			$product = wc_get_product($cart_item['product_id']);
			$cryptum_nft_enabled = $product->get_meta('_cryptum_nft_options_nft_enable');
			if (isset($cryptum_nft_enabled) and $cryptum_nft_enabled == 'yes') {
				$has_nft_enabled = true;
				array_push($cryptum_product_ids, $product->get_meta('_cryptum_nft_options_product_id'));
			}
		}
		if ($has_nft_enabled) {
			$current_user = wp_get_current_user();
			$user_wallet = json_decode(get_user_meta($current_user->ID, '_cryptum_nft_user_wallet', true));

			$should_show_hathor_wallet_address = false;
			$should_show_eth_wallet_address = false;
			$error_message = "";
			$res = Api::get_products_by_ids($cryptum_product_ids);
			if (isset($res['error'])) {
				$error_message = __('Error on connecting to Cryptum APIs, please try again in a few minutes', 'cryptum-nft-domain');
			} else {

				$not_linked_product_names = [];
				$not_available_product_names = [];
				foreach ($res['products'] as $p) {
					// Log::info($p);
					if ($p['status'] === 'null' or !isset($p['nft'])) {
						array_push($not_linked_product_names, "<li>{$p['name']}</li>");
					} elseif ($p['status'] === 'transferred' or $p['nft']['amount'] === 0) {
						array_push($not_available_product_names, "<li>{$p['name']}</li>");
					} elseif ($p['status'] === 'available' or $p['nft']['amount'] > 0) {
						if (Blockchain::is_EVM($p['nft']['protocol'])) {
							$should_show_eth_wallet_address = true;
						} elseif ($p['nft']['protocol'] === 'HATHOR') {
							$should_show_hathor_wallet_address = true;
						}
					}
				}
				if (count($not_linked_product_names) > 0) {
					$not_linked_product_names = join('', $not_linked_product_names);
					$error_message .= '<br>' . __('The following products are not linked to NFTs:', 'cryptum-nft-domain') .
						"<ul>{$not_linked_product_names}</ul>";
				}
				if (count($not_available_product_names) > 0) {
					$not_available_product_names = join('', $not_available_product_names);
					$error_message .= '<br>' . __('The following products have no more linked NFTs:', 'cryptum-nft-domain') .
						"<ul>{$not_available_product_names}</ul>";
				}
			}
?>
			<div class="cryptum-nft-wallet-info">
				<h3><?php echo __('Wallet information', 'cryptum-nft-domain') ?></h3>

				<div class="<?php !empty($error_message) ? esc_attr_e('error-notice') : esc_attr_e('') ?>">
					<?php
					if (!empty($error_message)) {
						$should_show_eth_wallet_address = false;
						$should_show_hathor_wallet_address = false;
						echo $error_message;
					} else {
						echo '<p>' . __('Insert or connect your wallet address below to receive the NFTs later.', 'cryptum-nft-domain') . '</p>';
					} ?>
				</div>
				<?php

				if ($should_show_hathor_wallet_address) {
					woocommerce_form_field(
						'user_hathor_wallet_address',
						array(
							'type' => 'text',
							'class' => array(
								'my-field-class form-row-wide user-wallet-form-field'
							),
							'label' => __('Hathor wallet address', 'cryptum-nft-domain'),
							'placeholder' => '',
							'required' => true
						),
						$user_wallet->hathor_address
					);
				}
				if ($should_show_eth_wallet_address) {
					woocommerce_form_field(
						'user_eth_wallet_address',
						array(
							'type' => 'text',
							'class' => array(
								'my-field-class form-row-wide user-wallet-form-field'
							),
							'label' => __('Ethereum wallet address or compatible (Celo, Polygon, BSC, ...)', 'cryptum-nft-domain'),
							'placeholder' => '',
							'required' => true
						),
						$user_wallet->eth_address
					);
				?>

					<div id="wallet-sign-modal" style="display:none;">
						<p><strong><?php echo __('Click to sign message in order to verify your wallet', 'cryptum-nft-domain') ?></p>
						<p id="user-wallet-modal-error" style="color:red; display:none;"></p>
					</div>

					<div id="user-wallet-block">
						<div id="user-wallet-connection-block">
							<p class="user-wallet-label">
								<?php echo __('Click the button to connect your wallet', 'cryptum-nft-domain') ?>:
							</p>
							<div class="loading-icon" style="display:none;">
								<div class="">
									<i class="fa fa-spinner fa-spin" style="--fa-animation-duration:2s;"></i>
									<?php echo __('Connecting ...', 'cryptum-nft-domain') ?>
								</div>
							</div>
							<button id="user-wallet-connection-button" class="button alt">
								<div id="user-wallet-connection-img-div">
									<img src="<?php echo esc_url(CRYPTUM_NFT_PLUGIN_DIR . 'public/img/walletconnect-logo.svg') ?>" alt="" />
								</div>
								<div>&nbsp;&nbsp;<?php echo __('Connect to Wallet', 'cryptum-nft-domain') ?></div>
							</button>
							<p id="user-walletconnect-info" style="color:green;"></p>
							<p id="user-walletconnect-error" style="color:red;"></p>
						</div>
					</div>
				<?php } ?>
			</div>
			<br>
<?php
		}
	}

	public function save_user_meta()
	{
		check_ajax_referer('save_user_meta', 'security');
		$user = wp_get_current_user();
		if (!empty($user)) {
			$hathor_address = trim($_POST['hathor_address']);
			$eth_address = trim($_POST['eth_address']);
			// Log::info($address);
			update_user_meta($user->ID, '_cryptum_nft_user_wallet', json_encode(array(
				'hathor_address' => $hathor_address,
				'eth_address' => $eth_address,
			)));
		}

		wp_die();
	}

	public function checkout_validation_process()
	{
		$cart = WC()->cart->get_cart();
		$has_nft_enabled = false;
		$cryptum_product_ids = [];
		foreach ($cart as $cart_item) {
			$product = wc_get_product($cart_item['product_id']);
			$cryptum_nft_enabled = $product->get_meta('_cryptum_nft_options_nft_enable');
			if (isset($cryptum_nft_enabled) and $cryptum_nft_enabled == 'yes') {
				$has_nft_enabled = true;
				array_push($cryptum_product_ids, $product->get_meta('_cryptum_nft_options_product_id'));
			}
		}
		if ($has_nft_enabled) {
			$error_message = "";
			$res = Api::get_products_by_ids($cryptum_product_ids);
			if (isset($res['error'])) {
				wc_add_notice(__('Error on connecting to Cryptum APIs, please try again in a few minutes', 'cryptum-nft-domain'), 'error');
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
				$error_message .= '<br>' . __('The following products are not linked to NFTs:', 'cryptum-nft-domain') .
					"<ul>{$not_linked_product_names}</ul>";
				wc_add_notice($error_message, 'error');
			}
			if (count($not_available_product_names) > 0) {
				$not_available_product_names = join('', $not_available_product_names);
				$error_message .= '<br>' . __('The following products have no more linked NFTs:', 'cryptum-nft-domain') .
					"<ul>{$not_available_product_names}</ul>";
				wc_add_notice($error_message, 'error');
			}
			if (isset($_POST['user_eth_wallet_address']) and !AddressValidator::is_eth_address($_POST['user_eth_wallet_address'])) {
				wc_add_notice(__('Please enter a valid Ethereum compatible wallet address.', 'cryptum-nft-domain'), 'error');
			}
			if (isset($_POST['user_hathor_wallet_address']) and !AddressValidator::is_hathor_address($_POST['user_hathor_wallet_address'])) {
				wc_add_notice(__('Please enter a valid Hathor wallet address.', 'cryptum-nft-domain'), 'error');
			}
		}
	}

	public function checkout_field_update_order_meta($order_id)
	{
		$user_eth_wallet_address = sanitize_text_field($_POST['user_eth_wallet_address']);
		if (!empty($user_eth_wallet_address)) {
			update_post_meta($order_id, 'user_eth_wallet_address', $user_eth_wallet_address);
		}
		$user_hathor_wallet_address = sanitize_text_field($_POST['user_hathor_wallet_address']);
		if (!empty($user_hathor_wallet_address)) {
			update_post_meta($order_id, 'user_hathor_wallet_address', $user_hathor_wallet_address);
		}
	}
}
