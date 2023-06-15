<?php

/**
 * Plugin Name: Cryptum NFT
 * Plugin URI: https://github.com/cryptum-official/cryptum-nft-wordpress-plugin
 * Description: Cryptum NFT Plugin
 * Version: 1.1.2
 * Author: Cryptum
 * Author URI: https://cryptum.io
 * Domain Path: /languages
 * Text Domain: cryptum-nft-domain
 * Requires at least: 5.7
 * Requires PHP: 7.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') or exit;

if (defined('CRYPTUM_NFT_PATH')) {
	return;
}

define('CRYPTUM_NFT_PATH', dirname(__FILE__));
define('CRYPTUM_NFT_PLUGIN_DIR', plugin_dir_url(__FILE__));

// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	add_action('admin_notices', function () {
		echo '<div id="setting-error-settings_updated" class="notice notice-error">
		<p>' . __("Cryptum NFT Plugin needs Woocommerce enabled to work correctly. Please install and/or enable Woocommerce plugin", 'cryptum_nft') . '</p>
		</div>';
	});
	return;
}

require_once(plugin_dir_path(__FILE__) . '/lib/autoload.php');

add_action('plugins_loaded', [Cryptum\NFT\PluginInit::instance(), 'load']);

add_action('init', function () {
	load_plugin_textdomain('cryptum-nft-domain', false, dirname(plugin_basename(__FILE__)) . '/languages');
});
