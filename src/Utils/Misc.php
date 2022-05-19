<?php
namespace Cryptum\NFT\Utils;

class Misc
{
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
}