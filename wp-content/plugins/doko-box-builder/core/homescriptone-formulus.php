<?php

namespace HS\Doko;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! function_exists( 'formulus_input_fields' ) ) {
	/**
	 * Generate appropriate fields for meta and page in WordPress.
	 *
	 * @param string $key Key.
	 * @param mixed  $args Arguments.
	 * @param string $value (default: null).
	 *
	 * @return mixed
	 */
	function formulus_input_fields( $key, $args, $value = null, $id_key = false ) {
		$id = '';
		if ( $id_key ) {
			$id = $id_key;
		}

		$defaults = array(
			'type'              => 'text',
			'label'             => '',
			'description'       => '',
			'placeholder'       => '',
			'maxlength'         => false,
			'required'          => false,
			'autocomplete'      => false,
			'id'                => $id,
			'class'             => array(),
			'label_class'       => array(),
			'input_class'       => array(),
			'return'            => true,
			'options'           => array(),
			'custom_attributes' => array(),
			'validate'          => array(),
			'default'           => '',
			'autofocus'         => '',
			'priority'          => '',
			'min'               => '',
			'max'               => '',
			'disabled_options'  => []
		);

		$args = wp_parse_args( $args, $defaults );
		/**
		 * Filter the arguments for a form field.
		 *
		 * @since 1.0.0
		 */
		$args     = apply_filters( 'formulus_form_field_args', $args, $key, $value );
		$required = '';
		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'doko-bundle-builder' ) . '">*</abbr>';
		}

		if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = array( $args['label_class'] );
		}

		if ( is_null( $value ) || empty( $value ) ) {
			$value = $args['default'];
		}

		$counter = 0;
		$limit   = 0;

		if ( ! isset( $args['textarea_class'] ) ) {
			$args['textarea_class'] = array();
		}

		// Custom attribute handling.
		$custom_attributes         = array();
		$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

		if ( $args['maxlength'] ) {
			$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
		}

		if ( ! empty( $args['autocomplete'] ) ) {
			$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
		}

		if ( true === $args['autofocus'] ) {
			$args['custom_attributes']['autofocus'] = 'autofocus';
		}

		if ( $args['description'] ) {
			$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
		}

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		if ( ! empty( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}

		$field    = '';
		$label_id = $args['id'];
		$sort     = $args['priority'] ? $args['priority'] : '';
		if ( isset( $custom_attributes['wrapper'] ) ) {
			$field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</p><br/>';
		} else {
			$field_container = '%3$s';
		}

		switch ( $args['type'] ) {
			case 'color':
				$field .= "<input type='color' />";
				break;
			case 'textarea':
				$field_container = '<div class="form-row %1$s ' . esc_attr( implode( ' ', $args['textarea_class'] ) ) . '" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</div><br/>';
				$n_time          = 1;
				if ( isset( $args['n_display'] ) ) {
					$n_time  = $args['n_display'];
					$limit   = $n_time;
					$counter = 1;
				}
				while ( $counter <= $limit ) {
					if ( isset( $args['multiple_values'] ) ) {
						$value = $args['multiple_values'][ $counter - 1 ];
					}
					$field .= '<textarea name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . ' data-id="' . $counter . '">' . esc_textarea( $value ) . '</textarea><br/>';
					++$counter;
				}
				break;
			case 'checkbox':
				$field = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ', $custom_attributes ) . '>
						<input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . checked( $value, 1, false ) . ' /> ' . $args['label'] . $required . '</label>';

				break;
			case 'text':
			case 'password':
			case 'datetime':
			case 'datetime-local':
			case 'date':
			case 'month':
			case 'time':
			case 'week':
			case 'email':
			case 'url':
			case 'tel':
				$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';

				break;
			case 'number':
				$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' min="' . esc_attr( $args['min'] ) . '" max="' . esc_attr( $args['max'] ) . '" />';
				break;
			case 'select':
				$field   = '';
				$options = '';

				if ( isset( $args['options'] ) ) {

					foreach ( $args['options'] as $option_key => $option_text ) {

						$disabled = '';
						if ( isset( $args['disabled_options'] ) && ! empty( $args['disabled_options'] ) ) {
							if ( in_array( $option_key, $args['disabled_options'] ) ) {
								$disabled = "disabled='disabled'";	
							}
						}
						$selected = '';
						if ( '' === $option_key ) {
							// If we have a blank option, select2 needs a placeholder.
							if ( empty( $args['placeholder'] ) ) {
								$args['placeholder'] = $option_text ? $option_text : esc_html__( 'Choose an option', 'doko-bundle-builder' );
							}
							$custom_attributes[] = 'data-allow_clear="true"';
						}

						if ( isset( $args['selected'] ) && ( in_array( $option_key, array_keys( $args['selected'] ) ) || in_array( $option_key, $args['selected'] ) ) ) {
							$selected = 'selected=selected';
						}

						$options .= '<option value="' . esc_attr( $option_key ) . '" ' . $disabled  .' ' . selected( $value, $option_key, false ) . ' ' . $selected . ' >' . esc_attr( $option_text ) . '</option>';
					}

					$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
							' . $options . '
						</select>';
				}

				break;
			case 'radio':
				$label_id .= '_' . current( array_keys( $args['options'] ) );

				if ( ! empty( $args['options'] ) ) {

					foreach ( $args['options'] as $option_key => $option_text ) {
						$field .= '<div> <input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';
						$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) . '">' . $option_text . '</label> </div>';
					}
				}

				break;
			case 'editor':
				$settings = array(
					'textarea_name' => isset( $args['custom_attributes']['name'] ) ? $args['custom_attributes']['name'] : $key,
					'media_buttons' => false,
					'textarea_rows' => 10,
				);
				ob_start();
				wp_editor( $value, $id, $settings );
				\_WP_Editors::enqueue_scripts();
				\_WP_Editors::editor_js();
				print_footer_scripts();
				$field .= ob_get_clean();
				break;
		}

		if ( ! empty( $field ) && isset( $custom_attributes['wrapper'] ) ) {
			$field_html = '';

			if ( $args['label'] && 'checkbox' !== $args['type'] ) {
				$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '"><strong>' . $args['label'] . $required . '</strong></label>';
			}

			$field_html .= '<span class="formulus-input-wrapper">' . $field;

			if ( $args['description'] ) {
				$field_html .= '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
			}

			$field_html .= '</span> ';

			$container_class = esc_attr( implode( ' ', $args['class'] ) );
			$container_id    = esc_attr( $args['id'] ) . '_field';
			$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
		} elseif ( ! empty( $field ) && ! isset( $custom_attributes['wrapper'] ) ) {
			$field_html = $field;
			$container_class = esc_attr( implode( ' ', $args['class'] ) );
			$container_id    = esc_attr( $args['id'] ) . '_field';
			$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
		}

		/**
		 * Filter by type.
		 *
		 * @since 3.4.0
		 */
		$field = apply_filters( 'formulus_form_field_' . $args['type'], $field, $key, $args, $value );

		/**
		 * General filter on form fields.
		 *
		 * @since 3.4.0
		 */
		$field = apply_filters(
			/**
			 * General filter on form fields.
			 *
			 * @since 1.0.0
			 */
			'formulus_form_field',
			$field,
			$key,
			$args,
			$value
		);

		if ( $args['return'] ) {
			return $field;
		} else {
			formulus_format_fields( $field );
		}
	}
}

