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
			$meta       = $this->resolve_item_tax_meta( $item );
			$category   = strtoupper( (string) ( $meta['category'] ?? '' ) );
			$scheme     = (string) ( $meta['scheme'] ?? 'VAT' );
			$percentage = $meta['percentage'] ?? null;

			$tax_category = array(
				array(
					'name'  => 'cbc:ID',
					'value' => $category,
				),
			);

			// For VAT category O ("Not subject to VAT"), do NOT emit Percent.
			if ( 'O' !== $category && null !== $percentage && '' !== $percentage ) {
				$tax_category[] = array(
					'name'  => 'cbc:Percent',
					'value' => $this->format_decimal( $percentage, 1 ),
				);
			}

			$tax_category[] = array(
				'name'  => 'cac:TaxScheme',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => $scheme,
					),
				),
			);

			// Price parts
			$parts = $this->compute_item_price_parts( $item, (bool) $include_coupon_lines );

			// Round gross/net units first (numeric), then derive discount, then recompute net.
			$gross_unit_f = (float) $this->format_decimal( $parts['gross_unit'], 2 );
			$net_unit_f   = (float) $this->format_decimal( $parts['net_unit'],   2 );

			$unit_discount_f = $gross_unit_f - $net_unit_f;
			if ( $unit_discount_f < 0 ) {
				$unit_discount_f = 0.0;
			}

			$unit_discount_f = (float) $this->format_decimal( $unit_discount_f, 2 );

			// Recompute net from gross - discount to guarantee equality in XML.
			$net_unit_f = $gross_unit_f - $unit_discount_f;

			$gross_unit    = $this->format_decimal( $gross_unit_f, 2 );
			$net_unit      = $this->format_decimal( $net_unit_f,   2 );
			$unit_discount = $this->format_decimal( $unit_discount_f, 2 );

			$price_value = array(
				array(
					'name'       => 'cbc:PriceAmount',
					'value'      => abs( $net_unit ), // unit price always positive (Credit Notes as well)
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

			/**
			* NOTE ABOUT DISABLING cac:Price/cac:AllowanceCharge FOR PEPPOL
			*
			* We intentionally do not emit price-level AllowanceCharge elements for Peppol BIS invoices.
			*
			* Reason: While BT-147 (Item price discount) and BT-148 (Gross price) are optional in EN16931,
			* the Peppol validator interprets cac:Price/cac:AllowanceCharge as a line-level allowance and
			* applies BR-24 and BR-42 rules. This caused the validator to:
			*
			*   - subtract the discount again from the already-net PriceAmount, and
			*   - require a reason code (BT-139/BT-140) for each price allowance,
			*
			* resulting in validation failures and incorrect recalculated line totals.
			*
			* To avoid double-discounting and maintain correct monetary values, we emit only:
			*
			*   PriceAmount (net unit price) + LineExtensionAmount (net line amount)
			*
			* This is fully compliant with Peppol BIS Billing 3.0 since BT-147/BT-148 are optional.
			*
			* If explicit gross/discount information is needed in the future, this can be reintroduced.
			*/
			// Only show AllowanceCharge when there is a discount at price level (already reflected in net price)
			// if ( $unit_discount > 0 ) {
			// 	$price_value[] = array(
			// 		'name'  => 'cac:AllowanceCharge',
			// 		'value' => array(
			// 			array(
			// 				'name'  => 'cbc:ChargeIndicator',
			// 				'value' => 'false',
			// 			),
			// 			array(
			// 				'name'       => 'cbc:Amount',
			// 				'value'      => $unit_discount,
			// 				'attributes' => array(
			// 					'currencyID' => $currency,
			// 				),
			// 			),
			// 			array(
			// 				'name'       => 'cbc:BaseAmount',
			// 				'value'      => $gross_unit,
			// 				'attributes' => array(
			// 					'currencyID' => $currency,
			// 				),
			// 			),
			// 		),
			// 	);
			// }

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

			// Compute line net amount from the same unit price we emit in PriceAmount
			$net_line_total = $this->format_decimal( $net_unit_f * $quantity_value, 2 );

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
						'value'      => $net_line_total,
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
		$net_total         = -1.0 * (float) $this->format_decimal( $discount_excl_tax, 2 );

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

		$zero_meta = $this->get_zero_tax_meta( $this->document->order );
		$category  = strtoupper( (string) ( $zero_meta['category'] ?? 'Z' ) );
		$scheme    = (string) ( $zero_meta['scheme'] ?? 'VAT' );

		$tax_category = array(
			array(
				'name'  => 'cbc:ID',
				'value' => $category,
			),
		);

		// For coupons with category O ("Not subject to VAT"), do not emit Percent.
		if ( 'O' !== $category ) {
			$tax_category[] = array(
				'name'  => 'cbc:Percent',
				'value' => '0.0',
			);
		}

		$tax_category[] = array(
			'name'  => 'cac:TaxScheme',
			'value' => array(
				array(
					'name'  => 'cbc:ID',
					'value' => $scheme,
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
