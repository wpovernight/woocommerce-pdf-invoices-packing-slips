<?php
namespace WPO\IPS\Settings;

use WPO\IPS\Documents\SequentialNumberStore;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Settings\\SettingsCallbacks' ) ) :

class SettingsCallbacks {

	protected static ?self $_instance = null;

	/**
	 * Instance of this class.
	 */
	public static function instance(): ?self {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Section null callback.
	 *
	 * @return void
	 */
	public function section(): void {}

	/**
	 * Debug section callback.
	 *
	 * @return void
	 */
	public function debug_section(): void {
		echo wp_kses_post( '<strong>' . __( 'Warning!', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong>' . ' ' .
			__( 'The settings below are meant for debugging/development only. Do not use them on a live website!' , 'woocommerce-pdf-invoices-packing-slips' ) );
	}

	/**
	 * Custom fields section callback.
	 *
	 * @return void
	 */
	public function custom_fields_section(): void {
		echo wp_kses_post( sprintf(
			/* translators: %s Modern (Premium) */
			__( 'These are used for the (optional) footer columns in the %s template, but can also be used for other elements in your custom template.' , 'woocommerce-pdf-invoices-packing-slips' ),
			'<em>Modern (Premium)</em>'
		) );
	}

	/**
	 * HTML section callback.
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function html_section( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );

		// output HTML
		echo wp_kses_post( $html );
	}

	/**
	 * Checkbox callback.
	 *
	 * args:
	 *   option_name       - name of the main option
	 *   id                - key of the setting
	 *   value             - value if not 1 (optional)
	 *   default           - default setting (optional)
	 *   description       - description (optional)
	 *   custom_attributes - custom attributes (optional)
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function checkbox( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );
		
		// output checkbox
		printf(
			'<input type="checkbox" id="%1$s" name="%2$s" value="%3$s" %4$s %5$s %6$s/>',
			esc_attr( $id ),
			esc_attr( $setting_name ),
			esc_attr( $value ),
			checked( $value, $current, false ), 
			! empty( $disabled ) ? 'disabled="disabled"' : '',
			wp_kses_post( $custom_attributes )
		);
		
		if ( ! empty( $title ) ) {
			printf(
				'<label for="%1$s">%2$s</label>',
				esc_attr( $id ),
				esc_html( $title )
			);
		}

		// print store empty input if true
		if ( $store_unchecked ) {
			printf(
				'<input type="hidden" name="%s[wpo_wcpdf_setting_store_empty][]" value="%s"/>',
				esc_attr( $option_name ),
				esc_attr( $id )
			);
		}

		// output description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}
	}

	/**
	 * Text input callback.
	 *
	 * args:
	 *   title             - secondary title of the input (optional)
	 *   option_name       - name of the main option
	 *   id                - key of the setting
	 *   size              - size of the text input (em)
	 *   default           - default setting (optional)
	 *   description       - description (optional)
	 *   type              - type (optional)
	 *   custom_attributes - custom attributes (optional)
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function text_input( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );

		if ( empty( $type ) ) {
			$type = 'text';
		}

		if ( ! empty( $action_button ) ) {
			echo '<div class="wpo-wcpdf-input-wrapper input ', esc_attr( $id ), '">';
		}
		
		if ( ! empty( $title ) ) {
			printf(
				'<label for="%1$s">%2$s</label>',
				esc_attr( $id ),
				esc_html( $title )
			);
		}

		$size = ! empty( $size ) ? sprintf( 'size="%s"', esc_attr( $size ) ) : '';
		printf(
			'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" %5$s placeholder="%6$s" %7$s %8$s/>',
			esc_attr( $type ),
			esc_attr( $id ),
			esc_attr( $setting_name ),
			esc_attr( $current ),
			esc_attr( $size ),
			esc_attr( $placeholder ),
			! empty( $disabled ) ? 'disabled="disabled"' : '',
			wp_kses_post( $custom_attributes )
		);

		// Output action button.
		if ( ! empty( $action_button ) ) {
			$this->output_action_button( $action_button, $id );
			echo '</div>';
		}

		// output description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}
	}

	/**
	 * URL input callback.
	 *
	 * args:
	 *   option_name       - name of the main option
	 *   id                - key of the setting
	 *   size              - size of the text input (em)
	 *   default           - default setting (optional)
	 *   description       - description (optional)
	 *   type              - type (optional)
	 *   custom_attributes - custom attributes (optional)
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function url_input( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );

		if ( empty( $type ) ) {
			$type = 'url';
		}

		$size = ! empty( $size ) ? sprintf( 'size="%s"', esc_attr( $size ) ) : '';
		
		printf(
			'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" %5$s placeholder="%6$s" %7$s %8$s/>',
			esc_attr( $type ),
			esc_attr( $id ),
			esc_attr( $setting_name ),
			esc_attr( $current ),
			esc_attr( $size ),
			esc_attr( $placeholder ),
			! empty( $disabled ) ? 'disabled="disabled"' : '',
			wp_kses_post( $custom_attributes )
		);

		// output description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}
	}

	/**
	 * Email input callback.
	 *
	 * args:
	 *   option_name       - name of the main option
	 *   id                - key of the setting
	 *   size              - size of the text input (em)
	 *   default           - default setting (optional)
	 *   description       - description (optional)
	 *   type              - type (optional)
	 *   custom_attributes - custom attributes (optional)
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function email_input( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );

		if ( empty( $type ) ) {
			$type = 'email';
		}

		$size = ! empty( $size ) ? sprintf( 'size="%s"', esc_attr( $size ) ) : '';
		
		printf(
			'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" %5$s placeholder="%6$s" %7$s %8$s/>',
			esc_attr( $type ),
			esc_attr( $id ),
			esc_attr( $setting_name ),
			esc_attr( sanitize_email( $current ) ),
			esc_attr( $size ),
			esc_attr( $placeholder ),
			! empty( $disabled ) ? 'disabled="disabled"' : '',
			wp_kses_post( $custom_attributes )
		);

		// output description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
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
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function checkbox_text_input( array $args ): void {
		$args = $this->normalize_settings_args( $args );
		extract( $args );
		unset( $args['description'] ); // already extracted, should only be used here

		// get checkbox
		ob_start();
		$this->checkbox( $args );
		$checkbox = ob_get_clean();

		// get text input for insertion in wrapper
		$input_args = array(
			'id'      => $args['text_input_id'],
			'default' => isset( $args['text_input_default'] ) ? (string) $args['text_input_default'] : null,
			'size'    => isset( $args['text_input_size'] ) ? $args['text_input_size'] : null,
		) + $args;
		unset( $input_args['current'] );
		unset( $input_args['setting_name'] );

		ob_start();
		$this->text_input( $input_args );
		$text_input = ob_get_clean();

		$allowed_html = array(
			'input' => array(
				'type'        => true,
				'name'        => true,
				'id'          => true,
				'value'       => true,
				'class'       => true,
				'placeholder' => true,
				'disabled'    => true,
				'checked'     => true,
				'size'        => true,
			),
		);

		if ( ! empty( $text_input_wrap ) ) {
			$text_input = sprintf( $text_input_wrap, $text_input );
		}

		echo wp_kses( "{$checkbox} {$text_input}", $allowed_html );

		// output description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}
	}

	/**
	 * Single text option (not part of any settings array)
	 * 
	 * args:
	 *   option_name       - name of the main option
	 *   id                - key of the setting
	 *   default           - default setting (optional)
	 *   description       - description (optional)
	 *   custom_attributes - custom attributes (optional)
	 * 
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function singular_text_element( array $args ): void {
		$args = $this->normalize_settings_args( $args );
		extract( $args );
		
		$size   = $size ?? '25';
		$class  = isset( $translatable ) && true === $translatable ? 'translatable' : '';
		$option = get_option( $option_name ?? '' );

		if ( ! empty( $option ) ) {
			$current = $option;
		} else {
			$current = $default ?? '';
		}

		printf(
			'<input type="text" id="%1$s" name="%2$s" value="%3$s" size="%4$s" class="%5$s" %6$s/>',
			esc_attr( $id ),
			esc_attr( $option_name ),
			esc_attr( $current ),
			esc_attr( $size ),
			esc_attr( $class ),
			wp_kses_post( $custom_attributes )
		);

		// output description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}
	}

	/**
	 * Textarea callback.
	 *
	 * args:
	 *   title             - secondary title of the input (optional)
	 *   option_name       - name of the main option
	 *   id                - key of the setting
	 *   width             - width of the text input (em)
	 *   height            - height of the text input (lines)
	 *   default           - default setting (optional)
	 *   description       - description (optional)
	 *   custom_attributes - custom attributes (optional)
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function textarea( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );

		printf(
			'<textarea id="%1$s" name="%2$s" cols="%4$s" rows="%5$s" placeholder="%6$s" %7$s/>%3$s</textarea>',
			esc_attr( $id ),
			esc_attr( $setting_name ),
			esc_textarea( $current ),
			esc_attr( $width ),
			esc_attr( $height ),
			esc_attr( $placeholder ),
			wp_kses_post( $custom_attributes )
		);

		// output description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}
	}

	/**
	 * Select element callback.
	 * 
	 * args:
	 *   title             - secondary title of the input (optional)
	 *   setting_name      - name of the main setting
	 *   id                - key of the setting
	 *   multiple          - whether the select is multiple (optional)
	 *   options           - array of options for the select
	 *   current           - current value(s) of the setting
	 *   disabled          - whether the select is disabled (optional)
	 *   custom_attributes - custom attributes (optional)
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function select( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );

		if ( ! empty( $action_button ) ) {
			echo '<div class="wpo-wcpdf-input-wrapper select ', esc_attr( $id ), '">';
		}
		
		if ( ! empty( $title ) ) {
			printf(
				'<label for="%1$s">%2$s</label>',
				esc_attr( $id ),
				esc_html( $title )
			);
		}

		if ( ! empty( $enhanced_select ) ) {
			if ( ! empty( $multiple ) ) {
				$setting_name = "{$setting_name}[]";
				$multiple = 'multiple=multiple';
			} else {
				$multiple = '';
			}

			$placeholder = ! empty( $placeholder ) ? esc_attr( $placeholder ) : '';
			$title       = ! empty( $title ) ? esc_attr( $title ) : '';
			$class       = 'wc-enhanced-select wpo-wcpdf-enhanced-select';
			$css         = 'width:400px';
			
			printf(
				'<select id="%1$s" name="%2$s" data-placeholder="%3$s" title="%4$s" class="%5$s" style="%6$s" %7$s %8$s %9$s>',
				esc_attr( $id ),
				esc_attr( $setting_name ),
				esc_attr( $placeholder ),
				esc_attr( $title ),
				esc_attr( $class ),
				esc_attr( $css ),
				esc_attr( $multiple ),
				! empty( $disabled ) ? 'disabled="disabled"' : '',
				wp_kses_post( $custom_attributes )
			);
		} else {
			printf(
				'<select id="%1$s" name="%2$s" %3$s %4$s>',
				esc_attr( $id ),
				esc_attr( $setting_name ),
				! empty( $disabled ) ? 'disabled="disabled"' : '',
				wp_kses_post( $custom_attributes )
			);
		}

		if ( ! empty( $options_callback ) ) {
			$options = isset( $options_callback_args ) ? call_user_func_array( $options_callback, $options_callback_args ) : call_user_func( $options_callback );
		}

		foreach ( $options as $key => $label ) {
			// Normalize both sides to string
			$key_str = (string) $key;

			// Determine if selected (works for both single and multiple)
			if ( ! empty( $multiple ) && is_array( $current ) ) {
				$is_selected   = in_array( $key_str, array_map( 'strval', $current ), true );
				$selected_attr = $is_selected ? ' selected="selected"' : '';
			} else {
				$selected_attr = selected( (string) $current, $key_str, false );
			}

			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $key_str ),
				esc_attr( $selected_attr ),
				esc_html( $label )
			);
		}

		echo '</select>';

		// Output action button.
		if ( ! empty( $action_button ) ) {
			$this->output_action_button( $action_button, $id );
			echo '</div>';
		}
		
		// output description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}

		if ( ! empty( $custom ) ) {
			printf(
				'<div class="%1$s_custom custom">',
				esc_attr( $id )
			);

			if ( is_callable( array( $this, $custom['type'] ) ) ) {
				$this->{$custom['type']}( $custom['args'] );
			}
			
			echo '</div>';
			
			$custom_option = ! empty( $custom['custom_option'] ) ? $custom['custom_option'] : 'custom';
			?>
				<script>
					jQuery( function( $ ) {
						function check_<?php echo esc_attr( $id ); ?>_custom() {
							var custom = $( '#<?php echo esc_attr( $id ); ?>' ).val();
							
							if ( custom == '<?php echo esc_attr( $custom_option ); ?>' ) {
								$( '.<?php echo esc_attr( $id ); ?>_custom' ).show();
							} else {
								$( '.<?php echo esc_attr( $id ); ?>_custom' ).hide();
							}
						}

						check_<?php echo esc_attr( $id ); ?>_custom();

						$( '#<?php echo esc_attr( $id ); ?>' ).on( 'change', function() {
							check_<?php echo esc_attr( $id ); ?>_custom();
						} );
					} );
				</script>
			<?php
		}
	}

	/**
	 * Radio button callback.
	 * 
	 * args:
	 *  title             - secondary title of the input (optional)
	 *  setting_name      - name of the main setting
	 *  id                - key of the setting
	 *  options           - array of options for the radio buttons
	 *  current           - current value of the setting
	 *  disabled          - whether the radio buttons are disabled (optional)
	 *  custom_attributes - custom attributes (optional)
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function radio_button( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );

		if ( ! empty( $options_callback ) ) {
			$options = isset( $options_callback_args ) ? call_user_func_array( $options_callback, $options_callback_args ) : call_user_func( $options_callback );
		}

		foreach ( $options as $key => $label ) {
			printf(
				'<input type="radio" class="radio" id="%1$s[%3$s]" name="%2$s" value="%3$s"%4$s %5$s />',
				esc_attr( $id ),
				esc_attr( $setting_name ),
				esc_attr( $key ),
				checked( $current, $key, false ),
				wp_kses_post( $custom_attributes )
			);
			
			printf(
				'<label for="%1$s[%2$s]"> %3$s</label><br>',
				esc_attr( $id ),
				esc_attr( $key ),
				esc_html( $label )
			);
		}


		// output description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}

	}

	/**
	 * Multiple text element callback.
	 * 
	 * args:
	 *  id                   - key of the setting
	 *  setting_name         - name of the main setting
	 *  fields_callback      - callback function to get the fields
	 *  fields_callback_args - arguments for the fields callback (optional)
	 *  current              - current values of the setting
	 *  header               - header for the table (optional)
	 *  description          - description for the table (optional)
	 *  custom_attributes    - custom attributes for the input fields (optional)
	 * 
	 * @param  array $args Field arguments.
	 * @return void
	 */
	public function multiple_text_input( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );

		if ( ! empty( $fields_callback ) ) {
			$fields = isset( $fields_callback_args ) ? call_user_func_array( $fields_callback, $fields_callback_args ) : call_user_func( $fields_callback );
		}

		printf( '<table class="%s multiple-text-input">', esc_attr( $id ) );
		
		if ( ! empty( $header ) ) {
			echo wp_kses_post( "<tr><td><strong>{$header}</strong>:</td></tr>" );
		}
		
		foreach ( $fields as $name => $field ) {
			echo '<tr>';
			
			$size              = $field['size'];
			$placeholder       = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
			$field_description = ! empty( $field['description'] ) ? $field['description']: '';

			// output field label
			if ( isset( $field['label'] ) ) {
				printf(
					'<td class="label"><label for="%1$s_%2$s">%3$s:</label></td>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_html( $field['label'] )
				);
			} else {
				echo '<td></td>';
			}
			
			$field_current = isset( $current[ $name ] ) ? $current[ $name ] : '';
			$type          = isset( $field['type'] ) ? $field['type'] : 'text';
			
			// output field
			printf(
				'<td><input type="%1$s" id="%2$s_%4$s" name="%3$s[%4$s]" value="%5$s" size="%6$s" placeholder="%7$s" %8$s/></td>',
				esc_attr( $type ),
				esc_attr( $id ),
				esc_attr( $setting_name ),
				esc_attr( $name ),
				esc_attr( $field_current ),
				esc_attr( $size ),
				esc_attr( $placeholder ),
				wp_kses_post( $custom_attributes )
			);

			// field description.
			if ( ! empty( $field_description ) ) {
				echo '<td>' . wp_kses_post( wc_help_tip( $field_description, true ) ) . '</td>';
			} else {
				echo '<td></td>';
			}
			echo '</tr>';
		}
		echo "</table>";

		// group description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}
	}

	/**
	 * Multiple text element callback.
	 * 
	 * args:
	 *  id                   - key of the setting
	 *  setting_name         - name of the main setting
	 *  fields_callback      - callback function to get the fields
	 *  fields_callback_args - arguments for the fields callback (optional)
	 *  current              - current values of the setting
	 *  header               - header for the table (optional)
	 *  description          - description for the table (optional)
	 *  custom_attributes    - custom attributes for the input fields (optional)
	 * 
	 * @param  array $args Field arguments.
	 * @return void
	 */
	public function multiple_checkboxes( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );

		if ( ! empty( $fields_callback ) ) {
			$fields = isset( $fields_callback_args ) ? call_user_func_array( $fields_callback, $fields_callback_args ) : call_user_func( $fields_callback );
		}

		foreach ( $fields as $name => $label ) {
			$field_current = isset( $current[ $name ] ) ? $current[ $name ] : '';
			
			// output checkbox
			printf(
				'<input type="checkbox" id="%1$s_%3$s" name="%2$s[%3$s]" value="%4$s"%5$s %6$s/>',
				esc_attr( $id ),
				esc_attr( $setting_name ),
				esc_attr( $name ),
				esc_attr( $value ),
				checked( $value, $field_current, false ),
				wp_kses_post( $custom_attributes )
			);

			// output field label
			printf(
				'<label for="%1$s_%2$s">%3$s</label><br>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_html( $label )
			);

		}

		// output description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}
	}

