<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IssueDateHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$date_instance    = $this->document->order_document->get_date();
		$issue_date_value = '';

		if ( ! empty( $date_instance ) ) {
			$issue_date_value = $date_instance->date_i18n( 'Y-m-d' );
		} else {
			$formatted_date = $this->get_formatted_date_fallback();

			if ( ! empty( $formatted_date ) && preg_match( '/^\d{4}-\d{2}-\d{2}/', $formatted_date, $matches ) ) {
				$issue_date_value = $matches[0];
			}
		}

		$issue_date = array(
			'name'  => 'cbc:IssueDate',
			'value' => $issue_date_value,
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_issue_date', $issue_date, $data, $options, $this );

		return $data;
	}

	/**
	 * Get the formatted date fallback from order meta.
	 *
	 * @return string
	 */
	protected function get_formatted_date_fallback(): string {
		$order_document = $this->document->order_document ?? null;

		if ( empty( $order_document->order ) || ! is_callable( array( $order_document->order, 'get_meta' ) ) ) {
			return '';
		}

		$document_slug = $order_document->slug ?? '';

		if ( empty( $document_slug ) ) {
			return '';
		}

		return (string) $order_document->order->get_meta( "_wcpdf_{$document_slug}_date_formatted" );
	}

}
