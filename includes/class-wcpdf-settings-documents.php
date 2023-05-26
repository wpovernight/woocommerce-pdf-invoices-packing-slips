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
		$section   = ! empty( $section ) ? $section : 'invoice';
		$documents = WPO_WCPDF()->documents->get_documents( 'all' );
		$output    = isset( $_REQUEST['output'] ) ? esc_attr( $_REQUEST['output'] ) : 'pdf';
		?>
		<div class="wcpdf_document_settings_sections">
			<?php 
			foreach ( $documents as $document ) {
				if ( $document->get_type() == $section ) {
					echo '<h2>'.esc_html( $document->get_title() ).'<span class="arrow-down">&#9660;</span></h2>';
				}
			}
			?>
			<ul>
				<?php
				foreach ( $documents as $document ) {
					if( $document->get_type() != $section ) {
						$title = strip_tags( $document->get_title() );
						if ( empty( trim( $title ) ) ) {
							$title = '['.__( 'untitled', 'woocommerce-pdf-invoices-packing-slips' ).']';
						}
						$active = $document->get_type() == $section ? 'active' : '';
						printf( '<li class="%2$s"><a href="%1$s" class="%2$s">%3$s</a></li>', esc_url( add_query_arg( 'section', $document->get_type() ) ), esc_attr( $active ), esc_html( $title ) );
					}
				}
				?>
			</ul>
		</div>
		<div class="wcpdf_document_settings_document_outputs">
		<?php 
			foreach ( $documents as $document ) {
				if ( $document->get_type() == $section && ! empty( $document->outputs ) ) {
					?>
					<h2 class="nav-tab-wrapper">
						<?php
							foreach( $document->outputs as $document_output ) {
								printf( '<a href="%1$s" class="nav-tab nav-tab-%2$s %3$s">%4$s</a>', esc_url( add_query_arg( 'output', $document_output ) ), esc_attr( $document_output ), ( ( $output == $document_output ) ? 'nav-tab-active' : '' ), strtoupper( esc_html( $document_output ) ) );
							}
						?>
					</h2>
					<?php
				}
			}
		?>
		</div>
		<?php
			$option_name = ( $output == 'pdf' ) ? "wpo_wcpdf_documents_settings_{$section}" : "wpo_wcpdf_documents_settings_{$section}_{$output}";
			settings_fields( $option_name );
			do_settings_sections( $option_name );
			submit_button();
	}

}

endif; // class_exists

return new Settings_Documents();