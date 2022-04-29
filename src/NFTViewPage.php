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
		$this->create_page(__('NFT View'), $this->get_content());
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
		$nfts = [array(
			'img' => 'https://blockforce.mypinata.cloud/ipfs/bafkreiatf4viunwlrdy625zsdafg5nvr76ucyn7xezqjzuw5ng7xrt2jfq',
			'title' => 'NFT 1',
			'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
		)];
		$html = '<!-- wp:columns --><div id="nft-columns" class="wp-block-columns">';
		foreach ($nfts as $value) {
			$html .= <<<HTML
				<!-- wp:column -->
				<div class="wp-block-column">
					<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
					<figure class="wp-block-image size-large"><img src="{$value['img']}" alt="" /></figure>
					<!-- /wp:image -->

					<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px"}}} -->
					<p style="font-size:14px"><strong>{$value['title']}</strong><br>{$value['text']}</p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:column -->
			HTML;
		}
		$html .= '</div><!-- /wp:columns -->';
		return $html;
	}
}
