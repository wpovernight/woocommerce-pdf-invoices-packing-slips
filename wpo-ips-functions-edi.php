<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| EDI Document global functions
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
	$edi_tax_settings = \WPO\IPS\EDI\TaxesSettings::get_tax_settings();

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

					$edi_tax_settings = \WPO\IPS\EDI\TaxesSettings::get_tax_settings();
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
 * Get EDI settings
 *
 * @return array
 */
function wpo_ips_edi_get_settings(): array {
	return get_option( 'wpo_ips_edi_settings', array() );
}

/**
 * Check if EDI is available
 *
 * @return bool
 */
function wpo_ips_edi_is_available(): bool {
	$edi_settings = wpo_ips_edi_get_settings();
	// Check `sabre/xml` library here: https://packagist.org/packages/sabre/xml
	return apply_filters( 'wpo_ips_edi_is_available', WPO_WCPDF()->is_dependency_version_supported( 'php' ) && ! empty( $edi_settings['enabled'] ) );
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

	$format = wpo_ips_edi_get_current_format();
	
	if ( empty( $format ) ) {
		return wcpdf_error_handling( 'EDI format not set. Cannot write EDI file.' );
	}
	
	$syntax = wpo_ips_edi_get_current_syntax();
	
	if ( empty( $syntax ) ) {
		return wcpdf_error_handling( 'EDI syntax not set. Cannot write EDI file.' );
	}
	
	$class  = wpo_ips_edi_syntaxes( 'class', $syntax );
	
	if ( ! $class ) {
		return wcpdf_error_handling( 'EDI Document class not found.' );
	}

	$edi_document = new $class( $format );
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
 * Get the current EDI syntax
 *
 * @return string
 */
function wpo_ips_edi_get_current_syntax(): string {
	$edi_settings = wpo_ips_edi_get_settings();
	$syntax       = 'ubl';

	if ( ! empty( $edi_settings['syntax'] ) ) {
		$syntax = $edi_settings['syntax'];
	}

	return apply_filters( 'wpo_ips_edi_current_syntax', $syntax, $edi_settings );
}

/**
 * Get the current EDI format
 *
 * @return string
 */
function wpo_ips_edi_get_current_format(): string {
	$edi_settings = wpo_ips_edi_get_settings();
	$format       = 'ubl_2_1';

	if ( ! empty( $edi_settings['syntax'] ) ) {
		$syntax     = $edi_settings['syntax'];
		$format_key = "{$syntax}_format";

		if ( ! empty( $edi_settings[ $format_key ] ) ) {
			$format = $edi_settings[ $format_key ];
		}
	}

	return apply_filters( 'wpo_ips_edi_current_format', $format, $edi_settings );
}

/**
 * Get the EDI document types
 *
 * @return array
 */
function wpo_ips_edi_get_document_types(): array {
	$edi_settings = wpo_ips_edi_get_settings();
	return apply_filters( 'wpo_ips_edi_document_types', $edi_settings['document_types'] ?? array() );
}

/**
 * Check if EDI attachments should be sent
 *
 * @return bool
 */
function wpo_ips_edi_send_attachments(): bool {
	$edi_settings = wpo_ips_edi_get_settings();
	return ! empty( $edi_settings['send_attachments'] );
}

/**
 * Check if EDI encrypted PDF should be embedded
 *
 * @return bool
 */
function wpo_ips_edi_embed_encrypted_pdf(): bool {
	$edi_settings = wpo_ips_edi_get_settings();
	return apply_filters( 'wpo_ips_edi_embed_encrypted_pdf', ! empty( $edi_settings['embed_encrypted_pdf'] ) );
}

/**
 * Check if EDI preview is enabled
 *
 * @return bool
 */
function wpo_ips_edi_preview_is_enabled(): bool {
	$edi_settings = wpo_ips_edi_get_settings();
	return ! empty( $edi_settings['enabled_preview'] );
}

/**
 * Get the EDI syntaxes or a specific syntax value.
 *
 * @param string      $value_type Either 'label' or 'class'. Defaults to 'label'.
 * @param string|null $syntax     Optional. The syntax slug (e.g. 'ubl', 'cii'). If set, returns a single value.
 * @return array|string|null
 */
function wpo_ips_edi_syntaxes( string $value_type = 'label', ?string $syntax = null ) {
	$syntaxes = apply_filters( 'wpo_ips_edi_syntaxes', array(
		'ubl' => array(
			'label' => 'Universal Business Language (UBL)',
			'class' => '\WPO\IPS\EDI\Syntax\Ubl\UblDocument',
		),
		'cii' => array(
			'label' => 'Cross Industry Invoice (CII)',
			'class' => '\WPO\IPS\EDI\Syntax\Cii\CiiDocument',
		),
	), $value_type, $syntax );

	if ( ! empty( $syntax ) ) {
		return $syntaxes[ $syntax ][ $value_type ] ?? null;
	}

	$output = array();
	foreach ( $syntaxes as $slug => $data ) {
		$output[ $slug ] = $data[ $value_type ] ?? null;
	}

	return $output;
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
				'ubl-2-1' => array(
					'name'  => 'UBL 2.1',
					'class' => \WPO\IPS\EDI\Syntax\Ubl\Formats\Ubl21::class,
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
				// 'svefaktura_2_0' => array(
				// 	'name'  => 'Svefaktura 2.0' . ' (' . __( 'Sweden', 'woocommerce-pdf-invoices-packing-slips' ) . ')',
				// 	'class' => '',
				// ),
				// 'eSENS' => array(
				// 	'name'  => 'eSENS' . ' (' . __( 'EU', 'woocommerce-pdf-invoices-packing-slips' ) . ')',
				// 	'class' => '',
				// ),
			),
			'cii' => array(
				'cii-d16b' => array(
					'name'  => 'CII D16B',
					'class' => \WPO\IPS\EDI\Syntax\Cii\Formats\CiiD16B::class,
				),
				// 'factur-x' => array(
				// 	'name'  => 'Factur-X',
				// 	'class' => '',
				// ),
			),
		)
	);

	return isset( $formats[ $syntax ] ) ? $formats[ $syntax ] : $formats;
}
