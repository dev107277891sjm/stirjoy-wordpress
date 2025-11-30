<?php
/**
* Hooks
*
* @since 3.0
*/
do_action( 'woocommerce_register_form_start' );
/**
* Hooks
*
* @since 3.0
*/
do_action( 'wwp_wholesaler_registration_fields_start', $registrations, $settings );
wp_nonce_field( 'wwp_wholesale_registrattion_nonce', 'wwp_wholesale_registrattion_nonce' );
?>

<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
	<label for="wwp_wholesaler_username"><?php esc_html_e( 'Username', 'woocommerce-wholesale-pricing' ); ?><span class="required">*</span></label>
	<input type="text" name="wwp_wholesaler_username" id="wwp_wholesaler_username" value="<?php esc_attr_e( $username ); ?>" required>
</<?php wwp_elements( 'p' ); ?>>

<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
	<label for="wwp_wholesaler_email"><?php esc_html_e( 'Email', 'woocommerce-wholesale-pricing' ); ?><span class="required">*</span></label>
	<input type="email" name="wwp_wholesaler_email" id="wwp_wholesaler_email" value="<?php esc_attr_e( $email ); ?>" required>
</<?php wwp_elements( 'p' ); ?>>

<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
	<label for="wwp_wholesaler_password"><?php esc_html_e( 'Password', 'woocommerce-wholesale-pricing' ); ?><span class="required">*</span></label>
	<div class="wwp-password-wrapper">
		<input type="password" name="wwp_wholesaler_password" id="wwp_wholesaler_password"  minlength="8" required>
		<span class="wwp-password-toggle">👁️</span>
	</div>
</<?php wwp_elements( 'p' ); ?>>

<?php
if ( isset( $registrations['extra_pass_field'] ) && 'yes' == $registrations['extra_pass_field'] ) {
	?>
	<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
		<label for="reg_password2"><?php esc_html_e( 'Confirm Password', 'woocommerce-wholesale-pricing' ); ?><span class="required">*</span></label>
		<div class="wwp-password-wrapper">
			<input type="password" name="password2" id="reg_password2" minlength="8" required value="">
			<span class="wwp-confirm-password-toggle">👁️</span>
		</div>
	</<?php wwp_elements( 'p' ); ?>>
<?php } ?>

<?php
$has_enabled_billing = false;
$has_enabled_shipping = false;
foreach ( $registrations as $key => $value ) {
	if ( strpos($key, 'enable_billing') === 0 && 'yes' === $value ) {
		$has_enabled_billing = true;
		continue;
	}
	if ( strpos($key, 'enable_shipping') === 0 && 'yes' === $value ) {
		$has_enabled_shipping = true;
		break;
	}
}

