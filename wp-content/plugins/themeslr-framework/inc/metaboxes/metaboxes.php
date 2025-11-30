<?php
add_filter( 'cmb2_admin_init', 'themeslr_metaboxes2' );
function themeslr_metaboxes2() {

      // TESTIMONIALS
      $fields_group = new_cmb2_box( array(
            'id'           => 'testimonials_metaboxs',
            'title'        => esc_html__( 'TSLR Testimonials Options', 'themeslr' ),
            'object_types' => array( 'testimonial' ),
            'priority'     => 'high',
      ) );
      $fields_group->add_field( array(
            'name'       => __( 'Job Position', 'themeslr' ),
            'desc'       => __( 'Enter testimonial author job position', 'themeslr' ),
            'id'   => 'job-position',
            'type' => 'text',
      ) );
      $fields_group->add_field( array(
            'name'       => __( 'Company', 'themeslr' ),
            'desc'       => __( 'Enter testimonial author company name', 'themeslr' ),
            'id'         => 'company',
            'type' => 'text',
      ) );


      /**
      ||-> Metaboxes: For - [page]
      */
      // REVSLIDERS
      global $wpdb;
      $limit_small    = 0;
      $limit_high     = 50;
      $tablename      = $wpdb->prefix . "revslider_sliders";
      if($wpdb->get_var("SHOW TABLES LIKE '$tablename'") == $tablename) {
            $sql            = $wpdb->prepare( "SELECT * FROM $tablename LIMIT %d, %d", $limit_small, $limit_high);
            $sliders        = $wpdb->get_results($sql, ARRAY_A);

            $revliders = array(); 
            if ($sliders) {
                  $revliders[''] = 'Please select a slider';
                  foreach($sliders as $slide){
                        $revliders[$slide['alias']] = $slide['title'];
                  }
            }
      }

      // SIDEBARS
      $sidebar_options = array();
      $sidebars = $GLOBALS['wp_registered_sidebars'];

      if($sidebars){
            foreach ( $sidebars as $sidebar ){
                  $sidebar_options[$sidebar['id']] = $sidebar['name'];
            }
      }



      // POST
      $fields_group = new_cmb2_box( array(
            'id'           => 'page_metapost_metaboxsboxs',
            'title'        => esc_html__( 'TSLR Post Layout', 'themeslr' ),
            'object_types' => array( 'post' ),
            'priority'     => 'high',
      ) );
      $fields_group->add_field( array(
            'name'    => __( 'Rewrite Sidebar Theme Options?', 'themeslr' ),
            'desc'    => __( 'If "Yes" - Page Options will rewrite Theme Options', 'themeslr' ),
            'id'      => 'select_post_layout',
            'type'    => 'select',
            'options' => array(
                'inherit' => __( 'Inherit from Theme Panel', 'themeslr' ),
                'left-sidebar' => __( 'Left Sidebar', 'themeslr' ),
                'right-sidebar' => __( 'Right Sidebar', 'themeslr' ),
                'no-sidebar' => __( 'No Sidebar', 'themeslr' ),
            ),
            'default' => 'inherit',
      ) );
      $fields_group->add_field( array(
            'name'    => __( 'Select Sidebar', 'themeslr' ),
            'desc'    => __( '', 'themeslr' ),
            'id'      => 'select_post_sidebar',
            'type'    => 'select',
            'options' => $sidebar_options,
            'default' => 'sidebar-1',
      ) );


      // NAV MENUS LIST for DropDown
      $menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
      $menu_select['default'] = 'Default Menu';
      foreach ( $menus as $menu ) {
            $menu_select[$menu->term_id] = $menu->name;
      }


      $fields_group = new_cmb2_box( array(
            'id'           => 'page_metaboxs',
            'title'        => esc_html__( 'TSLR Custom Options', 'themeslr' ),
            'object_types' => array( 'page', 
                                    'post'
                               ),
            'priority'     => 'high',
      ) );

      /**
      HEADER
      */
      $fields_group->add_field( array(
            'name' => '<h1>Custom Header Options</h1>',
            'desc' => 'These options replaces the Theme Options for current page.',
            'type' => 'title',
            'id' => 'themeslr_test_titleheader'
      ) );
      $fields_group->add_field( array(
            'name'    => __( 'Header Custom background color', 'themeslr' ),
            'desc'    => __( 'Replaces the current background color of the header(Default: Theme Panel Color)', 'themeslr' ),
            'id'      => 'themeslr_header_custom_bg_color',
            'type'    => 'colorpicker',
      ) );
      $fields_group->add_field( array(
          'name'    => 'Semi Transparent Header',
          'desc'    => 'For use if page has Slider or Featured Image',
          'id'      => 'themeslr_header_semitransparent',
          'type'    => 'select',
          'options' => array(
              'disabled'   => __( 'Disabled', 'themeslr' ),
              'enabled' => __( 'Enabled', 'themeslr' ),
          ),

      ) );
      $fields_group->add_field( array(
          'name'    => 'Semi Transparent Header Opacity',
          'desc'    => 'Select a value from 0 to 1(0.1, 0.2 etc)',
          'id'      => 'themeslr_header_semitransparentr_rgba_value',
          'type'    => 'select',
          'options' => array(
              '0.0'       => __( '0', 'themeslr' ),
              '0.1'     => __( '0.1', 'themeslr' ),
              '0.2'     => __( '0.2', 'themeslr' ),
              '0.3'     => __( '0.3', 'themeslr' ),
              '0.4'     => __( '0.4', 'themeslr' ),
              '0.5'     => __( '0.5', 'themeslr' ),
              '0.6'     => __( '0.6', 'themeslr' ),
              '0.7'     => __( '0.7', 'themeslr' ),
              '0.8'     => __( '0.8', 'themeslr' ),
              '0.9'     => __( '0.9', 'themeslr' ),
              '1'       => __( '1', 'themeslr' )
          ),
          'default' => '0.2',
      ) );
      $fields_group->add_field( array(
          'name'    => 'Semi Transparent Header Opacity (After Scroll)',
          'desc'    => 'Select a value from 0 to 1(0.1, 0.2 etc)',
          'id'      => 'thecrate_header_semitransparentr_rgba_value_scroll',
          'type'    => 'select',
          'options' => array(
              '0'       => __( '0', 'themeslr' ),
              '0.1'     => __( '0.1', 'themeslr' ),
              '0.2'     => __( '0.2', 'themeslr' ),
              '0.3'     => __( '0.3', 'themeslr' ),
              '0.4'     => __( '0.4', 'themeslr' ),
              '0.5'     => __( '0.5', 'themeslr' ),
              '0.6'     => __( '0.6', 'themeslr' ),
              '0.7'     => __( '0.7', 'themeslr' ),
              '0.8'     => __( '0.8', 'themeslr' ),
              '0.9'     => __( '0.9', 'themeslr' ),
              '1'       => __( '1', 'themeslr' )
          ),
          'default' => '0.9',
      ) );
      $fields_group->add_field( array(
            'name' => 'Custom Logo',
            'desc' => 'Upload an image or enter an URL.',
            'id' => 'header_custom_logo',
            'type' => 'file',
            'allow' => array( 
                  'url', 
                  'attachment' 
            )
      ) );
      $fields_group->add_field( array(
            'name' => __( 'Custom Logo - Width', 'themeslr' ),
            'desc' => __( 'Override the default theme panel width (Example: 250) 250 = 250px', 'themeslr' ),
            'id'   => 'header_custom_logo_width',
            'type' => 'text_small',
            // 'repeatable' => true,
      ) );
      $fields_group->add_field( array(
          'name'    => 'Select Header Variant',
          'id'      => 'themeslr_header_custom_variant',
          'type'    => 'radio',
          'options' => array(
              ''               => 'Default Header (Selected from the Theme Panel)',
              'header1'        => 'Header v1',
              'header2'        => 'Header v2',
          ),
          'default' => '',
      ) );
      $fields_group->add_field( array(
            'name' => __( 'Header Button Text', 'themeslr' ),
            'desc' => __( 'Override the default theme panel header button text', 'themeslr' ),
            'id'   => 'header_btn_custom_text_meta',
            'type' => 'text_medium',
            // 'repeatable' => true,
      ) );
      $fields_group->add_field( array(
            'name'    => __( 'Select Revolution Slider', 'themeslr' ),
            'id'      => 'select_revslider_shortcode',
            'type'    => 'select',
            'options' => $revliders,
            'default' => 'default',
      ) );
      $fields_group->add_field( array(
            'name'    => __( 'Page title-breadcrumbs', 'themeslr' ),
            'id'      => 'breadcrumbs_on_off',
            'type'    => 'select',
            'options' => array(
                  'no' => __( 'Off - Hide title-breadcrumbs area', 'themeslr' ),
                  'yes' => __( 'On - Show title-breadcrumbs area', 'themeslr' ),
            ),
            'default' => 'yes',
      ) );
      $fields_group->add_field( array(
            'name' => 'Custom Header Image (title/breadcrumbs)',
            'desc' => 'Upload an image or enter an URL.',
            'id' => 'header_custom_breadcrumbs',
            'type' => 'file',
            'allow' => array( 
                  'url', 
                  'attachment' 
            )
      ) );
      $fields_group->add_field( array(
            'id'   => 'tslr_site_hide_header', 
            'name' => 'Hide Header on current page', 
            'type' => 'checkbox', 
      ) );
      /**
      General Page Options
      */
      $fields_group->add_field( array(
          'name' => '<h1>General Page Options</h1>',
          'desc' => 'These options replaces the Theme Options for current page.',
          'type' => 'title',
          'id' => 'themeslr_test_titlepage'
      ) );
      $fields_group->add_field( array(
            'name'    => __( 'Page top/bottom spacing', 'themeslr' ),
            'desc'    => __( '', 'themeslr' ),
            'id'      => 'page_spacing',
            'type'    => 'select',
            'options' => array(
                  'high-padding' => __( 'High Padding', 'themeslr' ),
                  'no-padding' => __( 'No Padding', 'themeslr' ),
                  'no-padding-top' => __( 'No Padding top', 'themeslr' ),
                  'no-padding-bottom' => __( 'No Padding bottom', 'themeslr' ),
            ),
            'default' => 'high-padding',
      ) );
      $fields_group->add_field( array(
            'id'   => 'tslr_site_layout_meta', 
            'name' => 'Boxed Layout?', 
            'type' => 'checkbox', 
      ) );
      $fields_group->add_field( array(
            'name'    => __( 'Custom Skin Background', 'themeslr' ),
            'desc'    => __( 'Replaces the current background color of the header(Default: Theme Panel Color)', 'themeslr' ),
            'id'      => 'themeslr_skin_color_meta',
            'type'    => 'colorpicker',
      ) );
      $fields_group->add_field( array(
            'name'    => __( 'Custom Skin Background - Hover', 'themeslr' ),
            'desc'    => __( 'Replaces the current background color of the header(Default: Theme Panel Color)', 'themeslr' ),
            'id'      => 'themeslr_skin_color_meta_hover',
            'type'    => 'colorpicker',
      ) );
      $fields_group->add_field( array(
            'name'    => __( 'Custom Skin Color', 'themeslr' ),
            'desc'    => __( 'Replaces the current background color of the header(Default: Theme Panel Color)', 'themeslr' ),
            'id'      => 'themeslr_skin_text_color_meta',
            'type'    => 'colorpicker',
      ) );
      /**
      FOOTER
      */
      $fields_group->add_field( array(
          'name' => '<h1>Custom Footer Options</h1>',
          'desc' => 'These options replaces the Theme Options for current page.',
          'type' => 'title',
          'id' => 'themeslr_footer'
      ) );
      $fields_group->add_field( array(
            'id'   => 'tslr_site_hide_footer', 
            'name' => 'Hide Footer on current page', 
            'type' => 'checkbox', 
      ) );
      $fields_group->add_field( array(
            'name' => __( 'Footer Image', 'themeslr' ),
            'desc' => __( 'Override the default footer styling settings', 'themeslr' ),
            'id'   => 'themeslr_footer_custom_bg_image',
            'type' => 'file',
            'allow' => array( 
                  'url', 
                  'attachment' 
            )
      ) );
      $fields_group->add_field( array(
            'name' => __( 'Footer (Padding Top)', 'themeslr' ),
            'desc' => __( 'Default is 60px', 'themeslr' ),
            'id'   => 'themeslr_footer_top_padding_top',
            'type' => 'text_medium',
            // 'repeatable' => true,
      ) );
      $fields_group->add_field( array(
            'name' => __( 'Footer (Padding Bottom)', 'themeslr' ),
            'desc' => __( 'Default is 60px', 'themeslr' ),
            'id'   => 'themeslr_footer_top_padding_bottom',
            'type' => 'text_medium',
            // 'repeatable' => true,
      ) );


}
