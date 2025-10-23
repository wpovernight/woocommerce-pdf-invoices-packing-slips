<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceLineHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$include_coupon_lines = apply_filters( 'wpo_ips_edi_ubl_discount_as_invoice_line', false, $this );
		$items                = $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) );
		$currency             = $this->document->order->get_currency();

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			$type               = $item->get_type();
			$tax_data_container = ( 'line_item' === $type ) ? 'line_tax_data' : 'taxes';
			$tax_data_key       = ( 'line_item' === $type ) ? 'subtotal'      : 'total';

			$line_tax_data      = $item[ $tax_data_container ];
			$tax_category       = array();

			$rows = ( isset( $line_tax_data[ $tax_data_key ] ) && is_array( $line_tax_data[ $tax_data_key ] ) )
				? $line_tax_data[ $tax_data_key ]
				: array();

			foreach ( $rows as $tax_id => $tax_amt ) {
				// only consider non-zero numeric rows
				if ( ! is_numeric( $tax_amt ) || (float) $tax_amt === 0.0 ) {
					continue;
				}

				$row = $this->document->order_tax_data[ $tax_id ] ?? array();

				$tax_category = array(
					array(
						'name'  => 'cbc:ID',
						'value' => strtoupper( $row['category'] ?? 'Z' ),
					),
					array(
						'name'  => 'cbc:Percent',
						'value' => $this->format_decimal( (float) ( $row['percentage'] ?? 0 ), 1 ),
					),
					array(
						'name'  => 'cac:TaxScheme',
						'value' => array(
							array(
								'name'  => 'cbc:ID',
								'value' => strtoupper( $row['scheme'] ?? 'VAT' ),
							),
						),
					),
				);

				break;
			}

			// Fallback: no non-zero tax rows -> Zero-rated (Z / 0%)
			if ( empty( $tax_category ) ) {
				$tax_category = array(
					array(
						'name'  => 'cbc:ID',
						'value' => 'Z',
					),
					array(
						'name'  => 'cbc:Percent',
						'value' => $this->format_decimal( 0, 1 ),
					),
					array(
						'name'  => 'cac:TaxScheme',
						'value' => array(
							array(
								'name' => 'cbc:ID',
								'value' => 'VAT',
							),
						),
					),
				);
			}

			// Calculate item totals
			if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
				$gross_total = (float) $item->get_subtotal();
				$net_total   = (float) ( $include_coupon_lines ? $item->get_subtotal() : $item->get_total() );
			} else {
				$gross_total = (float) $item->get_total();
				$net_total   = (float) $item->get_total();
			}
			
			$qty           = ( is_a( $item, 'WC_Order_Item_Product' ) ) ? max( 1, (int) $item->get_quantity() ) : 1;
			$gross_unit    = $qty > 0 ? $gross_total / $qty : 0.0;
			$net_unit      = $qty > 0 ? $net_total   / $qty : (float) $item->get_total();
			$gross_unit    = $this->format_decimal( $gross_unit, 2 );
			$net_unit      = $this->format_decimal( $net_unit,   2 );
			$unit_discount = max( 0.0, $this->format_decimal( $gross_unit - $net_unit, 2 ) );

			$price_value = array(
				array(
					'name'       => 'cbc:PriceAmount',
					'value'      => $this->format_decimal( $net_unit ),
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
							'value'      => $this->format_decimal( $gross_unit ),
							'attributes' => array(
								'currencyID' => $currency,
							),
						),
					),
				);
			}

			$invoice_line = array(
				'name'  => 'cac:InvoiceLine',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => $item_id,
					),
					array(
						'name'  => 'cbc:InvoicedQuantity',
						'value' => $qty,
						'attributes' => array(
							'unitCode' => 'C62', // https://docs.peppol.eu/pracc/catalogue/1.0/codelist/UNECERec20/
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
								'value' => wpo_ips_edi_sanitize_string( $item->get_name() ),
							),
							array(
								'name'  => 'cac:ClassifiedTaxCategory',
								'value' => $tax_category,
							),
						),
					),
					array(
						'name'  => 'cac:Price',
						'value' => $price_value,
					),
				),
			);

			$data[] = apply_filters( 'wpo_ips_edi_ubl_invoice_line', $invoice_line, $data, $options, $item, $this );
		}
		
		// Append coupon lines as negative invoice lines
		if ( $include_coupon_lines ) {
			$coupons = $this->document->order->get_items( 'coupon' );
			
			if ( empty( $coupons ) ) {
				return $data;
			}

			foreach ( $coupons as $order_item_id => $coupon_item ) {
				$line = $this->build_coupon_invoice_line( $coupon_item, $order_item_id, $currency );
				if ( $line ) {
					$data[] = $line;
				}
			}
		}

		return $data;
	}
	
	/**
	 * Create the InvoiceLine array for a single coupon item.
	 *
	 * @param \WC_Order_Item_Coupon $coupon_item
	 * @param int                   $fallback_id
	 * @param string                $currency
	 * @return array|null
	 */
	protected function build_coupon_invoice_line( \WC_Order_Item_Coupon $coupon_item, int $fallback_id, string $currency ): ?array {
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
				'value' => $this->format_decimal( 0, 1 ),
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

		$invoice_line = array(
			'name'  => 'cac:InvoiceLine',
			'value' => array(
				array(
					'name'  => 'cbc:ID',
					'value' => $coupon_post_id > 0 ? $coupon_post_id : $fallback_id,
				),
				array(
					'name'       => 'cbc:InvoicedQuantity',
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
		
		return apply_filters( 'wpo_ips_edi_ubl_coupon_invoice_line', $invoice_line, $coupon_item, $this );
	}

}
