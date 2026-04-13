<?php
namespace WPO\IPS\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Settings\\SettingsDocuments' ) ) :

class SettingsDocuments {

	protected static ?self $_instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// WP
		if ( \WPO_WCPDF()->is_settings_page() ) {
			add_action( 'admin_init', array( $this, 'init_settings' ) );
		}
		
		// IPS
		add_action( 'wpo_wcpdf_settings_output_documents', array( $this, 'output' ), 10, 2 );
	}

	/**
	 * Initialize document settings.
	 *
	 * @return void
	 */
	public function init_settings(): void {
		$documents = WPO_WCPDF()->get_instance( 'documents' )->get_documents( 'all' );
		foreach ( $documents as $document ) {
			if ( is_callable( array( $document, 'init_settings' ) ) ) {
				$document->init_settings();
			}
		}
	}

	/**
	 * Output the document settings.
	 *
	 * @param string $section
	 * @param string $nonce
	 * @return void
	 */
	public function output( string $section, string $nonce ): void {
		if ( ! \WPO_WCPDF()->get_instance( 'settings' )->user_can_manage_settings() ) {
			return;
		}

		$section          = ! empty( $section ) ? sanitize_key( $section ) : 'invoice';
		$option_name      = "wpo_wcpdf_documents_settings_{$section}";
		$documents        = WPO_WCPDF()->get_instance( 'documents' )->get_documents( 'all' );
		$section_document = null;

		foreach ( $documents as $document ) {
			if ( $document->get_type() === $section ) {
				$section_document = $document;
				break;
			}
		}

		if ( empty( $section_document ) ) {
			return;
		}
		?>
		<div class="wcpdf_document_settings_sections">
			<span><?php esc_html_e( 'Choose document', 'woocommerce-pdf-invoices-packing-slips' ); ?></span>

			<h2>
				<?php echo esc_html( $section_document->get_title() ); ?>
				<span class="arrow-down">&#9660;</span>
			</h2>

			<ul>
				<?php foreach ( $documents as $document ) : ?>
					<?php
					if ( $document->get_type() === $section ) {
						continue;
					}

					$title = wp_strip_all_tags( $document->get_title() );

					if ( '' === trim( $title ) ) {
						$title = '[' . esc_html__( 'untitled', 'woocommerce-pdf-invoices-packing-slips' ) . ']';
					}
					?>
					<li>
						<a href="<?php echo esc_url( add_query_arg( 'section', $document->get_type() ) ); ?>">
							<?php echo esc_html( $title ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php if ( ! function_exists( 'WPO_WCPDF_Pro' ) ) : ?>
			<p class="wcpdf_document_settings_more_documents">
				<i>
					<?php
						printf(
							/* translators: 1. opening anchor tag, 2. closing anchor tag */
							esc_html__( 'Looking for more documents? Learn more %1$shere%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
							'<a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/more-document-types/" target="_blank" rel="noopener noreferrer">',
							'</a>'
						);
					?>
				</i>
			</p>
		<?php endif; ?>

		<?php
		settings_fields( $option_name );
		do_settings_sections( $option_name );
		submit_button();
	}

}

endif; // class_exists
