<?php

namespace WPO\IPS\UBL\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class TaxesSettings {

	/** @var array */
	public $settings;

	public function __construct() {
		$this->settings = get_option( 'wpo_wcpdf_settings_ubl_taxes', array() );
	}

	public function output() {
		settings_fields( 'wpo_wcpdf_settings_ubl_taxes' );
		do_settings_sections( 'wpo_wcpdf_settings_ubl_taxes' );

		$rates                       = \WC_Tax::get_tax_rate_classes();
		$formatted_rates             = array();
		$formatted_rates['standard'] = __( 'Standard', 'woocommerce' );

		foreach ( $rates as $rate ) {
			if ( empty( $rate->slug ) ) {
				continue;
			}
			$formatted_rates[ $rate->slug ] = ! empty( $rate->name ) ? esc_attr( $rate->name ) : esc_attr( $rate->slug );
		}

		foreach ( $formatted_rates as $slug => $name ) {
			$this->output_table_for_tax_class( $slug, $name );
		}

		submit_button();
	}

	public function output_table_for_tax_class( $slug, $name ) {
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_class = %s;", ( $slug == 'standard' ) ? '' : $slug ) );
		?>

		<h4><?php echo $name; ?></h4>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php _e( 'Country code', 'woocommerce' ); ?></th>
					<th><?php _e( 'State code', 'woocommerce' ); ?></th>
					<th><?php _e( 'Postcode / ZIP', 'woocommerce' ); ?></th>
					<th><?php _e( 'City', 'woocommerce' ); ?></th>
					<th><?php _e( 'Rate', 'woocommerce' ); ?></th>
					<th><a href="https://service.unece.org/trade/untdid/d00a/tred/tred5153.htm" target="_blank"><?php _e( 'Tax Scheme', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></th>
					<th><a href="https://unece.org/fileadmin/DAM/trade/untdid/d16b/tred/tred5305.htm" target="_blank"><?php _e( 'Tax Category', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></th>
					<th width="10%"><a href="https://docs.peppol.eu/poacc/billing/3.0/codelist/vatex/" target="_blank"><?php _e( 'Reason', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></th>
					<th width="15%"><?php _e( 'Remarks', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
				</tr>
			</thead>
			<tbody id="rates">
				<?php
					if ( ! empty( $results ) ) {
						foreach ( $results as $result ) {
							$locationResults = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = %d;", $result->tax_rate_id ) );
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
							
							$scheme           = isset( $this->settings['rate'][ $result->tax_rate_id ]['scheme'] )   ? $this->settings['rate'][ $result->tax_rate_id ]['scheme']   : 'default';
							$scheme_default   = isset( $this->settings['class'][ $slug ]['scheme'] ) ? $this->settings['class'][ $slug ]['scheme'] : 'default';
							$scheme_code      = ( 'default' === $scheme ) ? $scheme_default : $scheme;

							$category         = isset( $this->settings['rate'][ $result->tax_rate_id ]['category'] ) ? $this->settings['rate'][ $result->tax_rate_id ]['category'] : 'default';
							$category_default = isset( $this->settings['class'][ $slug ]['category'] ) ? $this->settings['class'][ $slug ]['category'] : 'default';
							$category_code    = ( 'default' === $category ) ? $category_default : $category;
							
							$reason           = isset( $this->settings['rate'][ $result->tax_rate_id ]['reason'] )   ? $this->settings['rate'][ $result->tax_rate_id ]['reason']   : 'default';
							$reason_default   = isset( $this->settings['class'][ $slug ]['reason'] ) ? $this->settings['class'][ $slug ]['reason'] : 'default';
							$reason_code      = ( 'default' === $reason ) ? $reason_default : $reason;
							
							echo '<tr>';
							echo '<td>' . $country . '</td>';
							echo '<td>' . $state . '</td>';
							echo '<td>' . $postcode . '</td>';
							echo '<td>' . $city . '</td>';
							echo '<td>' . wc_round_tax_total( $result->tax_rate ) . '%</td>';
							echo '<td>';
							echo $this->get_select_for( 'scheme', 'rate', $result->tax_rate_id, $scheme );
							echo '<div class="current" style="margin-top:6px;">' . __( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . $scheme_code . '</code></div>';
							echo '</td>';
							echo '<td>';
							echo $this->get_select_for( 'category', 'rate', $result->tax_rate_id, $category );
							echo '<div class="current" style="margin-top:6px;">' . __( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . $category_code . '</code></div>';
							echo '</td>';
							echo '<td>';
							echo $this->get_select_for( 'reason', 'rate', $result->tax_rate_id, $reason );
							echo '<div class="current" style="margin-top:6px;">' . __( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . $reason_code . '</code></div>';
							echo '</td>';
							echo '<td class="remark">';
							
							foreach ( self::get_available_remarks() as $field => $remarks ) {
								foreach ( array( 'scheme', 'category', 'reason' ) as $f ) {
									if ( isset( $remarks[ ${$f} ] ) ) {
										echo '<p><code>' . ${$f} . '</code>: ' . $remarks[ ${$f} ] . '</p>';
									}
								}
							}
							
							echo '</td>';
							echo '</tr>';
						}
					} else {
						echo '<tr><td colspan="9">' . __( 'No taxes found for this class.', 'woocommerce-pdf-invoices-packing-slips' ) . '</td></tr>';
					}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="5" style="text-align: right;"><?php _e( 'Tax class default', 'woocommerce-pdf-invoices-packing-slips' ); ?>:</th>
					<?php
						$scheme   = isset( $this->settings['class'][ $slug ]['scheme'] )   ? $this->settings['class'][ $slug ]['scheme']   : 'default';
						$category = isset( $this->settings['class'][ $slug ]['category'] ) ? $this->settings['class'][ $slug ]['category'] : 'default';
						$reason   = isset( $this->settings['class'][ $slug ]['reason'] )   ? $this->settings['class'][ $slug ]['reason']   : 'default';
					?>
					<th>
						<?php
							echo $this->get_select_for( 'scheme', 'class', $slug, $scheme );
							echo '<div class="current" style="margin-top:6px;">' . __( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . $scheme . '</code></div>';
						?>
					</th>
					<th>
						<?php
							echo $this->get_select_for( 'category', 'class', $slug, $category );
							echo '<div class="current" style="margin-top:6px;">' . __( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . $category . '</code></div>';
						?>
					</th>
					<th>
						<?php
							echo $this->get_select_for( 'reason', 'class', $slug, $reason );
							echo '<div class="current" style="margin-top:6px;">' . __( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . $reason . '</code></div>';
						?>
					</th>
					<th class="remark">
						<?php
							foreach ( self::get_available_remarks() as $field => $remarks ) {
								foreach ( array( 'scheme', 'category', 'reason' ) as $f ) {
									if ( isset( $remarks[ ${$f} ] ) ) {
										echo '<p><code>' . ${$f} . '</code>: ' . $remarks[ ${$f} ] . '</p>';
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

	public function get_available_schemes(): array {
		return apply_filters( 'wpo_wcpdf_ubl_tax_schemes', array(
			'VAT' => __( 'Value added tax (VAT)', 'woocommerce-pdf-invoices-packing-slips' ),
			'GST' => __( 'Goods and services tax (GST)', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAA' => __( 'Petroleum tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAB' => __( 'Provisional countervailing duty cash', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAC' => __( 'Provisional countervailing duty bond', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAD' => __( 'Tobacco tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAE' => __( 'Energy fee', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAF' => __( 'Coffee tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAG' => __( 'Harmonised sales tax, Canadian', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAH' => __( 'Quebec sales tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAI' => __( 'Canadian provincial sales tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAJ' => __( 'Tax on replacement part', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAK' => __( 'Mineral oil tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'AAL' => __( 'Special tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'ADD' => __( 'Anti-dumping duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'BOL' => __( 'Stamp duty (Imposta di Bollo)', 'woocommerce-pdf-invoices-packing-slips' ),
			'CAP' => __( 'Agricultural levy', 'woocommerce-pdf-invoices-packing-slips' ),
			'CAR' => __( 'Car tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'COC' => __( 'Paper consortium tax (Italy)', 'woocommerce-pdf-invoices-packing-slips' ),
			'CST' => __( 'Commodity specific tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'CUD' => __( 'Customs duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'CVD' => __( 'Countervailing duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'ENV' => __( 'Environmental tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'EXC' => __( 'Excise duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'EXP' => __( 'Agricultural export rebate', 'woocommerce-pdf-invoices-packing-slips' ),
			'FET' => __( 'Federal excise tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'FRE' => __( 'Free', 'woocommerce-pdf-invoices-packing-slips' ),
			'GNC' => __( 'General construction tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'ILL' => __( 'Illuminants tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'IMP' => __( 'Import tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'IND' => __( 'Individual tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'LAC' => __( 'Business license fee', 'woocommerce-pdf-invoices-packing-slips' ),
			'LCN' => __( 'Local construction tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'LDP' => __( 'Light dues payable', 'woocommerce-pdf-invoices-packing-slips' ),
			'LOC' => __( 'Local sales tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'LST' => __( 'Lust tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'MCA' => __( 'Monetary compensatory amount', 'woocommerce-pdf-invoices-packing-slips' ),
			'MCD' => __( 'Miscellaneous cash deposit', 'woocommerce-pdf-invoices-packing-slips' ),
			'OTH' => __( 'Other taxes', 'woocommerce-pdf-invoices-packing-slips' ),
			'PDB' => __( 'Provisional duty bond', 'woocommerce-pdf-invoices-packing-slips' ),
			'PDC' => __( 'Provisional duty cash', 'woocommerce-pdf-invoices-packing-slips' ),
			'PRF' => __( 'Preference duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'SCN' => __( 'Special construction tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'SSS' => __( 'Shifted social securities', 'woocommerce-pdf-invoices-packing-slips' ),
			'STT' => __( 'State/provincial sales tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'SUP' => __( 'Suspended duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'SUR' => __( 'Surtax', 'woocommerce-pdf-invoices-packing-slips' ),
			'SWT' => __( 'Shifted wage tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'TAC' => __( 'Alcohol mark tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'TOT' => __( 'Total', 'woocommerce-pdf-invoices-packing-slips' ),
			'TOX' => __( 'Turnover tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'TTA' => __( 'Tonnage taxes', 'woocommerce-pdf-invoices-packing-slips' ),
			'VAD' => __( 'Valuation deposit', 'woocommerce-pdf-invoices-packing-slips' ),
		) );
	}

	public function get_available_categories(): array {
		return apply_filters( 'wpo_wcpdf_ubl_tax_categories', array(
			'A'  => __( 'Mixed tax rate', 'woocommerce-pdf-invoices-packing-slips' ),
			'AA' => __( 'Lower rate', 'woocommerce-pdf-invoices-packing-slips' ),
			'AB' => __( 'Exempt for resale', 'woocommerce-pdf-invoices-packing-slips' ),
			'AC' => __( 'Value Added Tax (VAT) not now due for payment', 'woocommerce-pdf-invoices-packing-slips' ),
			'AD' => __( 'Value Added Tax (VAT) due from a previous invoice', 'woocommerce-pdf-invoices-packing-slips' ),
			'AE' => __( 'VAT Reverse Charge', 'woocommerce-pdf-invoices-packing-slips' ),
			'B'  => __( 'Transferred (VAT)', 'woocommerce-pdf-invoices-packing-slips' ),
			'C'  => __( 'Duty paid by supplier', 'woocommerce-pdf-invoices-packing-slips' ),
			'D'  => __( 'Value Added Tax (VAT) margin scheme - travel agents', 'woocommerce-pdf-invoices-packing-slips' ),
			'E'  => __( 'Exempt from tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'F'  => __( 'Value Added Tax (VAT) margin scheme - second-hand goods', 'woocommerce-pdf-invoices-packing-slips' ),
			'G'  => __( 'Free export item, tax not charged', 'woocommerce-pdf-invoices-packing-slips' ),
			'H'  => __( 'Higher rate', 'woocommerce-pdf-invoices-packing-slips' ),
			'I'  => __( 'Value Added Tax (VAT) margin scheme - works of art', 'woocommerce-pdf-invoices-packing-slips' ),
			'J'  => __( 'Value Added Tax (VAT) margin scheme - collector\'s items and antiques', 'woocommerce-pdf-invoices-packing-slips' ),
			'K'  => __( 'VAT exempt for EEA intra-community supply of goods and services', 'woocommerce-pdf-invoices-packing-slips' ),
			'L'  => __( 'Canary Islands general indirect tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'M'  => __( 'Tax for production, services and importation in Ceuta and Melilla', 'woocommerce-pdf-invoices-packing-slips' ),
			'O'  => __( 'Services outside scope of tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'S'  => __( 'Standard rate', 'woocommerce-pdf-invoices-packing-slips' ),
			'Z'  => __( 'Zero rated goods', 'woocommerce-pdf-invoices-packing-slips' ),
		) );
	}

	public static function get_available_reasons(): array {
		return apply_filters( 'wpo_wcpdf_ubl_tax_reasons', array(
			'VATEX-EU-79-C'      => __( 'Exempt based on article 79, point c of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132'       => __( 'Exempt based on article 132 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1A'    => __( 'Exempt based on article 132, section 1 (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1B'    => __( 'Exempt based on article 132, section 1 (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1C'    => __( 'Exempt based on article 132, section 1 (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1D'    => __( 'Exempt based on article 132, section 1 (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1E'    => __( 'Exempt based on article 132, section 1 (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1F'    => __( 'Exempt based on article 132, section 1 (f) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1G'    => __( 'Exempt based on article 132, section 1 (g) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1H'    => __( 'Exempt based on article 132, section 1 (h) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1I'    => __( 'Exempt based on article 132, section 1 (i) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1J'    => __( 'Exempt based on article 132, section 1 (j) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1K'    => __( 'Exempt based on article 132, section 1 (k) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1L'    => __( 'Exempt based on article 132, section 1 (l) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1M'    => __( 'Exempt based on article 132, section 1 (m) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1N'    => __( 'Exempt based on article 132, section 1 (n) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1O'    => __( 'Exempt based on article 132, section 1 (o) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1P'    => __( 'Exempt based on article 132, section 1 (p) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1Q'    => __( 'Exempt based on article 132, section 1 (q) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143'       => __( 'Exempt based on article 143 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1A'    => __( 'Exempt based on article 143, section 1 (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1B'    => __( 'Exempt based on article 143, section 1 (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1C'    => __( 'Exempt based on article 143, section 1 (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1D'    => __( 'Exempt based on article 143, section 1 (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1E'    => __( 'Exempt based on article 143, section 1 (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1F'    => __( 'Exempt based on article 143, section 1 (f) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1FA'   => __( 'Exempt based on article 143, section 1 (fa) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1G'    => __( 'Exempt based on article 143, section 1 (g) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1H'    => __( 'Exempt based on article 143, section 1 (h) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1I'    => __( 'Exempt based on article 143, section 1 (i) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1J'    => __( 'Exempt based on article 143, section 1 (j) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1K'    => __( 'Exempt based on article 143, section 1 (k) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1L'    => __( 'Exempt based on article 143, section 1 (l) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148'       => __( 'Exempt based on article 148 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-A'     => __( 'Exempt based on article 148, section (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-B'     => __( 'Exempt based on article 148, section (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-C'     => __( 'Exempt based on article 148, section (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-D'     => __( 'Exempt based on article 148, section (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-E'     => __( 'Exempt based on article 148, section (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-F'     => __( 'Exempt based on article 148, section (f) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-G'     => __( 'Exempt based on article 148, section (g) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1A'    => __( 'Exempt based on article 151, section 1 (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1AA'   => __( 'Exempt based on article 151, section 1 (aa) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1B'    => __( 'Exempt based on article 151, section 1 (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1C'    => __( 'Exempt based on article 151, section 1 (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1D'    => __( 'Exempt based on article 151, section 1 (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1E'    => __( 'Exempt based on article 151, section 1 (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-309'       => __( 'Exempt based on article 309 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-AE'        => __( 'Reverse charge', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-D'         => __( 'Travel agents VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-F'         => __( 'Second hand goods VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-G'         => __( 'Export outside the EU', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-I'         => __( 'Works of art VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-IC'        => __( 'Intra-community supply', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-J'         => __( 'Collectors items and antiques VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-O'         => __( 'Not subject to VAT', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-FRANCHISE' => __( 'France domestic VAT franchise in base', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CNWVAT'    => __( 'France domestic Credit Notes without VAT, due to supplier forfeit of VAT for discount', 'woocommerce-pdf-invoices-packing-slips' ),
		) );
	}
	
	/**
	 * Get available remarks
	 *
	 * @return array
	 */
	public static function get_available_remarks(): array {
		/* translators: %s: tax category code */
		$reason_common_remark = __( 'Only use with tax category code %s', 'woocommerce-pdf-invoices-packing-slips' );

		return apply_filters( 'wpo_wcpdf_ubl_tax_remarks', array(
			'scheme'   => array(),
			'category' => array(),
			'reason'   => array(
				'VATEX-EU-AE' => sprintf( $reason_common_remark, '<code>AE</code>' ),
				'VATEX-EU-D'  => sprintf( $reason_common_remark, '<code>E</code>' ),
				'VATEX-EU-F'  => sprintf( $reason_common_remark, '<code>E</code>' ),
				'VATEX-EU-G'  => sprintf( $reason_common_remark, '<code>G</code>' ),
				'VATEX-EU-I'  => sprintf( $reason_common_remark, '<code>E</code>' ),
				'VATEX-EU-IC' => sprintf( $reason_common_remark, '<code>K</code>' ),
				'VATEX-EU-J'  => sprintf( $reason_common_remark, '<code>E</code>' ),
				'VATEX-EU-O'  => sprintf( $reason_common_remark, '<code>O</code>' ),
			),
		) );
	}

}