	/**
	 * Media upload callback.
	 * 
	 * args:
	 *  id                   - key of the setting
	 *  setting_name         - name of the main setting
	 *  current              - current value of the setting
	 *  uploader_title       - title of the media uploader
	 *  uploader_button_text - text of the media uploader button
	 *  remove_button_text   - text of the remove button
	 *  description          - description for the media upload field
	 *  custom_attributes    - custom attributes for the input field
	 *
	 * @param  array $args Field arguments.
	 * @return void
	 */
	public function media_upload( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );

		$setting_name = $this->append_language( $setting_name, $args );
		$attachment   = ! empty( $current ) ? wp_get_attachment_image_src( $current, 'full', false ) : '';

		if ( ! empty( $attachment ) ) {
			$general_settings  = get_option( 'wpo_wcpdf_settings_general', array() );
			$attachment_src    = $attachment[0];
			$attachment_width  = $attachment[1];
			$attachment_height = $attachment[2];
			
			// check if we have the height saved on settings
			$header_logo_height = ! empty( $general_settings['header_logo_height'] ) ? $general_settings['header_logo_height'] : '3cm';
			
			if ( false !== stripos( $header_logo_height, 'mm' ) ) {
				$in_height = floatval( $header_logo_height ) / 25.4;
			} elseif ( false !== stripos( $header_logo_height, 'cm' ) ) {
				$in_height = floatval( $header_logo_height ) / 2.54;
			} elseif ( false !== stripos( $header_logo_height, 'in' ) ) {
				$in_height = floatval( $header_logo_height );
			} else {
				// don't display resolution
			}

			/**
			 * .webp support can be disabled but still showing the image in settings.
			 * We should add a notice because this will display an error when redering the PDF using DOMPDF.
			 */
			if ( 'webp' === wp_check_filetype( $attachment_src )['ext'] && ! function_exists( 'imagecreatefromwebp' ) ) {
				printf(
					'<div class="notice notice-warning inline" style="display:inline-block; width:auto;"><p>%s</p></div>',
					wp_kses_post(
						/* translators: %1$s: file type, %2$s: System Configurations, %3$s: Advanced */
						__( 'File type %1$s is not supported by your server! Please check your %2$s under the %3$s tab.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<strong>webp</strong>',
						'<strong>' . __( 'System Configurations', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong>',
						'<strong>' . __( 'Advanced', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong>'
					)
				);
			}

			printf(
				'<img src="%1$s" style="display:block" id="img-%2$s" class="media-upload-preview"/>',
				esc_attr( $attachment_src ),
				esc_attr( $id )
			);
			
			if ( ! empty( $attachment_height ) && ! empty( $in_height ) ) {
				$attachment_resolution = round( absint( $attachment_height ) / $in_height );
				
				printf(
					'<div class="attachment-resolution"><p class="description">%s: %sdpi</p></div>',
					esc_html__( 'Image resolution', 'woocommerce-pdf-invoices-packing-slips' ),
					esc_html( $attachment_resolution )
				);

				// warn the user if the image is unnecessarily large
				if ( $attachment_resolution > 600 ) {
					printf(
						'<div class="attachment-resolution-warning notice notice-warning inline"><p>%s</p></div>',
						esc_html__( 'The image resolution exceeds the recommended maximum of 600dpi. This will unnecessarily increase the size of your PDF files and could negatively affect performance.', 'woocommerce-pdf-invoices-packing-slips' )
					);
				}
			}

			printf(
				'<span class="button wpo_remove_image_button" data-input_id="%1$s">%2$s</span> ',
				esc_attr( $id ),
				esc_attr( $remove_button_text )
			);
		}

		printf(
			'<input id="%1$s" name="%2$s" type="hidden" value="%3$s" data-settings_callback_args="%4$s" data-ajax_nonce="%5$s" class="media-upload-id"/>',
			esc_attr( $id ),
			esc_attr( $setting_name ),
			esc_attr( $current ),
			esc_attr( wp_json_encode( $args ) ),
			esc_attr( wp_create_nonce( 'wpo_wcpdf_get_media_upload_setting_html' ) )
		);

		printf(
			'<span class="button wpo_upload_image_button %4$s" data-uploader_title="%1$s" data-uploader_button_text="%2$s" data-remove_button_text="%3$s" data-input_id="%4$s">%2$s</span>',
			esc_attr( $uploader_title ),
			esc_attr( $uploader_button_text ),
			esc_attr( $remove_button_text ),
			esc_attr( $id )
		);

		// Displays option description.
		if ( ! empty( $description ) ) {
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}
	}

	/**
	 * Next document number edit callback.
	 * 
	 * args:
	 *  store               - name of the store (e.g. 'invoice', 'packing_slip')
	 *  size                - size of the input field (optional)
	 *  description         - description of the field (optional)
	 *  store_callback      - callback function to get the store (optional)
	 *  store_callback_args - arguments for the store callback (optional)
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function next_number_edit( array $args ): void {
		extract( $args ); // $store, $size, $description

		if ( ! empty( $store_callback ) ) {
			$store = isset( $store_callback_args ) ? call_user_func_array( $store_callback, $store_callback_args ) : call_user_func( $store_callback );
		}

		// SequentialNumberStore object
		if ( is_object( $store ) ) {
			$next_number         = $store->get_next();
			$store               = $store->store_name;
		// legacy
		} else {
			$number_store_method = WPO_WCPDF()->settings->get_sequential_number_store_method();
			$number_store        = new SequentialNumberStore( $store, $number_store_method );
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
			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $description )
			);
		}
	}

	/**
	 * Wrapper function to create tabs for settings in different languages
	 * 
	 * args:
	 *  option_name       - name of the main option
	 *  id                - key of the setting
	 *  callback          - callback function to render the fields
	 *  fields            - array of fields to render (optional, used for multiple_text_input)
	 *  i18n_description  - description for the internationalized fields
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function i18n_wrap( array $args ): void {
		extract( $this->normalize_settings_args( $args ) );

		$languages = wpo_wcpdf_get_multilingual_languages();

		if ( ! empty( $languages ) ) {
			printf(
				'<div id="%s-%s-translations" class="translations">',
				esc_attr( $option_name ),
				esc_attr( $id )
			);
			?>
					<ul>
						<?php
							foreach ( $languages as $lang_code => $language_name ) {
								$translation_id = "{$option_name}_{$id}_{$lang_code}";
								
								printf(
									'<li><a href="#%s">%s</a></li>',
									esc_attr( $translation_id ),
									esc_html( $language_name )
								);
							}
						?>
					</ul>
					<?php
						foreach ( $languages as $lang_code => $language_name ) {
							$translation_id = "{$option_name}_{$id}_{$lang_code}";
							
							printf(
								'<div id="%s">',
								esc_attr( $translation_id )
							);
							
							$args['lang'] = $lang_code;
							
							// don't use internationalized placeholders since they're not translated,
							// to avoid confusion (user thinking they're all the same)
							if ( 'multiple_text_input' === $callback ) {
								foreach ( $fields as $key => $field_args ) {
									if ( ! empty( $field_args['placeholder'] ) && isset( $field_args['i18n_placeholder'] ) ) {
										$args['fields'][$key]['placeholder'] = '';
									}
								}
							} else {
								if ( ! empty( $args['placeholder'] ) && isset( $args['i18n_placeholder'] ) ) {
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

	/**
	 * Normalize settings arguments.
	 *
	 * @param array $args Field arguments.
	 * @return array
	 */
	public function normalize_settings_args( array $args ): array {
		$args['value']           = isset( $args['value'] ) ? $args['value'] : 1;
		$args['placeholder']     = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
		$args['store_unchecked'] = isset( $args['store_unchecked'] ) && $args['store_unchecked'] ? true : false;

		// Get main settings array
		$option = get_option( $args['option_name'] );

		if ( empty( $args['setting_name'] ) ) {
			$args['setting_name'] = "{$args['option_name']}[{$args['id']}]";
		}

		if ( ! isset( $args['lang'] ) && ! empty( $args['translatable'] ) ) {
			$args['lang'] = 'default';
		}

		if ( ! array_key_exists( 'current', $args ) ) {
			if ( isset( $args['lang'] ) ) {
				// i18n settings name
				$args['setting_name'] = "{$args['setting_name']}[{$args['lang']}]";
				// Copy current option value if set

				if ( 'default' === $args['lang'] && ! empty( $option[ $args['id'] ] ) && ! isset( $option[ $args['id'] ]['default'] ) ) {
					// We're switching back from WPML to normal
					// Try English first
					if ( isset( $option[ $args['id'] ]['en'] ) ) {
						$args['current'] = $option[ $args['id'] ]['en'];
						
					} elseif ( is_array( $option[ $args['id'] ] ) ) {
						// Fallback to the first language if English not found
						$first = array_shift( $option[ $args['id'] ] );
						if ( ! empty( $first ) ) {
							$args['current'] = $first;
						}
						
					} elseif ( is_string( $option[ $args['id'] ] ) ) {
						$args['current'] = $option[ $args['id'] ];
						
					} else {
						// Nothing, really?
						$args['current'] = '';
					}
					
				} else {
					if ( isset( $option[ $args['id'] ][ $args['lang'] ] ) ) {
						$args['current'] = $option[ $args['id'] ][ $args['lang'] ];
						
					} elseif ( isset( $option[ $args['id'] ]['default'] ) ) {
						$args['current'] = $option[ $args['id'] ]['default'];
						
					} elseif ( isset( $option[ $args['id'] ] ) && ! is_array( $option[ $args['id'] ] ) ) {
						$args['current'] = $option[ $args['id'] ];
					}
					
				}
			} else {
				// Copy current option value if set
				if ( isset( $option[ $args['id'] ] ) ) {
					$args['current'] = $option[ $args['id'] ];
				}
			}
		}

		// Fallback to default or empty if no value in option
		if ( ! isset( $args['current'] ) ) {
			$args['current'] = isset( $args['default'] ) ? $args['default'] : '';
			
		} elseif ( empty( $args['current'] ) && isset( $args['default_if_empty'] ) && true === $args['default_if_empty'] ) {
			// Force fallback if empty 'current' and 'default_if_empty' equals to true
			$args['current'] = isset( $args['default'] ) ? $args['default'] : '';
		}
		
		// Normalize custom attributes
		$args['custom_attributes'] = $this->normalize_custom_attributes( $args );

		return $args;
	}
	
	/**
	 * Normalize custom attributes.
	 *
	 * @param array $args Field arguments.
	 * @return string
	 */
	public function normalize_custom_attributes( array $args ): string {
		$custom_attributes = array();

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		return ! empty( $custom_attributes ) ? implode( ' ', $custom_attributes ) : '';
	}

	/**
	 * Validate options.
	 *
	 * @param array|false|null $input Options to validate.
	 * @return array Validated options.
	 */
	public function validate( $input ): array {
		$output = array(); // Create our array for storing the validated options.

		if ( ! empty( $input ) && is_array( $input ) ) {
			if ( ! empty( $input['wpo_wcpdf_setting_store_empty'] ) && is_array( $input['wpo_wcpdf_setting_store_empty'] ) ) {
				foreach ( $input['wpo_wcpdf_setting_store_empty'] as $key ) {
					if ( empty( $input[ $key ] ) ) {
						$output[ $key ] = 0;
					}
				}
				unset( $input['wpo_wcpdf_setting_store_empty'] );
			}
			
			// Loop through each of the incoming options.
			foreach ( $input as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $sub_key => $sub_value ) {
						$output[ $key ][ $sub_key ] = $sub_value;
					}
				} else {
					// Normalize identifiers like VAT / CoC on save.
					if ( in_array( $key, array( 'vat_number', 'coc_number' ), true ) ) {
						$value = $this->normalize_identifier( (string) $value );
					}

					$output[ $key ] = $value;
				}
			}
		}

		// Return the array processing any additional functions filtered by this action.
		return apply_filters( 'wpo_wcpdf_validate_input', $output, $input );
	}

