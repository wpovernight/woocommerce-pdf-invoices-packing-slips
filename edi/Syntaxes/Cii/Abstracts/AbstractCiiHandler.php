<?php

namespace WPO\IPS\EDI\Syntaxes\Cii\Abstracts;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractCiiHandler extends AbstractHandler {
	
	/**
	 * Get the date format code for CII documents.
	 * 
	 * @return string The default date format code.
	 */
	public function get_date_format_code(): string {
		return apply_filters( 'wpo_ips_edi_cii_document_date_format_code', '102', $this );
	}

	/**
	 * Validate CII date format.
	 *
	 * @param string $value  The date value to validate.
	 * @param string $format The date format (102, 610, or 616).
	 * @return bool True if valid, false otherwise.
	 */
	public function validate_date_format( string $value, string $format = '102' ): bool {
		$allowed_formats = array( '102', '610', '616' );

		if ( ! in_array( $format, $allowed_formats, true ) ) {
			return false;
		}

		switch ( $format ) {
			case '102': // YYYYMMDD
				if ( strlen( $value ) !== 8 || ! ctype_digit( $value ) ) {
					return false;
				}
				$year  = (int) substr( $value, 0, 4 );
				$month = (int) substr( $value, 4, 2 );
				$day   = (int) substr( $value, 6, 2 );
				return checkdate( $month, $day, $year );

			case '610': // YYYYMM
				if ( strlen( $value ) !== 6 || ! ctype_digit( $value ) ) {
					return false;
				}
				$year  = (int) substr( $value, 0, 4 );
				$month = (int) substr( $value, 4, 2 );
				return ( $year > 0 && $month >= 1 && $month <= 12 );

			case '616': // YYYYWW
				if ( strlen( $value ) !== 6 || ! ctype_digit( $value ) ) {
					return false;
				}
				$year = (int) substr( $value, 0, 4 );
				$week = (int) substr( $value, 4, 2 );
				return ( $year > 0 && $week >= 1 && $week <= 53 );
		}

		return false;
	}

	/**
	 * Converts a CII date format code to a PHP date format.
	 *
	 * @param string $code CII date format code (e.g. 102, 610, 616).
	 * @return string PHP-compatible date format string.
	 */
	public function get_php_date_format_from_code( string $code ): string {
		switch ( $code ) {
			case '102': // Full date: YYYYMMDD
				return 'Ymd';
			case '610': // Year + Month: YYYYMM
				return 'Ym';
			case '616': // Year + Week: YYYYWW (ISO)
				return 'oW';
			default:
				return 'Ymd'; // Fallback
		}
	}

}
