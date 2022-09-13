<?php

namespace Cryptum\NFT;

use Cryptum\NFT\Admin\AdminSettings;
use Cryptum\NFT\Admin\OrderSettings;
use Cryptum\NFT\Admin\ProductEditPage;
use Cryptum\NFT\Utils\Log;

// @codeCoverageIgnoreStart
defined('ABSPATH') or exit;
// @codeCoverageIgnoreEnd

class PluginInit
{
	private static $instance = null;
	public static function instance()
	{
		if (self::$instance == null) {
			self::$instance = new PluginInit();
		}
		return self::$instance;
	}
	private function __construct()
	{
	}

	public function load()
	{
		add_action('wp_enqueue_scripts', function () {
			wp_enqueue_style('fontawesome', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/vendor/fontawesome@5.15.4.min.css');
			wp_enqueue_script('myutils', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/utils.js', [], true);
		});

		add_filter('plugin_action_links_' . plugin_basename(__FILE__), [AdminSettings::instance(), 'show_plugin_action_links']);
		add_action('admin_init', [AdminSettings::instance(), 'load']);
		add_action('admin_menu', [AdminSettings::instance(), 'show_cryptum_nft_settings']);

		add_filter('woocommerce_product_data_tabs', [ProductEditPage::instance(), 'show_product_data_tab']);
		add_action('woocommerce_product_data_panels', [ProductEditPage::instance(), 'show_product_data_tab_panel']);
		add_action('woocommerce_update_product', [ProductEditPage::instance(), 'on_update_product']);
		add_action('woocommerce_process_product_meta', [ProductEditPage::instance(), 'on_process_product_metadata']);
		add_action('wp_ajax_process_product_metadata', [ProductEditPage::instance(), 'process_product_metadata']);

		add_action('woocommerce_product_thumbnails', [ProductInfoPage::instance(), 'show_product_nft_blockchain_info'], 20);

		add_action('woocommerce_checkout_billing', [CheckoutPage::instance(), 'show_wallet_connection_form']);
		add_action('woocommerce_before_checkout_process', [CheckoutPage::instance(), 'checkout_validation_process']);
		add_action('woocommerce_checkout_update_order_meta', [CheckoutPage::instance(), 'checkout_field_update_order_meta']);
		add_action('wp_ajax_save_user_meta', [CheckoutPage::instance(), 'save_user_meta']);
		add_action('wp_ajax_nopriv_save_user_meta', [CheckoutPage::instance(), 'save_user_meta']);

		add_action('woocommerce_order_status_changed', [OrderSettings::instance(), 'on_order_status_changed'], 10, 3);
		add_action('woocommerce_api_cryptum_nft_order_status_changed_callback', [OrderSettings::instance(), 'nft_order_status_changed_callback']);
		add_action('add_meta_boxes', [OrderSettings::instance(), 'show_transactions_info_panel']);
	}
}