	/**
	 * Appends language at the end of the setting provided, in case the setting is translatable
	 * and it does not have a language set.
	 *
	 * @param string $setting Settings field that needs a language.
	 * @param array  $args Setting arguments.
	 *
	 * @return string
	 */
	public function append_language( string $setting, array $args ): string {
		if (
			isset( $args['translatable'] ) &&
			true === $args['translatable'] &&
			isset( $args['lang'] )         &&
			'default' !== $args['lang']    &&
			! ( substr( $setting, -strlen( "[{$args['lang']}]" ) ) === "[{$args['lang']}]" )
		) {
			return $setting .= "[{$args['lang']}]";
		} else {
			return $setting;
		}
	}

	/**
	 * Output the action button.
	 *
	 * @param array $action_button
	 * @param string $id
	 *
	 * @return void
	 */
	private function output_action_button( array $action_button, string $id ): void {
		printf(
			'<button type="button" %1$s %2$s %3$s>%4$s%5$s</button><span class="sync-tooltip"></span>',
			! empty( $action_button['class'] ) ? sprintf( 'class="%s"', esc_attr( $action_button['class'] ) ) : '',
			sprintf( 'id="%s"', esc_attr( $action_button['id'] ?? esc_attr( $id ) ) . '_action' ),
			! empty( $action_button['title'] ) ? sprintf( 'title="%s"', esc_attr( $action_button['title'] ) ) : '',
			esc_html( $action_button['text'] ),
			! empty( $action_button['icon'] ) ? sprintf( '<span class="dashicons dashicons-%s"></span>', esc_attr( $action_button['icon'] ) ) : ''
		);
	}
	
	/**
	 * Normalize identifier-like values (VAT / CoC).
	 *
	 * @param string $value
	 * @return string
	 */
	protected function normalize_identifier( string $value ): string {
		$value = wp_strip_all_tags( $value );
		$value = trim( $value );

		// Uppercase for consistency (VAT formats often expect it).
		$value = strtoupper( $value );

		// Keep only A-Z and 0-9, strip spaces, dots, dashes, etc.
		$value = preg_replace( '/[^A-Z0-9]/', '', $value );

		return $value;
	}
	
}

endif; // class_exists
