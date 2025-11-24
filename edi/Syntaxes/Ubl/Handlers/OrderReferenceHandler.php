<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class OrderReferenceHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$order = \wpo_ips_edi_get_parent_order( $this->document->order );

		$sales_order_reference = (string) $order->get_id();

		// UBL requires cac:OrderReference/cbc:ID to be present.
		// Since we do not know the PO number by default, we use "NA".
		$purchase_order_reference = apply_filters( 'wpo_ips_edi_ubl_order_reference_po_number', 'NA', $order, $this );

		$order_reference = array(
			'name'  => 'cac:OrderReference',
			'value' => array(
				array(
					'name'  => 'cbc:ID',
					'value' => $purchase_order_reference,
				),
				array(
					'name'  => 'cbc:SalesOrderID',
					'value' => $sales_order_reference,
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_order_reference', $order_reference, $data, $options, $this );

		return $data;
	}

}
