<?php

namespace Cryptum\NFT;

use Cryptum\NFT\Utils\Api;
use Cryptum\NFT\Utils\Db;
use Cryptum\NFT\Utils\Log;

class NFTViewPage
{
	private $page_id;
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

		global $post;
		if ($this->page_id != $post->ID) {
			return;
		}
		add_action('wp_enqueue_scripts', function () {
			wp_enqueue_style('nft-view', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/nft-view.css');
			wp_enqueue_script('nft-view', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/nft-view.js', ['jquery', 'utils'], true, true);
		});

		$this->init_db();

		$environment = get_option('cryptum_nft') || '';
		$walletAddress = '0x31ec6686ee1597a41747507A931b5e12cacb920e';
		$tokenAddresses = Db::get_key('_token_addresses');
		wc_enqueue_js(<<<JS
			jQuery(function() {
				const protocol = 'CELO';
				const walletAddress = "{$walletAddress}";
				const tokenAddresses = "{$tokenAddresses}".split(',');
				for (const tokenAddress of tokenAddresses) {
					loadNftsFromWallet(walletAddress, tokenAddress, protocol)
						.then(data => formatNftData(tokenAddress, "{$environment}", protocol, data))
						.then(nfts => showNftColumns(nfts));
				}
			});
		JS);
		add_action('wp_ajax_load_nft_info', [$this::$instance, 'load_nft_info']);
		add_action('wp_ajax_nopriv_load_nft_info', [$this::$instance, 'load_nft_info']);
	}

	private function init_db()
	{
		Db::create_cryptum_nft_meta_table();
	}

	public function load_nft_info()
	{
		$nftInfo = $_POST['nftInfo'];
		Log::info($nftInfo);

		$info = Api::get_nft_info_from_wallet($nftInfo['walletAddress'], $nftInfo['tokenAddress'], $nftInfo['protocol']);
		if (isset($info['error'])) {
			wp_send_json_error($info, 400);
		} else {
			wp_send_json($info);
		}

		wp_die();
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
		Log::info("Created page_id = '" . $page_id . "' for page '" . $title_of_the_page . "'");
		$this->page_id = $page_id;
		return $page_id;
	}
	public function delete_page()
	{
		wp_delete_post($this->page_id);
	}
	private function get_content()
	{
		$walletAddress = '0x31ec6686ee1597a41747507A931b5e12cacb920e';
		$walletAddressText = __('Wallet address');
		$notFoundText = __('No NFTs found yet.');
		return <<<HTML
			<p><strong>{$walletAddressText}:</strong> {$walletAddress}</p>
			<!-- wp:columns -->
			<div id="nft-columns" class="wp-block-columns nft-columns">{$notFoundText}</div>
			<!-- /wp:columns -->
		HTML;
	}
}
