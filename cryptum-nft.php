<?php

/**
 * Plugin Name: Cryptum NFT Plugin
 * Plugin URI: https://github.com/blockforce-official/cryptum-nft-wordpress-plugin
 * Description: Cryptum NFT Plugin
 * Version: 2.0.2
 * Author: Blockforce
 * Author URI: https://blockforce.in
 * Domain Path: /public/languages
 * Requires at least: 5.7
 * Requires PHP: 7.0
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') or exit;

if (defined('CRYPTUM_NFT_PATH')) {
	return;
}

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
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

// require_once CRYPTUM_NFT_PLUGIN_DIR . '/src/PluginInit.php';

add_action('plugins_loaded', [Cryptum\NFT\PluginInit::instance(), 'load']);
