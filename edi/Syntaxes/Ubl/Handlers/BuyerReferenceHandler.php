<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BuyerReferenceHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$buyer_reference = array(
			'name'  => 'cbc:BuyerReference',
			'value' => $this->document->order->get_id(),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_buyer_reference', $buyer_reference, $data, $options, $this );

		return $data;
	}

}
