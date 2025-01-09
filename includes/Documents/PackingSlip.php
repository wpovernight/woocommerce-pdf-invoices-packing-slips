<?php
namespace WPO\IPS\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Documents\\PackingSlip' ) ) :

/**
 * Packing Slip Document
 */

class PackingSlip extends OrderDocumentMethods {

	/**
	 * Init/load the order object.
	 *
	 * @param  int|object|WC_Order $order Order to init.
	 */
	public function __construct( $order = 0 ) {
		// set properties
		$this->type  = 'packing-slip';
		$this->title = __( 'Packing Slip', 'woocommerce-pdf-invoices-packing-slips' );
		$this->icon  = WPO_WCPDF()->plugin_url() . "/assets/images/packing-slip.svg";

		// call parent constructor
		parent::__construct( $order );

		// output formats (placed after parent construct to override the abstract default)
		$this->output_formats = apply_filters( 'wpo_wcpdf_document_output_formats', array( 'pdf' ), $this );
	}

	/**
	 * Get the document title
	 *
	 * @return string
	 */
	public function get_title() {
		// override/not using $this->title to allow for language switching!
		$title = __( 'Packing Slip', 'woocommerce-pdf-invoices-packing-slips' );
		$title = apply_filters_deprecated( "wpo_wcpdf_{$this->slug}_title", array( $title, $this ), '3.8.7', 'wpo_wcpdf_document_title' ); // deprecated
		return apply_filters( 'wpo_wcpdf_document_title', $title, $this );
	}

	/**
	 * Get the document number title
	 *
	 * @return string
	 */
	public function get_number_title() {
		// override to allow for language switching!
		$title = __( 'Packing Slip Number:', 'woocommerce-pdf-invoices-packing-slips' );
		$title = apply_filters_deprecated( "wpo_wcpdf_{$this->slug}_number_title", array( $title, $this ), '3.8.7', 'wpo_wcpdf_document_number_title' ); // deprecated
		return apply_filters( 'wpo_wcpdf_document_number_title', $title, $this );
	}

	/**
	 * Get the document date title
	 *
	 * @return string
	 */
	public function get_date_title() {
		// override to allow for language switching!
		$title = __( 'Packing Slip Date:', 'woocommerce-pdf-invoices-packing-slips' );
		$title = apply_filters_deprecated( "wpo_wcpdf_{$this->slug}_date_title", array( $title, $this ), '3.8.7', 'wpo_wcpdf_document_date_title' ); // deprecated
		return apply_filters( 'wpo_wcpdf_document_date_title', $title, $this );
	}

	public function get_filename( $context = 'download', $args = array() ) {
		$order_count = isset($args['order_ids']) ? count($args['order_ids']) : 1;

		$name = _n( 'packing-slip', 'packing-slips', $order_count, 'woocommerce-pdf-invoices-packing-slips' );

		if ( $order_count == 1 ) {
			if ( isset( $this->settings['display_number'] ) ) {
				$suffix = (string) $this->get_number();
			} else {
				if ( empty( $this->order ) && isset( $args['order_ids'] ) ) {
					$order = wc_get_order( $args['order_ids'][0] );
					$suffix = is_callable( array( $order, 'get_order_number' ) ) ? $order->get_order_number() : '';
				} else {
					$suffix = is_callable( array( $this->order, 'get_order_number' ) ) ? $this->order->get_order_number() : '';
				}
			}
		} else {
			$suffix = date_i18n( 'Y-m-d' ); // 2024-12-31
		}

		// get filename
		$output_format = ! empty( $args['output'] ) ? esc_attr( $args['output'] ) : 'pdf';
		$filename      = $name . '-' . $suffix . wcpdf_get_document_output_format_extension( $output_format );

		// Filter filename
		$order_ids = isset( $args['order_ids'] ) ? $args['order_ids'] : array( $this->order_id );
		$filename  = apply_filters( 'wpo_wcpdf_filename', $filename, $this->get_type(), $order_ids, $context, $args );

		// sanitize filename (after filters to prevent human errors)!
		return sanitize_file_name( $filename );
	}

	public function init_settings() {
		// Register settings.
		$page = $option_group = $option_name = 'wpo_wcpdf_documents_settings_packing-slip';

		$settings_fields = array(
			array(
				'type'			=> 'section',
				'id'			=> 'packing_slip',
				'title'			=> '',
				'callback'		=> 'section',
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'enabled',
				'title'			=> __( 'Enable', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'enabled',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_billing_address',
				'title'			=> __( 'Display billing address', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'select',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'display_billing_address',
					'options' 		=> array(
						''				=> __( 'No' , 'woocommerce-pdf-invoices-packing-slips' ),
						'when_different'=> __( 'Only when different from shipping address' , 'woocommerce-pdf-invoices-packing-slips' ),
						'always'		=> __( 'Always' , 'woocommerce-pdf-invoices-packing-slips' ),
					),
					// 'description'	=> __( 'Display billing address (in addition to the default shipping address) if different from shipping address', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_email',
				'title'			=> __( 'Display email address', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'display_email',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_phone',
				'title'			=> __( 'Display phone number', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'display_phone',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_customer_notes',
				'title'			=> __( 'Display customer notes', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_customer_notes',
					'store_unchecked'	=> true,
					'default'			=> 1,
				)
			),
		);

		if ( ! function_exists( 'WPO_WCPDF_Pro' ) ) {
			ob_start();
			?>
			<div class="notice notice-info inline">
				<p><a href="https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/" target="_blank"><?php _e( 'Upgrade to our Professional extension to attach packing slips to any email!', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
			</div>
			<?php
			$html = ob_get_clean();

			$pro_notice = array(
				array(
					'type'			=> 'setting',
					'id'			=> 'attach_to_email_ids',
					'title'			=> __( 'Attach to:', 'woocommerce-pdf-invoices-packing-slips' ),
					'callback'		=> 'html_section',
					'section'		=> 'packing_slip',
					'args'			=> array(
						'option_name' => $option_name,
						'id'          => 'attach_to_email_ids',
						'html'        => $html,
					)
				),
			);
			$settings_fields = WPO_WCPDF()->settings->move_setting_after_id( $settings_fields, $pro_notice, 'enabled' );
		}

		// Legacy filter to allow plugins to alter settings fields.
		$settings_fields = apply_filters( 'wpo_wcpdf_settings_fields_documents_packing_slip', $settings_fields, $page, $option_group, $option_name );

		// Allow plugins to alter settings fields.
		$settings_fields = apply_filters( "wpo_wcpdf_settings_fields_documents_{$this->type}_pdf", $settings_fields, $page, $option_group, $option_name, $this );

		if ( ! empty( $settings_fields ) ) {
			WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
		}
	}

	/**
	 * Get the settings categories.
	 *
	 * @param string $output_format
	 *
	 * @return array
	 */
	public function get_settings_categories( string $output_format ): array {
		if ( ! in_array( $output_format, $this->output_formats, true ) ) {
			return array();
		}

		$settings_categories = array(
			'pdf' => array(
				'general'          => array(
					'title'   => __( 'General', 'woocommerce-pdf-invoices-packing-slips' ),
					'members' => array(
						'enabled',
						'attach_to_email_ids',
					),
				),
				'document_details' => array(
					'title'   => __( 'Document details', 'woocommerce-pdf-invoices-packing-slips' ),
					'members' => array(
						'display_email',
						'display_phone',
						'display_customer_notes',
						'display_billing_address',
					),
				),
			),
		);

		return apply_filters( 'wpo_wcpdf_document_settings_categories', $settings_categories[ $output_format ], $output_format, $this );
	}

}

endif; // class_exists
