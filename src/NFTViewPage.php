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
		$this->try_create_page($this->get_content());
	}

	public function load_page()
	{
		$options = get_option('cryptum_nft');
		if ($options['isNFTViewEnabled'] != 'yes') {
			$this->delete_page();
			return;
		}
		global $_SERVER;

		$pageName = sanitize_title($this->pageName);
		if (str_contains($_SERVER['REQUEST_URI'], "/{$pageName}/")) {

			add_action('wp_enqueue_scripts', function () {
				wp_enqueue_style('nft-view', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/nft-view.css');
			});

			$walletAddress = '';
			$current_user = wp_get_current_user();
			if (isset($current_user)) {
				$userWallet = json_decode(get_user_meta($current_user->ID, '_cryptum_nft_user_wallet', true));
				// Log::info($userWallet);
				if (isset($userWallet)) {
					$walletAddress = $userWallet->address;
				}
			}

			if (isset($_REQUEST['address'])) {
				// address querystring is set
				$walletAddress = $_REQUEST['address'];
			}
			if (!empty($walletAddress)) {
				$tokenAddresses = preg_split("/[\s,]+/", $options['tokenAddresses']);

				add_action('wp_enqueue_scripts', function () use ($walletAddress, $tokenAddresses, $options) {
					// Log::info($tokenAddresses);
					wp_enqueue_script('nft-view', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/nft-view.js', ['jquery'], true, true);
					wp_localize_script('nft-view', 'wpScriptObject', array(
						'walletAddress' => $walletAddress,
						'tokenAddresses' => $tokenAddresses,
						'environment' => $options['environment'],
						'ajaxUrl' => admin_url('admin-ajax.php'),
						'action' => 'load_nft_info',
						'security' => wp_create_nonce('load_nft_info'),
					));
				});
			}
		}
	}

	public function load_nft_info()
	{
		check_ajax_referer('load_nft_info', 'security');
		$nftInfo = $_POST['nftInfo'];
		$tokenAddress = $nftInfo['tokenAddress'];
		$walletAddress = $nftInfo['walletAddress'];
		$protocol = $nftInfo['protocol'];
		$tokenId = $nftInfo['tokenId'];

		$info = Api::get_nft_info_from_wallet($walletAddress, $tokenAddress, $protocol, $tokenId);
		if (isset($info['error'])) {
			Log::error($info);
			wp_send_json_error($info, 400);
		} else {
			wp_send_json($info);
		}

		wp_die();
	}

	private function try_create_page($content, $parent_id = NULL)
	{
		$objPage = get_page_by_title($this->pageName, 'OBJECT', 'page');
		if (!empty($objPage)) {
			$this->pageId = $objPage->ID;
			return $objPage->ID;
		}

		$pageId = wp_insert_post(
			array(
				'comment_status' => 'close',
				'ping_status'    => 'close',
				'post_author'    => 1,
				'post_title'     => ucwords($this->pageName),
				'post_name'      => sanitize_title($this->pageName),
				'post_status'    => 'publish',
				'post_content'   => $content,
				'post_type'      => 'page',
				'post_parent'    =>  $parent_id
			)
		);
		Log::info("Created pageId = '" . $pageId . "' for page '" . $this->pageName . "'");
		$this->pageId = $pageId;
	}
	public function delete_page()
	{
		$objPage = get_page_by_title($this->pageName, 'OBJECT', 'page');
		if (empty($objPage)) {
			return;
		}
		Log::info('Deleting page ' . $objPage->ID);
		wp_delete_post($objPage->ID, true);
	}
	private function get_content()
	{
		$notFoundText = __('No NFTs found yet.', 'cryptum-nft-domain');
		$userWalletText = __('User wallet address', 'cryptum-nft-domain');
		return <<<HTML
			<p id="user-wallet-address-title" style="display:none;">
				<strong>{$userWalletText}:</strong> <span id="user-wallet-address"></span>
			</p>
			<!-- wp:columns -->
			<div id="nft-columns" class="wp-block-columns nft-columns">{$notFoundText}</div>
			<!-- /wp:columns -->
		HTML;
	}
}
