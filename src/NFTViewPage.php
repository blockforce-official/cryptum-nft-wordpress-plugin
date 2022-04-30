<?php

namespace Cryptum\NFT;

use Cryptum\NFT\Utils\Log;

class NFTViewPage
{
	private static $instance = null;
	public static function instance()
	{
		if (self::$instance == null) {
			self::$instance = new NFTViewPage();
		}
		return self::$instance;
	}
	private function __construct()
	{
	}

	public function init()
	{
		$this->create_page(__('Your NFTs'), $this->get_content());
		wp_enqueue_style('nft-view', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/nft-view.css');
		wp_enqueue_script('nft-view', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/nft-view.js', ['jquery'], true, true);
	}

	private function create_page($title_of_the_page, $content, $parent_id = NULL)
	{
		$objPage = get_page_by_title($title_of_the_page, 'OBJECT', 'page');
		if (!empty($objPage)) {
			return $objPage->ID;
		}

		$page_id = wp_insert_post(
			array(
				'comment_status' => 'close',
				'ping_status'    => 'close',
				'post_author'    => 1,
				'post_title'     => ucwords($title_of_the_page),
				'post_name'      => sanitize_title($title_of_the_page),
				'post_status'    => 'publish',
				'post_content'   => $content,
				'post_type'      => 'page',
				'post_parent'    =>  $parent_id
			)
		);
		Log::info("Created page_id=" . $page_id . " for page '" . $title_of_the_page);
		return $page_id;
	}
	private function get_content()
	{
		return '<!-- wp:columns --><div id="nft-columns" class="wp-block-columns nft-columns">'. __('No NFTs found yet.') .'</div><!-- /wp:columns -->';
	}
}
