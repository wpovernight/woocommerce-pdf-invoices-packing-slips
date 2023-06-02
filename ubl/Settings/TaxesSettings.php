<?php

namespace WPO\WC\UBL\Settings;

defined( 'ABSPATH' ) or exit;

class TaxesSettings
{
	/** @var array */
	public $settings;

	public function __construct()
	{
		$this->settings = get_option('wpo_wcpdf_settings_ubl_taxes');
	}

	public function output()
	{
		settings_fields( 'wpo_wcpdf_settings_ubl_taxes' );

		$rates                       = \WC_Tax::get_tax_rate_classes();
		$formatted_rates             = [];
		$formatted_rates['standard'] = __( 'Standard', 'woocommerce' );
		
		foreach( $rates as $rate ) {
			if ( empty( $rate->slug ) ) {
				continue;
			}
			$formatted_rates[$rate->slug] = ! empty( $rate->name ) ? esc_attr( $rate->name ) : esc_attr( $rate->slug );
		}
		
		foreach ( $formatted_rates as $slug => $name ) {
			$this->outputTableForTaxClass( $slug, $name );
		}

		submit_button();
	}

	public function outputTableForTaxClass( $slug, $name )
	{
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
								if ( $locationResult->location_type == 'postcode' ) {
									$postcode = $locationResult->location_code;
									continue;
								}
	
								if ( $locationResult->location_type == 'city' ) {
									$city = $locationResult->location_code;
									continue;
								}
							}
	
							$scheme   = isset( $this->settings['rate'][$result->tax_rate_id]['scheme'] ) ? $this->settings['rate'][$result->tax_rate_id]['scheme'] : '';
							$category = isset( $this->settings['rate'][$result->tax_rate_id]['category'] ) ? $this->settings['rate'][$result->tax_rate_id]['category'] : '';
							
							echo '<tr>';
							echo '<td>'.$result->tax_rate_country.'</td>';
							echo '<td>'.$result->tax_rate_state.'</td>';
							echo '<td>'.$postcode.'</td>';
							echo '<td>'.$city.'</td>';
							echo '<td>'.$result->tax_rate.'</td>';
							echo '<td>'.$this->getSchemeSelect( 'rate', $result->tax_rate_id, $scheme ).'</td>';
							echo '<td>'.$this->getCategorySelect( 'rate', $result->tax_rate_id, $category ).'</td>';
							echo '</tr>';
						}	
					} else {
						echo '<tr><td colspan="7">'.__( 'No taxes found for this class.', 'woocommerce-pdf-invoices-packing-slips' ).'</td></tr>';
					}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="5" style="text-align: right;"><?php _e( 'Tax class default', 'woocommerce-pdf-invoices-packing-slips' ); ?>:</th>
					<?php
						$scheme   = isset( $this->settings['class'][$slug]['scheme'] ) ? $this->settings['class'][$slug]['scheme'] : '';
						$category = isset( $this->settings['class'][$slug]['category'] ) ? $this->settings['class'][$slug]['category'] : '';
					?>
					<th><?php echo $this->getSchemeSelect( 'class', $slug, $scheme ); ?></th>
					<th><?php echo $this->getCategorySelect( 'class', $slug, $category ); ?></th>
				</tr>
			</tfoot>
		</table>
		<?php
	}

	public function getSchemeSelect($type, $id, $selected)
	{
		$select = '<select name="ubl_wc_taxes['.$type.']['.$id.'][scheme]"><option value="">Default</option>';
		foreach ($this->getAvailableSchemes() as $key => $value ) {
			$select .= '<option '.selected($key, $selected, false).' value="'.$key.'">'.$value.'</option>';
		}
		$select .= '</select>';
		return $select;
	}

	public function getAvailableSchemes()
	{
		return [
			'vat' => 'Value added tax (VAT)',
			'gst' => 'Goods and services tax (GST)',
			'aaa' => 'Petroleum tax',
			'aab' => 'Provisional countervailing duty cash',
			'aac' => 'Provisional countervailing duty bond',
			'aad' => 'Tobacco tax',
			'aae' => 'Energy fee',
			'aaf' => 'Coffee tax',
			'aag' => 'Harmonised sales tax, Canadian',
			'aah' => 'Quebec sales tax',
			'aai' => 'Canadian provincial sales tax',
			'aaj' => 'Tax on replacement part',
			'aak' => 'Mineral oil tax',
			'aal' => 'Special tax',
			'add' => 'Anti-dumping duty',
			'bol' => 'Stamp duty (Imposta di Bollo)',
			'cap' => 'Agricultural levy',
			'car' => 'Car tax',
			'coc' => 'Paper consortium tax (Italy)',
			'cst' => 'Commodity specific tax',
			'cud' => 'Customs duty',
			'cvd' => 'Countervailing duty',
			'env' => 'Environmental tax',
			'exc' => 'Excise duty',
			'exp' => 'Agricultural export rebate',
			'fet' => 'Federal excise tax',
			'fre' => 'Free',
			'gnc' => 'General construction tax',
			'ill' => 'Illuminants tax',
			'imp' => 'Import tax',
			'ind' => 'Individual tax',
			'lac' => 'Business license fee',
			'lcn' => 'Local construction tax',
			'ldp' => 'Light dues payable',
			'loc' => 'Local sales tax',
			'lst' => 'Lust tax',
			'mca' => 'Monetary compensatory amount',
			'mcd' => 'Miscellaneous cash deposit',
			'oth' => 'Other taxes',
			'pdb' => 'Provisional duty bond',
			'pdc' => 'Provisional duty cash',
			'prf' => 'Preference duty',
			'scn' => 'Special construction tax',
			'sss' => 'Shifted social securities',
			'stt' => 'State/provincial sales tax',
			'sup' => 'Suspended duty',
			'sur' => 'Surtax',
			'swt' => 'Shifted wage tax',
			'tac' => 'Alcohol mark tax',
			'tot' => 'Total',
			'tox' => 'Turnover tax',
			'tta' => 'Tonnage taxes',
			'vad' => 'Valuation deposit',
		];
	}

	public function getCategorySelect($type, $id, $selected)
	{
		$select = '<select name="ubl_wc_taxes['.$type.']['.$id.'][category]"><option value="">Default</option>';
		foreach ($this->getAvailableCategories() as $key => $value ) {
			$select .= '<option '.selected($key, $selected, false).' value="'.$key.'">'.$value.'</option>';
		}
		$select .= '</select>';
		return $select;
	}

	public function getAvailableCategories()
	{
		return [
			's'  => 'Standard rate',
			'aa' => 'Lower rate',
			'z'  => 'Zero rated goods',
			'a'  => 'Mixed tax rate',
			'ab' => 'Exempt for resale',
			'ac' => 'Value Added Tax (VAT) not now due for payment',
			'ad' => 'Value Added Tax (VAT) due from a previous invoice',
			'b'  => 'Transferred (VAT)',
			'c'  => 'Duty paid by supplier',
			'e'  => 'Exempt from tax',
			'g'  => 'Free export item, tax not charged',
			'h'  => 'Higher rate',
			'o'  => 'Services outside scope of tax',
		];
	}
}