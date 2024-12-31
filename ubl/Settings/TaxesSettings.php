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
		<table class="widefat">
			<thead>
				<tr>
					<th width="8%"><?php _e( 'Country&nbsp;code', 'woocommerce' ); ?></th>
					<th width="8%"><?php _e( 'State code', 'woocommerce' ); ?></th>
					<th><?php _e( 'Postcode / ZIP', 'woocommerce' ); ?></th>
					<th><?php _e( 'City', 'woocommerce' ); ?></th>
					<th width="8%"><?php _e( 'Rate&nbsp;%', 'woocommerce' ); ?></th>
					<th width="20%"><a href="https://service.unece.org/trade/untdid/d00a/tred/tred5153.htm" target="_blank"><?php _e( 'Tax Scheme', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></th>
					<th width="20%"><a href="https://docs.peppol.eu/poacc/billing/3.0/codelist/UNCL5305/" target="_blank"><?php _e( 'Tax Category', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></th>
					<th width="20%"><a href="https://docs.peppol.eu/poacc/billing/3.0/codelist/vatex/" target="_blank"><?php _e( 'Reason', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></th>
				</tr>
			</thead>
			<tbody id="rates">
				<?php
					if ( ! empty( $results ) ) {
						foreach ( $results as $result ) {
							$locationResults = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = %d;", $result->tax_rate_id ) );
							$postcode        = array();
							$city            = '';
							
							foreach ( $locationResults as $locationResult ) {
								if ( ! isset( $locationResult->location_type ) ) {
									continue;
								}
								
								switch ( $locationResult->location_type ) {
									case 'postcode':
										$postcode[] = $locationResult->location_code;
										break;
									case 'city':
										$city = $locationResult->location_code;
										break;
								}
							}
							
							$postcode = empty( $postcode ) ? '*' : implode( '; ', $postcode );
							$city     = empty( $city ) ? '*' : $city;
							$scheme   = isset( $this->settings['rate'][ $result->tax_rate_id ]['scheme'] )   ? $this->settings['rate'][ $result->tax_rate_id ]['scheme']   : '';
							$category = isset( $this->settings['rate'][ $result->tax_rate_id ]['category'] ) ? $this->settings['rate'][ $result->tax_rate_id ]['category'] : '';
							$reason   = isset( $this->settings['rate'][ $result->tax_rate_id ]['reason'] )   ? $this->settings['rate'][ $result->tax_rate_id ]['reason']   : '';

							echo '<tr>';
							echo '<td>' . $result->tax_rate_country . '</td>';
							echo '<td>' . $result->tax_rate_state . '</td>';
							echo '<td>' . $postcode . '</td>';
							echo '<td>' . $city . '</td>';
							echo '<td>' . $result->tax_rate . '</td>';
							echo '<td>' . $this->get_select_for( 'scheme', 'rate', $result->tax_rate_id, $scheme ) . '</td>';
							echo '<td>' . $this->get_select_for( 'category', 'rate', $result->tax_rate_id, $category ) . '</td>';
							echo '<td>' . $this->get_select_for( 'reason', 'rate', $result->tax_rate_id, $reason ) . '</td>';
							echo '</tr>';
						}
					} else {
						echo '<tr><td colspan="7">' . __( 'No taxes found for this class.', 'woocommerce-pdf-invoices-packing-slips' ) . '</td></tr>';
					}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="5" style="text-align: right;"><?php _e( 'Tax class default', 'woocommerce-pdf-invoices-packing-slips' ); ?>:</th>
					<?php
						$scheme   = isset( $this->settings['class'][ $slug ]['scheme'] )   ? $this->settings['class'][ $slug ]['scheme']   : '';
						$category = isset( $this->settings['class'][ $slug ]['category'] ) ? $this->settings['class'][ $slug ]['category'] : '';
						$reason   = isset( $this->settings['class'][ $slug ]['reason'] )   ? $this->settings['class'][ $slug ]['reason']   : '';
					?>
					<th><?php echo $this->get_select_for( 'scheme', 'class', $slug, $scheme ); ?></th>
					<th><?php echo $this->get_select_for( 'category', 'class', $slug, $category ); ?></th>
					<th><?php echo $this->get_select_for( 'reason', 'class', $slug, $reason ); ?></th>
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
		switch ( $for ) {
			case 'scheme':
				$options = $this->get_available_schemes();
				break;
			case 'category':
				$options = $this->get_available_categories();
				break;
			case 'reason':
				$options = self::get_available_reasons();
				break;
			default:
				$options = array();
		}

		$select = '<select name="wpo_wcpdf_settings_ubl_taxes[' . $type . '][' . $id . '][' . $for . ']"><option value="">' . __( 'Default', 'woocommerce-pdf-invoices-packing-slips' ) . '</option>';
		foreach ( $options as $key => $value ) {
			$select .= '<option ' . selected( $key, $selected, false ) . ' value="' . $key . '">' . $value . '</option>';
		}
		$select .= '</select>';
		return $select;
	}

	public function get_available_schemes(): array {
		return apply_filters( 'wpo_wcpdf_ubl_tax_schemes', array(
			'vat' => __( 'Value added tax (VAT)', 'woocommerce-pdf-invoices-packing-slips' ),
			'gst' => __( 'Goods and services tax (GST)', 'woocommerce-pdf-invoices-packing-slips' ),
			'aaa' => __( 'Petroleum tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'aab' => __( 'Provisional countervailing duty cash', 'woocommerce-pdf-invoices-packing-slips' ),
			'aac' => __( 'Provisional countervailing duty bond', 'woocommerce-pdf-invoices-packing-slips' ),
			'aad' => __( 'Tobacco tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'aae' => __( 'Energy fee', 'woocommerce-pdf-invoices-packing-slips' ),
			'aaf' => __( 'Coffee tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'aag' => __( 'Harmonised sales tax, Canadian', 'woocommerce-pdf-invoices-packing-slips' ),
			'aah' => __( 'Quebec sales tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'aai' => __( 'Canadian provincial sales tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'aaj' => __( 'Tax on replacement part', 'woocommerce-pdf-invoices-packing-slips' ),
			'aak' => __( 'Mineral oil tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'aal' => __( 'Special tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'add' => __( 'Anti-dumping duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'bol' => __( 'Stamp duty (Imposta di Bollo)', 'woocommerce-pdf-invoices-packing-slips' ),
			'cap' => __( 'Agricultural levy', 'woocommerce-pdf-invoices-packing-slips' ),
			'car' => __( 'Car tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'coc' => __( 'Paper consortium tax (Italy)', 'woocommerce-pdf-invoices-packing-slips' ),
			'cst' => __( 'Commodity specific tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'cud' => __( 'Customs duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'cvd' => __( 'Countervailing duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'env' => __( 'Environmental tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'exc' => __( 'Excise duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'exp' => __( 'Agricultural export rebate', 'woocommerce-pdf-invoices-packing-slips' ),
			'fet' => __( 'Federal excise tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'fre' => __( 'Free', 'woocommerce-pdf-invoices-packing-slips' ),
			'gnc' => __( 'General construction tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'ill' => __( 'Illuminants tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'imp' => __( 'Import tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'ind' => __( 'Individual tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'lac' => __( 'Business license fee', 'woocommerce-pdf-invoices-packing-slips' ),
			'lcn' => __( 'Local construction tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'ldp' => __( 'Light dues payable', 'woocommerce-pdf-invoices-packing-slips' ),
			'loc' => __( 'Local sales tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'lst' => __( 'Lust tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'mca' => __( 'Monetary compensatory amount', 'woocommerce-pdf-invoices-packing-slips' ),
			'mcd' => __( 'Miscellaneous cash deposit', 'woocommerce-pdf-invoices-packing-slips' ),
			'oth' => __( 'Other taxes', 'woocommerce-pdf-invoices-packing-slips' ),
			'pdb' => __( 'Provisional duty bond', 'woocommerce-pdf-invoices-packing-slips' ),
			'pdc' => __( 'Provisional duty cash', 'woocommerce-pdf-invoices-packing-slips' ),
			'prf' => __( 'Preference duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'scn' => __( 'Special construction tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'sss' => __( 'Shifted social securities', 'woocommerce-pdf-invoices-packing-slips' ),
			'stt' => __( 'State/provincial sales tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'sup' => __( 'Suspended duty', 'woocommerce-pdf-invoices-packing-slips' ),
			'sur' => __( 'Surtax', 'woocommerce-pdf-invoices-packing-slips' ),
			'swt' => __( 'Shifted wage tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'tac' => __( 'Alcohol mark tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'tot' => __( 'Total', 'woocommerce-pdf-invoices-packing-slips' ),
			'tox' => __( 'Turnover tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'tta' => __( 'Tonnage taxes', 'woocommerce-pdf-invoices-packing-slips' ),
			'vad' => __( 'Valuation deposit', 'woocommerce-pdf-invoices-packing-slips' ),
		) );
	}

	public function get_available_categories(): array {
		return apply_filters( 'wpo_wcpdf_ubl_tax_categories', array(
			'AE' => __( 'VAT Reverse Charge', 'woocommerce-pdf-invoices-packing-slips' ),
			'E'  => __( 'Exempt from Tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'S'  => __( 'Standard rate', 'woocommerce-pdf-invoices-packing-slips' ),
			'Z'  => __( 'Zero rated goods', 'woocommerce-pdf-invoices-packing-slips' ),
			'G'  => __( 'Free export item, VAT not charged', 'woocommerce-pdf-invoices-packing-slips' ),
			'O'  => __( 'Services outside scope of tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'K'  => __( 'VAT exempt for EEA intra-community supply of goods and services', 'woocommerce-pdf-invoices-packing-slips' ),
			'L'  => __( 'Canary Islands general indirect tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'M'  => __( 'Tax for production, services and importation in Ceuta and Melilla', 'woocommerce-pdf-invoices-packing-slips' ),
			'B'  => __( 'Transferred (VAT), In Italy', 'woocommerce-pdf-invoices-packing-slips' ),
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
			'VATEX-EU-151'       => __( 'Exempt based on article 151 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-AE'        => __( 'Reverse charge', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-D'         => __( 'Intra-Community acquisition from second hand means of transport', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-F'         => __( 'Intra-Community acquisition of second hand goods', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-G'         => __( 'Export outside the EU', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-I'         => __( 'Intra-Community acquisition of works of art', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-FRANCHISE' => __( 'France domestic VAT franchise in base', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CNWVAT'    => __( 'France domestic Credit Notes without VAT, due to supplier forfeit of VAT for discount', 'woocommerce-pdf-invoices-packing-slips' ),
		) );
	}

}
