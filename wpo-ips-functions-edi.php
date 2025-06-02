<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| UBL Document global functions
|--------------------------------------------------------------------------
*/

/**
 * Sanitizes a string for use in EDI documents by stripping all HTML tags and decoding HTML entities to plain text.
 *
 * @param string $string
 *
 * @return string
 */
function wpo_ips_edi_sanitize_string( string $string ): string {
	$string = wp_strip_all_tags( $string );
	return htmlspecialchars_decode( $string, ENT_QUOTES );
}

/**
 * Get EDI tax data from fallback
 *
 * @param string                  $key      Can be category, scheme, or reason
 * @param int|null                $rate_id  The tax rate ID
 * @param \WC_Abstract_Order|null $order    The order object
 * @return string
 */
function wpo_ips_edi_get_tax_data_from_fallback( string $key, ?int $rate_id, ?\WC_Abstract_Order $order ): string {
	$result = '';

	if ( ! in_array( $key, array( 'category', 'scheme', 'reason' ) ) ) {
		return $result;
	}

	$tax_rate_class   = '';
	$edi_tax_settings = get_option( 'wpo_ips_edi_tax_settings', array() );

	if ( ! is_null( $rate_id ) && class_exists( '\WC_TAX' ) && is_callable( array( '\WC_TAX', '_get_tax_rate' ) ) ) {
		$tax_rate = \WC_Tax::_get_tax_rate( $rate_id, OBJECT );

		if ( ! empty( $tax_rate ) && is_numeric( $tax_rate->tax_rate ) ) {
			$result         = isset( $edi_tax_settings['rate'][ $tax_rate->tax_rate_id ][ $key ] ) ? $edi_tax_settings['rate'][ $tax_rate->tax_rate_id ][ $key ] : '';
			$tax_rate_class = $tax_rate->tax_rate_class;
		}
	}

	if ( empty( $tax_rate_class ) ) {
		$tax_rate_class = 'standard';
	}

	if ( empty( $result ) || 'default' === $result ) {
		$result = isset( $edi_tax_settings['class'][ $tax_rate_class ][ $key ] ) ? $edi_tax_settings['class'][ $tax_rate_class ][ $key ] : '';
	}

	// check if order is tax exempt
	if ( wpo_wcpdf_order_is_vat_exempt( $order ) ) {
		switch ( $key ) {
			case 'scheme':
				$result = 'VAT';
				break;
			case 'category':
				$result = 'AE';
				break;
			case 'reason':
				$result = 'VATEX-EU-AE';
				break;
		}

		$result = apply_filters( 'wpo_ips_edi_get_tax_data_from_fallback_vat_exempt', $result, $key, $rate_id, $order );
	}

	return $result;
}

/**
 * Save EDI order taxes
 *
 * @param \WC_Abstract_Order $order
 * @return void
 */
function wpo_ips_edi_save_order_taxes( \WC_Abstract_Order $order ): void {
	foreach ( $order->get_taxes() as $item_id => $tax_item ) {
		if ( is_a( $tax_item, '\WC_Order_Item_Tax' ) && is_callable( array( $tax_item, 'get_rate_id' ) ) ) {
			// get tax rate id from item
			$tax_rate_id = $tax_item->get_rate_id();

			// read tax rate data from db
			if ( class_exists( '\WC_TAX' ) && is_callable( array( '\WC_TAX', '_get_tax_rate' ) ) ) {
				$tax_rate = \WC_Tax::_get_tax_rate( $tax_rate_id, OBJECT );

				if ( ! empty( $tax_rate ) && is_numeric( $tax_rate->tax_rate ) ) {
					// store percentage in tax item meta
					wc_update_order_item_meta( $item_id, '_wcpdf_rate_percentage', $tax_rate->tax_rate );

					$edi_tax_settings = get_option( 'wpo_ips_edi_tax_settings', array() );
					$tax_fields       = array( 'category', 'scheme', 'reason' );

					foreach ( $tax_fields as $field ) {
						$value = isset( $edi_tax_settings['rate'][ $tax_rate->tax_rate_id ][ $field ] ) ? $edi_tax_settings['rate'][ $tax_rate->tax_rate_id ][ $field ] : '';

						if ( empty( $value ) || 'default' === $value ) {
							$value = wpo_ips_edi_get_tax_data_from_fallback( $field, $tax_rate_id, $order );
						}

						wc_update_order_item_meta( $item_id, '_wpo_ips_edi_tax_' . $field, $value );
					}
				}
			}
		}
	}
}

/**
 * Get EDI Maker
 * Use `wpo_ips_edi_maker` filter to change the EDI class (which can wrap another EDI library).
 *
 * @return WPO\IPS\Makers\EDIMaker
 */
function wpo_ips_edi_get_maker() {
	$class = '\\WPO\\IPS\\Makers\\EDIMaker';

	if ( ! class_exists( $class ) ) {
		include_once( WPO_WCPDF()->plugin_path() . '/includes/Makers/EDIMaker.php' );
	}

	$class = apply_filters( 'wpo_ips_edi_maker', $class );

	return new $class();
}

/**
 * Check if EDI is available
 *
 * @return bool
 */
