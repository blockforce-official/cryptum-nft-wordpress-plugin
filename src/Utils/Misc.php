<?php

namespace Cryptum\NFT\Utils;

class Misc
{
	/**
	 * @param string $qs
	 */
	static function get_post_type_from_querystring($qs)
	{
		$postId = -1;
		sscanf($qs, "post=%d", $postId);
		$post = get_post($postId);
		if (isset($post)) {
			return $post->post_type;
		}
		return null;
	}
	/**
	 * @param string $uuid
	 */
	static function is_uuid_valid($uuid)
	{
		return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) === 1;
	}
	/**
	 * @param string $apikey
	 */
	static function is_apikey_valid($apikey)
	{
		if (empty($apikey)) {
			return false;
		}
		return preg_match('/^[a-zA-Z0-9]{32,}$/', $apikey) === 1;
	}
}