if ( $has_enabled_billing && ( isset( $registrations['custommer_billing_address'] ) && 'yes' == $registrations['custommer_billing_address'] ) ) {
	?>
	<h2><?php esc_html_e( 'Customer billing address', 'woocommerce-wholesale-pricing' ); ?></h2>
	<?php
	if ( isset( $registrations['enable_billing_first_name'] ) && 'yes' == $registrations['enable_billing_first_name'] ) {
		$fname_label = esc_html__('First Name', 'woocommerce-wholesale-pricing');
		if ( ! empty($registrations['billing_first_name']) ) {
			$fname_label = $registrations['billing_first_name'];
		}
		?>
		<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
			<label for="wwp_wholesaler_fname"> <?php esc_attr_e($fname_label); ?> <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_first_name'] ) ? $registrations['required_billing_first_name'] : '', 'span' ); ?></label>
			<input type="text" name="wwp_wholesaler_fname" id="wwp_wholesaler_fname" value="<?php esc_attr_e( $fname ); ?>" <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_first_name'] ) ? $registrations['required_billing_first_name'] : '' ); ?>>
		</<?php wwp_elements( 'p' ); ?>>

		<?php
	}
	if ( isset( $registrations['enable_billing_last_name'] ) && 'yes' == $registrations['enable_billing_last_name'] ) {
		$lname_label = esc_html__('Last Name', 'woocommerce-wholesale-pricing');
		if ( ! empty($registrations['billing_last_name']) ) {
			$lname_label = $registrations['billing_last_name'];
		}
		?>
		<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
			<label for="wwp_wholesaler_lname"><?php esc_attr_e( $lname_label ); ?> <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_last_name'] ) ? $registrations['required_billing_last_name'] : '', 'span' ); ?></label>
			<input type="text" name="wwp_wholesaler_lname" id="wwp_wholesaler_lname" value="<?php esc_attr_e( $lname ); ?>" <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_last_name'] ) ? $registrations['required_billing_last_name'] : '' ); ?>>
		</<?php wwp_elements( 'p' ); ?>>

		<?php
	}
	if ( isset( $registrations['enable_billing_company'] ) && 'yes' == $registrations['enable_billing_company'] ) {
		$billing_company_label = esc_html__('Company', 'woocommerce-wholesale-pricing');
		if ( ! empty($registrations['billing_company']) ) {
			$billing_company_label = $registrations['billing_company'];
		}
		?>
		<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
			<label for="wwp_wholesaler_company"><?php esc_attr_e( $billing_company_label ); ?> <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_company'] ) ? $registrations['required_billing_company'] : '', 'span' ); ?></label>
			<input type="text" name="wwp_wholesaler_company" id="wwp_wholesaler_company" value="<?php esc_attr_e( $company ); ?>"  <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_company'] ) ? $registrations['required_billing_company'] : '' ); ?>>
		</<?php wwp_elements( 'p' ); ?>>

		<?php
	}
	if ( isset( $registrations['enable_billing_address_1'] ) && 'yes' == $registrations['enable_billing_address_1'] ) {
		$billing_address_1_label = esc_html__('Address line 1', 'woocommerce-wholesale-pricing');
		if ( ! empty($registrations['billing_address_1']) ) {
			$billing_address_1_label = $registrations['billing_address_1'];
		}
		?>
		<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
			<label for="wwp_wholesaler_address_line_1"><?php esc_attr_e( $billing_address_1_label ); ?> <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_address_1'] ) ? $registrations['required_billing_address_1'] : '', 'span' ); ?></label>
			<input type="text" name="wwp_wholesaler_address_line_1" id="wwp_wholesaler_address_line_1" value="<?php esc_attr_e( $addr1 ); ?>" <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_address_1'] ) ? $registrations['required_billing_address_1'] : '' ); ?>>
		</<?php wwp_elements( 'p' ); ?>>

		<?php
	}
	if ( isset( $registrations['enable_billing_address_2'] ) && 'yes' == $registrations['enable_billing_address_2'] ) {
		$billing_address_2_label = esc_html__('Address line 2 (optional)', 'woocommerce-wholesale-pricing');
		if ( ! empty($registrations['billing_address_2']) ) {
			$billing_address_2_label = $registrations['billing_address_2'];
		}
		?>
		<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
			<label for="wwp_wholesaler_address_line_2"><?php esc_attr_e( $billing_address_2_label ); ?><?php wwp_registration_field_required_attr( isset( $registrations['required_billing_address_2'] ) ? $registrations['required_billing_address_2'] : '', 'span' ); ?></label>
			<input type="text" name="wwp_wholesaler_address_line_2" id="wwp_wholesaler_address_line_2" value="<?php esc_attr_e( $wwp_wholesaler_address_line_2 ); ?>" <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_address_2'] ) ? $registrations['required_billing_address_2'] : '' ); ?>>
		</<?php wwp_elements( 'p' ); ?>>

		<?php
	}
	if ( isset( $registrations['enable_billing_city'] ) && 'yes' == $registrations['enable_billing_city'] ) {
		$billing_city_label = esc_html__('City', 'woocommerce-wholesale-pricing');
		if ( ! empty($registrations['billing_city']) ) {
			$billing_city_label = $registrations['billing_city'];
		}
		?>
		<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
			<label for="wwp_wholesaler_city"><?php esc_attr_e( $billing_city_label ); ?><?php wwp_registration_field_required_attr( isset( $registrations['required_billing_city'] ) ? $registrations['required_billing_city'] : '', 'span' ); ?></label>
			<input type="text" name="wwp_wholesaler_city" id="wwp_wholesaler_city" value="<?php esc_attr_e( $wwp_wholesaler_city ); ?>" <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_city'] ) ? $registrations['required_billing_city'] : '' ); ?>>
		</<?php wwp_elements( 'p' ); ?>>

		<?php
	}
	if ( isset( $registrations['enable_billing_post_code'] ) && 'yes' == $registrations['enable_billing_post_code'] ) {
		$billing_post_code_label = esc_html__('Postcode / ZIP', 'woocommerce-wholesale-pricing');
		if ( ! empty($registrations['billing_post_code']) ) {
			$billing_post_code_label = $registrations['billing_post_code'];
		}
		?>
		<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
			<label for="wwp_wholesaler_post_code"><?php esc_attr_e( $billing_post_code_label ); ?> <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_post_code'] ) ? $registrations['required_billing_post_code'] : '', 'span' ); ?></label>
			<input type="text" name="wwp_wholesaler_post_code" id="wwp_wholesaler_post_code" value="<?php esc_attr_e( $wwp_wholesaler_post_code ); ?>" <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_post_code'] ) ? $registrations['required_billing_post_code'] : '' ); ?>>
		</<?php wwp_elements( 'p' ); ?>>
		<?php
	}
	?>
	<div class="parent">
	<?php
	if ( isset( $registrations['enable_billing_country'] ) && 'yes' == $registrations['enable_billing_country'] ) {
		$billing_countries_label = esc_html__('Select billing country', 'woocommerce-wholesale-pricing');
		if ( ! empty($registrations['billing_countries']) ) {
			$billing_countries_label = $registrations['billing_countries'];
		}
		?>
		<!-- <<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>"> -->
		<?php
			woocommerce_form_field(
				'billing_country',
				array(
					'type'        => 'country',
					'class'       => array( 'chzn-drop', 'wwp-billing-field' ),
					'label'       => $billing_countries_label,
					'default'     => $default_country,
					'options'     => $countries,
					'required'    => ( isset( $registrations['required_billing_country'] ) && ( 'yes' == $registrations['required_billing_country'] ) ? true : false ),
				)
			);
		?>
		<!-- </<?php wwp_elements( 'p' ); ?>> -->

		<?php
	}

	if ( isset( $registrations['enable_billing_state'] ) && 'yes' == $registrations['enable_billing_state'] ) {
		$billing_state_label = esc_html__('State / County or state code', 'woocommerce-wholesale-pricing');
		if ( ! empty($registrations['billing_state']) ) {
			$billing_state_label = $registrations['billing_state'];
		}
		?>
		<!-- <<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>"> -->
			<?php
			woocommerce_form_field(
				'billing_state',
				array(
					'type'       => 'state',
					'class'      => array( 'chzn-drop', 'wwp-check-field-type' ),
					'label'      => $billing_state_label,
					'options'     => $default_county_states,
					'required'    => ( isset( $registrations['required_billing_state'] ) && ( 'yes' == $registrations['required_billing_state'] ) ? true : false ),
					)
				);
			?>
		<!-- </<?php wwp_elements( 'p' ); ?>> -->

		<?php
	}
	?>
		</div>
	<?php
	if ( isset( $registrations['enable_billing_phone'] ) && 'yes' == $registrations['enable_billing_phone'] ) {
		$billing_phone_label = esc_html__('Phone', 'woocommerce-wholesale-pricing');
		if ( ! empty($registrations['billing_phone']) ) {
			$billing_phone_label = $registrations['billing_phone'];
		}
		?>
		<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
			<label for="wwp_wholesaler_phone"><?php esc_attr_e( $billing_phone_label ); ?> <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_phone'] ) ? $registrations['required_billing_phone'] : '', 'span' ); ?></label>
			<input type="text" name="wwp_wholesaler_phone" id="wwp_wholesaler_phone" value="<?php esc_attr_e( $wwp_wholesaler_phone ); ?>"  <?php wwp_registration_field_required_attr( isset( $registrations['required_billing_phone'] ) ? $registrations['required_billing_phone'] : '' ); ?>>
		</<?php wwp_elements( 'p' ); ?>>

		<?php
	}
}
if ( $has_enabled_shipping && ( isset( $registrations['custommer_shipping_address'] ) && 'yes' == $registrations['custommer_shipping_address'] ) ) {
	?>
	<h2><?php esc_html_e( 'Customer shipping address', 'woocommerce-wholesale-pricing' ); ?></h2>
	<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row wwp-shipping-address' ) ); ?>">
		<label for="wwp_wholesaler_copy_billing_address"><?php esc_html_e( 'Copy from billing address', 'woocommerce-wholesale-pricing' ); ?></label>
		<input type="checkbox" name="wwp_wholesaler_copy_billing_address" id="wwp_wholesaler_copy_billing_address" value="yes" >
	</<?php wwp_elements( 'p' ); ?>>
	<div id="wholesaler_shipping_address">
		<?php
		if ( isset( $registrations['enable_shipping_first_name'] ) && 'yes' == $registrations['enable_shipping_first_name'] ) {
			$shipping_first_name_label = esc_html__('First Name', 'woocommerce-wholesale-pricing');
			if ( ! empty($registrations['shipping_first_name']) ) {
				$shipping_first_name_label = $registrations['shipping_first_name'];
			}
			?>
			<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
				<label for="wwp_wholesaler_shipping_lname"><?php esc_attr_e( $shipping_first_name_label ); ?> <?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_first_name'] ) ? $registrations['required_shipping_first_name'] : '', 'span' ); ?></label>
				<input type="text" name="wwp_wholesaler_shipping_fname" id="wwp_wholesaler_shipping_fname"value="<?php esc_attr_e( $wwp_wholesaler_shipping_fname ); ?>" <?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_first_name'] ) ? $registrations['required_shipping_first_name'] : '' ); ?>>
			</<?php wwp_elements( 'p' ); ?>>

			<?php
		}
		if ( isset( $registrations['enable_shipping_last_name'] ) && 'yes' == $registrations['enable_shipping_last_name'] ) {
			$shipping_last_name_label = esc_html__('Last Name', 'woocommerce-wholesale-pricing');
			if ( ! empty($registrations['shipping_last_name']) ) {
				$shipping_last_name_label = $registrations['shipping_last_name'];
			}
			?>
			<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
				<label for="wwp_wholesaler_shipping_fname"> <?php esc_attr_e($shipping_last_name_label); ?> <?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_last_name'] ) ? $registrations['required_shipping_last_name'] : '', 'span' ); ?> </label>
				<input type="text" name="wwp_wholesaler_shipping_lname" id="wwp_wholesaler_shipping_lname"value="<?php esc_attr_e( $wwp_wholesaler_shipping_lname ); ?>"  <?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_last_name'] ) ? $registrations['required_shipping_last_name'] : '' ); ?>>
			</<?php wwp_elements( 'p' ); ?>>

			<?php
		}
		if ( isset( $registrations['enable_shipping_company'] ) && 'yes' == $registrations['enable_shipping_company'] ) {
			$shipping_company_label = esc_html__('Company', 'woocommerce-wholesale-pricing');
			if ( ! empty($registrations['shipping_company']) ) {
				$shipping_company_label = $registrations['shipping_company'];
			}
			?>
			<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
				<label for="wwp_wholesaler_shipping_company"><?php esc_attr_e( $shipping_company_label ); ?> <?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_company'] ) ? $registrations['required_shipping_company'] : '', 'span' ); ?></label>
				<input type="text" name="wwp_wholesaler_shipping_company" id="wwp_wholesaler_shipping_company" value="<?php esc_attr_e( $wwp_wholesaler_shipping_company ); ?>"  <?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_company'] ) ? $registrations['required_shipping_company'] : '' ); ?>>
			</<?php wwp_elements( 'p' ); ?>>

			<?php
		}
		if ( isset( $registrations['enable_shipping_address_1'] ) && 'yes' == $registrations['enable_shipping_address_1'] ) {
			$shipping_address_1_label = esc_html__('Address line 1', 'woocommerce-wholesale-pricing');
			if ( ! empty($registrations['shipping_address_1']) ) {
				$shipping_address_1_label = $registrations['shipping_address_1'];
			}
			?>
			<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
				<label for="wwp_wholesaler_shipping_address_line_1"><?php esc_attr_e( $shipping_address_1_label ); ?> <?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_address_1'] ) ? $registrations['required_shipping_address_1'] : '', 'span' ); ?></label>
				<input type="text" name="wwp_wholesaler_shipping_address_line_1" id="wwp_wholesaler_shipping_address_line_1" value="<?php esc_attr_e( $wwp_wholesaler_shipping_address_line_1 ); ?>"  <?php wwp_registration_field_required_attr( isset( $registrations['shipping_address_1'] ) ? $registrations['shipping_address_1'] : '' ); ?>>
			</<?php wwp_elements( 'p' ); ?>>

			<?php
		}
		if ( isset( $registrations['enable_shipping_address_2'] ) && 'yes' == $registrations['enable_shipping_address_2'] ) {
			$shipping_address_2_label = esc_html__('Address line 2 (optional)', 'woocommerce-wholesale-pricing');
			if ( ! empty($registrations['shipping_address_2']) ) {
				$shipping_address_2_label = $registrations['shipping_address_2'];
			}
			?>
			<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
				<label for="wwp_wholesaler_shipping_address_line_2"><?php esc_attr_e( $shipping_address_2_label ); ?><?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_address_2'] ) ? $registrations['required_shipping_address_2'] : '', 'span' ); ?></label>
				<input type="text" name="wwp_wholesaler_shipping_address_line_2" id="wwp_wholesaler_shipping_address_line_2" value="<?php esc_attr_e( $wwp_wholesaler_shipping_address_line_2 ); ?>"  <?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_address_2'] ) ? $registrations['required_shipping_address_2'] : '' ); ?>>
			</<?php wwp_elements( 'p' ); ?>>

			<?php
		}
		if ( isset( $registrations['enable_shipping_city'] ) && 'yes' == $registrations['enable_shipping_city'] ) {
			$shipping_city_label = esc_html__('City', 'woocommerce-wholesale-pricing');
			if ( ! empty($registrations['shipping_city']) ) {
				$shipping_city_label = $registrations['shipping_city'];
			}
			?>
			<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
				<label for="wwp_wholesaler_shipping_city"><?php esc_attr_e( $shipping_city_label ); ?> <?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_city'] ) ? $registrations['required_shipping_city'] : '', 'span' ); ?></label>
				<input type="text" name="wwp_wholesaler_shipping_city" id="wwp_wholesaler_shipping_city" value="<?php esc_attr_e( $wwp_wholesaler_shipping_city ); ?>"  <?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_city'] ) ? $registrations['required_shipping_city'] : '' ); ?>>
			</<?php wwp_elements( 'p' ); ?>>

			<?php
		}
		if ( isset( $registrations['enable_shipping_post_code'] ) && 'yes' == $registrations['enable_shipping_post_code'] ) {
			$shipping_post_code_label = esc_html__('Postcode / ZIP', 'woocommerce-wholesale-pricing');
			if ( ! empty($registrations['shipping_post_code']) ) {
				$shipping_post_code_label = $registrations['shipping_post_code'];
			}
			?>
			<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
				<label for="wwp_wholesaler_shipping_post_code"><?php esc_attr_e( $shipping_post_code_label ); ?><?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_post_code'] ) ? $registrations['required_shipping_post_code'] : '', 'span' ); ?></label>
				<input type="text" name="wwp_wholesaler_shipping_post_code" id="wwp_wholesaler_shipping_post_code" value="<?php esc_attr_e( $wwp_wholesaler_shipping_post_code ); ?>" <?php wwp_registration_field_required_attr( isset( $registrations['required_shipping_post_code'] ) ? $registrations['required_shipping_post_code'] : '' ); ?>>
			</<?php wwp_elements( 'p' ); ?>>

			<?php
		}
		?>
			<div class="parent">
		<?php
		if ( isset( $registrations['enable_shipping_country'] ) && 'yes' == $registrations['enable_shipping_country'] ) {
			?>
			<!-- <<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>"> -->
			<?php
				woocommerce_form_field(
					'shipping_country',
					array(
						'type'        => 'country',
						'class'       => array( 'chzn-drop' ),
						'label'       => esc_html__( 'Select shipping country', 'woocommerce-wholesale-pricing' ),
						'placeholder' => esc_html__( 'Enter something', 'woocommerce-wholesale-pricing' ),
						'default'     => $default_country,
						'options'     => $countries,
						'required'    => ( isset( $registrations['required_shipping_country'] ) && ( 'yes' == $registrations['required_shipping_country'] ) ? true : false ),
					)
				);
			?>
			<!-- </<?php wwp_elements( 'p' ); ?>> -->

			<?php
		}
		if ( isset( $registrations['enable_shipping_state'] ) && 'yes' == $registrations['enable_shipping_state'] ) {
			$shipping_state_label = esc_html__('State / County', 'woocommerce-wholesale-pricing');
			if ( ! empty($registrations['shipping_state']) ) {
				$shipping_state_label = $registrations['shipping_state'];
			}
			?>
			<!-- <<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>"> -->
				<?php
					woocommerce_form_field(
						'shipping_state',
						array(
							'type'       => 'state',
							'class'      => array( 'chzn-drop' ),
							'label'      => $shipping_state_label,
							'options'     => $default_county_states,
							'required'    => ( isset( $registrations['required_shipping_state'] ) && ( 'yes' == $registrations['required_shipping_state'] ) ? true : false ),
							)
					);
				?>
			<!-- </<?php wwp_elements( 'p' ); ?>> -->

			<?php
		}
		?>
		</div>
	</div>
	<?php
}
if ( isset( $registrations['enable_tex_id'] ) && 'yes' == $registrations['enable_tex_id'] ) {
	$required = ( ! empty( $registrations['required_tex_id'] ) && 'yes' == $registrations['required_tex_id'] ) ? 'required' : '';
	$woo_tax_id_label = esc_html__('Tax ID', 'woocommerce-wholesale-pricing');
	if ( ! empty($registrations['woo_tax_id']) ) {
		$woo_tax_id_label = $registrations['woo_tax_id'];
	}
	?>
	<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
		<label for="wwp_wholesaler_tax_id">
			<?php esc_attr_e( $woo_tax_id_label  ); ?>
			<?php
			if ( 'required' == $required ) {
				echo '<span class="required">*</span>';
			}
			?>
		</label>
		<input type="text" name="wwp_wholesaler_tax_id" id="wwp_wholesaler_tax_id" value="<?php esc_attr_e( $wwp_wholesaler_tax_id ); ?>" <?php esc_attr_e( $required ); ?>>
	</<?php wwp_elements( 'p' ); ?>>

	<?php
}
if ( isset( $settings['wholesale_role'] ) && 'multiple' == $settings['wholesale_role'] ) {
	if ( isset( $registrations['extra_role_fields'] ) && 'yes' == $registrations['extra_role_fields'] && ! empty( $registrations['show_role_on_reg_page'] ) ) {
		?>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row">
			<label><?php esc_html_e( 'User Role', 'woocommerce-wholesale-pricing' ); ?></label>
			<select class="" name="wwp_wholesale_role_request">
				<?php
				$allterms = get_terms( array( 'taxonomy' => 'wholesale_user_roles', 'hide_empty' => false ) );
				foreach ( $allterms as $key => $value ) {
					if ( ! empty( $registrations['show_role_on_reg_page'] ) && in_array( $value->slug, $registrations['show_role_on_reg_page'] ) ) {
						?>
						<option value="<?php esc_attr_e( $value->slug ); ?>"><?php esc_html_e( $value->name ); ?></option>
					<?php
					}
				}
				?>
			</select>
		</p>
		<?php
	}
}

