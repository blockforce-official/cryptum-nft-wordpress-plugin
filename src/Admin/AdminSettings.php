<?php

namespace Cryptum\NFT\Admin;

use Cryptum\NFT\Utils\Api;
use Cryptum\NFT\Utils\Log;
use Cryptum\NFT\Utils\Misc;

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
		if (is_admin()) {
			add_action('admin_enqueue_scripts', function ($hook) {
				if (!strstr($hook, "cryptum_nft_settings")) {
					return;
				}
				wp_enqueue_style('admin-style', CRYPTUM_NFT_PLUGIN_DIR . 'public/css/admin.css');
			});
			register_setting('cryptum_nft_settings', 'cryptum_nft', function ($input) {

				$options = get_option('cryptum_nft');
				$store_id = $input['storeId'];
				$apikey = $input['apikey'];
				$environment = $input['environment'];

				if (!Misc::is_uuid_valid($store_id)) {
					add_settings_error(
						'cryptum_nft_settings',
						'error',
						__('Store id is not valid UUID string', 'cryptum-nft-domain'),
						'error'
					);
					return $options;
				}
				if (!Misc::is_apikey_valid($apikey)) {
					add_settings_error(
						'cryptum_nft_settings',
						'error',
						__('API key is not valid', 'cryptum-nft-domain'),
						'error'
					);
					return $options;
				}

				$response = Api::verify_store_credentials($apikey, $store_id, $environment);
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
		<div class="cryptum_nft_admin_wrap">
			<div class="cryptum_nft_admin_top">
				<h1><?php _e('Cryptum NFT Settings', 'cryptum-nft-domain') ?></h1>
				<hr>
			</div>
			<div class="cryptum_nft_admin_main_wrap">
				<?php
				settings_errors('cryptum_nft_settings');
				?>
				<div class="cryptum_nft_admin_main_left">
					<p class="cryptum_nft_admin_main_p">
						<?php _e('This plugin allows to configure your store environment.', 'cryptum-nft-domain') ?>
						<br>
						<?php _e('It is necessary to create an account in
						Cryptum Dashboard to receive the store id and API key to fill out the fields below.', 'cryptum-nft-domain') ?>
					</p>
					<br>

					<form method="post" action="options.php" id="options">

						<?php
						settings_fields('cryptum_nft_settings');
						$options = get_option('cryptum_nft');
						if (!is_array($options)) {
							$options = array();
						}
						?>
						<table class="form-table">

							<tr valign="top">
								<th scope="row"><label for="order"><?php _e('Environment', 'cryptum-nft-domain'); ?></label></th>
								<td>
									<select name="cryptum_nft[environment]">
										<option value="production" <?php if ($options['environment'] == 'production') {
																		echo ' selected="selected"';
																	} ?>><?php _e('Production', 'cryptum-nft-domain'); ?></option>
										<option value="test" <?php if ($options['environment'] == 'test') {
																	echo ' selected="selected"';
																} ?>><?php _e('Test', 'cryptum-nft-domain'); ?></option>
									</select>
									<br>
									<p><?php _e('Choose your environment. The Test environment should be used for testing only (testnets).', 'cryptum-nft-domain'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="storeId"><?php _e('Store Id', 'cryptum-nft-domain'); ?></label></th>
								<td>
									<input id="storeId" type="text" name="cryptum_nft[storeId]" value="<?php echo esc_attr($options['storeId']); ?>" style="width: 70%" />
									<p><?php _e('Enter your Store ID generated in Cryptum Dashboard', 'cryptum-nft-domain'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="apikey"><?php _e('API key', 'cryptum-nft-domain'); ?></label></th>
								<td>
									<input id="apikey" type="text" name="cryptum_nft[apikey]" value="<?php echo esc_attr($options['apikey']); ?>" style="width: 70%" />
									<p><?php _e('Enter your Cryptum API Key (Generated in Cryptum Dashboard)', 'cryptum-nft-domain'); ?></p>
								</td>
							</tr>
							<br>
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
