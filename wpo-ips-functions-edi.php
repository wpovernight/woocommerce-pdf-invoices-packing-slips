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
 * Maybe save EDI order Peppol data.
 *
 * @param \WC_Abstract_Order $order
 * @param array              $data
 * @return void
 */
function wpo_ips_edi_maybe_save_order_peppol_data( \WC_Abstract_Order $order, array $data = array() ): void {
	if ( ! wpo_ips_edi_peppol_is_available() ) {
		return; // only save for Peppol formats
	}
	
	$identifier     = '';
	$scheme         = '';
	$save_meta_data = false;

	if ( ! empty( $data ) ) {
		$mode   = wpo_ips_edi_peppol_identifier_input_mode();
		$raw    = trim( sanitize_text_field( wp_unslash( $data['peppol_endpoint_id'] ?? '' ) ) );
		$scheme = $identifier = '';

		// Determine parts
		if ( 'full' === $mode && false !== strpos( $raw, ':' ) ) {
			[ $scheme, $identifier ] = array_map(
				'trim',
				explode( ':', $raw, 2 ) + array( '', '' )
			);

		// Select mode, plain identifier
		} else {
			$identifier = $raw;
		}
	}
	
	if ( empty( $identifier ) || empty( $scheme ) ) {
		$customer_id = is_callable( array( $order, 'get_customer_id' ) )
			? $order->get_customer_id()
			: 0;
			
		if ( $customer_id <= 0 ) {
			return;
		}

		$identifier = get_user_meta( $customer_id, 'peppol_endpoint_id', true );
		$scheme     = get_user_meta( $customer_id, 'peppol_endpoint_eas', true );
	}

	if ( ! empty( $identifier ) ) {
		$order->update_meta_data( '_peppol_endpoint_id', $identifier );
		$save_meta_data = true;
	}

	if ( ! empty( $scheme ) ) {
		$order->update_meta_data( '_peppol_endpoint_eas', $scheme );
		$save_meta_data = true;
	}

	if ( ! $save_meta_data ) {
		return;
	}
	
	$order->save_meta_data();
}

/**
 * Get EDI Maker
 * Use `wpo_ips_edi_maker` filter to change the EDI class (which can wrap another EDI library).
 *
 * @return WPO\IPS\Makers\EDIMaker|null
 */
function wpo_ips_edi_get_maker(): ?WPO\IPS\Makers\EDIMaker {
	$class = '\\WPO\\IPS\\Makers\\EDIMaker';

	if ( ! class_exists( $class ) ) {
		include_once WPO_WCPDF()->plugin_path() . '/includes/Makers/EDIMaker.php';
	}

	$class = apply_filters( 'wpo_ips_edi_maker', $class );

	if ( ! class_exists( $class ) ) {
		wcpdf_error_handling( 'EDI Maker class not found: ' . $class );
		return null;
	}

	return new $class();
}

/**
 * Get EDI settings
 *
 * @param string|null $key
 * @return array|string|null
 */
function wpo_ips_edi_get_settings( ?string $key = null ) {
	$settings = get_option( 'wpo_ips_edi_settings', array() );
	return $key ? ( $settings[ $key ] ?? null ) : $settings;
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
	// Check `sabre/xml` library here: https://packagist.org/packages/sabre/xml
	return apply_filters( 'wpo_ips_edi_is_available', WPO_WCPDF()->is_dependency_version_supported( 'php' ) && ! empty( wpo_ips_edi_get_settings( 'enabled' ) ) );
}

/**
 * Check if EDI Peppol is available
 *
 * @return bool
 */
function wpo_ips_edi_peppol_is_available(): bool {
	$format = wpo_ips_edi_get_current_format();
	return apply_filters( 'wpo_ips_edi_peppol_is_available', wpo_ips_edi_is_available() && ! empty( $format ) && false !== strpos( $format, 'peppol' ) );
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

	return $edi_maker->write( $filename, $contents );
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
	header( 'Content-Type: application/xml; charset=' . $charset );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Expires: 0' );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Pragma: public' );
	header( 'Content-Length: ' . $size );

	do_action( 'wpo_ips_edi_after_headers', $filename, $size );
}

/**
 * Get the current EDI syntax
 *
 * @return string|null
 */
