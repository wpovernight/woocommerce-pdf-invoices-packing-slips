<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class LineHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$root_element         = $this->document->get_root_element();
		$quantity_role		  = $this->document->get_quantity_role();
		$include_coupon_lines = apply_filters( 'wpo_ips_edi_ubl_discount_as_line', false, $this );
		$items                = $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) );
		$currency             = $this->document->order->get_currency();

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			// Resolve tax meta for this line
			$meta = $this->resolve_item_tax_meta( $item );

			$tax_category = array(
				array(
					'name'  => 'cbc:ID',
					'value' => $meta['category'],
				),
				array(
					'name'  => 'cbc:Percent',
					'value' => $this->format_decimal( $meta['percentage'], 1 ),
				),
				array(
					'name'  => 'cac:TaxScheme',
					'value' => array(
						array(
							'name'  => 'cbc:ID',
							'value' => $meta['scheme'],
						),
					),
				),
			);

			// Price parts
			$parts = $this->compute_item_price_parts( $item, (bool) $include_coupon_lines );

			$gross_unit    = $this->format_decimal( $parts['gross_unit'], 2 );
			$net_unit      = $this->format_decimal( $parts['net_unit'],   2 );
			$unit_discount = max( 0.0, $this->format_decimal( $parts['gross_unit'] - $parts['net_unit'], 2 ) );

			$price_value = array(
				array(
					'name'       => 'cbc:PriceAmount',
					'value'      => $this->format_decimal( abs( $parts['net_unit'] ) ),
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
				array(
					'name'       => 'cbc:BaseQuantity',
					'value'      => 1,
					'attributes' => array(
						'unitCode' => 'C62',
					),
				),
			);

			// Only show AllowanceCharge when there is a discount at price level
			if ( $unit_discount > 0 ) {
				$price_value[] = array(
					'name'  => 'cac:AllowanceCharge',
					'value' => array(
						array(
							'name'  => 'cbc:ChargeIndicator',
							'value' => 'false',
						),
						array(
							'name'       => 'cbc:Amount',
							'value'      => $this->format_decimal( $unit_discount ),
							'attributes' => array(
								'currencyID' => $currency,
							),
						),
						array(
							'name'       => 'cbc:BaseAmount',
							'value'      => $gross_unit,
							'attributes' => array(
								'currencyID' => $currency,
							),
						),
					),
				);
			}

			// Build base Item node
			$item_value = array(
				array(
					'name'  => 'cbc:Name',
					'value' => wpo_ips_edi_sanitize_string( $item->get_name() ),
				),
				array(
					'name'  => 'cac:ClassifiedTaxCategory',
					'value' => $tax_category,
				),
			);

			// Optionally append AdditionalItemProperty from meta
			if ( wpo_ips_edi_include_item_meta() ) {
				$meta_rows = $this->get_item_meta( $item );

				if ( ! empty( $meta_rows ) ) {
					foreach ( $meta_rows as $row ) {
						$item_value[] = array(
							'name'  => 'cac:AdditionalItemProperty',
							'value' => array(
								array(
									'name'  => 'cbc:Name',
									'value' => $row['name'],
								),
								array(
									'name'  => 'cbc:Value',
									'value' => $row['value'],
								),
							),
						);
					}
				}
			}
			
			$quantity_value = $parts['qty'];

			// For credit notes: quantity must carry the sign, price stays positive
			if ( 'Credited' === $quantity_role && $parts['net_total'] < 0 ) {
				$quantity_value = -abs( $quantity_value );
			}

			$line = array(
				'name'  => "cac:{$root_element}Line",
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => $item_id,
					),
					array(
						'name'       => "cbc:{$quantity_role}Quantity",
						'value'      => $quantity_value,
						'attributes' => array(
							'unitCode' => 'C62', // https://docs.peppol.eu/pracc/catalogue/1.0/codelist/UNECERec20/
						),
					),
					array(
						'name'       => 'cbc:LineExtensionAmount',
						'value'      => $this->format_decimal( $parts['net_total'] ),
						'attributes' => array(
							'currencyID' => $currency,
						),
					),
					array(
						'name'  => 'cac:Item',
						'value' => $item_value,
					),
					array(
						'name'  => 'cac:Price',
						'value' => $price_value,
					),
				),
			);

			$data[] = apply_filters( 'wpo_ips_edi_ubl_line', $line, $data, $options, $item, $this );
		}
		
		// Append coupon lines as negative lines
		if ( $include_coupon_lines ) {
			$coupons = $this->document->order->get_items( 'coupon' );
			
			if ( empty( $coupons ) ) {
				return $data;
			}

			foreach ( $coupons as $order_item_id => $coupon_item ) {
				$_line = $this->build_coupon_line( $coupon_item, $order_item_id, $currency );
				if ( $_line ) {
					$data[] = $_line;
				}
			}
		}

		return $data;
	}
	
	/**
	 * Create the Line array for a single coupon item.
	 *
	 * @param \WC_Order_Item_Coupon $coupon_item
	 * @param int                   $fallback_id
	 * @param string                $currency
	 * @return array|null
	 */
	protected function build_coupon_line( \WC_Order_Item_Coupon $coupon_item, int $fallback_id, string $currency ): ?array {
		if ( ! is_object( $coupon_item ) || ! method_exists( $coupon_item, 'get_discount' ) ) {
			return null;
		}

		$code              = method_exists( $coupon_item, 'get_code' ) ? $coupon_item->get_code() : '';
		$discount_excl_tax = (float) $coupon_item->get_discount();
		$net_total         = -1 * $this->format_decimal( $discount_excl_tax, 2 );

		if ( 0.0 === $net_total ) {
			return null;
		}

		$coupon_post_id = ( function_exists( 'wc_get_coupon_id_by_code' ) && $code )
			? (int) wc_get_coupon_id_by_code( $code )
			: 0;

		$label_template = apply_filters(
			'wpo_ips_edi_ubl_coupon_line_label',
			/* Translators: %s is the coupon code applied to the discount. */
			__( 'Discount %s', 'woocommerce-pdf-invoices-packing-slips' ),
			$this
		);

		$line_label = apply_filters(
			'wpo_ips_edi_ubl_coupon_line_name',
			sprintf( $label_template, $code ),
			$coupon_item,
			$this
		);

		$tax_category = array(
			array(
				'name'  => 'cbc:ID',
				'value' => 'Z',
			),
			array(
				'name'  => 'cbc:Percent',
				'value' => '0.0',
			),
			array(
				'name'  => 'cac:TaxScheme',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => 'VAT',
					),
				),
			),
		);
		
		$root_element  = $this->document->get_root_element();
		$quantity_role = $this->document->get_quantity_role();

		$line = array(
			'name'  => "cac:{$root_element}Line",
			'value' => array(
				array(
					'name'  => 'cbc:ID',
					'value' => $coupon_post_id > 0 ? $coupon_post_id : $fallback_id,
				),
				array(
					'name'       => "cbc:{$quantity_role}Quantity",
					'value'      => 1,
					'attributes' => array(
						'unitCode' => 'C62',
					),
				),
				array(
					'name'       => 'cbc:LineExtensionAmount',
					'value'      => $this->format_decimal( $net_total ),
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
				array(
					'name'  => 'cac:Item',
					'value' => array(
						array(
							'name'  => 'cbc:Name',
							'value' => wpo_ips_edi_sanitize_string( $line_label ),
						),
						array(
							'name' => 'cac:ClassifiedTaxCategory',
							'value' => $tax_category,
						),
					),
				),
				array(
					'name'  => 'cac:Price',
					'value' => array(
						array(
							'name'       => 'cbc:PriceAmount',
							'value'      => $this->format_decimal( $net_total ), // unit price (negative)
							'attributes' => array(
								'currencyID' => $currency,
							),
						),
						array(
							'name'       => 'cbc:BaseQuantity',
							'value'      => 1,
							'attributes' => array(
								'unitCode' => 'C62',
							),
						),
					),
				),
			),
		);
		
		return apply_filters( 'wpo_ips_edi_ubl_coupon_line', $line, $coupon_item, $this );
	}

}