function wpo_ips_edi_is_available(): bool {
	// Check `sabre/xml` library here: https://packagist.org/packages/sabre/xml
	return apply_filters( 'wpo_ips_edi_is_available', WPO_WCPDF()->is_dependency_version_supported( 'php' ) );
}

/**
 * Write EDI file
 *
 * @param \WPO\IPS\Documents\OrderDocument $document
 * @param bool $attachment
 * @param bool $contents_only
 *
 * @return string|false
 */
function wpo_ips_edi_write_file( \WPO\IPS\Documents\OrderDocument $document, bool $attachment = false, bool $contents_only = false ) {
	$edi_maker = wpo_ips_edi_get_maker();

	if ( ! $edi_maker ) {
		return wcpdf_error_handling( 'EDI Maker not available. Cannot write EDI file.' );
	}

	if ( $attachment ) {
		$tmp_path = WPO_WCPDF()->main->get_tmp_path( 'attachments' );

		if ( ! $tmp_path ) {
			return wcpdf_error_handling( 'Temporary path not available. Cannot write EDI file.' );
		}

		$edi_maker->set_file_path( $tmp_path );
	}

	$edi_document = new \WPO\IPS\EDI\Syntax\Ubl\UblDocument(); //TODO: We need to check the sintax/format from settings
	$edi_document->set_order_document( $document );

	$builder  = new \WPO\IPS\EDI\SabreBuilder();
	$contents = apply_filters( 'wpo_ips_edi_contents',
		$builder->build( $edi_document ),
		$edi_document,
		$document
	);

	if ( empty( $contents ) ) {
		return wcpdf_error_handling( 'Failed to build EDI contents.' );
	}

	if ( $contents_only ) {
		return $contents;
	}

	$filename = apply_filters( 'wpo_ips_edi_filename',
		$document->get_filename(
			'download',
			array( 'output' => 'xml' )
		),
		$document
	);

	$full_filename = $edi_maker->write( $filename, $contents );

	return $full_filename;
}

/**
 * EDI file headers
 *
 * @param string $filename
 * @param int|false $size
 * @return void
 */
function wpo_ips_edi_file_headers( string $filename, $size ): void {
	$charset = apply_filters( 'wpo_ips_edi_file_header_content_type_charset', 'UTF-8' );

	header( 'Content-Description: File Transfer' );
	header( 'Content-Type: text/xml; charset=' . $charset );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Connection: Keep-Alive' );
	header( 'Expires: 0' );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Pragma: public' );
	header( 'Content-Length: ' . $size );

	do_action( 'wpo_ips_edi_after_headers', $filename, $size );
}

/**
 * Check if the country format extension is active
 *
 * @return bool
 */
function wpo_ips_edi_is_country_format_extension_active(): bool {
	return apply_filters( 'wpo_ips_edi_is_country_format_extension_active', false );
}

/**
 * Get the EDI syntaxes
 * 
 * @return array
 */
function wpo_ips_edi_syntaxes(): array {
	return apply_filters(
		'wpo_ips_edi_syntaxes',
		array(
			'ubl'       => 'Universal Business Language (UBL)',
			'cii'       => 'Cross Industry Invoice (CII)',
			// 'tacturae'  => 'Facturae' . ' (' . __( 'Spain', 'woocommerce-pdf-invoices-packing-slips' ) . ')',
			// 'fatturapa' => 'FatturaPA' . ' (' . __( 'Italy', 'woocommerce-pdf-invoices-packing-slips' ) . ')',
			// 'gs1'       => 'GS1',
		)
	);
}

/**
 * Get the EDI formats
 * 
 * @param string $syntax
 * 
 * @return array
 */
function wpo_ips_edi_formats( string $syntax = '' ): array {
	$formats = apply_filters(
		'wpo_ips_edi_formats',
		array(
			'ubl' => array(
				'ubl_2_1' => array(
					'name'  => 'UBL 2.1',
					'class' => \WPO\IPS\edi\Syntax\Ubl\Formats\UblTwoDotOne::class,
				),
				// 'peppol_bis_3_0' => array(
				// 	'name'  => 'Peppol BIS Billing 3.0',
				// 	'class' => '',
				// ),
				// 'xrechnung' => array(
				// 	'name'  => 'XRechnung' . ' (' . __( 'Germany', 'woocommerce-pdf-invoices-packing-slips' ) . ')',
				// 	'class' => '',
				// ),
				// 'ehfbilling_3_0' => array(
				// 	'name'  => 'EHF Billing 3.0' . ' (' . __( 'Norway', 'woocommerce-pdf-invoices-packing-slips' ) . ')',
				// 	'class' => '',
				// ),
				// 'svefakturatwodotzero' => array(
				// 	'name'  => 'Svefaktura 2.0' . ' (' . __( 'Sweden', 'woocommerce-pdf-invoices-packing-slips' ) . ')',
				// 	'class' => '',
				// ),
				// 'eSENS' => array(
				// 	'name'  => 'eSENS' . ' (' . __( 'EU', 'woocommerce-pdf-invoices-packing-slips' ) . ')',
				// 	'class' => '',
				// ),
			),
			'cii' => array(
				'factur-x' => array(
					'name'  => 'Factur-X',
					'class' => '',
				),
			),
		)
	);
	
	return isset( $formats[ $syntax ] ) ? $formats[ $syntax ] : $formats;
}
