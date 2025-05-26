<?php

namespace WPO\IPS\UBL\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class TaxesSettings {

	/**
	 * The standard.
	 *
	 * @var string
	 */
	public static string $standard = 'EN16931';
	
	/**
	 * The version of the standard.
	 *
	 * @var string
	 */
	public static string $standard_version = '15.0';
	
	/**
	 * Output the settings page for UBL taxes.
	 * 
	 * @return void
	 */
	public function output(): void {
		settings_fields( 'wpo_wcpdf_settings_ubl_taxes' );
		do_settings_sections( 'wpo_wcpdf_settings_ubl_taxes' );

		$rates                       = \WC_Tax::get_tax_rate_classes();
		$formatted_rates             = array();
		$formatted_rates['standard'] = __( 'Standard rate', 'woocommerce-pdf-invoices-packing-slips' );

		foreach ( $rates as $rate ) {
			if ( empty( $rate->slug ) ) {
				continue;
			}
			
			$formatted_rates[ $rate->slug ] = ! empty( $rate->name ) ? esc_attr( $rate->name ) : esc_attr( $rate->slug );
		}

		// Dropdown selector for tax classes
		echo '<select id="ubl-tax-class-select" style="margin-bottom: 1em;">';
		foreach ( $formatted_rates as $slug => $name ) {
			echo '<option value="' . esc_attr( $slug ) . '">' . esc_html( $name ) . '</option>';
		}
		echo '</select>';

		// Output all tables wrapped in containers
		foreach ( $formatted_rates as $slug => $name ) {
			echo '<div class="ubl-tax-class-table" data-tax-class="' . esc_attr( $slug ) . '" style="display:none;">';
			$this->output_table_for_tax_class( $slug );
			echo '</div>';
		}

		submit_button();
	}
	
