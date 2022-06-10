<?php

namespace Cryptum\NFT\Utils;

use Exception;

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
	public static function info($message, $print_backtrace = false)
	{
		self::log($message, 'info');
		if ($print_backtrace) {
			self::log(self::generateCallTrace());
		}
	}
	public static function error($message)
	{
		self::log($message, 'error');
		self::log(self::generateCallTrace());
	}
	private static function generateCallTrace()
	{
		$e = new Exception();
		$trace = explode("\n", $e->getTraceAsString());
		// reverse array to make steps line up chronologically
		$trace = array_reverse($trace);
		array_shift($trace); // remove {main}
		array_pop($trace); // remove call to this method
		$length = count($trace);
		$result = array();

		for ($i = 0; $i < $length; $i++) {
			$result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
		}
		return "\t" . implode("\n\t", $result);
	}
}
