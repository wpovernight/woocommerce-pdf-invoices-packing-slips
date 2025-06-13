<?php
namespace WPO\IPS\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Documents\\DocumentNumber' ) ) :

class DocumentNumber {

	public ?int $number;
	public string $formatted_number;
	public ?string $prefix;
	public ?string $suffix;
	public string $document_type;
	public int $order_id;
	public ?int $padding;

	public function __construct( $number, array $settings = array(), ?\WPO\IPS\Documents\OrderDocument $document = null, ?\WC_Abstract_Order $order = null ) {
		$number = apply_filters( 'wpo_wcpdf_raw_document_number', $number, $settings, $document, $order );
		
		if ( ! is_array( $number ) && ! empty( $number ) ) {
			// we're creating a new number with settings as passed
			$this->number = $number;

			foreach ( $settings as $key => $value ) {
				if ( in_array( $key, array( 'number', 'order_id', 'padding' ) ) ) {
					$value = absint( $value );
				}
				$this->{$key} = $value;
			}

		} elseif ( is_array( $number ) ) {
			// loaded with full number data
			foreach ( $number as $key => $value ) {
				if ( in_array( $key, array( 'number', 'order_id', 'padding' ) ) ) {
					$value = absint( $value );
				}
				$this->{$key} = $value;
			}
		}

		if ( ! empty( $document ) ) {
			$this->document_type = $document->get_type();
		}
		
		if ( ! empty( $order ) ) {
			$this->order_id = $order->get_id();
		}
		
		if ( ! isset( $this->formatted_number ) && ! empty( $document ) ) {
			$this->apply_formatting( $document, ( ! empty( $document->order ) ? $document->order : $order ) );
		}
	}

	/**
	 * Returns the formatted string representation of the object.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return (string) $this->get_formatted();
	}

	/**
	 * Returns the formatted document number.
	 *
	 * @return string
	 */
	public function get_formatted(): string {
		$formatted_number = isset( $this->formatted_number ) ? $this->formatted_number : '';
		return apply_filters( 'wpo_wcpdf_formatted_document_number', $formatted_number, $this, $this->document_type, $this->order_id );
	}

	/**
	 * Returns the plain document number.
	 *
	 * @return int|null
	 */
	public function get_plain(): ?int {
		return $this->number;
	}
	
	/**
	 * Returns the document number prefix.
	 *
	 * @return string|null
	 */
	public function get_prefix(): ?string {
		return $this->prefix;
	}

	/**
	 * Returns the document number suffix.
	 *
	 * @return string|null
	 */
	public function get_suffix(): ?string {
		return $this->suffix;
	}
	
	/**
	 * Returns the document number padding.
	 *
	 * @return int|null
	 */
	public function get_padding(): ?int {
		return $this->padding;
	}

	/**
	 * Applies formatting to the document number based on the settings and order/document data.
	 *
	 * @param \WPO\IPS\Documents\OrderDocument $document
	 * @param \WC_Abstract_Order $order
	 * @return string
	 */
	public function apply_formatting( \WPO\IPS\Documents\OrderDocument $document, \WC_Abstract_Order $order ): string {
		if ( empty( $document ) || empty( $order ) ) {
			$this->formatted_number = $this->number;
			return $this->formatted_number;
		}
		
		$formatted_number = wpo_wcpdf_format_document_number( $this->number, $this->prefix, $this->suffix, $this->padding, $document, $order );
		
		// Apply filters and store
		$this->formatted_number = apply_filters(
			'wpo_wcpdf_format_document_number',
			$formatted_number,
			$this,
			$document,
			$order
		);

		return $this->formatted_number;
	}

	/**
	 * Returns the document number as an array.
	 *
	 * @return array
	 */
	public function to_array(): array {
		return (array) $this;
	}
}

endif; // class_exists
