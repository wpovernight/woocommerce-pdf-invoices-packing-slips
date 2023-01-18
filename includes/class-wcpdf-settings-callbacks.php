<?php
namespace WPO\WC\PDF_Invoices;

use WPO\WC\PDF_Invoices\Documents\Sequential_Number_Store;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Settings_Callbacks' ) ) :

class Settings_Callbacks {
	/**
	 * Section null callback.
	 *
	 * @return void.
	 */
	public function section() {
	}
	
	/**
	 * Debug section callback.
	 *
	 * @return void.
	 */
	public function debug_section() {
		echo wp_kses_post( __( '<b>Warning!</b> The settings below are meant for debugging/development only. Do not use them on a live website!' , 'woocommerce-pdf-invoices-packing-slips' ) );
	}
	
	/**
	 * Custom fields section callback.
	 *
	 * @return void.
	 */
	public function custom_fields_section() {
		echo wp_kses_post( __( 'These are used for the (optional) footer columns in the <em>Modern (Premium)</em> template, but can also be used for other elements in your custom template' , 'woocommerce-pdf-invoices-packing-slips' ) );
	}
	
	/**
	 * HTML section callback.
	 *
	 * @return void.
	 */
	public function html_section( $args ) {
		extract( $this->normalize_settings_args( $args ) );
		
		// output HTML	
		echo wp_kses_post( $html );
	}