function wpo_ips_edi_get_current_syntax(): ?string {
	$syntax = wpo_ips_edi_get_settings( 'syntax' );
	return apply_filters( 'wpo_ips_edi_current_syntax', $syntax ?: null );
}

/**
 * Get the current EDI format
 *
 * @param bool $full_details Optional. If true, returns full format details.
 * @return string|array|null
 */
function wpo_ips_edi_get_current_format( bool $full_details = false ) {
	$syntax = wpo_ips_edi_get_settings( 'syntax' );
	$format = null;

	if ( ! empty( $syntax ) ) {
		$format = wpo_ips_edi_get_settings( "{$syntax}_format" );

		if ( ! empty( $format ) && $full_details ) {
			$format = wpo_ips_edi_syntax_formats( $syntax, $format );
		}
	}

	return apply_filters( 'wpo_ips_edi_current_format', $format ?: null, $syntax, $full_details );
}

/**
 * Check if EDI attachments should be sent
 *
 * @return bool
 */
function wpo_ips_edi_send_attachments(): bool {
	return ! empty( wpo_ips_edi_get_settings( 'send_attachments' ) );
}

/**
 * Check if EDI encrypted PDF should be embedded
 *
 * @return bool
 */
function wpo_ips_edi_embed_encrypted_pdf(): bool {
	return apply_filters( 'wpo_ips_edi_embed_encrypted_pdf', ! empty( wpo_ips_edi_get_settings( 'embed_encrypted_pdf' ) ) );
}

/**
 * Check if EDI item meta should be included
 *
 * @return bool
 */
function wpo_ips_edi_include_item_meta(): bool {
	return apply_filters( 'wpo_ips_edi_include_item_meta', ! empty( wpo_ips_edi_get_settings( 'include_item_meta' ) ) );
}

/**
 * Check if EDI preview is enabled
 *
 * @return bool
 */
