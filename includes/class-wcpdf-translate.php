<?php
namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Translate' ) ) :

class Translate {
	
	public  $active_plugin     = '';
	private $supported_plugins = [];

	public function __construct() {
		$debug_settings = get_option( 'wpo_wcpdf_settings_debug', array() );
		
		if ( isset( $debug_settings['enable_translate'] ) ) {
			$this->supported_plugins = $this->get_supported_plugins();
			$this->active_plugin     = $this->get_active_plugin();
			$this->init();
		}
	}
	
	public function init() {
		if ( ! empty( $this->active_plugin ) ) {
			switch ( $this->active_plugin ) {
				case 'weglot':
					add_filter( 'weglot_translate_pdf', '__return_true' );
					break;
				default:
					add_filter( 'wpo_wcpdf_before_dompdf_render', array( $this, 'translate_html' ), 99, 4 );
					add_filter( 'wpo_wcpdf_after_mpdf_write', array( $this, 'translate_html' ), 99, 4 );
					break;
			}
		}
	}
	
	private function get_supported_plugins() {
		return apply_filters( 'wpo_wcpdf_translate_supported_plugins', [
			'weglot'         => 'weglot/weglot.php',
			'translatepress' => 'translatepress-multilingual/index.php',
		] );
	}
	
	private function get_active_plugin() {
		$active_plugin = '';
		
		if ( ! empty( $this->supported_plugins ) ) {
			foreach ( $this->supported_plugins as $slug => $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$active_plugin = $slug;
					break;
				}
			}
		}
		
		return $active_plugin;
	}
	
	public function translate_html( $engine, $html, $options, $document ) {
		if ( empty( $this->active_plugin ) ) {
			return $engine;
		}
		
		if ( ! empty( sanitize_key( $_GET['order_ids'] ) ) ) { // phpcs:ignore
			$order_id = sanitize_key( $_GET['order_ids'] );    // phpcs:ignore
		}

		if ( empty( $order_id ) ) {
			return $engine;
		}
		
		$order = wc_get_order( $order_id );
		
		if ( empty( $order ) ) {
			return $engine;
		}
		
		$plugin_args = [];
		switch ( $this->active_plugin ) {
			case 'translatepress':
				$plugin_args['meta_key'] = 'trp_language';
				$plugin_args['callback'] = 'trp_translate';
				break;
		}
		
		$plugin_args = apply_filters( 'wpo_wcpdf_translate_plugin_args', $plugin_args, $this );
		
		if ( empty( $plugin_args['meta_key'] ) || empty( $plugin_args['callback'] ) ) {
			return $engine;
		}

		// get order language
		$woocommerce_order_language = $order->get_meta( $plugin_args['meta_key'], true );
		
		if ( empty( $woocommerce_order_language ) ) {
			return $engine;
		}
		
		// check if HTML needs translation (different language)
		$needs_translation = false;
		switch ( $this->active_plugin ) {
			case 'translatepress':
				if ( class_exists( 'TRP_Translate_Press' ) ) {
					$trp          = \TRP_Translate_Press::get_trp_instance();
					$trp_settings = $trp->get_component( 'settings' );
					$settings     = $trp_settings->get_settings();
					
					if ( ! empty( $settings ) && isset( $settings['default-language'] ) && $settings['default-language'] != $woocommerce_order_language ) {
						$needs_translation = true;
					}
				}
				break;
		}
		
		// if no need for translation bail
		if ( ! apply_filters( 'wpo_wcpdf_translate_html_needs_translation', $needs_translation, $this ) ) {
			return $engine;
		}

		$translated_output     = $plugin_args['callback']( $html, $woocommerce_order_language );
		$translated_output_key = apply_filters( 'wpo_wcpdf_translate_html_translated_output_key', 'content', $this );
		
		if ( isset( $translated_output[$translated_output_key] ) && is_string( $translated_output[$translated_output_key] ) ) {
			$translated_html = $translated_output[$translated_output_key];
		} elseif ( is_string( $translated_output ) ) {
			$translated_html = $translated_output;
		} else {
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