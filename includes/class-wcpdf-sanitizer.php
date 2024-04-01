<?php
namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Sanitizer' ) ) :

class Sanitizer {
	
	protected static $_instance = null;
		
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Sanitize HTML
	 *
	 * @param string $html
	 * @param array $exclude_from_sanitize
	 * @param array $exclude_from_strip_tags
	 * @param array $additional_allowed_html_tags
	 *
	 * @return string
	 */
	public function sanitize_html( string $html, array $exclude_from_sanitize = array(), array $exclude_from_strip_tags = array(), array $additional_allowed_html_tags = array() ): string {
		$html = html_entity_decode( $html );
		$html = apply_filters( 'wpo_wcpdf_before_sanitize_html', $html, $exclude_from_sanitize, $exclude_from_strip_tags, $additional_allowed_html_tags, $this );
		$html = $this->run_sanitizations( $html, $exclude_from_sanitize );
		$html = $this->run_strip_tags( $html, $exclude_from_strip_tags );
		$html = $this->run_allow_html_tags( $html, $additional_allowed_html_tags );
		$html = apply_filters( 'wpo_wcpdf_after_sanitize_html', $html, $exclude_from_sanitize, $exclude_from_strip_tags, $additional_allowed_html_tags, $this );
		
		return esc_html( $html );
	}

	/**
	 * Sanitize attributes
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function sanitize_script_attributes( string $text ): string {
		return preg_replace( '/<[^>]+?on[a-z]+?=[\'"].*?[\'"].*?>/i', '', $text );
	}
	
	/**
	 * Sanitize urls
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function sanitize_script_urls( string $text ): string {
		return preg_replace( '/<[^>]+?href=[\'"]javascript:.*?[\'"].*?>/i', '', $text );
	}
	
	/**
	 * Sanitize css
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function sanitize_css( string $text ): string {
		return preg_replace( '/<[^>]+?style=[\'"].*?[\'"].*?>/i', '', $text );
	}
	
	/**
	 * Sanitize comments
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function sanitize_comments( string $text ): string {
		return preg_replace( '/<!--.*?-->/s', '', $text );
	}
	
	/**
	 * Sanitize phone
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function sanitize_phone( string $text ): string {
		return preg_replace( '/[^0-9\+]/', '', $text );
	}
	
	/**
	 * Sanitize email
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function sanitize_email( string $text ): string {
		return sanitize_email( $text );
	}
	
	/**
	 * Sanitize title
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function sanitize_title( string $text ): string {
		return sanitize_title( $text );
	}
	
	/**
	 * Strip invalid tags
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function strip_invalid( string $text ): string {
		return preg_replace( "/<([^a-z\/!]|\/(?![a-z])|!(?!--))[^>]*>/i", " ", $text );
	}
	
	/**
	 * Strip style tags
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function strip_style( string $text ): string {
		return preg_replace( "/<\/?style[^>]*>/i", " ", $text );
	}
	
	/**
	 * Strip script tags
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function strip_script( string $text ): string {
		return preg_replace( "/<\/?script[^>]*>/i", " ", $text );
	}
	
	/**
	 * Strip img tags
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function strip_img( string $text ): string {
		return preg_replace( "/<\/?img[^>]*>/i", " ", $text );
	}
	
	/**
	 * Run sanitizations
	 *
	 * @param string $text
	 * @param array $exclude_from_sanitize
	 *
	 * @return string
	 */
	private function run_sanitizations( string $html, array $exclude_from_sanitize ): string {
		$sanitizations = apply_filters( 'wpo_wcpdf_valid_html_sanitizations', array( 'script_attributes', 'script_urls', 'css', 'comments' ) );
	
		if ( ! empty( $exclude_from_sanitize ) ) {
			$sanitizations = array_diff( $sanitizations, $exclude_from_sanitize );
		}
		
		if ( empty( $sanitizations ) ) {
			return $html;
		}
	
		return $this->run_operations( $html, $sanitizations, 'sanitize' );
	}
	
	/**
	 * Run strip tags
	 *
	 * @param string $text
	 * @param array $exclude_from_strip_tags
	 *
	 * @return string
	 */
	private function run_strip_tags( string $html, array $exclude_from_strip_tags = array() ): string {
		$strip_tags = apply_filters( 'wpo_wcpdf_valid_html_strip_tags', array( 'invalid', 'style', 'script', 'img' ) );
	
		if ( ! empty( $exclude_from_strip_tags ) ) {
			$strip_tags = array_diff( $strip_tags, $exclude_from_strip_tags );
		}
	
		if ( empty( $strip_tags ) ) {
			return $html;
		}
	
		return $this->run_operations( $html, $strip_tags, 'strip' );
	}
	
	/**
	 * Run sanitizations or tag stripping
	 *
	 * @param string $html
	 * @param array $operations
	 * @param string $prefix
	 *
	 * @return string
	 */
	private function run_operations( string $html, array $operations, string $prefix ): string {
		foreach ( $operations as $operation ) {
			$method = "{$prefix}_{$operation}";

			if ( is_callable( array( $this, $method ) ) ) {
				$html = $this->$method( $html );
			}
		}

		return $html;
	}
	
	/**
	 * Run allow html tags
	 *
	 * @param string $html
	 * @param array $additional_allowed_html_tags
	 *
	 * @return string
	 */
	private function run_allow_html_tags( string $html, array $additional_allowed_html_tags = array() ): string {
		$default_allowed_html_tags = apply_filters( 'wpo_wcpdf_allowed_html_tags', array(
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
		) );
		
		$allowed_html_tags = array_merge( $default_allowed_html_tags, $additional_allowed_html_tags );
	
		return wp_kses( $html, $allowed_html_tags );
	}

}

endif; // class_exists
