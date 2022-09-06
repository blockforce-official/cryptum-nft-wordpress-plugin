<?php

require_once(plugin_dir_path(__FILE__) . '/lib/autoload.php');
// if uninstall.php is not called by WordPress, die

if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}
Cryptum\NFT\Utils\Log::info('Deleting Cryptum NFT information');
$option_name = 'cryptum_nft';

delete_option($option_name);

// for site options in Multisite
delete_site_option($option_name);

// drop a custom database table
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}_cryptum_nft_item_meta");
