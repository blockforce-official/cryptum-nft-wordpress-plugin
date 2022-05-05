<?php

namespace Cryptum\NFT\Utils;

// @codeCoverageIgnoreStart
defined('ABSPATH') or exit;
// @codeCoverageIgnoreEnd

class Db
{
	static function create_cryptum_nft_meta_table()
	{
		global $wpdb;
		$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cryptum_nft_item_meta (
			`meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`cryptum_nft_item_id` bigint(20) unsigned NOT NULL,
			`meta_key` varchar(255) DEFAULT NULL,
			`meta_value` longtext,
			PRIMARY KEY (`meta_id`),
			KEY `cryptum_nft_item_id` (`cryptum_nft_item_id`),
			KEY `meta_key` (`meta_key`(32))
		)");

		$row = Db::get_key('_token_addresses');
		Log::info($row);
		if (!isset($row)) {
			$wpdb->insert("{$wpdb->prefix}cryptum_nft_item_meta", array(
				'cryptum_nft_item_id' => 1, 'meta_key' => '_token_addresses', 'meta_value' => '0xC7c0CC29217cB615d45587b2ce2D06b10f7d25f3'
			));
		}
	}

	static function get_key($key)
	{
		global $wpdb;
		$row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}cryptum_nft_item_meta WHERE meta_key = '$key'");
		return $row->meta_value;
	}
}
