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
	public ?int $order_id;
	public ?int $padding;

	/**
	 * Document number constructor.
	 *
	 * @param mixed                    $number   Raw number value or full number array.
	 * @param array                    $settings Additional settings used when building the number (e.g. prefix, suffix, padding).
	 * @param OrderDocument|null       $document Optional related document object.
	 * @param \WC_Abstract_Order|null  $order    Optional related order object.
	 */
	public function __construct( $number, array $settings = array(), ?OrderDocument $document = null, ?\WC_Abstract_Order $order = null ) {
		$number = apply_filters( 'wpo_wcpdf_raw_document_number', $number, $settings, $document, $order );

		// Normalize data from either a raw number or a full array
		$data = is_array( $number )
			? $number
			: ( ! empty( $number ) ? array_merge( array( 'number' => $number ), $settings ) : array() );

		$this->load_number_data( $data );

		if ( null !== $document ) {
			$this->document_type = $document->get_type();
		}

		if ( null !== $order ) {
			$this->order_id = $order->get_id();
		}

		if ( ! isset( $this->formatted_number ) && null !== $document ) {
			$this->apply_formatting( $document, $document->order ?? $order );
		}
	}
	
	/**
	 * Loads number data values into the object, applying casting and normalization.
	 *
	 * @param array $data Associative array of values to load into object properties.
	 *
	 * @return void
	 */
	public function load_number_data( array $data ): void {
		$numeric_properties = apply_filters(
			'wpo_wcpdf_document_number_numeric_properties',
			array( 'number', 'order_id', 'padding' ),
			$this
		);

		foreach ( $data as $key => $value ) {
			if ( in_array( $key, $numeric_properties, true ) ) {
				$value = absint( $value );

				// Only treat 0 as null for numeric keys
				if ( $value === 0 ) {
					$value = null;
				}
			}

			$this->{$key} = $value;
		}
	}

	/**
	 * Returns the formatted string representation of the object.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return $this->get_formatted();
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
		return $this->number ?? null;
	}
	
	/**
	 * Returns the document number prefix.
	 *
	 * @return string|null
	 */
	public function get_prefix(): ?string {
		return $this->prefix ?? null;
	}

	/**
	 * Returns the document number suffix.
	 *
	 * @return string|null
	 */
	public function get_suffix(): ?string {
		return $this->suffix ?? null;
	}

	/**
	 * Returns the document number padding.
	 *
	 * @return int|null
	 */
	public function get_padding(): ?int {
		return $this->padding ?? null;
	}

	/**
	 * Applies formatting to the document number based on the settings and order/document data.
	 *
	 * @param OrderDocument $document
	 * @param \WC_Abstract_Order $order
	 * @return string
	 */
	public function apply_formatting( OrderDocument $document, \WC_Abstract_Order $order ): string {
		$formatted_number = wpo_wcpdf_format_document_number( $this->get_plain(), $this->get_prefix(), $this->get_suffix(), $this->get_padding(), $document, $order );

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
