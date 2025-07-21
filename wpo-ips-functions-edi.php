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
	$edi_tax_settings = wpo_ips_edi_get_tax_settings();

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

					$edi_tax_settings = wpo_ips_edi_get_tax_settings();
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
 * Maybe save EDI order customer Peppol data.
 *
 * @param \WC_Abstract_Order $order
 * @return void
 */
function wpo_ips_edi_maybe_save_order_customer_peppol_data( \WC_Abstract_Order $order ): void {
	if ( ! wpo_ips_edi_peppol_is_available() ) {
		return; // only save for Peppol formats
	}
	
	$user_id = $order->get_customer_id();

	if ( ! $user_id ) {
		return;
	}

	$legal_identifier     = get_user_meta( $user_id, 'peppol_legal_identifier', true );
	$legal_identifier_icd = get_user_meta( $user_id, 'peppol_legal_identifier_icd', true );

	if ( ! empty( $legal_identifier ) ) {
		$order->update_meta_data( '_peppol_legal_identifier', $legal_identifier );
	}

	if ( ! empty( $legal_identifier_icd ) ) {
		$order->update_meta_data( '_peppol_legal_identifier_icd', $legal_identifier_icd );
	}
	
	$order->save_meta_data();
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
 * Get EDI Tax settings
 *
 * @return array
 */
function wpo_ips_edi_get_tax_settings(): array {
	return get_option( 'wpo_ips_edi_tax_settings', array() );
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
 * Check if EDI Peppol is available
 *
 * @return bool
 */
function wpo_ips_edi_peppol_is_available(): bool {
	return apply_filters( 'wpo_ips_edi_peppol_is_available', wpo_ips_edi_is_available() && false !== strpos( wpo_ips_edi_get_current_format(), 'peppol' ) );
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

	$edi_document = new \WPO\IPS\EDI\Document( $syntax, $format, $document );
	$builder      = new \WPO\IPS\EDI\SabreBuilder();
	
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
 * @param bool $full_details Optional. If true, returns full format details.
 * @return string|array
 */
function wpo_ips_edi_get_current_format( bool $full_details = false ) {
	$edi_settings = wpo_ips_edi_get_settings();
	$format       = 'ubl_2_1';

	if ( ! empty( $edi_settings['syntax'] ) ) {
		$syntax     = $edi_settings['syntax'];
		$format_key = "{$syntax}_format";

		if ( ! empty( $edi_settings[ $format_key ] ) ) {
			$format = $edi_settings[ $format_key ];
			
			if ( $full_details ) {
				$format = wpo_ips_edi_syntax_formats( $syntax, $format );
			}
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
 * Get the list of syntaxes (slug ⇒ human‑readable name).
 *
 * @return array
 */
function wpo_ips_edi_syntaxes(): array {
	$syntaxes = array();

	foreach ( wpo_ips_edi_syntax_formats() as $slug => $data ) {
		$syntaxes[ $slug ] = $data['name'];
	}

	return apply_filters( 'wpo_ips_edi_syntaxes', $syntaxes );
}

/**
 * Get the complete "syntax → formats" map, or a slice of it.
 *
 * `wpo_ips_edi_syntax_formats()`                   → everything
 * `wpo_ips_edi_syntax_formats( 'ubl' )`            → only the UBL block
 * `wpo_ips_edi_syntax_formats( 'ubl', 'ubl-2p1' )` → just that format
 *
 * @param string $syntax Optional. Syntax key (e.g. 'ubl', 'cii').
 * @param string $format Optional. Format key (e.g. 'ubl‑2p1').
 *
 * @return array
 */
function wpo_ips_edi_syntax_formats( string $syntax = '', string $format = '' ): array {
	$map = apply_filters(
		'wpo_ips_edi_syntax_formats',
		array(
			'ubl' => array(
				'name'    => 'Universal Business Language (UBL)',
				'formats' => array(
					'ubl-2p1' => array(
						'name'      => 'UBL 2.1',
						'hybrid'    => false,
						'documents' => array(
							'invoice' => \WPO\IPS\EDI\Syntaxes\Ubl\Formats\Ubl2p1\Invoice::class,
						),
					),
					'peppol-bis-3p0' => array(
						'name'      => 'PEPPOL BIS 3.0',
						'hybrid'    => false,
						'documents' => array(
							'invoice' => \WPO\IPS\EDI\Syntaxes\Ubl\Formats\PeppolBis3p0\Invoice::class,
						),
					),
				),
			),
			'cii' => array(
				'name'    => 'Cross Industry Invoice (CII)',
				'formats' => array(
					'cii-d16b' => array(
						'name'      => 'CII D16B',
						'hybrid'    => false,
						'documents' => array(
							'invoice' => \WPO\IPS\EDI\Syntaxes\Cii\Formats\CiiD16B\Invoice::class,
						),
					),
					'factur-x-1p0' => array(
						'name'      => 'Factur-X 1.0',
						'hybrid'    => true,
						'documents' => array(
							'invoice' => \WPO\IPS\EDI\Syntaxes\Cii\Formats\FacturX1p0\Invoice::class,
						),
					),
					'zugferd-1p0' => array(
						'name'      => 'ZUGFeRD 1.0',
						'hybrid'    => true,
						'documents' => array(
							'invoice' => \WPO\IPS\EDI\Syntaxes\Cii\Formats\Zugferd1p0\Invoice::class,
						),
					),
					'zugferd-2p0' => array(
						'name'      => 'ZUGFeRD 2.0',
						'hybrid'    => true,
						'documents' => array(
							'invoice' => \WPO\IPS\EDI\Syntaxes\Cii\Formats\Zugferd2p0\Invoice::class,
						),
					),
				),
			),
		)
	);

	// Slicing
	if ( '' === $syntax ) {
		return $map;
	}

	if ( ! isset( $map[ $syntax ] ) ) {
		return array();
	}

	if ( '' === $format ) {
		return $map[ $syntax ];
	}

	return $map[ $syntax ]['formats'][ $format ] ?? array();
}

/**
 * Log EDI messages
 *
 * @param string $message The log message.
 * @param string $level   The log level (default: 'info').
 * @param \Throwable|null $e Optional. Exception to log.
 * @return void
 */
function wpo_ips_edi_log( string $message, string $level = 'info', ?\Throwable $e = null ): void {
	$edi_settings = wpo_ips_edi_get_settings();
	
	if ( empty( $edi_settings['enabled_logs'] ) ) {
		return;
	}
	
	wcpdf_log_error( $message, $level, $e, 'wpo-ips-edi' );
}

/**
 * Check if a VAT number starts with a valid country prefix (ISO 3166-1 alpha-2).
 *
 * @param string $vat_number The VAT number to check.
 * @return bool True if the prefix is valid, false otherwise.
 */
function wpo_ips_edi_vat_number_has_country_prefix( string $vat_number ): bool {
	$vat_number = strtoupper( trim( $vat_number ) );

	// Special handling for Greece
	if ( 'EL' === substr( $vat_number, 0, 2 ) ) {
		return true;
	}

	$valid_prefixes = array(
		'AD','AE','AF','AG','AI','AL','AM','AO','AQ','AR','AS','AT','AU','AW','AX','AZ',
		'BA','BB','BD','BE','BF','BG','BH','BI','BJ','BL','BM','BN','BO','BQ','BR','BS',
		'BT','BV','BW','BY','BZ','CA','CC','CD','CF','CG','CH','CI','CK','CL','CM','CN',
		'CO','CR','CU','CV','CW','CX','CY','CZ','DE','DJ','DK','DM','DO','DZ','EC','EE',
		'EG','EH','ES','ET','FI','FJ','FK','FM','FO','FR','GA','GB','GD','GE','GF','GG',
		'GH','GI','GL','GM','GN','GP','GQ','GR','GS','GT','GU','GW','GY','HK','HM','HN',
		'HR','HT','HU','ID','IE','IL','IM','IN','IO','IQ','IR','IS','IT','JE','JM','JO',
		'JP','KE','KG','KH','KI','KM','KN','KP','KR','KW','KY','KZ','LA','LB','LC','LI',
		'LK','LR','LS','LT','LU','LV','LY','MA','MC','MD','ME','MF','MG','MH','MK','ML',
		'MM','MN','MO','MP','MQ','MR','MS','MT','MU','MV','MW','MX','MY','MZ','NA','NC',
		'NE','NF','NG','NI','NL','NO','NP','NR','NU','NZ','OM','PA','PE','PF','PG','PH',
		'PK','PL','PM','PN','PR','PS','PT','PW','PY','QA','RE','RO','RS','RU','RW','SA',
		'SB','SC','SD','SE','SG','SH','SI','SJ','SK','SL','SM','SN','SO','SR','SS','ST',
		'SV','SX','SY','SZ','TC','TD','TF','TG','TH','TJ','TK','TL','TM','TN','TO','TR',
		'TT','TV','TW','TZ','UA','UG','UM','US','UY','UZ','VA','VC','VE','VG','VI','VN',
		'VU','WF','WS','XI','YE','YT','ZA','ZM','ZW'
	);

	$prefix = substr( $vat_number, 0, 2 );

	return in_array( $prefix, $valid_prefixes, true );
}