function wpo_ips_edi_preview_is_enabled(): bool {
	return ! empty( wpo_ips_edi_get_settings( 'enabled_preview' ) );
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
	if ( empty( wpo_ips_edi_get_settings( 'enabled_logs' ) ) ) {
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

/**
 * Get supplier identifiers data for EDI.
 *
 * @return array
 */
function wpo_ips_edi_get_supplier_identifiers_data(): array {
	$general_settings = WPO_WCPDF()->settings->general;
	$language         = wpo_ips_edi_get_settings( 'supplier_identifiers_language' );
	$data             = array();

	if ( empty( $language ) ) {
		$language = 'default';
	}

	$data[ $language ] = array(
		'name' => array(
			'label'    => __( 'Name', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $general_settings->get_setting( 'shop_name', $language ),
			'required' => true,
		),
		'address' => array(
			'label'    => __( 'Street address', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $general_settings->get_setting( 'shop_address_line_1', $language ),
			'required' => true,
		),
		'postcode' => array(
			'label'    => __( 'Postcode', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $general_settings->get_setting( 'shop_address_postcode', $language ),
			'required' => true,
		),
		'city' => array(
			'label'    => __( 'City', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $general_settings->get_setting( 'shop_address_city', $language ),
			'required' => true,
		),
		'state' => array(
			'label'    => __( 'State', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $general_settings->get_setting( 'shop_address_state', $language ),
			'required' => false,
		),
		'country' => array(
			'label'    => __( 'Country Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $general_settings->get_setting( 'shop_address_country', $language ),
			'required' => true,
		),
		'vat_number' => array(
			'label'    => __( 'VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $general_settings->get_setting( 'vat_number', $language ),
			'required' => true,
		),
		'coc_number' => array(
			'label'    => __( 'Registration number', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $general_settings->get_setting( 'coc_number', $language ),
			'required' => false,
		),
		'email' => array(
			'label'    => __( 'Email', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $general_settings->get_setting( 'shop_email_address', $language ),
			'required' => true,
		),
	);

	if ( wpo_ips_edi_peppol_is_available() ) {
		$endpoint_id             = wpo_ips_edi_get_settings( 'peppol_endpoint_id' );
		$endpoint_scheme         = wpo_ips_edi_get_settings( 'peppol_endpoint_eas' );
		$legal_identifier        = wpo_ips_edi_get_settings( 'peppol_legal_identifier' );
		$legal_identifier_scheme = wpo_ips_edi_get_settings( 'peppol_legal_identifier_icd' );

		$legal_identifier_value  = $legal_identifier && ! empty( $data[ $language ][ $legal_identifier ]['value'] )
			? $data[ $language ][ $legal_identifier ]['value']
			: '';

		if ( 'vat_number' === $legal_identifier && wpo_ips_edi_vat_number_has_country_prefix( $legal_identifier_value ) ) {
			$legal_identifier_value = substr( $legal_identifier_value, 2 );
		}

		$data[ $language ]['peppol_endpoint_id'] = array(
			'label'    => __( 'PEPPOL Endpoint ID', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => ! empty( $endpoint_scheme ) && ! empty( $endpoint_id )
				? sprintf( '%s:%s', $endpoint_scheme, $endpoint_id )
				: '',
			'required' => true,
		);

		$data[ $language ]['peppol_legal_identifier'] = array(
			'label'    => __( 'PEPPOL Legal Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => ! empty( $legal_identifier_scheme ) && ! empty( $legal_identifier ) && ! empty( $legal_identifier_value )
				? sprintf( '%s:%s', $legal_identifier_scheme, $legal_identifier_value )
				: '',
			'required' => true,
		);
	}

	return apply_filters(
		'wpo_ips_edi_supplier_identifier_data',
		$data,
		$language,
		$general_settings
	);
}

/**
 * Get order customer identifiers data for EDI.
 *
 * @param \WC_Order $order The order object.
 * @return array
 */
function wpo_ips_edi_get_order_customer_identifiers_data( \WC_Order $order ): array {
	$data = array(
		'name' => array(
			'label'    => __( 'Name', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $order->get_billing_company() ?: $order->get_formatted_billing_full_name(),
			'required' => true,
		),
		'address' => array(
			'label'    => __( 'Address', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => trim( $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() ),
			'required' => true,
		),
		'postcode' => array(
			'label'    => __( 'Postcode', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $order->get_billing_postcode(),
			'required' => true,
		),
		'city' => array(
			'label'    => __( 'City', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $order->get_billing_city(),
			'required' => true,
		),
		'state' => array(
			'label'    => __( 'State', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $order->get_billing_state(),
			'required' => false,
		),
		'country' => array(
			'label'    => __( 'Country Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $order->get_billing_country(),
			'required' => true,
		),
		'vat_number' => array(
			'label'    => __( 'VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => apply_filters( 'wpo_ips_edi_order_customer_vat_number', wpo_wcpdf_get_order_customer_vat_number( $order ), $order ),
			'required' => true,
		),
		'email' => array(
			'label'    => __( 'Email', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => $order->get_billing_email(),
			'required' => true,
		),
	);

	if ( wpo_ips_edi_peppol_is_available() ) {
		$user_id         = $order->get_customer_id();
		$endpoint_id     = $order->get_meta( '_peppol_endpoint_id' );
		$endpoint_scheme = $order->get_meta( '_peppol_endpoint_eas' );

		if ( empty( $endpoint_id ) && $user_id ) {
			$endpoint_id = get_user_meta( $user_id, 'peppol_endpoint_id', true );
		}

		if ( empty( $endpoint_scheme ) && $user_id ) {
			$endpoint_scheme = get_user_meta( $user_id, 'peppol_endpoint_eas', true );
		}

		$data['peppol_endpoint_id'] = array(
			'label'    => __( 'Endpoint ID', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'    => ! empty( $endpoint_scheme ) && ! empty( $endpoint_id )
				? sprintf( '%s:%s', $endpoint_scheme, $endpoint_id )
				: ( ! empty( $endpoint_id ) ? $endpoint_id : '' ),
			'required' => true,
		);
	}

	return apply_filters(
		'wpo_ips_edi_order_customer_identifier_data',
		$data,
		$order
	);
}

/**
 * Check if EDI Peppol customer fields are enabled for a specific location.
 *
 * @param string $location Can be 'checkout' or 'my_account'.
 * @return bool True if fields should be shown in the given location.
 */
function wpo_ips_edi_peppol_enabled_for_location( string $location ): bool {
	if ( ! wpo_ips_edi_peppol_is_available() ) {
		return false;
	}

	$location_setting = wpo_ips_edi_get_settings( 'peppol_customer_identifier_fields_location' );

	// Always return false if the field is not properly set
	if ( empty( $location_setting ) || ! in_array( $location_setting, array( 'checkout', 'my_account', 'both' ), true ) ) {
		return false;
	}

	// Return true if location matches or if both locations are enabled
	return $location === $location_setting || 'both' === $location_setting;
}

/**
 * Get the input mode for customer Peppol identifiers.
 *
 * @return string 'select' or 'full'. Defaults to 'full'.
 */
function wpo_ips_edi_peppol_identifier_input_mode(): string {
	return 'full'; // Default is full mode; may change in the future.
	// $mode = wpo_ips_edi_get_settings( 'peppol_customer_identifiers_input_mode' );
	// return 'select' === $mode ? 'select' : 'full';
}

/**
 * Save Peppol identifiers to user‑meta (only if new or different).
 *
 * - full   mode: text = scheme:identifier
 * - select mode: text = identifier; scheme from <select>
 *                (but if user typed scheme:identifier we respect that)
 *
 * @param int   $user_id User ID.
 * @param array $request $_POST / REST payload.
 */
function wpo_ips_edi_peppol_save_customer_identifiers( int $user_id, array $request ): void {
	if ( $user_id <= 0 ) {
		return;
	}
	
	$mode = wpo_ips_edi_peppol_identifier_input_mode();

	// [ text‑field , scheme‑field ]
	$pairs = array(
		array(
			'peppol_endpoint_id',
			'peppol_endpoint_eas',
		),
	);

	foreach ( $pairs as list( $id_key, $scheme_key ) ) {
		if ( ! isset( $request[ $id_key ] ) ) {
			continue;
		}

		$raw    = trim( sanitize_text_field( wp_unslash( $request[ $id_key ] ) ) );
		$scheme = $identifier = '';

		// Determine parts
		if ( 'full' === $mode && false !== strpos( $raw, ':' ) ) {
			[ $scheme, $identifier ] = array_map(
				'trim',
				explode( ':', $raw, 2 ) + array( '', '' )
			);

		// Select mode, plain identifier
		} else {
			$identifier = $raw;
		}

		// Fallback to select mode value when scheme still empty
		if ( empty( $scheme ) && isset( $request[ $scheme_key ] ) ) {
			$scheme = trim( sanitize_text_field( wp_unslash( $request[ $scheme_key ] ) ) );
		}

		// Validate scheme list
		$valid_schemes = false !== strpos( $scheme_key, '_eas' )
			? \WPO\IPS\EDI\Standards\EN16931::get_eas()
			: \WPO\IPS\EDI\Standards\EN16931::get_icd();

		if ( ! empty( $scheme ) && ! isset( $valid_schemes[ $scheme ] ) ) {
			$scheme = ''; // invalid scheme, discard
		}

		// Save identifier if changed
		if ( ! empty( $identifier ) ) {
			$existing_identifier = get_user_meta( $user_id, $id_key, true );
			if ( $existing_identifier !== $identifier ) {
				update_user_meta( $user_id, $id_key, $identifier, $existing_identifier );
			}
		}

		// Save scheme if changed
		if ( ! empty( $scheme ) ) {
			$existing_scheme = get_user_meta( $user_id, $scheme_key, true );
			if ( $existing_scheme !== $scheme ) {
				update_user_meta( $user_id, $scheme_key, $scheme, $existing_scheme );
			}
		}
	}
}

/**
 * Get the parent order for refunds.
 *
 * @return \WC_Order
 */
function wpo_ips_edi_get_parent_order( \WC_Abstract_Order $order ): \WC_Order {
	if ( is_a( $order, 'WC_Order_Refund' ) ) {
		$parent_id = $order->get_parent_id();
		if ( $parent_id ) {
			$parent_order = wc_get_order( $parent_id );
			if ( $parent_order ) {
				return $parent_order;
			}
		}
	}

	return $order;
}

/**
 * Small helper to generate action button HTML.
 *
 * @param string $url
 * @param string $class
 * @param string $label
 * @param string $icon
 *
 * @return string
 */
function wpo_ips_edi_generate_action_button_html( string $url, string $class, string $label, string $icon ): string {
	if ( empty( $url ) ) {
		return '';
	}

	return sprintf(
		'<a href="%1$s" class="%2$s" alt="%3$s" title="%3$s">
			<span class="dashicons %4$s"></span>
		</a>',
		esc_url( $url ),
		esc_attr( $class ),
		esc_attr( $label ),
		esc_attr( $icon )
	);
}