	/**
	 * Output the table for a specific tax class.
	 *
	 * @param string $slug The slug of the tax class.
	 *
	 * @return void
	 */
	public function output_table_for_tax_class( string $slug ): void {
		global $wpdb;
		
		$tax_settings = get_option( 'wpo_wcpdf_settings_ubl_taxes', array() );
		
		$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_class = %s;",
				( $slug == 'standard' ) ? '' : $slug
			)
		);
		
		$allowed_html = array(
			'select' => array(
				'name'         => true,
				'id'           => true,
				'class'        => true,
				'style'        => true,
				'data-current' => true
			),
			'option' => array(
				'value'        => true,
				'selected'     => true,
			)
		);
		?>

		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Country code', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'State code', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'Postcode / ZIP', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'City', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'Rate', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'Scheme', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'Category', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th width="10%"><?php esc_html_e( 'Reason', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th width="15%"><?php esc_html_e( 'Remarks', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
				</tr>
			</thead>
			<tbody id="rates">
				<?php
					if ( ! empty( $results ) ) {
						foreach ( $results as $result ) {
							$locationResults = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
								$wpdb->prepare(
									"SELECT * FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = %d;",
									$result->tax_rate_id
								)
							);
							$postcode        = array();
							$city            = array();
							
							foreach ( $locationResults as $locationResult ) {
								if ( ! isset( $locationResult->location_type ) ) {
									continue;
								}
								
								switch ( $locationResult->location_type ) {
									case 'postcode':
										$postcode[] = $locationResult->location_code;
										break;
									case 'city':
										$city[] = $locationResult->location_code;
										break;
								}
							}
							
							$country          = empty( $result->tax_rate_country ) ? '*' : $result->tax_rate_country;
							$state            = empty( $result->tax_rate_state ) ? '*' : $result->tax_rate_state;
							$postcode         = empty( $postcode ) ? '*' : implode( '; ', $postcode );
							$city             = empty( $city ) ? '*' : implode( '; ', $city );
							
							$scheme           = isset( $tax_settings['rate'][ $result->tax_rate_id ]['scheme'] )   ? $tax_settings['rate'][ $result->tax_rate_id ]['scheme']   : 'default';
							$scheme_default   = isset( $tax_settings['class'][ $slug ]['scheme'] ) ? $tax_settings['class'][ $slug ]['scheme'] : 'default';
							$scheme_code      = ( 'default' === $scheme ) ? $scheme_default : $scheme;

							$category         = isset( $tax_settings['rate'][ $result->tax_rate_id ]['category'] ) ? $tax_settings['rate'][ $result->tax_rate_id ]['category'] : 'default';
							$category_default = isset( $tax_settings['class'][ $slug ]['category'] ) ? $tax_settings['class'][ $slug ]['category'] : 'default';
							$category_code    = ( 'default' === $category ) ? $category_default : $category;
							
							$reason           = isset( $tax_settings['rate'][ $result->tax_rate_id ]['reason'] )   ? $tax_settings['rate'][ $result->tax_rate_id ]['reason']   : 'default';
							$reason_default   = isset( $tax_settings['class'][ $slug ]['reason'] ) ? $tax_settings['class'][ $slug ]['reason'] : 'default';
							$reason_code      = ( 'default' === $reason ) ? $reason_default : $reason;
							
							echo '<tr>';
							echo '<td>' . esc_html( $country ) . '</td>';
							echo '<td>' . esc_html( $state ) . '</td>';
							echo '<td>' . esc_html( $postcode ) . '</td>';
							echo '<td>' . esc_html( $city ) . '</td>';
							echo '<td>' . esc_html( wc_round_tax_total( $result->tax_rate ) ) . '%</td>';
							echo '<td>';
							$select_for_scheme = $this->get_select_for( 'scheme', 'rate', $result->tax_rate_id, $scheme );
							echo wp_kses( $select_for_scheme, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $scheme_code ) . '</code></div>';
							echo '</td>';
							echo '<td>';
							$select_for_category = $this->get_select_for( 'category', 'rate', $result->tax_rate_id, $category );
							echo wp_kses( $select_for_category, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $category_code ) . '</code></div>';
							echo '</td>';
							echo '<td>';
							$select_for_reason = $this->get_select_for( 'reason', 'rate', $result->tax_rate_id, $reason );
							echo wp_kses( $select_for_reason, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $reason_code ) . '</code></div>';
							echo '</td>';
							echo '<td class="remark">';
							
							foreach ( self::get_available_remarks() as $field => $remarks ) {
								foreach ( array( 'scheme', 'category', 'reason' ) as $f ) {
									if ( isset( $remarks[ ${$f} ] ) ) {
										echo '<p><code>' . esc_html( ${$f} ) . '</code>: ' . esc_html( $remarks[ ${$f} ] ) . '</p>';
									}
								}
							}
							
							echo '</td>';
							echo '</tr>';
						}
					} else {
						echo '<tr><td colspan="9">' . esc_html__( 'No taxes found for this class.', 'woocommerce-pdf-invoices-packing-slips' ) . '</td></tr>';
					}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="5" style="text-align: right;"><?php esc_html_e( 'Tax class default', 'woocommerce-pdf-invoices-packing-slips' ); ?>:</th>
					<?php
						$scheme   = isset( $tax_settings['class'][ $slug ]['scheme'] )   ? $tax_settings['class'][ $slug ]['scheme']   : 'default';
						$category = isset( $tax_settings['class'][ $slug ]['category'] ) ? $tax_settings['class'][ $slug ]['category'] : 'default';
						$reason   = isset( $tax_settings['class'][ $slug ]['reason'] )   ? $tax_settings['class'][ $slug ]['reason']   : 'default';
					?>
					<th>
						<?php
							$select_for_scheme = $this->get_select_for( 'scheme', 'class', $slug, $scheme );
							echo wp_kses( $select_for_scheme, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $scheme ) . '</code></div>';
						?>
					</th>
					<th>
						<?php
							$select_for_category = $this->get_select_for( 'category', 'class', $slug, $category );
							echo wp_kses( $select_for_category, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $category ) . '</code></div>';
						?>
					</th>
					<th>
						<?php
							$select_for_reason = $this->get_select_for( 'reason', 'class', $slug, $reason );
							echo wp_kses( $select_for_reason, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $reason ) . '</code></div>';
						?>
					</th>
					<th class="remark">
						<?php
							foreach ( self::get_available_remarks() as $field => $remarks ) {
								foreach ( array( 'scheme', 'category', 'reason' ) as $f ) {
									if ( isset( $remarks[ ${$f} ] ) ) {
										echo '<p><code>' . esc_html( ${$f} ) . '</code>: ' . esc_html( $remarks[ ${$f} ] ) . '</p>';
									}
								}
							}
						?>
					</th>
				</tr>
			</tfoot>
		</table>
		<?php
	}

	/**
	 * Get select field for tax rate
	 *
	 * @param string $for
	 * @param string $type
	 * @param string $id
	 * @param string $selected
	 *
	 * @return string
	 */
	public function get_select_for( string $for, string $type, string $id, string $selected ): string {
		$defaults = array(
			'default' => __( 'Default', 'woocommerce-pdf-invoices-packing-slips' ),
		);
		
		switch ( $for ) {
			case 'scheme':
				$options = $this->get_available_schemes();
				break;
			case 'category':
				$options = $this->get_available_categories();
				break;
			case 'reason':
				$defaults['none'] = __( 'None', 'woocommerce-pdf-invoices-packing-slips' );
				$options          = self::get_available_reasons();
				break;
			default:
				$options = array();
		}

		$select  = '<select name="wpo_wcpdf_settings_ubl_taxes[' . $type . '][' . $id . '][' . $for . ']" data-current="' . $selected . '" style="width:100%; box-sizing:border-box;">';
		
		foreach ( $defaults as $key => $value ) {
			if ( 'class' === $type && 'default' === $key ) {
				continue;
			}
			
			$select .= '<option ' . selected( $key, $selected, false ) . ' value="' . $key . '">' . $value . '</option>';
		}
		
		foreach ( $options as $key => $value ) {
			$select .= '<option ' . selected( $key, $selected, false ) . ' value="' . $key . '">' . $value . '</option>';
		}
		
		$select .= '</select>';
		
		return $select;
	}

	/**
	 * Get available tax schemes according to standard.
	 * 
	 * @return array
	 */
	public function get_available_schemes(): array {
		return apply_filters( 'wpo_wcpdf_ubl_tax_schemes', array(
			'VAT' => __( 'Value added tax (VAT)', 'woocommerce-pdf-invoices-packing-slips' ),
		) );
	}

	/**
	 * Get available VAT tax categories according to standard 5305 code list.
	 *
	 * @return array
	 */
	public function get_available_categories(): array {
		return apply_filters( 'wpo_wcpdf_ubl_tax_categories', array(
			'AE' => __( 'VAT Reverse Charge', 'woocommerce-pdf-invoices-packing-slips' ),
			'E'  => __( 'Exempt from tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'G'  => __( 'Free export item, tax not charged', 'woocommerce-pdf-invoices-packing-slips' ),
			'K'  => __( 'VAT exempt for EEA intra-community supply of goods and services', 'woocommerce-pdf-invoices-packing-slips' ),
			'L'  => __( 'Canary Islands general indirect tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'M'  => __( 'Tax for production, services and importation in Ceuta and Melilla', 'woocommerce-pdf-invoices-packing-slips' ),
			'O'  => __( 'Services outside scope of tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'S'  => __( 'Standard rate', 'woocommerce-pdf-invoices-packing-slips' ),
			'Z'  => __( 'Zero rated goods', 'woocommerce-pdf-invoices-packing-slips' ),
		) );
	}
	
	/**
	 * Get available VAT exemption reasons according to standard VATEX code list.
	 *
	 * @return array
	 */
	public static function get_available_reasons(): array {
		return apply_filters( 'wpo_wcpdf_ubl_tax_reasons', array(
				// EU VAT exemptions
				'VATEX-EU-79-C'          => __( 'Exempt based on article 79, point c of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132'           => __( 'Exempt based on article 132 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1A'        => __( 'Exempt based on article 132, section 1 (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1B'        => __( 'Exempt based on article 132, section 1 (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1C'        => __( 'Exempt based on article 132, section 1 (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1D'        => __( 'Exempt based on article 132, section 1 (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1E'        => __( 'Exempt based on article 132, section 1 (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1F'        => __( 'Exempt based on article 132, section 1 (f) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1G'        => __( 'Exempt based on article 132, section 1 (g) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1H'        => __( 'Exempt based on article 132, section 1 (h) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1I'        => __( 'Exempt based on article 132, section 1 (i) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1J'        => __( 'Exempt based on article 132, section 1 (j) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1K'        => __( 'Exempt based on article 132, section 1 (k) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1L'        => __( 'Exempt based on article 132, section 1 (l) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1M'        => __( 'Exempt based on article 132, section 1 (m) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1N'        => __( 'Exempt based on article 132, section 1 (n) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1O'        => __( 'Exempt based on article 132, section 1 (o) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1P'        => __( 'Exempt based on article 132, section 1 (p) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-132-1Q'        => __( 'Exempt based on article 132, section 1 (q) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143'           => __( 'Exempt based on article 143 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1A'        => __( 'Exempt based on article 143, section 1 (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1B'        => __( 'Exempt based on article 143, section 1 (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1C'        => __( 'Exempt based on article 143, section 1 (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1D'        => __( 'Exempt based on article 143, section 1 (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1E'        => __( 'Exempt based on article 143, section 1 (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1F'        => __( 'Exempt based on article 143, section 1 (f) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1FA'       => __( 'Exempt based on article 143, section 1 (fa) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1G'        => __( 'Exempt based on article 143, section 1 (g) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1H'        => __( 'Exempt based on article 143, section 1 (h) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1I'        => __( 'Exempt based on article 143, section 1 (i) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1J'        => __( 'Exempt based on article 143, section 1 (j) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1K'        => __( 'Exempt based on article 143, section 1 (k) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-143-1L'        => __( 'Exempt based on article 143, section 1 (l) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-144'           => __( 'Exempt based on article 144 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-146-1E'        => __( 'Exempt based on article 146 section 1 (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-148'           => __( 'Exempt based on article 148 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-148-A'         => __( 'Exempt based on article 148, section (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-148-B'         => __( 'Exempt based on article 148, section (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-148-C'         => __( 'Exempt based on article 148, section (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-148-D'         => __( 'Exempt based on article 148, section (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-148-E'         => __( 'Exempt based on article 148, section (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-148-F'         => __( 'Exempt based on article 148, section (f) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-148-G'         => __( 'Exempt based on article 148, section (g) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-151'           => __( 'Exempt based on article 151 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-151-1A'        => __( 'Exempt based on article 151, section 1 (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-151-1AA'       => __( 'Exempt based on article 151, section 1 (aa) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-151-1B'        => __( 'Exempt based on article 151, section 1 (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-151-1C'        => __( 'Exempt based on article 151, section 1 (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-151-1D'        => __( 'Exempt based on article 151, section 1 (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-151-1E'        => __( 'Exempt based on article 151, section 1 (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-153'           => __( 'Exempt based on article 153 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-159'           => __( 'Exempt based on article 159 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-309'           => __( 'Exempt based on article 309 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-AE'            => __( 'Reverse charge', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-D'             => __( 'Travel agents VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-F'             => __( 'Second hand goods VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-G'             => __( 'Export outside the EU', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-I'             => __( 'Works of art VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-IC'            => __( 'Intra-community supply', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-J'             => __( 'Collectors items and antiques VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-EU-O'             => __( 'Not subject to VAT', 'woocommerce-pdf-invoices-packing-slips' ),

				// France specific VAT exemptions
				'VATEX-FR-AE'            => __( 'Exempt based on 2 of article 283 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261-1'      => __( 'Exempt based on 1 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261-2'      => __( 'Exempt based on 2 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261-3'      => __( 'Exempt based on 3 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261-4'      => __( 'Exempt based on 4 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261-5'      => __( 'Exempt based on 5 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261-7'      => __( 'Exempt based on 7 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261-8'      => __( 'Exempt based on 8 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261A'       => __( 'Exempt based on article 261 A of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261B'       => __( 'Exempt based on article 261 B of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261C-1'     => __( 'Exempt based on 1° of article 261 C of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261C-2'     => __( 'Exempt based on 2° of article 261 C of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261C-3'     => __( 'Exempt based on 3° of article 261 C of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261D-1'     => __( 'Exempt based on 1° of article 261 D of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261D-1BIS'  => __( 'Exempt based on 1°bis of article 261 D of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261D-2'     => __( 'Exempt based on 2° of article 261 D of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261D-3'     => __( 'Exempt based on 3° of article 261 D of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261D-4'     => __( 'Exempt based on 4° of article 261 D of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261E-1'     => __( 'Exempt based on 1° of article 261 E of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261E-2'     => __( 'Exempt based on 2° of article 261 E of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI277A'       => __( 'Exempt based on article 277 A of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI275'        => __( 'Exempt based on article 275 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI295'        => __( 'Exempt based on article 295 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CNWVAT'        => __( 'France domestic Credit Notes without VAT, due to supplier forfeit of VAT for discount', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-FRANCHISE'     => __( 'France domestic VAT franchise in base', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-298SEXDECIESA' => __( 'Exempt based on article 298 sexdecies A of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			),
		);
	}
	
	/**
	 * Get available VAT exemption remarks according to standard VATEX codes.
	 *
	 * @return array
	 */
	public static function get_available_remarks(): array {
		/* translators: %s: tax category code */
		$reason_common_remark             = __( 'Only use with tax category code %s', 'woocommerce-pdf-invoices-packing-slips' );
		$domestic_invoicing_france_remark = __( 'Only for domestic invoicing in France', 'woocommerce-pdf-invoices-packing-slips' );

		return apply_filters( 'wpo_wcpdf_ubl_tax_remarks', array(
			'scheme'   => array(),
			'category' => array(),
			'reason'   => array(
					// EU VAT exemption remarks
					'VATEX-EU-AE'            => sprintf( $reason_common_remark, '<code>AE</code>' ),
					'VATEX-EU-D'             => sprintf( $reason_common_remark, '<code>E</code>' ),
					'VATEX-EU-F'             => sprintf( $reason_common_remark, '<code>E</code>' ),
					'VATEX-EU-G'             => sprintf( $reason_common_remark, '<code>G</code>' ),
					'VATEX-EU-I'             => sprintf( $reason_common_remark, '<code>E</code>' ),
					'VATEX-EU-IC'            => sprintf( $reason_common_remark, '<code>K</code>' ),
					'VATEX-EU-J'             => sprintf( $reason_common_remark, '<code>E</code>' ),
					'VATEX-EU-O'             => sprintf( $reason_common_remark, '<code>O</code>' ),
					
					// France specific VAT exemption remarks
					'VATEX-FR-FRANCHISE'     => __( 'For domestic invoicing in France', 'woocommerce-pdf-invoices-packing-slips' ),
					'VATEX-FR-CNWVAT'        => __( 'For domestic Credit Notes only in France', 'woocommerce-pdf-invoices-packing-slips' ),
					'VATEX-FR-CGI261-1'      => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261-2'      => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261-3'      => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261-4'      => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261-5'      => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261-7'      => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261-8'      => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261A'       => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261B'       => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261C-1'     => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261C-2'     => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261C-3'     => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261D-1'     => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261D-1BIS'  => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261D-2'     => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261D-3'     => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261D-4'     => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261E-1'     => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI261E-2'     => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI277A'       => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI275'        => $domestic_invoicing_france_remark,
					'VATEX-FR-298SEXDECIESA' => $domestic_invoicing_france_remark,
					'VATEX-FR-CGI295'        => $domestic_invoicing_france_remark,
					'VATEX-FR-AE'            => $domestic_invoicing_france_remark,
				),
			),
		);
	}
	
	/**
	 * Show notice about the standard update.
	 *
	 * @return void
	 */
	public static function standard_update_notice(): void {
		$tax_settings     = get_option( 'wpo_wcpdf_settings_ubl_taxes', array() );
		$current_standard = $tax_settings['standard'] ?? null;
		$current_version  = $tax_settings['standard_version'] ?? null;
		$request          = stripslashes_deep( $_GET );

		// Handle dismissal
		if (
			isset( $request['dismiss_standard_notice'], $request['dismiss_standard_notice_nonce'] ) &&
			wp_verify_nonce( $request['dismiss_standard_notice_nonce'], 'dismiss_standard_notice' )
		) {
			self::update_standard_version();

			wp_safe_redirect( remove_query_arg( array( 'dismiss_standard_notice', 'dismiss_standard_notice_nonce' ) ) );
			exit;
		}

		// Only show notice if standard name or version is missing or outdated
		if (
			$current_standard === self::$standard &&
			version_compare( $current_version, self::$standard_version, '>=' )
		) {
			return;
		}

		// Output notice
		$dismiss_url = wp_nonce_url(
			add_query_arg( 'dismiss_standard_notice', '1' ),
			'dismiss_standard_notice',
			'dismiss_standard_notice_nonce'
		);

		echo '<div class="notice notice-info is-dismissible">';
		echo '<p>' . wp_kses_post( sprintf(
			/* translators: %1$s: plugin name, %2$s: standard name, %3$s: version number, %4$s: changelog link */
			__( 'The %1$s UBL tax settings were updated to %2$s version %3$s. %4$s', 'woocommerce-pdf-invoices-packing-slips' ),
			'<strong>PDF Invoices & Packing Slips for WooCommerce</strong>',
			'<strong>' . esc_html( self::$standard ) . '</strong>',
			'<code>' . esc_html( self::$standard_version ) . '</code>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=ubl' ) ) . '" id="ubl-toggle-changelog">' . esc_html__( 'View changelog', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>'
		) ) . '</p>';

		echo '<p><a href="' . esc_url( $dismiss_url ) . '" class="button">' . esc_html__( 'Dismiss', 'woocommerce-pdf-invoices-packing-slips' ) . '</a></p>';
		echo '</div>';
	}
	
	/**
	 * Check if the standard name and version are set and update them if missing or outdated.
	 *
	 * @return void
	 */
	public static function update_standard_version(): void {
		$tax_settings = get_option( 'wpo_wcpdf_settings_ubl_taxes', array() );

		if (
			! isset( $tax_settings['standard'] ) ||
			$tax_settings['standard'] !== self::$standard ||
			! isset( $tax_settings['standard_version'] ) ||
			version_compare( $tax_settings['standard_version'], self::$standard_version, '<' )
		) {
			$tax_settings['standard']          = self::$standard;
			$tax_settings['standard_version']  = self::$standard_version;
			update_option( 'wpo_wcpdf_settings_ubl_taxes', $tax_settings );
		}
	}
	
	/**
	 * Get changes from EN16931 version 15.0.
	 *
	 * @return array
	 */	
	public static function get_changes_from_EN16931_15_0(): array {
		return array(
			'Deprecated all tax schemes except VAT, which is the only one allowed by EN16931 v15.',
			'Deprecated tax category codes: A, AA, AB, AC, AD, B, C, D, F, H, I, J.',
			'Added VAT exemption reason codes: VATEX-EU-144, VATEX-EU-146-1E, VATEX-EU-151, VATEX-EU-153, VATEX-EU-159, VATEX-FR-CGI261-1, VATEX-FR-CGI261-2, VATEX-FR-CGI261-3, VATEX-FR-CGI261-4, VATEX-FR-CGI261-5, VATEX-FR-CGI261-7, VATEX-FR-CGI261-8, VATEX-FR-CGI261A, VATEX-FR-CGI261B, VATEX-FR-CGI261C-1, VATEX-FR-CGI261C-2, VATEX-FR-CGI261C-3, VATEX-FR-CGI261D-1, VATEX-FR-CGI261D-1BIS, VATEX-FR-CGI261D-2, VATEX-FR-CGI261D-3, VATEX-FR-CGI261D-4, VATEX-FR-CGI261E-1, VATEX-FR-CGI261E-2, VATEX-FR-CGI277A, VATEX-FR-CGI275, VATEX-FR-298SEXDECIESA, VATEX-FR-CGI295, VATEX-FR-AE.',
		);
	}

}