if ( isset( $registrations['display_fields_registration'] ) && 'yes' == $registrations['display_fields_registration'] ) {
	echo wp_kses_post( (string) render_form_builder('get_option', ''));
}

if ( isset( $registrations['enable_file_upload'] ) && 'yes' == $registrations['enable_file_upload'] ) {
	$required = ( ! empty( $registrations['required_file_upload'] ) && 'yes' == $registrations['required_file_upload'] ) ? 'required' : '';
	$woo_file_upload_label = esc_html__('File Upload', 'woocommerce-wholesale-pricing');
	if ( ! empty($registrations['woo_file_upload']) ) {
		$woo_file_upload_label = $registrations['woo_file_upload'];
	}
	?>
	<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
		<label for="wwp_wholesaler_file_upload"><?php esc_attr_e( $woo_file_upload_label ); ?>
		<?php
		if ( 'required' == $required ) {
			echo '<span class="required">*</span>';
		}
		$attr  = null;
		$elem = '';
		if ( isset($registrations['multiple_file_upload']) && 'yes' == $registrations['multiple_file_upload'] ) {
			$attr = '[]';
			$elem = '<button type="button" class="wwp_file_add_more">Add More</button>';
		}
		?>
		</label>
		<input type="file" name="wwp_wholesaler_file_upload<?php esc_attr_e( $attr ); ?>" id="wwp_wholesaler_file_upload" <?php esc_attr_e( $required ); ?> value="">
	</<?php wwp_elements( 'p' ); ?>>
	<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
		<?php echo wp_kses_post( $elem); ?>
	</<?php wwp_elements( 'p' ); ?>>
	<?php
}

?>
<<?php wwp_elements( 'p' ); ?> class="<?php echo wp_kses_post( registration_form_class( ' woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide wwp_form_css_row ' ) ); ?>">
<?php
/**
* Hooks
*
* @since 2.4.0
*/
do_action( 'wwp_wholesaler_registration_form_fields_end', $registrations, $settings );
?>
</<?php wwp_elements( 'p' ); ?>>

<<?php wwp_elements( 'p' ); ?> class="woocomerce-FormRow form-row">
	<input type="submit" class="woocommerce-Button button" id="register" name="wwp_register" value="<?php esc_html_e( 'Register', 'woocommerce-wholesale-pricing' ); ?>">
</<?php wwp_elements( 'p' ); ?>>

<?php
/**
* Hooks
*
* @since 3.0
*/
do_action( 'wwp_wholesaler_registration_fields_end', $registrations, $settings );