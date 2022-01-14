<?php

/**
 * Plugin Name: Cryptum NFT Plugin
 * Plugin URI: https://github.com/blockforce-official/cryptum-nft-wordpress-plugin
 * Description: Cryptum NFT Plugin
 * Version: 1.0.0
 * Author: Blockforce
 * Author URI: https://blockforce.in
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') or exit;

// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	add_action('admin_notices', function () {
		echo '<div id="setting-error-settings_updated" class="notice notice-error">
			<p>' . __("Cryptum Checkout Plugin needs Woocommerce enabled to work correctly. Please install and/or enable Woocommerce plugin", 'cryptum_nft') . '</p>
		</div>';
	});
	return;
}

if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)) {
	require 'class-admin.php';
}

require_once 'utils.php';
require_once 'product-admin.php';
require_once 'order.php';
require_once 'product-info.php';

function cryptum_nft_plugin_loaded()
{
	add_action('wp_enqueue_scripts', function () {
		wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
		wp_enqueue_style('fontawesome', 'https://use.fontawesome.com/releases/v5.7.1/css/all.css');
		wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', ['jquery'], true);
	});

	add_filter('woocommerce_product_data_tabs', 'show_cryptum_nft_product_data_tab');

	add_action('woocommerce_product_data_panels', 'show_cryptum_nft_product_data_tab_panel');

	add_action('woocommerce_process_product_meta', 'on_process_product_metadata');
	add_action('wp_ajax_process_product_metadata', 'process_product_metadata');

	add_action('woocommerce_product_thumbnails', 'show_product_nft_blockchain_info', 20);

	add_action('woocommerce_order_status_changed', 'on_order_status_changed', 10, 3);
}

function _log($message, $level = 'info')
{
	wc_get_logger()->log($level, $message, array('source' => 'cryptum_nft'));
}

function cryptum_nft_plugin_links($links)
{
	$plugin_links = array(
		'<a href="admin.php?page=cryptum_nft_settings">' . __('Settings', 'cryptum_nft') . '</a>'
	);
	return array_merge($plugin_links, $links);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'cryptum_nft_plugin_links');

add_action('plugins_loaded', 'cryptum_nft_plugin_loaded', 11);
