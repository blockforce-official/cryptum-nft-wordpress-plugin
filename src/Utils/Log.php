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
		if (function_exists('wc_get_logger')) {
			wc_get_logger()->log($level, $log, array('source' => 'cryptum_nft'));
		}
        error_log($log);
	}
	public static function info($message)
	{
		$log = $message;
		if (is_array($message) || is_object($message)) {
			$log = print_r($message, true);
		}
		if (function_exists('wc_get_logger')) {
			wc_get_logger()->log('info', $log, array('source' => 'cryptum_nft'));
		}
        error_log($log);
	}
	public static function error($message)
	{
		$log = $message;
		if (is_array($message) || is_object($message)) {
			$log = print_r($message, true);
		}
		if (function_exists('wc_get_logger')) {
			wc_get_logger()->log('error', $log, array('source' => 'cryptum_nft'));
		}
        error_log($log);
	}
}
