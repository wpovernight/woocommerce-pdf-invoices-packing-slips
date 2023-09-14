<?php

namespace WPO\WC\UBL\Settings;

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
					<th width="20%"><a href="https://service.unece.org/trade/untdid/d97a/uncl/uncl5305.htm" target="_blank"><?php _e( 'Tax Category', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></th>
				</tr>
			</thead>
			<tbody id="rates">
				<?php
					if ( ! empty( $results ) ) {
						foreach ( $results as $result ) {
							$locationResults = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = %d;", $result->tax_rate_id ) );
							$postcode = $city = '';
	
							foreach ( $locationResults as $locationResult ) {
								if ( 'postcode' === $locationResult->location_type ) {
									$postcode = $locationResult->location_code;
									continue;
								}
	
								if ( 'city' === $locationResult->location_type ) {
									$city = $locationResult->location_code;
									continue;
								}
							}
	
							$scheme   = isset( $this->settings['rate'][ $result->tax_rate_id ]['scheme'] )   ? $this->settings['rate'][ $result->tax_rate_id ]['scheme']   : '';
							$category = isset( $this->settings['rate'][ $result->tax_rate_id ]['category'] ) ? $this->settings['rate'][ $result->tax_rate_id ]['category'] : '';
							
							echo '<tr>';
							echo '<td>'.$result->tax_rate_country.'</td>';
							echo '<td>'.$result->tax_rate_state.'</td>';
							echo '<td>'.$postcode.'</td>';
							echo '<td>'.$city.'</td>';
							echo '<td>'.$result->tax_rate.'</td>';
							echo '<td>'.$this->get_scheme_select( 'rate', $result->tax_rate_id, $scheme ).'</td>';
							echo '<td>'.$this->get_category_select( 'rate', $result->tax_rate_id, $category ).'</td>';
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
						$scheme   = isset( $this->settings['class'][ $slug ]['scheme'] ) ? $this->settings['class'][ $slug ]['scheme'] : '';
						$category = isset( $this->settings['class'][ $slug ]['category'] ) ? $this->settings['class'][ $slug ]['category'] : '';
					?>
					<th><?php echo $this->get_scheme_select( 'class', $slug, $scheme ); ?></th>
					<th><?php echo $this->get_category_select( 'class', $slug, $category ); ?></th>
				</tr>
			</tfoot>
		</table>
		<?php
	}

	public function get_scheme_select( $type, $id, $selected ) {
		$select = '<select name="wpo_wcpdf_settings_ubl_taxes['.$type.']['.$id.'][scheme]"><option value="">' . __( 'Default', 'woocommerce-pdf-invoices-packing-slips' ) . '</option>';
		foreach ( $this->get_available_schemes() as $key => $value ) {
			$select .= '<option '.selected( $key, $selected, false ).' value="'.$key.'">'.$value.'</option>';
		}
		$select .= '</select>';
		return $select;
	}

	public function get_available_schemes() {
		return array(
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
		);
	}

	public function get_category_select( $type, $id, $selected ) {
		$select = '<select name="wpo_wcpdf_settings_ubl_taxes['.$type.']['.$id.'][category]"><option value="">' . __( 'Default', 'woocommerce-pdf-invoices-packing-slips' ) . '</option>';
		foreach ( $this->get_available_categories() as $key => $value ) {
			$select .= '<option '.selected( $key, $selected, false ).' value="'.$key.'">'.$value.'</option>';
		}
		$select .= '</select>';
		return $select;
	}

	public function get_available_categories() {
		return array(
			's'  => __( 'Standard rate', 'woocommerce-pdf-invoices-packing-slips' ),
			'aa' => __( 'Lower rate', 'woocommerce-pdf-invoices-packing-slips' ),
			'z'  => __( 'Zero rated goods', 'woocommerce-pdf-invoices-packing-slips' ),
			'a'  => __( 'Mixed tax rate', 'woocommerce-pdf-invoices-packing-slips' ),
			'ab' => __( 'Exempt for resale', 'woocommerce-pdf-invoices-packing-slips' ),
			'ac' => __( 'Value Added Tax (VAT) not now due for payment', 'woocommerce-pdf-invoices-packing-slips' ),
			'ad' => __( 'Value Added Tax (VAT) due from a previous invoice', 'woocommerce-pdf-invoices-packing-slips' ),
			'b'  => __( 'Transferred (VAT)', 'woocommerce-pdf-invoices-packing-slips' ),
			'c'  => __( 'Duty paid by supplier', 'woocommerce-pdf-invoices-packing-slips' ),
			'e'  => __( 'Exempt from tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'g'  => __( 'Free export item, tax not charged', 'woocommerce-pdf-invoices-packing-slips' ),
			'h'  => __( 'Higher rate', 'woocommerce-pdf-invoices-packing-slips' ),
			'o'  => __( 'Services outside scope of tax', 'woocommerce-pdf-invoices-packing-slips' ),
		);
	}
}