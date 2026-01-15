<?php
/**
 * Use this file for all your template filters and actions.
 * Requires PDF Invoices & Packing Slips for WooCommerce 1.4.13 or higher
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter( 'wpo_ips_ink_saving_supported_templates', function( $templates ) {
	$templates[] = 'default/Simple';
	return $templates;
}, 9, 1 );

add_filter( 'wpo_ips_color_supported_templates', function( $templates ) {
	$templates[] = 'default/Simple';
	return $templates;
}, 9, 1 );

add_filter( 'wpo_ips_template_style_features_css', function( $css, $document, $current_template, $features, $settings ) {
	if ( 'default/Simple' !== $current_template ) {
		return $css;
	}

	$ink_saving_enabled = ! empty( $features['ink_saving']['enabled'] ) && ! empty( $features['ink_saving']['value'] );
	$color_enabled      = ! empty( $features['color']['enabled'] ) && ! empty( $features['color']['value'] );
	$color              = $color_enabled ? $features['color']['value'] : '';

	// Nothing to do.
	if ( ! $ink_saving_enabled && ! $color_enabled ) {
		return $css;
	}

	if ( $ink_saving_enabled ) {
		// Ink saving: background must stay white.
		$bg     = 'white';
		$border = $color_enabled ? $color : 'black';

		// Header text color:
		// - If we have a main color and it is dark, use it.
		// - Otherwise, use black.
		if ( $color_enabled && ! wpo_ips_is_light_color( $color ) ) {
			$text = $color;
		} else {
			$text = 'black';
		}

		$css .= "
			.order-details thead th {
				color: {$text};
				background-color: {$bg};
				border-width: 0 0 0.8pt 0;
				border-style: solid;
				border-color: {$border};
			}

			.notes-totals .totals tfoot tr.order_total th,
			.notes-totals .totals tfoot tr.order_total td {
				border-top: .8pt solid {$border};
				border-bottom: .8pt solid {$border};
				color: {$text};
			}
		";

		if ( $color_enabled ) {
			$css .= "
				.document-type-label {
					color: {$color};
				}
			";
		}
	} elseif ( $color_enabled ) {
		// No ink saving, full color usage is fine.
		$text = wpo_ips_is_light_color( $color ) ? 'black' : 'white';
		$css .= "
			.order-details thead th {
				background-color: {$color};
				color: {$text};
				border-color: {$color};
			}

			.document-type-label {
				color: {$color};
			}

			.notes-totals .totals tfoot tr.order_total th,
			.notes-totals .totals tfoot tr.order_total td {
				border-top: 0.8pt solid {$color};
				border-bottom: 0.8pt solid {$color};
				color: {$color};
			}
		";
	}

	return $css;
}, 9, 5 );
