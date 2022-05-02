<?php

namespace Cryptum\NFT;

use Cryptum\NFT\Utils\Api;
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
		add_action('wp_enqueue_scripts', function () {
			wp_enqueue_style('nft-view', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/nft-view.css');
			wp_enqueue_script('nft-view', CRYPTUM_NFT_PLUGIN_DIR . 'public/js/nft-view.js', ['jquery'], true, true);
		});
		wc_enqueue_js(<<<JS
			jQuery(async function() {
				// const nfts = [{
				// 	'img': 'https://blockforce.mypinata.cloud/ipfs/bafkreiatf4viunwlrdy625zsdafg5nvr76ucyn7xezqjzuw5ng7xrt2jfq',
				// 	'title': 'NFT 1 title bla bla bla bbbbbbbb wwww',
				// 	'description': 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
				// 	'tokenId': 18337,
				// 	'address': '0x88663cedfe505c144b19295504760de075d20335',
				// 	'url': 'https://alfajores-blockscout.celo-testnet.org/token/0x88663cedfe505c144b19295504760de075d20335/instance/18337/token-transfers'
				// }, {
				// 	'img': 'https://blockforce.mypinata.cloud/ipfs/bafkreiatf4viunwlrdy625zsdafg5nvr76ucyn7xezqjzuw5ng7xrt2jfq',
				// 	'title': 'NFT 2 title',
				// 	'description': 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
				// 	'tokenId': 29,
				// 	'address': '0x88663cedfe505c144b19295504760de075d20335',
				// 	'url': 'https://alfajores-blockscout.celo-testnet.org/token/0x88663cedfe505c144b19295504760de075d20335/instance/29/token-transfers'
				// }];
				const walletAddress = '0x31ec6686ee1597a41747507A931b5e12cacb920e';
				const tokenAddress = '0xdd461d17800797581030d936c3155fe37bd67436';
				const protocol = 'CELO';
				const nfts = await loadNftsFromWallet(walletAddress, tokenAddress, protocol);
				showNftColumns(nfts);
			});
		JS);
		add_action('wp_ajax_load_nft_info', [$this::$instance, 'load_nft_info']);
		add_action('wp_ajax_nopriv_load_nft_info', [$this::$instance, 'load_nft_info']);
	}

	public function load_nft_info()
	{
		$nftInfo = $_POST['nftInfo'];
		Log::info($nftInfo);

		$info = Api::get_nft_info_from_wallet($nftInfo['walletAddress'], $nftInfo['tokenAddress'], $nftInfo['protocol']);
		if (isset($info['error'])) {
			wp_send_json_error($info, 400);
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
		Log::info("Created page_id=" . $page_id . " for page '" . $title_of_the_page);
		return $page_id;
	}
	private function get_content()
	{
		return '<!-- wp:columns --><div id="nft-columns" class="wp-block-columns nft-columns">' . __('No NFTs found yet.') . '</div><!-- /wp:columns -->';
	}
}
