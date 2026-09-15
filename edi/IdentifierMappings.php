<?php
namespace WPO\IPS\EDI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\EDI\\IdentifierMappings' ) ) :

class IdentifierMappings {

	/**
	 * Get identifier mappings by country.
	 *
	 * @param string $country Optional country code in ISO 3166-1 alpha-2 format.
	 * @param string $type    Optional identifier type, e.g. 'vat_number' or 'registration_number'.
	 * @param string $key     Optional mapping key, e.g. 'eas', 'icd' or 'label'.
	 * @return array|string
	 */
	public static function get( string $country = '', string $type = '', string $key = '' ): array|string {
		$mappings = array(
			'AT' => array(
				'name'     => 'Austria',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9914',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 11,
						),
					),
					'registration_number' => array(),
				),
			),
			'BE' => array(
				'name'     => 'Belgium',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '0208',
							'strip_prefixes' => array( 'BE' ),
							'keep_pattern'   => '/\d+/',
							'length'         => 10,
						),
						array(
							'eas'            => '9925',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 12,
						),
					),
					'registration_number' => array(
						array(
							'icd'   => '0208',
							'label' => __( 'Enterprise number', 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				),
			),
			'BG' => array(
				'name'     => 'Bulgaria',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9926',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => array( 11, 12 ),
						),
					),
					'registration_number' => array(),
				),
			),
			'CY' => array(
				'name'     => 'Cyprus',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9928',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 11,
						),
					),
					'registration_number' => array(),
				),
			),
			'CZ' => array(
				'name'     => 'Czech Republic',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9929',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => array( 10, 11, 12 ),
						),
					),
					'registration_number' => array(),
				),
			),
			'DE' => array(
				'name'     => 'Germany',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9930',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 11,
						),
					),
					'registration_number' => array(),
				),
			),
			'DK' => array(
				'name'     => 'Denmark',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '0184',
							'strip_prefixes' => array( 'DK' ),
							'keep_pattern'   => '/\d+/',
							'length'         => 8,
						),
					),
					'registration_number' => array(),
				),
			),
			'EE' => array(
				'name'     => 'Estonia',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9931',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 11,
						),
					),
					'registration_number' => array(
						array(
							'icd'   => '0191',
							'label' => __( 'Company Code', 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				),
			),
			'ES' => array(
				'name'     => 'Spain',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9920',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 11,
						),
					),
					'registration_number' => array(),
				),
			),
			'FI' => array(
				'name'     => 'Finland',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '0213',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 10,
						),
					),
					'registration_number' => array(
						array(
							'icd'   => '0212',
							'label' => __( 'Organization Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				),
			),
			'FR' => array(
				'name'     => 'France',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '0002',
							'strip_prefixes' => array( 'FR' ),
							'keep_pattern'   => '/\d{9}$/',
							'length'         => 9,
						),
						array(
							'eas'            => '9957',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 13,
						),
					),
					'registration_number' => array(
						array(
							'icd'   => '0002',
							'label' => __( 'SIREN', 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				),
			),
			'GB' => array(
				'name'     => 'United Kingdom',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9932',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => array( 11, 14 ),
						),
					),
					'registration_number' => array(),
				),
			),
			'GR' => array(
				'name'     => 'Greece',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9933',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 11,
						),
					),
					'registration_number' => array(),
				),
			),
			'HR' => array(
				'name'     => 'Croatia',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9934',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 13,
						),
					),
					'registration_number' => array(),
				),
			),
			'HU' => array(
				'name'     => 'Hungary',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9910',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 10,
						),
					),
					'registration_number' => array(),
				),
			),
			'IE' => array(
				'name'     => 'Ireland',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9935',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => array( 10, 11 ),
						),
					),
					'registration_number' => array(),
				),
			),
			'IT' => array(
				'name'     => 'Italy',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '0211',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 13,
						),
					),
					'registration_number' => array(
						array(
							'icd'   => '0210',
							'label' => __( 'Codice Fiscale', 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				),
			),
			'LT' => array(
				'name'     => 'Lithuania',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9937',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => array( 11, 14 ),
						),
					),
					'registration_number' => array(
						array(
							'icd'   => '0200',
							'label' => __( 'Legal entity code', 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				),
			),
			'LU' => array(
				'name'     => 'Luxembourg',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9938',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 10,
						),
					),
					'registration_number' => array(),
				),
			),
			'LV' => array(
				'name'     => 'Latvia',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9939',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 13,
						),
					),
					'registration_number' => array(
						array(
							'icd'   => '0218',
							'label' => __( 'Unified registration number', 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				),
			),
			'MT' => array(
				'name'     => 'Malta',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9943',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 10,
						),
					),
					'registration_number' => array(),
				),
			),
			'NL' => array(
				'name'     => 'Netherlands',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9944',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 14,
						),
					),
					'registration_number' => array(
						array(
							'icd'   => '0106',
							'label' => __( 'KVK number', 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				),
			),
			'NO' => array(
				'name'     => 'Norway',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '0192',
							'strip_prefixes' => array( 'NO' ),
							'keep_pattern'   => '/\d+/',
							'length'         => 9,
						),
					),
					'registration_number' => array(
						array(
							'icd'   => '0192',
							'label' => __( 'Organisasjonsnummer', 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				),
			),
			'PL' => array(
				'name'     => 'Poland',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9945',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 12,
						),
					),
					'registration_number' => array(),
				),
			),
			'PT' => array(
				'name'     => 'Portugal',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9946',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 11,
						),
					),
					'registration_number' => array(),
				),
			),
			'RO' => array(
				'name'     => 'Romania',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9947',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => range( 4, 12 ),
						),
					),
					'registration_number' => array(),
				),
			),
			'SE' => array(
				'name'     => 'Sweden',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '0007',
							'strip_prefixes' => array( 'SE' ),
							'keep_pattern'   => '/^\d{10}/',
							'length'         => 10,
						),
					),
					'registration_number' => array(
						array(
							'icd'   => '0007',
							'label' => __( 'Organisationsnummer', 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				),
			),
			'SI' => array(
				'name'     => 'Slovenia',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9949',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 10,
						),
					),
					'registration_number' => array(),
				),
			),
			'SK' => array(
				'name'     => 'Slovakia',
				'mappings' => array(
					'vat_number' => array(
						array(
							'eas'            => '9950',
							'strip_prefixes' => array(),
							'keep_pattern'   => '/[A-Z0-9]+/',
							'length'         => 12,
						),
					),
					'registration_number' => array(),
				),
			),
		);

		// Convert mappings to the legacy VAT-only format before running the deprecated filter.
		if ( has_filter( 'wpo_ips_edi_peppol_vat_mappings' ) ) {
			$legacy_mappings = array();
			foreach ( $mappings as $country_code => $country_data ) {
				$legacy_mappings[ $country_code ] = array(
					'name'     => $country_data['name'],
					'mappings' => $country_data['mappings']['vat_number'],
				);
			}

			$legacy_mappings = (array) apply_filters_deprecated(
				'wpo_ips_edi_peppol_vat_mappings',
				array( $legacy_mappings ),
				'6.0.0',
				'wpo_ips_edi_identifier_mappings'
			);

			// A removed country must no longer provide VAT endpoint candidates.
			foreach ( $mappings as $country_code => &$country_data ) {
				if ( ! isset( $legacy_mappings[ $country_code ] ) ) {
					$country_data['mappings']['vat_number'] = array();
				}
			}
			unset( $country_data );

			foreach ( $legacy_mappings as $country_code => $country_data ) {
				if ( ! is_array( $country_data ) ) {
					continue;
				}
				$mappings[ $country_code ]['name'] = $country_data['name'] ?? $country_code;
				$mappings[ $country_code ]['mappings']['vat_number'] = isset( $country_data['mappings'] ) && is_array( $country_data['mappings'] )
					? $country_data['mappings']
					: array( $country_data );
			}
		}

		$mappings = (array) apply_filters(
			'wpo_ips_edi_identifier_mappings',
			$mappings,
			$country,
			$type,
			$key
		);

		if ( empty( $country ) && empty( $type ) && empty( $key ) ) {
			return $mappings;
		}

		$country_mapping = ! empty( $country )
			? $mappings[ strtoupper( trim( $country ) ) ] ?? array()
			: array();

		if ( empty( $type ) ) {
			return $country_mapping;
		}

		$identifier_mappings = $country_mapping['mappings'][ $type ] ?? array();

		if ( empty( $key ) ) {
			return $identifier_mappings;
		}

		$value = $identifier_mappings[0][ $key ] ?? '';

		if ( empty( $value ) && 'registration_number' === $type && 'label' === $key ) {
			$value = __( 'Company registration number', 'woocommerce-pdf-invoices-packing-slips' );
		}

		return (string) $value;
	}

}

endif;
