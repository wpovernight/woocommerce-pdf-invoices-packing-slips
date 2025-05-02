<?php
namespace WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeAgreement;

use WPO\IPS\EInvoice\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ContractReferencedDocumentHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$order        = $this->document->order;
		$reference_id = apply_filters( 'wpo_ips_einvoice_cii_contract_reference_id', null, $order, $this );

		if ( empty( $reference_id ) ) {
			return $data; // Don't output anything if empty
		}

		$contractDocument = array(
			'name'  => 'ram:ContractReferencedDocument',
			'value' => array(
				array(
					'name'  => 'ram:IssuerAssignedID',
					'value' => wpo_ips_ubl_sanitize_string( $reference_id ),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_einvoice_cii_handle_ContractReferencedDocument', $contractDocument, $data, $options, $this );

		return $data;
	}
	
}
