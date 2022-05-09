<?php

namespace Cryptum\NFT\Admin;

use Cryptum\NFT\Utils\Api;
use Cryptum\NFT\Utils\Log;

// @codeCoverageIgnoreStart
defined('ABSPATH') or exit;
// @codeCoverageIgnoreEnd

class AdminSettings
{
	private static $instance = null;
	public static function instance()
	{
		if (self::$instance == null) {
			self::$instance = new AdminSettings();
		}
		return self::$instance;
	}
	private function __construct()
	{
	}

	public function load()
	{
		register_setting('cryptum_nft_settings', 'cryptum_nft', function ($input) {
			$options = get_option('cryptum_nft');
			$storeId = $input['storeId'];
			$apikey = $input['apikey'];

			$url = Api::get_cryptum_store_url($input['environment']);
			$response = Api::request($url . '/stores/' . $storeId, array(
				'headers' => array(
					'x-api-key' => $apikey,
					'Content-type' => 'application/json'
				),
				'data_format' => 'body',
				'method' => 'GET',
				'timeout' => 60
			));
			if (isset($response['error'])) {
				Log::error($response);
				add_settings_error(
					'cryptum_nft_settings',
					'error',
					__('Store not configured yet or not existent. You must configure a store in Cryptum dashboard first', 'cryptum-nft-domain'),
					'error'
				);
				return $options;
			}
			add_settings_error(
				'cryptum_nft_settings',
				'success',
				__('Changes updated successfully', 'cryptum-nft-domain'),
				'success'
			);
			return $input;
		});
	}

	public function show_plugin_action_links($links)
	{
		$plugin_links = array(
			'<a href="admin.php?page=cryptum_nft_settings">' . __('Settings', 'cryptum-nft-domain') . '</a>'
		);
		return array_merge($plugin_links, $links);
	}

	public function show_cryptum_nft_settings()
	{
		add_menu_page(
			'Cryptum NFT',
			'Cryptum NFT',
			'manage_options',
			'cryptum_nft_settings',
			[self::$instance, 'cryptum_nft_settings'],
			'dashicons-images-alt'
		);
	}

	public function cryptum_nft_settings()
	{ ?>
		<link rel="stylesheet" href="<?php echo CRYPTUM_NFT_PLUGIN_DIR . 'public/css/admin.css' ?>">
		<div class="cryptum_nft_admin_wrap">
			<div class="cryptum_nft_admin_top">
				<h1><?php echo __('Cryptum NFT Settings', 'cryptum-nft-domain') ?></h1>
				<hr>
			</div>
			<div class="cryptum_nft_admin_main_wrap">
				<?php
				settings_errors('cryptum_nft_settings');
				?>
				<div class="cryptum_nft_admin_main_left">
					<p class="cryptum_nft_admin_main_p">
						<?php echo __('This plugin allows to configure your store environment.', 'cryptum-nft-domain') ?>
						<br>
						<?php echo __('It is necessary to create an account in
						Cryptum Dashboard to receive the store id and API key to fill out the fields below.', 'cryptum-nft-domain') ?>
					</p>
					<br>

					<form method="post" action="options.php" id="options">

						<?php
						settings_fields('cryptum_nft_settings');
						$options = get_option('cryptum_nft');
						?>
						<table class="form-table">

							<tr valign="top">
								<th scope="row"><label for="order"><?php echo __('Environment', 'cryptum-nft-domain'); ?></label></th>
								<td>
									<select name="cryptum_nft[environment]">
										<option value="production" <?php if ($options['environment'] == 'production') {
																		echo ' selected="selected"';
																	} ?>>Production</option>
										<option value="test" <?php if ($options['environment'] == 'test') {
																	echo ' selected="selected"';
																} ?>>Test</option>
									</select>
									<br>
									<p><?php echo __('Choose your environment. The Test environment should be used for testing only.', 'cryptum-nft-domain'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="storeId">Store Id</label></th>
								<td>
									<input id="storeId" type="text" name="cryptum_nft[storeId]" value="<?php echo $options['storeId']; ?>" style="width: 70%" />
									<p><?php echo __('Enter your Store ID generated in Cryptum Dashboard', 'cryptum-nft-domain'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="apikey">API key</label></th>
								<td>
									<input id="apikey" type="text" name="cryptum_nft[apikey]" value="<?php echo $options['apikey']; ?>" style="width: 70%" />
									<p><?php echo __('Enter your Cryptum API Key (Generated in Cryptum Dashboard)', 'cryptum-nft-domain'); ?></p>
								</td>
							</tr>
						</table>

						<p class="submit">
							<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'cryptum-nft-domain') ?>" />
						</p>
					</form>
				</div>
			</div>

	<?php
	}
}