	/**
	 * Checkbox callback.
	 *
	 * args:
	 *   option_name - name of the main option
	 *   id          - key of the setting
	 *   value       - value if not 1 (optional)
	 *   default     - default setting (optional)
	 *   description - description (optional)
	 *
	 * @return void.
	 */
	public function checkbox( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		// output checkbox	
		printf( '<input type="checkbox" id="%1$s" name="%2$s" value="%3$s" %4$s %5$s/>', esc_attr( $id ), esc_attr( $setting_name ), esc_attr( $value ), checked( $value, $current, false ), ! empty( $disabled ) ? 'disabled="disabled"' : '' );

		// print store empty input if true
		if( $store_unchecked ) {
			printf( '<input type="hidden" name="%s[wpo_wcpdf_setting_store_empty][]" value="%s"/>', $option_name, esc_attr( $id ) );
		}
	
		// output description.
		if ( ! empty( $description ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
		}
	}

	/**
	 * Text input callback.
	 *
	 * args:
	 *   option_name - name of the main option
	 *   id          - key of the setting
	 *   size        - size of the text input (em)
	 *   default     - default setting (optional)
	 *   description - description (optional)
	 *   type        - type (optional)
	 *
	 * @return void.
	 */
	public function text_input( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		if ( empty( $type ) ) {
			$type = 'text';
		}

		$size = ! empty( $size ) ? sprintf( 'size="%s"', esc_attr( $size ) ) : '';
		printf( '<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" %5$s placeholder="%6$s" %7$s/>', esc_attr( $type ), esc_attr( $id ), esc_attr( $setting_name ), esc_attr( $current ), $size, esc_attr( $placeholder ), ! empty( $disabled ) ? 'disabled="disabled"' : '' );
	
		// output description.
		if ( ! empty( $description ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
		}
	}

	/**
	 * Combined checkbox & text input callback.
	 *
	 * args:
	 *   option_name - name of the main option
	 *   id          - key of the setting
	 *   value       - value if not 1 (optional)
	 *   default     - default setting (optional)
	 *   description - description (optional)
	 *
	 * @return void.
	 */
	public function checkbox_text_input( $args ) {
		$args = $this->normalize_settings_args( $args );
		extract( $args );
		unset( $args['description'] ); // already extracted, should only be used here
		
		// get checkbox	
		ob_start();
		$this->checkbox( $args );
		$checkbox = ob_get_clean();

		// get text input for insertion in wrapper
		$input_args = array(
			'id'			=> $args['text_input_id'],
			'default'		=> isset( $args['text_input_default'] ) ? (string) $args['text_input_default'] : NULL,
			'size'			=> isset( $args['text_input_size'] ) ? $args['text_input_size'] : NULL,
		)  + $args;
		unset( $input_args['current'] );
		unset( $input_args['setting_name'] );

		ob_start();
		$this->text_input( $input_args );
		$text_input = ob_get_clean();

		if (! empty( $text_input_wrap ) ) {
		 	printf( "{$checkbox} {$text_input_wrap}", $text_input);
		} else {
			echo "{$checkbox} {$text_input}";
		}
	
		// output description.
		if ( ! empty( $description ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
		}
	}


	// Single text option (not part of any settings array)
	public function singular_text_element( $args ) {
		$option_name = $args['option_name'];
		$id = $args['id'];
		$size = isset( $args['size'] ) ? $args['size'] : '25';
		$class = isset( $args['translatable'] ) && $args['translatable'] === true ? 'translatable' : '';
	
		$option = get_option( $option_name );

		if ( isset( $option ) ) {
			$current = $option;
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
	
		printf( '<input type="text" id="%1$s" name="%2$s" value="%3$s" size="%4$s" class="%5$s"/>', esc_attr( $id ), esc_attr( $option_name ), esc_attr( $current ), esc_attr( $size ), esc_attr( $class ) );
	
		// output description.
		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $args['description'] ) );
		}
	}


	/**
	 * Textarea callback.
	 *
	 * args:
	 *   option_name - name of the main option
	 *   id          - key of the setting
	 *   width       - width of the text input (em)
	 *   height      - height of the text input (lines)
	 *   default     - default setting (optional)
	 *   description - description (optional)
	 *
	 * @return void.
	 */
	public function textarea( $args ) {
		extract( $this->normalize_settings_args( $args ) );
	
		printf( '<textarea id="%1$s" name="%2$s" cols="%4$s" rows="%5$s" placeholder="%6$s"/>%3$s</textarea>', esc_attr( $id ), esc_attr( $setting_name ), esc_textarea( $current ), esc_attr( $width ), esc_attr( $height ), esc_attr( $placeholder ) );
	
		// output description.
		if ( ! empty( $description ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
		}
	}

	/**
	 * Select element callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return void
	 */
	public function select( $args ) {
		extract( $this->normalize_settings_args( $args ) );
	
		if ( ! empty( $enhanced_select ) ) {
			if ( ! empty( $multiple ) ) {
				$setting_name = "{$setting_name}[]";
				$multiple = 'multiple=multiple';
			} else {
				$multiple = '';
			}

			$placeholder = ! empty( $placeholder ) ? esc_attr( $placeholder ) : '';
			$title = ! empty( $title ) ? esc_attr( $title ) : '';
			$class = 'wc-enhanced-select wpo-wcpdf-enhanced-select';
			$css = 'width:400px';
			printf( '<select id="%1$s" name="%2$s" data-placeholder="%3$s" title="%4$s" class="%5$s" style="%6$s" %7$s>', esc_attr( $id ), esc_attr( $setting_name ), esc_attr( $placeholder ), esc_attr( $title ), esc_attr( $class ), esc_attr( $css ), esc_attr( $multiple ) );
		} else {
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $setting_name ) );
		}

		if ( ! empty( $options_callback ) ) {
			$options = isset( $options_callback_args ) ? call_user_func_array( $options_callback, $options_callback_args ) : call_user_func( $options_callback );
		}

		foreach ( $options as $key => $label ) {
			if ( ! empty( $multiple ) && is_array( $current ) ) {
				$selected = in_array( $key, $current ) ? ' selected="selected"' : '';
				printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), esc_attr( $selected ), esc_html( $label ) );
			} else {
				printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), esc_attr( selected( $current, $key, false ) ), esc_html( $label ) );
			}
		}

		echo '</select>';

		if ( ! empty( $custom ) ) {
			printf( '<div class="%1$s_custom custom">', esc_attr( $id ) );

			if ( is_callable( array( $this, $custom['type'] ) ) ) {
				$this->{$custom['type']}( $custom['args'] );
			}
			echo '</div>';
			$custom_option = ! empty( $custom['custom_option'] ) ? $custom['custom_option'] : 'custom';
			?>
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				function check_<?php echo esc_attr( $id ); ?>_custom() {
					var custom = $('#<?php echo esc_attr( $id ); ?>').val();
					if (custom == '<?php echo $custom_option; ?>') {
						$( '.<?php echo esc_attr( $id ); ?>_custom').show();
					} else {
						$( '.<?php echo esc_attr( $id ); ?>_custom').hide();
					}
				}

				check_<?php echo esc_attr( $id ); ?>_custom();

				$( '#<?php echo esc_attr( $id ); ?>' ).on( 'change', function() {
					check_<?php echo esc_attr( $id ); ?>_custom();
				});

			});
			</script>
			<?php
		}
	
		// output description.
		if ( ! empty( $description ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
		}

	}

	public function radio_button( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		if ( ! empty( $options_callback ) ) {
			$options = isset( $options_callback_args ) ? call_user_func_array( $options_callback, $options_callback_args ) : call_user_func( $options_callback );
		}
	
		foreach ( $options as $key => $label ) {
			printf( '<input type="radio" class="radio" id="%1$s[%3$s]" name="%2$s" value="%3$s"%4$s />', esc_attr( $id ), esc_attr( $setting_name ), esc_attr( $key ), checked( $current, $key, false ) );
			printf( '<label for="%1$s[%3$s]"> %4$s</label><br>', esc_attr( $id ), esc_attr( $setting_name ), esc_attr( $key ), esc_html( $label ) );
		}
		
	
		// output description.
		if ( ! empty( $description ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
		}

	}

	/**
	 * Multiple text element callback.
	 * @param  array $args Field arguments.
	 * @return void
	 */
	public function multiple_text_input( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		if ( ! empty( $fields_callback ) ) {
			$fields = isset( $fields_callback_args ) ? call_user_func_array( $fields_callback, $fields_callback_args ) : call_user_func( $fields_callback );
		}

		printf( '<table class="%s multiple-text-input">', esc_attr( $id ) );
		if ( ! empty( $header ) ) {
			echo wp_kses_post( "<tr><td><strong>{$header}</strong>:</td></tr>" );
		}
		foreach ($fields as $name => $field) {
			echo '<tr>';
			$size = $field['size'];
			$placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : '';

			$field_description = ! empty( $field['description'] ) ? $field['description']: '';

			// output field label
			if ( isset( $field['label'] ) ) {
				printf( '<td class="label"><label for="%1$s_%2$s">%3$s:</label></td>', esc_attr( $id ), esc_attr( $name ), esc_html( $field['label'] ) );
			} else {
				echo '<td></td>';
			}

			// output field
			$field_current = isset( $current[$name] ) ? $current[$name] : '';
			$type = isset( $field['type'] ) ? $field['type'] : 'text';
			printf( '<td><input type="%1$s" id="%2$s_%4$s" name="%3$s[%4$s]" value="%5$s" size="%6$s" placeholder="%7$s"/></td>', esc_attr( $type ), esc_attr( $id ), esc_attr( $setting_name ), esc_attr( $name ), esc_attr( $field_current ), esc_attr( $size ), esc_attr( $placeholder ) );

			// field description.
			if ( ! empty( $field_description ) ) {
				echo '<td>' . wc_help_tip( $field_description, true ) . '</td>';
			} else {
				echo '<td></td>';
			}
			echo '</tr>';
		}
		echo "</table>";
		
		// group description.
		if ( ! empty( $description ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
		}
	}

	/**
	 * Multiple text element callback.
	 * @param  array $args Field arguments.
	 * @return void
	 */
	public function multiple_checkboxes( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		if ( ! empty( $fields_callback ) ) {
			$fields = isset( $fields_callback_args ) ? call_user_func_array( $fields_callback, $fields_callback_args ) : call_user_func( $fields_callback );
		}

		foreach ( $fields as $name => $label ) {
			// output checkbox	
			$field_current = isset( $current[$name] ) ? $current[$name] : '';
			printf( '<input type="checkbox" id="%1$s_%3$s" name="%2$s[%3$s]" value="%4$s"%5$s />',  esc_attr( $id ),  esc_attr( $setting_name ),  esc_attr( $name ),  esc_attr( $value ), checked( $value, $field_current, false ) );

			// output field label
			printf( '<label for="%1$s_%2$s">%3$s</label><br>',  esc_attr( $id ),  esc_attr( $name ), esc_html( $label ) );

		}
	
		// output description.
		if ( ! empty( $description ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
		}
	}

	/**
	 * Media upload callback.
	 *
	 * @param  array $args Field arguments.
	 * @return void
	 */
	public function media_upload( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		if( ! empty( $current ) && $attachment = wp_get_attachment_image_src( $current, 'full', false ) ) {
			$general_settings = get_option('wpo_wcpdf_settings_general');
			$attachment_src = $attachment[0];
			$attachment_width = $attachment[1];
			$attachment_height = $attachment[2];
			// check if we have the height saved on settings
			$header_logo_height =  !empty( $general_settings['header_logo_height'] ) ? $general_settings['header_logo_height'] : '3cm';
			if ( stripos( $header_logo_height, 'mm' ) != false ) {
				$in_height = floatval( $header_logo_height )/25.4;
			} elseif ( stripos( $header_logo_height, 'cm' ) != false ) {
				$in_height = floatval( $header_logo_height )/2.54;
			} elseif ( stripos( $header_logo_height, 'in' ) != false ) {
				$in_height = floatval( $header_logo_height );
			} else {
				// don't display resolution
			}

			/*
			 * .webp support can be disabled but still showing the image in settings.
			 * We should add a notice because this will display an error when redering the PDF using DOMPDF.
			 */
			if ( 'webp' === wp_check_filetype( $attachment_src )['ext'] && ! function_exists( 'imagecreatefromwebp' ) ) {
				printf(
					'<div class="notice notice-warning inline" style="display:inline-block; width:auto;"><p>%s</p></div>',
					wp_kses_post( 'File type <strong>webp</strong> is not supported by your server! Please check your <strong>System Configurations</strong> under the <strong>Status</strong> tab.', 'woocommerce-pdf-invoices-packing-slips' )
				);
			}

			printf( '<img src="%1$s" style="display:block" id="img-%2$s" class="media-upload-preview"/>', esc_attr( $attachment_src ), esc_attr( $id ) );
			if ( ! empty( $attachment_height ) && ! empty( $in_height ) ) {
				$attachment_resolution = round( absint( $attachment_height ) / $in_height );
				printf(
					'<div class="attachment-resolution"><p class="description">%s: %sdpi</p></div>',
					esc_html__( 'Image resolution', 'woocommerce-pdf-invoices-packing-slips' ),
					$attachment_resolution
				);

				// warn the user if the image is unnecessarily large
				if ( $attachment_resolution > 600 ) {
					printf(
						'<div class="attachment-resolution-warning notice notice-warning inline"><p>%s</p></div>',
						esc_html__( 'The image resolution exceeds the recommended maximum of 600dpi. This will unnecessarily increase the size of your PDF files and could negatively affect performance.', 'woocommerce-pdf-invoices-packing-slips' )
					); 
				}
			}

			printf('<span class="button wpo_remove_image_button" data-input_id="%1$s">%2$s</span> ', esc_attr( $id ), esc_attr( $remove_button_text ) );
		}

		printf( '<input id="%1$s" name="%2$s" type="hidden" value="%3$s" data-settings_callback_args="%4$s" data-ajax_nonce="%5$s" class="media-upload-id"/>', esc_attr( $id ), esc_attr( $setting_name ), esc_attr( $current ), esc_attr( json_encode( $args ) ), wp_create_nonce( "wpo_wcpdf_get_media_upload_setting_html" ) );
		
		printf( '<span class="button wpo_upload_image_button %4$s" data-uploader_title="%1$s" data-uploader_button_text="%2$s" data-remove_button_text="%3$s" data-input_id="%4$s">%2$s</span>', esc_attr( $uploader_title ), esc_attr( $uploader_button_text ), esc_attr( $remove_button_text ), esc_attr( $id ) );
	
		// Displays option description.
		if ( ! empty( $description ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
		}
	}

	/**
	 * Next document number edit callback.
	 *
	 * @param  array $args Field arguments.
	 */
	public function next_number_edit( $args ) {
		extract( $args ); // $store, $size, $description

		if ( ! empty( $store_callback ) ) {
			$store = isset( $store_callback_args ) ? call_user_func_array( $store_callback, $store_callback_args ) : call_user_func( $store_callback );
		}

		// Sequential_Number_Store object
		if( is_object( $store ) ) {
			$next_number         = $store->get_next();
			$store               = $store->store_name;
		// legacy
		} else {
			$number_store_method = WPO_WCPDF()->settings->get_sequential_number_store_method(); 
			$number_store        = new Sequential_Number_Store( $store, $number_store_method ); 
			$next_number         = $number_store->get_next();
		}

		$nonce = wp_create_nonce( "wpo_wcpdf_next_{$store}" );
		printf(
			'<input id="next_%1$s" class="next-number-input" type="number" size="%2$s" value="%3$s" disabled="disabled" data-store="%1$s" data-nonce="%4$s"/> <span class="edit-next-number dashicons dashicons-edit"></span><span class="save-next-number button secondary" style="display:none;">%5$s</span>',
			esc_attr( $store ),
			esc_attr( $size ),
			esc_attr( $next_number ),
			esc_attr( $nonce ),
			esc_html__( 'Save', 'woocommerce-pdf-invoices-packing-slips' )
		);
		// Displays option description.
		if ( ! empty( $description ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
		}
	}	

	/**
	 * Wrapper function to create tabs for settings in different languages
	 * @param  array $args
	 * @return void
	 */
	public function i18n_wrap ( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		if ( $languages = $this->get_languages() ) {
			printf( '<div id="%s-%s-translations" class="translations">', esc_attr( $option_name ), esc_attr( $id ) );
			?>
				<ul>
					<?php foreach ( $languages as $lang_code => $language_name ) {
						$translation_id = "{$option_name}_{$id}_{$lang_code}";
						printf( '<li><a href="#%s">%s</a></li>', esc_attr( $translation_id ), esc_html( $language_name ) );
					}
					?>
				</ul>
				<?php foreach ( $languages as $lang_code => $language_name ) {
					$translation_id = "{$option_name}_{$id}_{$lang_code}";
					printf( '<div id="%s">', esc_attr( $translation_id ) );
					$args['lang'] = $lang_code;
					// don't use internationalized placeholders since they're not translated,
					// to avoid confusion (user thinking they're all the same)
					if ( $callback == 'multiple_text_input' ) {
						foreach ( $fields as $key => $field_args ) {
							if ( ! empty( $field_args['placeholder'] ) && isset( $field_args['i18n_placeholder'] ) ) {
								$args['fields'][$key]['placeholder'] = '';
							}
						}
					} else {
						if ( !empty( $args['placeholder'] ) && isset( $args['i18n_placeholder'] ) ) {
							$args['placeholder'] = '';
						}
					}
					// specific description for internationalized fields (to compensate for missing placeholder)
					if ( ! empty( $args['i18n_description'] ) ) {
						$args['description'] = $args['i18n_description'];
					}
					if ( is_array( $callback ) ) {
						call_user_func( $callback, $args );
					} else {
						call_user_func( array( $this, $callback ), $args );
					}
					echo '</div>';
				}
				?>
			
			</div>
			<?php
		} else {
			$args['lang'] = 'default';
			if ( is_array( $callback ) ) {
				call_user_func( $callback, $args );
			} else {
				call_user_func( array( $this, $callback ), $args );
			}
		}
	}

	public function get_languages () {
		$multilingual = function_exists( 'icl_get_languages' );
		// $multilingual = true; // for development

		if ( $multilingual ) {
			// use this instead of function call for development outside of WPML
			// $icl_get_languages = 'a:3:{s:2:"en";a:8:{s:2:"id";s:1:"1";s:6:"active";s:1:"1";s:11:"native_name";s:7:"English";s:7:"missing";s:1:"0";s:15:"translated_name";s:7:"English";s:13:"language_code";s:2:"en";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/en.png";s:3:"url";s:23:"http://yourdomain/about";}s:2:"fr";a:8:{s:2:"id";s:1:"4";s:6:"active";s:1:"0";s:11:"native_name";s:9:"Français";s:7:"missing";s:1:"0";s:15:"translated_name";s:6:"French";s:13:"language_code";s:2:"fr";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/fr.png";s:3:"url";s:29:"http://yourdomain/fr/a-propos";}s:2:"it";a:8:{s:2:"id";s:2:"27";s:6:"active";s:1:"0";s:11:"native_name";s:8:"Italiano";s:7:"missing";s:1:"0";s:15:"translated_name";s:7:"Italian";s:13:"language_code";s:2:"it";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/it.png";s:3:"url";s:26:"http://yourdomain/it/circa";}}';
			// $icl_get_languages = unserialize($icl_get_languages);
			
			$icl_get_languages = icl_get_languages( 'skip_missing=0' );
			$languages = array();
			foreach ($icl_get_languages as $lang => $data) {
				$languages[$data['language_code']] = $data['native_name'];
			}
		} else {
			return false;
		}

		return $languages;
	}

	public function normalize_settings_args ( $args ) {
		$args['value'] = isset( $args['value'] ) ? $args['value'] : 1;

		$args['placeholder'] = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
		$args['store_unchecked'] = isset( $args['store_unchecked'] ) && $args['store_unchecked'] ? true : false;

		// get main settings array
		$option = get_option( $args['option_name'] );
	
		if ( empty ( $args['setting_name'] ) ) {
			$args['setting_name'] = "{$args['option_name']}[{$args['id']}]";
		}

		if ( !isset($args['lang']) && !empty($args['translatable']) ) {
			$args['lang'] = 'default';
		}

		if ( ! array_key_exists( 'current', $args ) ) {
			if (isset($args['lang'])) {
				// i18n settings name
				$args['setting_name'] = "{$args['setting_name']}[{$args['lang']}]";
				// copy current option value if set
				
				if ( $args['lang'] == 'default' && !empty($option[$args['id']]) && !isset( $option[$args['id']]['default'] ) ) {
					// we're switching back from WPML to normal
					// try english first
					if ( isset( $option[$args['id']]['en'] ) ) {
						$args['current'] = $option[$args['id']]['en'];
					} elseif ( is_array( $option[$args['id']] ) ) {
						// fallback to the first language if english not found
						$first = array_shift($option[$args['id']]);
						if (!empty($first)) {
							$args['current'] = $first;
						}
					} elseif ( is_string( $option[$args['id']] ) ) {
						$args['current'] = $option[$args['id']];
					} else {
						// nothing, really?
						$args['current'] = '';
					}
				} else {
					if ( isset( $option[$args['id']][$args['lang']] ) ) {
						$args['current'] = $option[$args['id']][$args['lang']];
					} elseif (isset( $option[$args['id']]['default'] )) {
						$args['current'] = $option[$args['id']]['default'];
					}
				}
			} else {
				// copy current option value if set
				if ( isset( $option[$args['id']] ) ) {
					$args['current'] = $option[$args['id']];
				}
			}
		}

		// falback to default or empty if no value in option
		if ( !isset($args['current']) ) {
			$args['current'] = isset( $args['default'] ) ? $args['default'] : '';
		} elseif ( empty($args['current']) && isset($args['default_if_empty']) && $args['default_if_empty'] == true ) { // force fallback if empty 'current' and 'default_if_empty' equals to true
			$args['current'] = isset( $args['default'] ) ? $args['default'] : '';
		}

		return $args;
	}

	/**
	 * Validate options.
	 *
	 * @param  array $input options to valid.
	 *
	 * @return array		validated options.
	 */
	public function validate( $input ) {
		// Create our array for storing the validated options.
		$output = array();

		if ( empty( $input ) || ! is_array( $input ) ) {
			return $input;
		}

		if ( ! empty( $input['wpo_wcpdf_setting_store_empty'] ) ) { //perhaps we should use a more unique/specific name for this
			foreach ( $input['wpo_wcpdf_setting_store_empty'] as $key ) {
				if ( empty( $input[$key] ) ) {
					$output[$key] = 0;
				}
			}
			unset($input['wpo_wcpdf_setting_store_empty']);
		}
	
		// Loop through each of the incoming options.
		foreach ( $input as $key => $value ) {
	
			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[$key] ) ) {
				if ( is_array( $input[$key] ) ) {
					foreach ( $input[$key] as $sub_key => $sub_value ) {
						$output[$key][$sub_key] = $input[$key][$sub_key];
					}
				} else {
					$output[$key] = $input[$key];
				}
			}
		}
	
		// Return the array processing any additional functions filtered by this action.
		return apply_filters( 'wpo_wcpdf_validate_input', $output, $input );
	}
}


endif; // class_exists

return new Settings_Callbacks();