if ( ! function_exists( 'formulus_input_table' ) ) {
	/**
	 * This method displays input fields into the dashboard.
	 */
	function formulus_input_table( $key, $args, $return = false, $class_name = false ) {
		$html = "<table style='border: 1px solid #c3c4c7;' name='formulus-input-" . $key . "' class='$class_name' > <tbody>";
		/**
		 * Filter before the loop.
		 *
		 * @since 1.0.0
		 */
		$html = apply_filters( 'formulus_before_loop_html', $html, $key, $args );
		foreach ( $args as $arg ) {
			$label_class = '';
			if ( isset( $arg['label_class'] ) ) {
				$label_class = $arg['label_class'];
			}
			$tr_class = '';
			if ( isset( $arg['tr_class'] ) ) {
				$tr_class = $arg['tr_class'];
			}
			$html .= "<tr class='" . $tr_class . "'><td class='" . $label_class . "'>";
			if ( isset( $arg['label'] ) ) {
				$html .= '<strong>' . $arg['label'] . '</strong>';
			}
			if ( isset( $arg['description'] ) ) {
				$html .= '<br/>' . $arg['description'];
			}
			$html .= '</td><td>';
			if ( isset( $arg['content'] ) ) {
				$html .= $arg['content'];
			}
			$html .= '</td></tr>';
		}
		/**
		 * Filter after the loop.
		 *
		 * @since 1.0.0
		 */
		$html  = apply_filters( 'formulus_after_loop_html', $html, $key, $args );
		$html .= '</tbody></table>';
		if ( $return ) {
			return $html;
		} else {
			formulus_format_fields( $html );
		}
	}
}

