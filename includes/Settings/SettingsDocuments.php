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
		add_action( 'wpo_wcpdf_settings_output_documents', array( $this, 'output' ), 10, 2 );
	}

	public function init_settings() {
		$documents = WPO_WCPDF()->documents->get_documents( 'all' );
		foreach ( $documents as $document ) {
			if ( is_callable( array( $document, 'init_settings' ) ) ) {
				$document->init_settings();
			}
		}
	}

	public function output( $section, $nonce ) {
		if ( ! wp_verify_nonce( $nonce, 'wp_wcpdf_settings_page_nonce' ) ) {
			return;
		}
		
		$section          = ! empty( $section ) ? $section : 'invoice';
		$option_name      = "wpo_wcpdf_documents_settings_{$section}";
		$documents        = WPO_WCPDF()->documents->get_documents( 'all' );
		$section_document = null;

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
						$title = wp_strip_all_tags( $document->get_title() );
						if ( empty( trim( $title ) ) ) {
							$title = '[' . __( 'untitled', 'woocommerce-pdf-invoices-packing-slips' ) . ']';
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
							esc_html__( 'Looking for more documents? Learn more %1$shere%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
							'<a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/more-document-types/" target="_blank">',
							'</a>'
						);
					?>
				</i>
			</p>
			<?php endif; ?>
		</div>
		<?php
			settings_fields( $option_name );
			do_settings_sections( $option_name );
			submit_button();
	}

}

endif; // class_exists
