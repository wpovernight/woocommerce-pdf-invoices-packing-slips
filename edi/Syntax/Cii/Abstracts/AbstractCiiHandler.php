<?php

namespace WPO\IPS\EDI\Syntax\Cii\Abstracts;

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
	 * Validate CII date format
	 *
	 * @param string $value  The date value to validate.
	 * @param string $format The date format (102, 610, or 616).
	 *
	 * @return bool True if valid, false otherwise.
	 */
	public function validate_date_format( string $value, string $format = '102' ): bool {
		$allowed_formats = array( '102', '610', '616' );

		if ( ! in_array( $format, $allowed_formats, true ) ) {
			return false;
		}

		// Only validate structure if format is 102 (YYYYMMDD)
		if ( $format === '102' ) {
			if ( strlen( $value ) !== 8 || ! ctype_digit( $value ) ) {
				return false;
			}

			$year  = (int) substr( $value, 0, 4 );
			$month = (int) substr( $value, 4, 2 );
			$day   = (int) substr( $value, 6, 2 );

			if ( $year <= 0 || $month < 1 || $month > 12 || $day < 1 || $day > 31 ) {
				return false;
			}
		}

		// No structural checks required for 610 (YYYYMM) or 616 (YYYYWW)
		return true;
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
