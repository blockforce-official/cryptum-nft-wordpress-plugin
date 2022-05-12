<?php

namespace Cryptum\NFT;

use Cryptum\NFT\Utils\Api;
use Cryptum\NFT\Utils\Db;
use Cryptum\NFT\Utils\Log;

class NFTViewPage
{
	private $pageId;
	private $pageName;
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
		$this->pageId = 0;
		$this->pageName = __('Your NFTs', 'cryptum-nft-domain');
	}

	public function create_page()
	{
		$options = get_option('cryptum_nft');
		if ($options['isNFTViewEnabled'] != 'yes') {
			return;
		}
		$this->try_create_page($this->pageName, $this->get_content());
	}

	public function load_page()
	{
		$options = get_option('cryptum_nft');
		if ($options['isNFTViewEnabled'] != 'yes') {
			return;
		}
		global $_SERVER;

		$pageName = sanitize_title($this->pageName);
		if ($_SERVER['REQUEST_URI'] == "/{$pageName}/") {
			Log::info('Page "Your NFTs"');

			add_action('wp_enqueue_scripts', function () {
				wp_enqueue_style('nft-view', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/nft-view.css');
				wp_enqueue_script('nft-view', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/nft-view.js', ['jquery', 'utils'], true, true);
			});

			// $this->init_db();

			$current_user = wp_get_current_user();
			// Log::info($current_user);
			$walletAddress = '';
			$userWallet = json_decode(get_user_meta($current_user->ID, '_cryptum_nft_user_wallet', true));
			// Log::info($userWallet);
			if (isset($userWallet)) {
				$walletAddress = $userWallet->address;
			}
			if (!empty($walletAddress)) {
				$tokenAddresses = $options['tokenAddresses'];
				// Log::info($options);
				wc_enqueue_js(<<<JS
					jQuery(function() {
						const protocol = 'CELO';
						const walletAddress = "{$walletAddress}";
						const tokenAddresses = "{$tokenAddresses}".split(',');
						console.log(walletAddress, tokenAddresses);
						for (const tokenAddress of tokenAddresses) {
							loadNftsFromWallet(walletAddress, tokenAddress, protocol)
								.then(data => formatNftData(tokenAddress, "{$options['environment']}", protocol, data))
								.then(nfts => showNftColumns(nfts));
						}
					});
				JS);
				add_action('wp_ajax_load_nft_info', [$this::$instance, 'load_nft_info']);
				add_action('wp_ajax_nopriv_load_nft_info', [$this::$instance, 'load_nft_info']);
			}
		}
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

	private function try_create_page($title_of_the_page, $content, $parent_id = NULL)
	{
		$objPage = get_page_by_title($title_of_the_page, 'OBJECT', 'page');
		if (!empty($objPage)) {
			$this->pageId = $objPage->ID;
			return $objPage->ID;
		}

		$pageId = wp_insert_post(
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
		Log::info("Created pageId = '" . $pageId . "' for page '" . $title_of_the_page . "'");
		$this->pageId = $pageId;
	}
	public function delete_page()
	{
		wp_delete_post($this->pageId);
	}
	private function get_content()
	{
		$notFoundText = __('No NFTs found yet.', 'cryptum-nft-domain');
		return <<<HTML
			<p id="user-wallet-address"></p>
			<!-- wp:columns -->
			<div id="nft-columns" class="wp-block-columns nft-columns">{$notFoundText}</div>
			<!-- /wp:columns -->
		HTML;
	}
}
