<?php
namespace WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeSettlement;

use WPO\IPS\EInvoice\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PaymentReferenceHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$order = $this->document->order;

		if ( ! $order ) {
			return $data;
		}
		
		$reference = $order->get_order_number(); // Default to WooCommerce order number
		$reference = apply_filters( 'wpo_ips_einvoice_cii_payment_reference', $reference, $order, $this );

		if ( empty( $reference ) ) {
			return $data;
		}
		
		$paymentReference = array(
			'name'  => 'ram:PaymentReference',
			'value' => wpo_ips_ubl_sanitize_string( $reference ),
		);
		
		$data[] = apply_filters( 'wpo_ips_einvoice_cii_handle_PaymentReference', $paymentReference, $data, $options, $this );

		return $data;
	}
	
}
