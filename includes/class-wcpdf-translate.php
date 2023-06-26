<?php
namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Translate' ) ) :

class Translate {
	
	public  $debug_settings    = null;
	public  $active_plugins    = [];
	public  $selected_plugin   = '';
	private $supported_plugins = [];

	public function __construct() {
		$this->debug_settings    = get_option( 'wpo_wcpdf_settings_debug', array() );
		$this->supported_plugins = $this->get_supported_plugins();
		$this->active_plugins    = $this->get_active_plugins();
		
		if ( isset( $this->debug_settings['translate_pdf_plugin'] ) && ! empty( $this->debug_settings['translate_pdf_plugin'] ) ) {
			$this->selected_plugin = sanitize_text_field( $this->debug_settings['translate_pdf_plugin'] );
			$this->init();
		}
	}
	
	public function init() {
		if ( ! empty( $this->selected_plugin ) ) {
			switch ( $this->selected_plugin ) {
				case 'weglot':
					add_filter( 'weglot_translate_pdf', '__return_true' );
					break;
				case 'translatepress':
					add_filter( 'wpo_wcpdf_before_dompdf_render', array( $this, 'translate_html' ), 99, 4 );
					add_filter( 'wpo_wcpdf_after_mpdf_write', array( $this, 'translate_html' ), 99, 4 );
					add_filter( 'trp_stop_translating_page', '__return_false', 99, 2 ); // they have a custom function 'trp_woo_pdf_invoices_and_packing_slips_compatibility_dont_translate_pdf()' which disables our PDF to be translated.
				default:
					add_filter( 'wpo_wcpdf_before_dompdf_render', array( $this, 'translate_html' ), 99, 4 );
					add_filter( 'wpo_wcpdf_after_mpdf_write', array( $this, 'translate_html' ), 99, 4 );
					break;
			}
		}
	}
	
	private function get_supported_plugins() {
		return apply_filters( 'wpo_wcpdf_translate_supported_plugins', [
			'weglot' => [
				'class' => 'Bootstrap_Weglot',
				'name'  => 'Weglot'
			],
			'translatepress' => [
				'class' => 'TRP_Translate_Press',
				'name'  => 'TranslatePress'
			],
			'gtranslate' => [
				'class' => 'GTranslate',
				'name'  => 'GTranslate'
			],
		] );
	}
	
	private function get_active_plugins() {
		$active_plugins = [];
		
		if ( ! empty( $this->supported_plugins ) ) {
			foreach ( $this->supported_plugins as $slug => $plugin ) {
				if ( class_exists( $plugin['class'] ) ) {
					$active_plugins[$slug] = $plugin['name'];
				}
			}
		}
		
		return $active_plugins;
	}
	
	public function translate_html( $engine, $html, $options, $document ) {
		if ( empty( $this->selected_plugin ) ) {
			return $engine;
		}
		
		if ( ! isset( $_GET['order_ids'] ) || empty( $_GET['order_ids'] ) ) {
			return $engine;
		}
		
		$order_id = sanitize_key( $_GET['order_ids'] ); // phpcs:ignore
		$order    = wc_get_order( $order_id );
		
		if ( empty( $order ) ) {
			return $engine;
		}
		
		$woocommerce_order_language = '';
		
		// check if HTML needs translation (different language)
		$needs_translation          = false;
		switch ( $this->selected_plugin ) {
			case 'translatepress':
				$woocommerce_order_language = $order->get_meta( 'trp_language', true );
				if ( class_exists( 'TRP_Translate_Press' ) ) {
					$trp          = \TRP_Translate_Press::get_trp_instance();
					$trp_settings = $trp->get_component( 'settings' );
					$settings     = $trp_settings->get_settings();
					
					if ( ! empty( $settings ) && isset( $settings['default-language'] ) && $settings['default-language'] != $woocommerce_order_language ) {
						$needs_translation = true;
					}
				}
				break;
			case 'gtranslate':
				$needs_translation = true;
				break;
		}
		
		// if no need for translation bail
		if ( ! apply_filters( 'wpo_wcpdf_translate_html_needs_translation', $needs_translation, $this ) ) {
			return $engine;
		}

		$translated_html = '';
		switch ( $this->selected_plugin ) {
			case 'translatepress':
				if ( function_exists( 'trp_translate' ) && ! empty( $woocommerce_order_language ) ) {
					$translated_html = trp_translate( $html, $woocommerce_order_language, false );
				}
				break;
			case 'gtranslate':
				if ( function_exists( 'gt_translate_invoice_pdf' ) ) {
					$translated_html = gt_translate_invoice_pdf( $html );	
				}
				break;
		}
		
		if ( empty( $translated_html ) || ! is_string( $translated_html ) ) {
			return $engine;
		}

		switch ( true ) {
			case $engine instanceof \Dompdf\Dompdf:
				$engine->loadHtml( $translated_html );
				return $engine;
			case $engine instanceof \Mpdf\Mpdf:
				$mpdf = new \Mpdf\Mpdf( $options );
				$mpdf->WriteHTML( $translated_html );
				unset( $engine );
				return $mpdf;
			default:
				return $engine;
		}
	}
	
}

endif; // class_exists

return new Translate();