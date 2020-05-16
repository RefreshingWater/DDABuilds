<?php

namespace system\util;

class StringUtil {
	/**
	 * Formats a numeric.
	 *
	 * @param number $numeric
	 *
	 * @return string
	 */
	public static function formatNumeric($numeric) {
		if ( is_int($numeric) ) {
			return self::formatInteger($numeric);
		}
		elseif ( is_float($numeric) || floatval($numeric) - (float) intval($numeric) ) {
			return self::formatDouble($numeric);
		}
		else {
			return self::formatInteger(intval($numeric));
		}
	}

	/**
	 * Formats an integer.
	 *
	 * @param integer $integer
	 *
	 * @return string
	 */
	public static function formatInteger($integer) {
		$integer = self::addThousandsSeparator($integer);

		// format minus
		$integer = self::formatNegative($integer);

		return $integer;
	}

	/**
	 * Formats a double.
	 *
	 * @param double  $double
	 * @param integer $maxDecimals
	 *
	 * @return    string
	 */
	public static function formatDouble($double, $maxDecimals = 0) {
		// round
		$double = round($double, ($maxDecimals > 0 ? $maxDecimals : 2));

		// consider as integer, if no decimal places found
		if ( !$maxDecimals && preg_match('~^(-?\d+)(?:\.(?:0*|00[0-4]\d*))?$~', $double, $match) ) {
			return self::formatInteger($match[1]);
		}

		// remove last 0
		if ( $maxDecimals < 2 && substr($double, -1) == '0' ) {
			$double = substr($double, 0, -1);
		}

		// replace decimal point
		$double = str_replace('.', '.', $double);

		// add thousands separator
		$double = self::addThousandsSeparator($double);

		// format minus
		$double = self::formatNegative($double);

		return $double;
	}

	/**
	 * Adds thousands separators to a given number.
	 *
	 * @param mixed $number
	 *
	 * @return string
	 */
	public static function addThousandsSeparator($number) {
		if ( $number >= 1000 || $number <= -1000 ) {
			$number = preg_replace('~(?<=\d)(?=(\d{3})+(?!\d))~', ',', $number);
		}

		return $number;
	}

	/**
	 * Replaces the MINUS-HYPHEN with the MINUS SIGN.
	 *
	 * @param mixed $number
	 *
	 * @return string
	 */
	public static function formatNegative($number) {
		return str_replace('-', "\xE2\x88\x92", $number);
	}

	public static function encodeHTML($string) {
		return @htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	}
}