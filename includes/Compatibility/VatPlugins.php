<?php
namespace WPO\IPS\Compatibility;

defined( 'ABSPATH' ) || exit;

class VatPlugins {

	protected static $_instance = null;

	/**
	 * Cached detection result (per request).
	 *
	 * @var array|null
	 */
	protected $detected = null;

	/**
	 * Singleton instance access.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Non-public to enforce singleton usage.
	 */
	protected function __construct() {}

	/**
	 * Detect active VAT plugin and return checkout selectors (placeholders for now).
	 *
	 * @return array{
	 *   active: bool,
	 *   key: string,
	 *   classic_form_selector: string,
	 *   block_form_selector: string,
	 *   match: mixed
	 * }
	 */
	public function detect(): array {
		if ( null !== $this->detected ) {
			return $this->detected;
		}

		$defaults = array(
			'active'                => false,
			'key'                   => '',
			'classic_form_selector' => '',
			'block_form_selector'   => '.wc-block-components-address-form__wpo-ips-checkout-field input',
			'match'                 => null,
		);

		$plugins = apply_filters(
			'wpo_ips_vat_plugin_detectors',
			array(
				'woocommerce_eu_vat_compliance' => array(
					'detector'  => static function () {
						return class_exists( 'WC_EU_VAT_Compliance' );
					},
					'selectors' => array(
						'classic_form_selector' => '',
						'block_form_selector'   => '#woocommerce_eu_vat_compliance_vat_number input',
					),
				),

				'eu_vat_for_woocommerce' => array(
					'detector'  => static function () {
						return defined( 'ALG_WC_EU_VAT_FILE' ) || class_exists( 'Alg_WC_EU_VAT' );
					},
					'selectors' => array(
						'classic_form_selector' => '',
						'block_form_selector'   => '',
					),
				),

				'aelia_eu_vat_assistant' => array(
					'detector'  => static function () {
						return class_exists( 'Aelia_WC_EU_VAT_Assistant_RequirementsChecks' )
							|| class_exists( 'WC_Aelia_EU_VAT_Assistant' )
							|| isset( $GLOBALS['wc-aelia-eu-vat-assistant'] );
					},
					'selectors' => array(
						'classic_form_selector' => '',
						'block_form_selector'   => '',
					),
				),

				'eu_vat_guard_for_woocommerce' => array(
					'detector'  => static function () {
						return defined( 'EU_VAT_GUARD_PLUGIN_FILE' )
							|| class_exists( 'Stormlabs\\EUVATGuard\\VAT_Guard' )
							|| class_exists( 'EU_VAT_Guard' );
					},
					'selectors' => array(
						'classic_form_selector' => '',
						'block_form_selector'   => '',
					),
				),
			)
		);

		if ( ! is_array( $plugins ) ) {
			return $this->detected = $defaults;
		}

		foreach ( $plugins as $key => $plugin ) {
			// Back-compat: allow old style ['key' => callable].
			if ( is_callable( $plugin ) ) {
				$plugin = array(
					'detector'  => $plugin,
					'selectors' => array(),
				);
			}

			$detector = $plugin['detector'] ?? null;
			if ( ! is_callable( $detector ) ) {
				continue;
			}

			$match = $detector();
			if ( empty( $match ) ) {
				continue;
			}

			$out = $defaults;
			$out['active'] = true;
			$out['key']    = (string) $key;
			$out['match']  = $match;

			$selectors = array();
			if ( isset( $plugin['selectors'] ) && is_array( $plugin['selectors'] ) ) {
				$selectors = $plugin['selectors'];
			}

			// If detector returns array, treat as overrides.
			if ( is_array( $match ) ) {
				$selectors = array_merge( $selectors, $match );
			}

			if ( ! empty( $selectors['classic_form_selector'] ) ) {
				$out['classic_form_selector'] = (string) $selectors['classic_form_selector'];
			}

			if ( ! empty( $selectors['block_form_selector'] ) ) {
				$out['block_form_selector'] = (string) $selectors['block_form_selector'];
			}

			return $this->detected = apply_filters( 'wpo_ips_vat_plugin_checkout_selectors', $out, $out['key'], $match );
		}

		return $this->detected = $defaults;
	}

	/**
	 * Whether an active VAT plugin was detected.
	 * 
	 * @return bool
	 */
	public function has_active(): bool {
		$info = $this->detect();
		return ! empty( $info['active'] );
	}

	/**
	 * Get the form selector for the detected VAT plugin, based on context.
	 * 
	 * @param string $context Context of the form selector, either 'block' or 'classic'.
	 * @return string
	 */
	public function get_form_selector( string $context = 'block' ): string {
		$info = $this->detect();
		return ( 'block' === $context )
			? (string) $info['block_form_selector']
			: (string) $info['classic_form_selector'];
	}
	
}
