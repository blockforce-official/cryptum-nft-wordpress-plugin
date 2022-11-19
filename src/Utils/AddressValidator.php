<?php

namespace Cryptum\NFT\Utils;

use kornrunner\Keccak;

class AddressValidator
{
	public static function is_eth_address(string $address)
	{
		if (preg_match('/^(0x)?[0-9a-f]{40}$/i', $address)) {

			$address = str_replace('0x', '', $address);
			$hash = Keccak::hash(strtolower($address), 256);
			// See: https://github.com/web3j/web3j/pull/134/files#diff-db8702981afff54d3de6a913f13b7be4R42
			$valid_checksum = true;
			for ($i = 0; $i < 40; $i++) {
				if (ctype_alpha($address[$i])) {
					// Each uppercase letter should correlate with a first bit of 1 in the hash char with the same index,
					// and each lowercase letter with a 0 bit.
					$charInt = intval($hash[$i], 16);
					if (ctype_upper($address[$i]) && $charInt <= 7 || ctype_lower($address[$i]) && $charInt > 7) {
						$valid_checksum = false;
						break;
					}
				}
			}
			$is_all_same_caps = preg_match('/^(0x)?[0-9a-f]{40}$/', $address) || preg_match('/^(0x)?[0-9A-F]{40}$/', $address);
			return $is_all_same_caps || $valid_checksum;
		}
		return false;
	}

	public static function is_hathor_address(string $address)
	{
		try {
			$decoded = self::decode_base58($address);

			$d1 = hash("sha256", substr($decoded, 0, 21), true);
			$d2 = hash("sha256", $d1, true);

			if (substr_compare($decoded, $d2, 21, 4)) {
				return false;
			}
			return true;
		} catch (\Throwable $e) {
			return false;
		}
	}

	protected static function decode_base58($input)
	{
		$alphabet = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";

		$out = array_fill(0, 25, 0);
		for ($i = 0; $i < strlen($input); $i++) {
			if (($p = strpos($alphabet, $input[$i])) === false) {
				throw new \Exception("invalid character found");
			}
			$c = $p;
			for ($j = 25; $j--;) {
				$c += (int)(58 * $out[$j]);
				$out[$j] = (int)($c % 256);
				$c /= 256;
				$c = (int)$c;
			}
			if ($c != 0) {
				throw new \Exception("address too long");
			}
		}

		$result = "";
		foreach ($out as $val) {
			$result .= chr($val);
		}
		return $result;
	}
}
