<?php

// require_once 'utils';

function cryptum_nft_init()
{
	register_setting('cryptum_nft_settings', 'cryptum_nft', function ($input) {
		$options = get_option('cryptum_nft');
		// $url = get_cryptum_url($input['environment']);
		// $contractAddress = $input['contractAddress'];
		// $blockchain = $input['blockchain'];

		// $response = wp_safe_remote_get("$url/account/admin-main/issue", [
		// 	'headers' => ['x-api-key' => $input['apikey']]
		// ]);

		// if (is_wp_error($response)) {
		// 	cryptum_nft__log(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		// 	add_settings_error(
		// 		'cryptum_nft',
		// 		'Configuration error',
		// 		__($response->get_error_message(), 'cryptum_nft'),
		// 		'error'
		// 	);
		// 	return $options;
		// }
		// $responseBody = json_decode($response['body'], true);

		// if (isset($responseBody['error'])) {
		// 	$error_message = $responseBody['error']['message'];
		// 	cryptum_nft__log(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		// 	add_settings_error(
		// 		'cryptum_nft',
		// 		'Configuration error',
		// 		__($error_message, 'cryptum_nft'),
		// 		'error'
		// 	);
		// 	return $options;
		// }

		// $foundAddress = array_filter($responseBody, function ($obj) use ($contractAddress, $blockchain) {
		// 	return $obj['deployAddress'] == $contractAddress and $obj['protocol'] == $blockchain and $obj['tokenType'] == 'erc721';
		// });
		// if (!isset($foundAddress) or empty($foundAddress)) {
		// 	add_settings_error(
		// 		'cryptum_nft',
		// 		'Configuration error',
		// 		__('Invalid NFT contract address. You must create or add the NFT contract in Cryptum dashboard first.', 'cryptum_nft'),
		// 		'error'
		// 	);
		// 	return $options;
		// }

		return $input;
	});
}
add_action('admin_init', 'cryptum_nft_init');

function show_cryptum_nft_settings()
{
	add_menu_page(
		'Cryptum NFT',
		'Cryptum NFT',
		'manage_options',
		'cryptum_nft_settings',
		'cryptum_nft_settings',
		'dashicons-images-alt'
	);
}
add_action('admin_menu', 'show_cryptum_nft_settings');

/**
 * [cryptum_nft_settings description]
 * @return [type] [description]
 */
function cryptum_nft_settings()
{
?>
	<link href="<?php echo plugins_url('css/admin.css', __FILE__); ?>" rel="stylesheet" type="text/css">
	<div class="cryptum_nft_admin_wrap">
		<div class="cryptum_nft_admin_top">
			<h1><?php echo __('Cryptum NFT Settings') ?></h1>
			<hr>
		</div>
		<div class="cryptum_nft_admin_main_wrap">
			<div class="cryptum_nft_admin_main_left">
				<p class="cryptum_nft_admin_main_p">
					<?php echo __('This plugin allows to configure your store environment.') ?>
					<br>
					<?php echo __('It is necessary to create an account in
					Cryptum Dashboard to receive the store id and API key to fill out the fields below.') ?>
				</p>
				<br>
				<form method="post" action="options.php" id="options">

					<?php
					settings_fields('cryptum_nft_settings');
					$options = get_option('cryptum_nft');
					?>
					<table class="form-table">

						<tr valign="top">
							<th scope="row"><label for="order"><?php echo __('Environment'); ?></label></th>
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
								<p><?php echo __('Choose your environment. The Test environment should be used for testing only.'); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="storeId">Store Id</label></th>
							<td>
								<input id="storeId" type="text" name="cryptum_nft[storeId]" value="<?php echo $options['storeId']; ?>" style="width: 70%" />
								<p><?php echo __('Enter your Store ID generated in Cryptum Dashboard'); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="apikey">API key</label></th>
							<td>
								<input id="apikey" type="text" name="cryptum_nft[apikey]" value="<?php echo $options['apikey']; ?>" style="width: 70%" />
								<p><?php echo __('Enter your Cryptum API Key (Generated in Cryptum Dashboard)'); ?></p>
							</td>
						</tr>
					</table>

					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
					</p>
				</form>
			</div>
		</div>

	<?php
}
	?>