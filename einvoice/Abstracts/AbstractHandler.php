<?php

namespace WPO\IPS\EInvoice\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractHandler {
	
	public AbstractDocument $document;

	/**
	 * Constructor.
	 *
	 * @param AbstractDocument $document The document instance.
	 */
	public function __construct( AbstractDocument $document ) {
		$this->document = $document;
	}

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	abstract public function handle( $data, $options = array() );
	
	/**
	 * Normalize a raw date input into a specific format.
	 *
	 * @param mixed  $raw    The input date (DateTime, string, timestamp, etc.)
	 * @param string $format The output format (default: 'Y-m-d') — e.g. 'Ymd' for format=102
	 * @return string
	 */
	protected function normalize_date( $raw, string $format = 'Y-m-d' ): string {
		if ( empty( $raw ) ) {
			return '';
		}
	
		// If it's already a DateTimeInterface, just format
		if ( $raw instanceof \DateTimeInterface ) {
			return $raw->format( $format );
		}
	
		// If it's a valid timestamp
		if ( is_numeric( $raw ) && (int) $raw > 1000000000 ) {
			$datetime = new \DateTimeImmutable( '@' . $raw );
			$datetime = $datetime->setTimezone( wc_timezone() );
			return $datetime->format( $format );
		}
	
		// If it's a string, parse it respecting WC timezone
		if ( function_exists( 'wc_string_to_datetime' ) ) {
			try {
				$datetime = wc_string_to_datetime( $raw );
				return $datetime->format( $format );
			} catch ( \Exception $e ) {
				// Silently ignore and fall back to strtotime()
			}
		}
	
		// Fallback for non-WC: parse with strtotime()
		$timestamp = strtotime( $raw );
		if ( $timestamp ) {
			$datetime = new \DateTimeImmutable( '@' . $timestamp );
			$datetime = $datetime->setTimezone( wp_timezone() );
			return $datetime->format( $format );
		}
	
		return '';
	}

}
