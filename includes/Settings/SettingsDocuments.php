<?php
namespace WPO\IPS\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Settings\\SettingsDocuments' ) ) :

class SettingsDocuments {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct()	{
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
		$section          = ! empty( $section ) ? $section : 'invoice';
		$documents        = WPO_WCPDF()->documents->get_documents( 'all' );
		$output_format    = 'pdf';
		$section_document = null;

		if ( ! empty( $_REQUEST['output_format'] ) ) {
			$output_format = esc_attr( $_REQUEST['output_format'] );
		}

		foreach ( $documents as $document ) {
			if ( $document->get_type() == $section ) {
				$section_document = $document;
				break;
			}
		}

		if ( empty( $section_document ) ) {
			return;
		}
		?>
		<div class="wcpdf_document_settings_sections">
			<?php echo '<h2>'.esc_html( $section_document->get_title() ).'<span class="arrow-down">&#9660;</span></h2>'; ?>
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
			<?php if ( ! function_exists( 'WPO_WCPDF_Pro' ) ) : ?>
			<p>
				<i>
					<?php
						printf(
							/* translators: 1. open anchor tag, 2. close anchor tag */
							__( 'Looking for more documents? Learn more %1$shere%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
							'<a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/more-document-types/" target="_blank">',
							'</a>'
						);
					?>
				</i>
			</p>
			<?php endif; ?>
		</div>
		<div class="wcpdf_document_settings_document_output_formats">
			<?php
				if ( ! empty( $section_document->output_formats ) ) {
					?>
					<h2 class="nav-tab-wrapper">
						<?php
							foreach ( $section_document->output_formats as $document_output_format ) {
								if ( ! wcpdf_is_ubl_available() && 'ubl' === $document_output_format ) {
									continue;
								}

								$active    = ( $output_format == $document_output_format ) || ( 'pdf' !== $output_format && ! in_array( $output_format, $section_document->output_formats ) ) ? 'nav-tab-active' : '';
								$tab_title = strtoupper( esc_html( $document_output_format ) );
								// if ( 'ubl' === $document_output_format ) {
								// 	$tab_title .= ' <sup class="wcpdf_beta">beta</sup>';
								// }
								printf( '<a href="%1$s" class="nav-tab nav-tab-%2$s %3$s">%4$s</a>', esc_url( add_query_arg( 'output_format', $document_output_format ) ), esc_attr( $document_output_format ), $active, $tab_title );
							}
						?>
					</h2>
					<?php
				}
			?>
		</div>
		<?php
			$output_format_compatible = false;
			if ( 'pdf' !== $output_format && in_array( $output_format, $section_document->output_formats ) ) {
				$output_format_compatible = true;
			}

			$option_name = ( 'pdf' === $output_format || ! $output_format_compatible ) ? "wpo_wcpdf_documents_settings_{$section}" : "wpo_wcpdf_documents_settings_{$section}_{$output_format}";
			settings_fields( $option_name );
			do_settings_sections( $option_name );
			submit_button();
	}

}

endif; // class_exists
