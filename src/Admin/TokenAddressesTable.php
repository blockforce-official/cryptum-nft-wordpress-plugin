<?php

namespace Cryptum\NFT\Admin;

// @codeCoverageIgnoreStart
defined('ABSPATH') or exit;
// @codeCoverageIgnoreEnd

class TokenAddressesTable extends \WP_List_Table
{
	private $tokenAddresses;

	function __construct()
	{
		parent::__construct();

		$this->tokenAddresses = array();
	}

	public function add_items($items = array())
	{
		$this->tokenAddresses = $items;
	}

	function get_columns()
	{
		return [
			'tokenType'      => __('Token Type', 'cryptum-nft-domain'),
			'tokenAddress'      => __('Token Address', 'cryptum-nft-domain'),
			'tokenId' => __('Token ID', 'cryptum-nft-domain'),
		];
	}

	public function prepare_items()
	{
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns);
		$this->items = $this->tokenAddresses;
	}

	public function column_default($item, $column_name)
	{
		switch ($column_name) {
			case 'tokenType':
			case 'tokenAddress':
			case 'tokenId':
			case 'action':
				return $item[$column_name];
			default:
				return print_r($item, true); //Show the whole array for troubleshooting purposes
		}
	}

	function column_tokenType($item)
	{
		return sprintf('<select><option value="ERC-721">ERC-721</option><option value="ERC-1155">ERC-1155</option></select>');
	}
}