if ( ! function_exists( 'formulus_format_fields' ) ) {


	function formulus_format_fields( $html_field ) {
		$allowedposttags = array();
		$allowed_atts    = array(
			'align'                   => array(),
			'class'                   => array(),
			'type'                    => array(),
			'id'                      => array(),
			'dir'                     => array(),
			'lang'                    => array(),
			'style'                   => array(),
			'xml:lang'                => array(),
			'src'                     => array(),
			'alt'                     => array(),
			'href'                    => array(),
			'rev'                     => array(),
			'target'                  => array(),
			'novalidate'              => array(),
			'value'                   => array(),
			'name'                    => array(),
			'tabindex'                => array(),
			'action'                  => array(),
			'method'                  => array(),
			'for'                     => array(),
			'width'                   => array(),
			'height'                  => array(),
			'data'                    => array(),
			'title'                   => array(),
			'checked'                 => array(),
			'disabled'                => array(),
			'placeholder'             => array(),
			'rel'                     => array(),
			'data-analytic-id'        => array(),
			'data-id'                 => array(),
			'rows'                    => array(),
			'selected'                => array(),
			'cols'                    => array(),
			'aria-label'              => array(),
			'data-package-mode'       => array(),
			'data-tr-products-name'   => array(),
			'data-tr-categories-name' => array(),
			'multiple'                => array(),
			'data-wp-editor-id'       => array(),
			'data-checkbox-type'      => array(),
			'hidden'                  => array(),
			'data-quantity'           => array(),
			'data-product-id'         => array(),
			'data-product-price'      => array(),
			'data-site-currency'      => array(),
			'data-product-name'       => array(),
			'data-image-url'          => array(),
			'data-bundle-id'          => array(),
			'data-bundle-rule-id'     => array(),
			'data-card-mode'          => array(),
			'min'                     => array(),
			'max'                     => array(),
			'data-tip'                => array(),
			'data-product-url'        => array(),
			'data-currency'           => array(),
			'data-title'              => array(),
			'data-product_sku'        => array(),
			'data-uniqId'             => array(),
			'data-personalisation-price' => array(),
			'data-product-description' => array(),
			'data-product-cat' => array(),
			'data-product-tag' => array(),
			'data-bundle-content-id' => array(),
			'data-row-index' => array()

		);
		$allowedposttags['wp:paragraph'] = $allowed_atts;
		$allowedposttags['form']         = $allowed_atts;
		$allowedposttags['label']        = $allowed_atts;
		$allowedposttags['select']       = $allowed_atts;
		$allowedposttags['input']        = $allowed_atts;
		$allowedposttags['textarea']     = $allowed_atts;
		$allowedposttags['iframe']       = $allowed_atts;
		$allowedposttags['script']       = $allowed_atts;
		$allowedposttags['style']        = $allowed_atts;
		$allowedposttags['strong']       = $allowed_atts;
		$allowedposttags['small']        = $allowed_atts;
		$allowedposttags['table']        = $allowed_atts;
		$allowedposttags['span']         = $allowed_atts;
		$allowedposttags['abbr']         = $allowed_atts;
		$allowedposttags['code']         = $allowed_atts;
		$allowedposttags['pre']          = $allowed_atts;
		$allowedposttags['div']          = $allowed_atts;
		$allowedposttags['img']          = $allowed_atts;
		$allowedposttags['h1']           = $allowed_atts;
		$allowedposttags['h2']           = $allowed_atts;
		$allowedposttags['h3']           = $allowed_atts;
		$allowedposttags['h4']           = $allowed_atts;
		$allowedposttags['h5']           = $allowed_atts;
		$allowedposttags['h6']           = $allowed_atts;
		$allowedposttags['ol']           = $allowed_atts;
		$allowedposttags['ul']           = $allowed_atts;
		$allowedposttags['li']           = $allowed_atts;
		$allowedposttags['em']           = $allowed_atts;
		$allowedposttags['hr']           = $allowed_atts;
		$allowedposttags['br']           = $allowed_atts;
		$allowedposttags['tr']           = $allowed_atts;
		$allowedposttags['th']           = $allowed_atts;
		$allowedposttags['td']           = $allowed_atts;
		$allowedposttags['p']            = $allowed_atts;
		$allowedposttags['a']            = $allowed_atts;
		$allowedposttags['b']            = $allowed_atts;
		$allowedposttags['i']            = $allowed_atts;
		$allowedposttags['s']            = $allowed_atts;
		$allowedposttags['option']       = $allowed_atts;
		$allowedposttags['button']       = $allowed_atts;
		$allowedposttags['link']         = $allowed_atts;
		$allowedposttags['thead']        = $allowed_atts;
		$allowedposttags['tbody']        = $allowed_atts;
		echo wp_kses( $html_field, $allowedposttags );
	}
}



function formulus_generate_repeatable_fields($id, $options, $key_name, $display_head = true, $include_skeleton = false)
{
	ob_start();
	if ($include_skeleton) {
		?>
			<table class="table">
		<?php
	}
	if ($display_head == true) {
		?>
			<thead>
				<tr>
					<?php
					if (isset($options['head'])) {
						foreach ($options['head'] as $thk => $thv) {
							echo "<th name='doko[$key_name][$id][$thk]' class='doko_discounts_thead'>" . $thv . "</th>";
						}
						?>
									<th><?php esc_textarea('Options'); ?></th>
								<?php
					}
					?>
				</tr>
			</thead>
		<?php
	}
	if ($include_skeleton) {
	?>
		<tbody>
	<?php
	}
	?>
	<tr class="doko_tr_element doko_tr_<?php echo esc_attr($id); ?>">
		<?php
			if (isset($options['head'])) {
				foreach ($options['head'] as $thk => $thv) {
					
					if ( isset( $options['body'][$thk] ) ) {
						echo "<td>";
						echo $options['body'][$thk]['content'];
						echo "</td>";
					}
					
				}
			}
		?>
		<td class="doko_<?php echo esc_attr($id); ?>_options_head">
			<button class="button button-secondary doko-remove-rule-row doko-remove-<?php echo esc_attr($id); ?>" style="background-color:red; color:white;">-</button>
		</td>
	</tr>
	<?php
		if ($include_skeleton) {
		?>
			</tbody>
			</table>
	<?php
		}
	return ob_get_clean();
}