<?php

namespace Cryptum\NFT\Utils;

class Log
{
	public static function log($message, $level = 'info')
	{
		$log = $message;
		if (is_array($message) || is_object($message)) {
			$log = print_r($message, true);
		}
		$log = '[Cryptum NFT Log]: ' . $log;
		if (function_exists('wc_get_logger')) {
			wc_get_logger()->log($level,  $log, array('source' => 'cryptum_nft'));
		}
		error_log($log);
	}
	public static function info($message)
	{
		self::log($message, 'info');
	}
	public static function error($message)
	{
		self::log($message, 'error');
	}
}
