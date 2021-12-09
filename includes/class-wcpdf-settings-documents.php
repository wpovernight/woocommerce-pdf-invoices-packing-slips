<?php
namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Settings_Documents' ) ) :

class Settings_Documents {

	function __construct()	{
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_documents', array( $this, 'output' ), 10, 1 );
	}
	
	public function init_settings() {
		$documents = WPO_WCPDF()->documents->get_documents( 'all' );
		foreach ( $documents as $document ) {
			if ( is_callable( array( $document, 'init_settings' ) ) ) {
				$document->init_settings();
			}
		}
	}

	public function output( $section ) {
		$section = ! empty( $section ) ? $section : 'invoice';
		$documents = WPO_WCPDF()->documents->get_documents( 'all' );
		?>
		<div class="wcpdf_document_settings_sections">
			<?php esc_attr_e( 'Documents', 'woocommerce-pdf-invoices-packing-slips' ); ?>:
			<ul>
				<?php
				foreach ( $documents as $document ) {
					$title = strip_tags( $document->get_title() );
					if ( empty( trim( $title ) ) ) {
						$title = '['.__( 'untitled', 'woocommerce-pdf-invoices-packing-slips' ).']';
					}
					printf( '<li><a href="%s" class="%s">%s</a></li>', add_query_arg( 'section', $document->get_type() ), $document->get_type() == $section ? 'active' : '', esc_html( $title ) );
				}
				?>
			</ul>
		</div>
		<?php
		settings_fields( "wpo_wcpdf_documents_settings_{$section}" );
		do_settings_sections( "wpo_wcpdf_documents_settings_{$section}" );
		submit_button();
	}

}

endif; // class_exists

return new Settings_Documents();