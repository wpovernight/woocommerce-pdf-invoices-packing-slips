<?php

namespace WPO\IPS\EDI\Abstracts;

use WPO\IPS\EDI\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractHandler {

	public Document $document;

	/**
	 * Constructor.
	 *
	 * @param Document $document The document instance.
	 */
	public function __construct( Document $document ) {
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
	 * Get normalized WooCommerce payment data for the current order.
	 *
	 * @return array {
	 *     @type string $type_code      EN16931 TypeCode
	 *     @type string $method         WooCommerce method ID
	 *     @type string $title          WooCommerce method title
	 *     @type string $iban           Optional IBAN
	 *     @type string $bic            Optional BIC
	 *     @type string $transaction_id Optional transaction ID
	 *     @type string $account_name   Optional account name
	 * }
	 */
	protected function get_payment_means_data(): array {
		$order     = $this->document->order;
		$method_id = $order ? $order->get_payment_method() : '';
		$title     = $order ? $order->get_payment_method_title() : '';

		$mapping = apply_filters( 'wpo_ips_edi_payment_means_code_mapping', array(
			'bacs'    => '58', // SEPA Credit Transfer
			'paypal'  => '68', // Online payment
			'stripe'  => '54', // Credit card
			'cod'     => '46', // Interbank debit transfer
			'default' => '97', // Clearing between partners
		), $method_id, $this );

		$type_code = $mapping[ $method_id ] ?? $mapping['default'];

		$data = array(
			'type_code'      => $type_code,
			'method'         => $method_id,
			'title'          => $title,
			'iban'           => '',
			'bic'            => '',
			'transaction_id' => '',
			'account_name'   => '',
		);

		switch ( $method_id ) {
			case 'bacs':
				$accounts = get_option( 'woocommerce_bacs_accounts', array() );
				$account  = is_array( $accounts ) ? reset( $accounts ) : array();

				$data['iban']         = $account['iban'] ?? '';
				$data['bic']          = $account['bic'] ?? '';
				$data['account_name'] = $account['account_name'] ?? '';
				break;

			case 'paypal':
				$data['transaction_id'] = $order->get_transaction_id();
				break;

			case 'stripe':
				$data['transaction_id'] = $order->get_meta( '_stripe_source_id', true );
				break;
		}

		return $data;
	}

	/**
	 * Normalize a raw date input into a specific format.
	 *
	 * @param mixed  $raw    The input date (DateTime, string, timestamp, etc.)
	 * @param string $format The output format (default: 'Y-m-d')
	 * @return string
	 */
	protected function normalize_date( $raw, string $format = 'Y-m-d' ): string {
		if ( empty( $raw ) ) {
			return '';
		}

		// Handle UNIX timestamp (int or numeric string)
		if ( is_numeric( $raw ) && (int) $raw > 1000000000 ) {
			try {
				$datetime = new \DateTimeImmutable( '@' . (int) $raw );
				$datetime = $datetime->setTimezone( function_exists( 'wc_timezone' ) ? \wc_timezone() : new \DateTimeZone( 'UTC' ) );
				return $datetime->format( $format );
			} catch ( \Exception $e ) {
				return '';
			}
		}

		// Handle DateTimeInterface objects
		if ( $raw instanceof \DateTimeInterface ) {
			return $raw->format( $format );
		}

		// Try WooCommerce string parser
		if ( function_exists( 'wc_string_to_datetime' ) ) {
			try {
				$datetime = \wc_string_to_datetime( $raw );
				return $datetime->format( $format );
			} catch ( \Exception $e ) {
				// fallback below
			}
		}

		// Fallback with strtotime
		$timestamp = strtotime( $raw );
		if ( $timestamp ) {
			$datetime = new \DateTimeImmutable( '@' . $timestamp );
			$datetime = $datetime->setTimezone( function_exists( 'wp_timezone' ) ? \wp_timezone() : new \DateTimeZone( 'UTC' ) );
			return $datetime->format( $format );
		}

		return '';
	}

}
