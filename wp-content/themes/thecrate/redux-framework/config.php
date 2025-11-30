<?php

    if ( ! class_exists( 'Redux' ) ) {
        return;
    }

    // This is your option name where all the Redux data is stored.
    $opt_name = "redux_demo";

    // This line is only for altering the demo. Can be easily removed.
    $opt_name = apply_filters( 'redux_demo/opt_name', $opt_name );


    /**
     * ---> SET ARGUMENTS
     * All the possible arguments for Redux.
     * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
     * */

    $theme = wp_get_theme(); // For use with some settings. Not necessary.

    $args = array(
        // TYPICAL -> Change these values as you need/desire
        'opt_name'             => $opt_name,
        // This is where your data is stored in the database and also becomes your global variable name.
        'display_name'         => $theme->get( 'Name' ),
        // Name that appears at the top of your panel
        'display_version'      => $theme->get( 'Version' ),
        // Version that appears at the top of your panel
        'menu_type'            => 'menu',
        //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
        'allow_sub_menu'       => true,
        // Show the sections below the admin menu item or not
        'menu_title'           => esc_attr__( 'Theme Panel', 'thecrate' ),
        'page_title'           => esc_attr__( 'Theme Panel', 'thecrate' ),
        // You will need to generate a Google API key to use this feature.
        // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
        'google_api_key'       => '',
        // Set it you want google fonts to update weekly. A google_api_key value is required.
        'google_update_weekly' => false,
        // Must be defined to add google fonts to the typography module
        'async_typography'     => true,
        // Use a asynchronous font on the front end or font string
        //'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
        'admin_bar'            => true,
        // Show the panel pages on the admin bar
        'admin_bar_icon'       => 'dashicons-portfolio',
        // Choose an icon for the admin bar menu
        'admin_bar_priority'   => 50,
        // Choose an priority for the admin bar menu
        'global_variable'      => 'thecrate_redux',
        // Set a different name for your global variable other than the opt_name
        'dev_mode'             => false,
        // Show the time the page took to load, etc
        'update_notice'        => true,
        // If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
        'customizer'           => true,
        // Enable basic customizer support
        // OPTIONAL -> Give you extra features
        'page_priority'        => 2,
        // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
        'page_parent'          => 'themes.php',
        // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
        'page_permissions'     => 'manage_options',
        // Permissions needed to access the options panel.
        'menu_icon'            => '',
        // Specify a custom URL to an icon
        'last_tab'             => '',
        // Force your panel to always open to a specific tab (by id)
        'page_icon'            => 'icon-themes',
        // Icon displayed in the admin panel next to your menu_title
        'page_slug'            => '',
        // Page slug used to denote the panel, will be based off page title then menu title then opt_name if not provided
        'save_defaults'        => true,
        // On load save the defaults to DB before user clicks save or not
        'default_show'         => false,
        // If true, shows the default value next to each field that is not the default value.
        'default_mark'         => '',
        // What to print by the field's title if the value shown is default. Suggested: *
        'show_import_export'   => true,
        // Shows the Import/Export panel when not used as a field.

        // CAREFUL -> These options are for advanced use only
        'transient_time'       => 60 * MINUTE_IN_SECONDS,
        'output'               => true,
        // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
        'output_tag'           => true,
        // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head

        // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
        'database'             => '',
        // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
        'use_cdn'              => true,
        // If you prefer not to use the CDN for Select2, Ace Editor, and others, you may download the Redux Vendor Support plugin yourself and run locally or embed it in your code.

        // HINTS
        'hints'                => array(
            'icon'          => 'el el-question-sign',
            'icon_position' => 'right',
            'icon_color'    => 'lightgray',
            'icon_size'     => 'normal',
            'tip_style'     => array(
                'color'   => 'red',
                'shadow'  => true,
                'rounded' => false,
                'style'   => '',
            ),
            'tip_position'  => array(
                'my' => 'top left',
                'at' => 'bottom right',
            ),
            'tip_effect'    => array(
                'show' => array(
                    'effect'   => 'slide',
                    'duration' => '500',
                    'event'    => 'mouseover',
                ),
                'hide' => array(
                    'effect'   => 'slide',
                    'duration' => '500',
                    'event'    => 'click mouseleave',
                ),
            ),
        )
    );

    // ADMIN BAR LINKS -> Setup custom links in the admin bar menu as external items.
    $args['admin_bar_links'][] = array(
        'href'  => esc_url('https://themeslr.ticksy.com/'),
        'title' => esc_attr__( 'Submit Support Ticket', 'thecrate' ),
    );
    $args['admin_bar_links'][] = array(
        'href'  => esc_url('http://themeforest.net/downloads'),
        'title' => esc_attr__( 'Love the Theme? Rate it!', 'thecrate' ),
    );

  

    // Panel Intro text -> before the form
    if ( ! isset( $args['global_variable'] ) || $args['global_variable'] !== false ) {
        if ( ! empty( $args['global_variable'] ) ) {
            $v = $args['global_variable'];
        } else {
            $v = str_replace( '-', '_', $args['opt_name'] );
        }
        $args['intro_text'] = sprintf( esc_attr__( '', 'thecrate' ), $v );
    } else {
        $args['intro_text'] = esc_attr__( '', 'thecrate' );
    }

    // Add content after the form.
    $args['footer_text'] = esc_attr__( '', 'thecrate' );

    Redux::setArgs( $opt_name, $args );

    /*
     * ---> END ARGUMENTS
     */

    /*
     * <--- END HELP TABS
     */

    /*
     *
     * ---> START SECTIONS
     *
     */

    /*
        As of Redux 3.5+, there is an extensive API. This API can be used in a mix/match mode allowing for
     */
    include_once(get_template_directory() . '/redux-framework/config.arrays.php');
    /**
    ||-> SECTION: General Settings
    */
    Redux::setSection( $opt_name, array(
        'title' => esc_attr__( 'General Settings', 'thecrate' ),
        'id'    => 'thecrate_general',
        'icon'  => 'el el-icon-wrench'
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'General Settings', 'thecrate' ),
        'id'         => 'thecrate_general_settings',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'        => 'tslr_site_layout',
                'type'      => 'select',
                'title'     => esc_attr__('Choose the layout of the site(Fullwidth or Boxed)', 'thecrate'),
                'subtitle'  => esc_attr__('Default: Fullwidth', 'thecrate'),
                'desc'      => esc_attr__('This option will be applied all over the site. If you want this option only on certain pages, edit each page and set the Page Layout to Fullwidth or Boxed.', 'thecrate'),
                'options'   => array(
                        'layout_fullwidth'   => 'Fullwidth Layout',
                        'layout_boxed'   => 'Boxed Layout'
                    ),
                'default'   => 'layout_fullwidth',
            ),
            array(         
                'id'       => 'tslr_site_layout_boxed_bg',
                'type'     => 'background',
                'title'    => esc_attr__('Boxed Layout - Body Background', 'thecrate'),
                'subtitle' => esc_attr__('Set the Body background when Boxed Layout is activated.', 'thecrate'),
                'output'      => array('body.layout_boxed'),
                'default'  => array(
                    'background-color' => '#E7E7E7',
                    'background-image' => get_template_directory_uri().'/images/boxed_pattern.png',
                ),
                'required' => array( 'tslr_site_layout', '=', 'layout_boxed' ),
            ),
        ),
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Breadcrumbs Section', 'thecrate' ),
        'id'         => 'thecrate_general_breadcrumbs',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_enable_breadcrumbs',
                'type'     => 'switch', 
                'title'    => esc_attr__('Breadcrumbs Status (below page title)', 'thecrate'),
                'subtitle' => esc_attr__('Enable or disable breadcrumbs', 'thecrate'),
                'default'  => true,
            ),
            array(
                'id'       => 'thecrate_breadcrumbs_delimitator',
                'type'     => 'text',
                'title'    => esc_attr__('Breadcrumbs delimitator', 'thecrate'),
                'default'  => '/',
                'required' => array( 'thecrate_enable_breadcrumbs', '=', '1' ),
            ),
            array(         
                'id'       => 'thecrate_breadcrumbs_background',
                'type'     => 'background',
                'title'    => esc_attr__('Breadcrumbs header image', 'thecrate'),
                'subtitle' => esc_attr__('Use the upload button to import media.', 'thecrate'),
                'output'      => array('.header-title-breadcrumb-overlay'),
                'default'  => array(
                    'background-color' => '#f4f4f4',
                    'background-image' => get_template_directory_uri().'/images/breadcrumbs.jpg',
                ),
            ),
            array(
                'id'       => 'thecrate_breadcrumbs_home_link_color',
                'type'     => 'color',
                'title'    => esc_attr__('Breadcrumbs Home Link Color', 'thecrate'), 
                'output' => array(
                    'color' => '.header-group .breadcrumb li a',
                )
            ),
            array(
                'id'        => 'thecrate_breadcrumbs_tb_spacing',
                'type'      => 'slider',
                'title'     => esc_attr__('Top/Bottom Space', 'thecrate'),
                'subtitle'  => esc_attr__('Incease or decrese the title top/bottom space', 'thecrate'),
                "default"   => 150,
                "min"       => 0,
                "step"      => 5,
                "max"       => 400,
                'display_value' => 'label',
            ),
            array(
                'id'        => 'thecrate_breadcrumbs_title_size',
                'type'      => 'slider',
                'title'     => esc_attr__('Breadcrumbs Heading Font-Size', 'thecrate'),
                'subtitle'  => esc_attr__('Font-size of the Breadcrumbs Heading.', 'thecrate'),
                "default"   => 35,
                "min"       => 1,
                "step"      => 1,
                "max"       => 150,
                'display_value' => 'label',
            ),
            array(
                'id'       => 'thecrate_title_breadcrumbs_alignment',
                'type'     => 'radio',
                'title'    => esc_attr__('Texts Alignment', 'thecrate'), 
                'subtitle' => esc_attr__('Select Preloader Animation', 'thecrate'),
                //Must provide key => value pairs for radio options
                'options'  => array(
                    'text-left' => esc_html__('Left', 'thecrate'), 
                    'text-center' => esc_html__('Center', 'thecrate'), 
                    'text-right' => esc_html__('Right', 'thecrate'), 
                ),
                'default' => 'text-center',
            )
        ),
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Back to Top Button', 'thecrate' ),
        'id'         => 'thecrate_general_back_to_top',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_backtotop_status',
                'type'     => 'switch', 
                'title'    => esc_attr__('Back to Top Button Status', 'thecrate'),
                'subtitle' => esc_attr__('Enable or disable "Back to Top Button"', 'thecrate'),
                'default'  => true,
            ),
            array(         
                'id'       => 'thecrate_backtotop_bg_color',
                'type'     => 'background',
                'title'    => esc_attr__('Back to Top Button Status Backgrond', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #f26226', 'thecrate'),
                'default'  => array(
                    'background-color' => '#f26226',
                    'background-image' => get_template_directory_uri().'/images/svg/back-to-top-arrow.svg',
                ),
                'required' => array( 'thecrate_backtotop_status', '=', '1' ),
            ),
            array(
                'id'            => 'thecrate_backtotop_height_width',
                'type'          => 'slider',
                'title'         => esc_attr__( 'Button Height/Width', 'thecrate' ),
                'subtitle'      => esc_attr__( 'Set the Height/Width of the "Back to Top button"', 'thecrate' ),
                'desc'          => esc_attr__( 'Default: 40px (Height/Width)', 'thecrate' ),
                'default'       => 40,
                'min'           => 10,
                'step'          => 1,
                'max'           => 300,
                'display_value' => 'label',
                'required' => array( 'thecrate_backtotop_status', '=', '1' ),
            ),
            array(
                'id'       => 'thecrate_backtotop_border_status',
                'type'     => 'switch', 
                'title'    => esc_attr__('Inner Border Status.', 'thecrate'),
                'subtitle' => esc_attr__('Show or Hide the inner border.', 'thecrate'),
                'default'  => true,
                'required' => array( 'thecrate_backtotop_status', '=', '1' ),
            ),
        ),
    ));
    Redux::setSection( $opt_name, array(
        'title' => esc_attr__( 'Page Preloader', 'thecrate' ),
        'id' => 'thecrate_general_preloader',
        'subsection' => true,
        'fields' => array(
            array(
                'id'       => 'thecrate_preloader_status',
                'type'     => 'switch', 
                'title'    => esc_attr__('Enable Page Preloader', 'thecrate'),
                'subtitle' => esc_attr__('Enable or disable page preloader', 'thecrate'),
                'default'  => true,
            ),
            array(         
                'id'       => 'thecrate_preloader_bg_color',
                'type'     => 'background',
                'title'    => esc_attr__('Page Preloader Backgrond', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #2a3cb7', 'thecrate'),
                'default'  => array(
                    'background-color' => '#2a3cb7',
                ),
                'output' => array(
                    'body .thecrate_preloader_holder'
                ),
                'required' => array( 'thecrate_preloader_status', '=', '1' ),
            ),
            array(
                'id'       => 'thecrate_preloader_color',
                'type'     => 'color',
                'title'    => esc_attr__('Preloader color:', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #ffffff', 'thecrate'),
                'default'  => '#ffffff',
                'validate' => 'color',
                'required' => array( 'thecrate_preloader_status', '=', '1' ),
            ),
            array(
                'id'       => 'thecrate_preloader_animation',
                'type'     => 'radio',
                'title'    => esc_attr__('Preloader Animation', 'thecrate'), 
                'subtitle' => esc_attr__('Select Preloader Animation', 'thecrate'),
                //Must provide key => value pairs for radio options
                'options'  => array(
                    'v1_ball_triangle' => esc_html__('Ball Triangle', 'thecrate'), 
                    'v2_ball_pulse' => esc_html__('Ball Pulse', 'thecrate'),
                    'v3_ball_grid_pulse' => esc_html__('Ball Grid Pulse', 'thecrate'),
                    'v4_ball_clip_rotate' => esc_html__('Ball Clip Rotate', 'thecrate'),
                    'v5_ball_clip_rotate_pulse' => esc_html__('Ball Clip Rotate Pulse', 'thecrate'),
                    'v6_square_spin' => esc_html__('Square Spin', 'thecrate'),
                    'v7_ball_clip_rotate_multiple' => esc_html__('Ball Clip Rotate Multiple', 'thecrate'),
                    'v8_ball_pulse_rise' => esc_html__('Ball Pulse Rise', 'thecrate'),
                    'v9_ball_rotate' => esc_html__('Ball Rotate', 'thecrate'),
                    'v10_cube_transition' => esc_html__('Cube Transition', 'thecrate'),
                    'v11_ball_zig_zag' => esc_html__('Triangle Zig Zag', 'thecrate'),
                    'v12_ball_zig_zag_deflect' => esc_html__('Triangle Zig Zag Deflect', 'thecrate'),
                    'v13_ball_scale' => esc_html__('Ball Scale', 'thecrate'),
                    'v14_line_scale' => esc_html__('Line Scale', 'thecrate'),
                    'v15_line_scale_party' => esc_html__('Line Scale Party', 'thecrate'),
                    'v16_ball_scale_multiple' => esc_html__('Ball Scale Multiple', 'thecrate'),
                    'v17_ball_pulse_sync' => esc_html__('Ball Pulse Sync', 'thecrate'),
                    'v18_ball_beat' => esc_html__('Ball Beat', 'thecrate'),
                    'v19_line_scale_pulse_out' => esc_html__('Line Scale Pulse Out', 'thecrate'),
                    'v20_line_scale_pulse_out_rapid' => esc_html__('Line Scale Pulse Out Rapid', 'thecrate')
                ),
                'default' => 'v13_ball_scale',
                'required' => array( 'thecrate_preloader_status', '=', '1' ),
            )
        ),
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Sidebars', 'thecrate' ),
        'id'         => 'thecrate_general_sidebars',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_dynamic_sidebars',
                'type'     => 'multi_text',
                'title'    => esc_attr__( 'Sidebars', 'thecrate' ),
                'subtitle' => esc_attr__( 'Use the "Add More" button to create unlimited sidebars.', 'thecrate' ),
                'add_text' => esc_attr__( 'Add one more Sidebar', 'thecrate' ),
                'default'  => array(esc_attr__('Single Article Sidebar', 'thecrate'), esc_attr__('Burger Sidebar', 'thecrate')),
            )
        ),
    ));

    /**
    ||-> SECTION: Styling Settings
    */
    Redux::setSection( $opt_name, array(
        'title' => esc_html__( 'Styling Settings', 'thecrate' ),
        'id'    => 'thecrate_styling',
        'icon'  => 'el el-icon-magic'
    ) );
    Redux::setSection( $opt_name, array(
        'title'      => esc_html__( 'Global Fonts', 'thecrate' ),
        'id'         => 'thecrate_styling_global_fonts',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_google_fonts_select',
                'type'     => 'select',
                'multi'    => true,
                'title'    => esc_html__('Import Google Font Globally', 'thecrate'), 
                'subtitle' => esc_html__('Select one or multiple fonts', 'thecrate'),
                'desc'     => esc_html__('Importing fonts made easy', 'thecrate'),
                'options'  => $google_fonts_list,
                'default'  => array(
                    'Work+Sans:100,200,300,regular,500,600,700,800,900,latin-ext,latin',
                    'Jost:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap'
                ),
            ),
        ),
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Skin color', 'thecrate' ),
        'id'         => 'thecrate_styling_skin_color',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_style_main_texts_color',
                'type'     => 'color',
                'title'    => esc_attr__('Main texts color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #F26226', 'thecrate'),
                'default'  => '#F26226',
                'validate' => 'color',
            ),
            array(
                'id'       => 'thecrate_style_main_backgrounds_color',
                'type'     => 'color',
                'title'    => esc_attr__('Main backgrounds color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #F26226', 'thecrate'),
                'default'  => '#F26226',
                'validate' => 'color',
            ),
            array(
                'id'       => 'thecrate_style_main_backgrounds_color_hover',
                'type'     => 'color',
                'title'    => esc_attr__('Main backgrounds color (hover)', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #2A3CB7', 'thecrate'),
                'default'  => '#2A3CB7',
                'validate' => 'color',
            ),
            array(
                'id'       => 'thecrate_style_semi_opacity_backgrounds',
                'type'     => 'color_rgba',
                'title'    => esc_attr__( 'Semitransparent blocks background', 'thecrate' ),
                'default'  => array(
                    'color' => '#ffffff',
                    'alpha' => '.95'
                ),
                'mode'     => 'background',
                'output'    => array('background' => '.thumbnail-overlay, .portfolio-hover, .pastor-image-content .details-holder, .item-description .holder-top, .slider_navigation .btn, .read-more-overlay, blockquote::before'),
            ),
            array(
                'id'       => 'thecrate_text_selection_color',
                'type'     => 'color',
                'title'    => esc_attr__('Text selection color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #ffffff', 'thecrate'),
                'default'  => '#ffffff',
                'validate' => 'color',
            ),
            array(
                'id'       => 'thecrate_text_selection_background_color',
                'type'     => 'color',
                'title'    => esc_attr__('Text selection background color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #F26226', 'thecrate'),
                'default'  => '#F26226',
                'validate' => 'color',
            )
        ),
    ));


    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Typography', 'thecrate' ),
        'id'         => 'thecrate_styling_typography',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'          => 'thecrate_body_typography',
                'type'        => 'typography', 
                'title'       => esc_attr__('Body Font family', 'thecrate'),
                'google'      => true, 
                'font-backup' => true,
                'color'       => false,
                'text-align'  => false,
                'letter-spacing'  => false,
                'line-height'  => false,
                'font-weight'  => false,
                'font-size'   => false,
                'font-style'  => false,
                'subsets'     => false,
                'output'      => array('body'),
                'units'       =>'px',
                'default'     => array(
                    'font-family' => 'Work Sans', 
                    'google'      => true
                ),
            ),
            array(
                'id'          => 'thecrate_heading_h1',
                'type'        => 'typography', 
                'title'       => esc_attr__('Heading H1 Font family', 'thecrate'),
                'google'      => true, 
                'font-backup' => true,
                'color'       => false,
                'text-align'  => false,
                'letter-spacing'  => true,
                'line-height'  => true,
                'font-weight'  => true,
                'font-size'   => true,
                'font-style'  => false,
                'subsets'     => false,
                'output'      => array('h1', 'h1 span'),
                'units'       =>'px',
                'default'     => array(
                    'font-family' => 'Jost', 
                    'font-weight' => '700', 
                    'font-size' => '36px', 
                    'google'      => true
                ),
            ),
            array(
                'id'          => 'thecrate_heading_h2',
                'type'        => 'typography', 
                'title'       => esc_attr__('Heading H2 Font family', 'thecrate'),
                'google'      => true, 
                'font-backup' => true,
                'color'       => false,
                'text-align'  => false,
                'letter-spacing'  => true,
                'line-height'  => true,
                'font-weight'  => true,
                'font-size'   => true,
                'font-style'  => false,
                'subsets'     => false,
                'output'      => array('h2'),
                'units'       =>'px',
                'default'     => array(
                    'font-family' => 'Jost', 
                    'font-weight' => '600', 
                    'font-size' => '30px', 
                    'google'      => true
                ),
            ),
            array(
                'id'          => 'thecrate_heading_h3',
                'type'        => 'typography', 
                'title'       => esc_attr__('Heading H3 Font family', 'thecrate'),
                'google'      => true, 
                'font-backup' => true,
                'color'       => false,
                'text-align'  => false,
                'letter-spacing'  => true,
                'line-height'  => true,
                'font-weight'  => true,
                'font-size'   => true,
                'font-style'  => false,
                'subsets'     => false,
                'output'      => array('h3'),
                'units'       =>'px',
                'default'     => array(
                    'font-family' => 'Jost', 
                    'font-weight' => '600', 
                    'font-size' => '24px', 
                    'google'      => true
                ),
            ),
            array(
                'id'          => 'thecrate_heading_h4',
                'type'        => 'typography', 
                'title'       => esc_attr__('Heading H4 Font family', 'thecrate'),
                'google'      => true, 
                'font-backup' => true,
                'color'       => false,
                'text-align'  => false,
                'letter-spacing'  => true,
                'line-height'  => true,
                'font-weight'  => true,
                'font-size'   => true,
                'font-style'  => false,
                'subsets'     => false,
                'output'      => array('h4'),
                'units'       =>'px',
                'default'     => array(
                    'font-family' => 'Jost', 
                    'font-weight' => '600', 
                    'font-size' => '18px', 
                    'google'      => true
                ),
            ),
            array(
                'id'          => 'thecrate_heading_h5',
                'type'        => 'typography', 
                'title'       => esc_attr__('Heading H5 Font family', 'thecrate'),
                'google'      => true, 
                'font-backup' => true,
                'color'       => false,
                'text-align'  => false,
                'letter-spacing'  => true,
                'line-height'  => true,
                'font-weight'  => true,
                'font-size'   => true,
                'font-style'  => false,
                'subsets'     => false,
                'output'      => array('h5'),
                'units'       =>'px',
                'default'     => array(
                    'font-family' => 'Jost', 
                    'font-weight' => '600', 
                    'font-size' => '14px', 
                    'google'      => true
                ),
            ),
            array(
                'id'          => 'thecrate_heading_h6',
                'type'        => 'typography', 
                'title'       => esc_attr__('Heading H6 Font family', 'thecrate'),
                'google'      => true, 
                'font-backup' => true,
                'color'       => false,
                'text-align'  => false,
                'letter-spacing'  => true,
                'line-height'  => true,
                'font-weight'  => true,
                'font-size'   => true,
                'font-style'  => false,
                'subsets'     => false,
                'output'      => array('h6'),
                'units'       =>'px',
                'default'     => array(
                    'font-family' => 'Jost', 
                    'font-weight' => '600', 
                    'font-size' => '12px', 
                    'google'      => true
                ),
            ),
            array(
                'id'                => 'thecrate_inputs_typography',
                'type'              => 'typography', 
                'title'             => esc_attr__('Inputs Font family', 'thecrate'),
                'google'            => true, 
                'font-backup'       => true,
                'color'             => false,
                'text-align'        => false,
                'letter-spacing'    => false,
                'line-height'       => false,
                'font-weight'       => false,
                'font-size'         => false,
                'font-style'        => false,
                'subsets'           => false,
                'output'            => array('input', 'textarea'),
                'units'             =>'px',
                'subtitle'          => esc_attr__('Font family for inputs and textareas', 'thecrate'),
                'default'           => array(
                    'font-family'       => 'Work Sans', 
                    'google'            => true
                ),
            ),

        ),
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_html__( 'Buttons', 'thecrate' ),
        'id'         => 'thecrate_styling_buttons',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_global_buttons_radius',
                'type'     => 'radio',
                'compiler' => true,
                'title'    => esc_attr__( 'Buttons Layout (radius)', 'thecrate' ),
                'options'  => array(
                    '0' => esc_attr__('Square (Default)', 'thecrate'),
                    '5' => esc_attr__('Rounded (5px Radius)', 'thecrate'),
                    '30' => esc_attr__('Round (30px Radius)', 'thecrate'),
                ),
                'default'  => '0'
            ),
            array(
                'id'                => 'thecrate_buttons_typography',
                'type'              => 'typography', 
                'title'             => esc_attr__('Buttons Font family', 'thecrate'),
                'google'            => true, 
                'font-backup'       => true,
                'color'             => false,
                'text-align'        => false,
                'letter-spacing'    => false,
                'line-height'       => false,
                'font-weight'       => false,
                'font-size'         => false,
                'font-style'        => false,
                'subsets'           => false,
                'output'            => array(
                    'input[type="submit"]'
                ),
                'units'             =>'px',
                'subtitle'          => esc_attr__('Font family for buttons', 'thecrate'),
                'default'           => array(
                    'font-family'       => 'Work Sans', 
                    'google'            => true
                ),
            ),
            array(
                'id'       => 'thecrate_buttons_shadow',
                'type'     => 'checkbox', 
                'title'    => esc_html__('Buttons Shadows', 'thecrate'),
                'subtitle' => esc_html__('Box shadow for buttons', 'thecrate'),
                'options'   => array(
                    'shadow'   => esc_html__( 'Yes', 'thecrate' ),
                ),
                'default' => array(
                    'shadow' => '0',
                )
            ),
        ),
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_html__( 'Custom CSS', 'thecrate' ),
        'id'         => 'thecrate_styling_custom_css',
        'subsection' => true,
        'fields'     => array(
            array(
                'id' => 'tslr_custom_css',
                'type' => 'ace_editor',
                'title' => esc_attr__('CSS Code', 'thecrate'),
                'subtitle' => esc_attr__('Paste your CSS code here.', 'thecrate'),
                'mode' => 'css',
                'theme' => 'monokai',
                'default' => ""
            )
        ),
    ));

    /**
    ||-> SECTION: Header Settings
    */
    Redux::setSection( $opt_name, array(
        'title' => esc_attr__( 'Header Settings', 'thecrate' ),
        'id'    => 'thecrate_header',
        'icon'  => 'el el-icon-arrow-up'
    ) );
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Header - General', 'thecrate' ),
        'id'         => 'thecrate_header_general',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_header_layout',
                'type'     => 'radio',
                'compiler' => true,
                'title'    => esc_attr__( 'Select Header layout', 'thecrate' ),
                'options'  => array(
                    'header1' => esc_html__('Header v1 - Default', 'thecrate'),
                    'header2' => esc_html__('Header v2 - Centered', 'thecrate'),
                    'header3' => esc_html__('Header v3', 'thecrate'),
                ),
                'default'  => 'header1'
            ),
            array(
                'id'        => 'thecrate_header_2_space_between_logo_menu',
                'type'      => 'slider',
                'title'     => esc_attr__('Header 2 & 3: Space between logo and menu', 'thecrate'),
                'subtitle'  => esc_attr__('Use the slider to increase/decrease space.', 'thecrate'),
                'desc'      => esc_attr__('Min: 0px, max: 500px, step: 1px, default value: 20px', 'thecrate'),
                "default"   => 20,
                "min"       => 0,
                "step"      => 1,
                "max"       => 300,
                'display_value' => 'label',
                'required' => array( 
                    array( 'thecrate_header_layout', '!=', 'header1' ),
                ),
            ),
            array(
                'id'             => 'thecrate_header_padding',
                'type'           => 'spacing',
                'output'         => array('header nav#theme-main-head'),
                'mode'           => 'padding',
                // 'units'          => array('em', 'px'),
                'units_extended' => 'false',
                'title'          => esc_attr__('Header Top/Bottom White Space', 'thecrate'),
                'subtitle'       => esc_attr__('Choose the spacing for the main header.', 'thecrate'),
                'default'            => array(
                    'padding-top'     => '27px', 
                    'padding-bottom'  => '27px', 
                    'units'          => 'px', 
                )
            ),
            array(
                'id'       => 'thecrate_is_nav_sticky',
                'type'     => 'switch', 
                'title'    => esc_attr__('Sticky Navigation Menu?', 'thecrate'),
                'subtitle' => esc_attr__('Enable or disable "sticky positioned navigation menu".', 'thecrate'),
                'default'  => false,
                'on'       => esc_attr__( 'Enabled', 'thecrate' ),
                'off'      => esc_attr__( 'Disabled', 'thecrate' )
            ),
        ),
    ) );
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Logo &amp; Favicon', 'thecrate' ),
        'id'         => 'thecrate_header_logo',
        'subsection' => true,
        'fields'     => array(
            array(
                'id' => 'thecrate_logo',
                'type' => 'media',
                'url' => true,
                'title' => esc_attr__('Logo image', 'thecrate'),
                'compiler' => 'true',
                'default' => array('url' => get_template_directory_uri().'/images/theme_logo_dark.png'),
            ),
            array(
                'id'        => 'thecrate_logo_max_width',
                'type'      => 'slider',
                'title'     => esc_attr__('Logo Max Width', 'thecrate'),
                'subtitle'  => esc_attr__('Use the slider to increase/decrease max size of the logo.', 'thecrate'),
                'desc'      => esc_attr__('Min: 1px, max: 500px, step: 1px, default value: 200px', 'thecrate'),
                "default"   => 200,
                "min"       => 1,
                "step"      => 1,
                "max"       => 500,
                'display_value' => 'label'
            ),
            array(
                'id' => 'thecrate_favicon',
                'type' => 'media',
                'url' => true,
                'title' => esc_attr__('Favicon url', 'thecrate'),
                'compiler' => 'true',
                'subtitle' => esc_attr__('Use the upload button to import media.', 'thecrate'),
                'default' => array('url' => get_template_directory_uri().'/images/theme_favicon.png'),
            )
        ),
    ) );

    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Header - Top Small Bar', 'thecrate' ),
        'id'         => 'thecrate_header_top_small',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_header_top_status',
                'type'     => 'switch', 
                'title'    => esc_attr__('Header Top Small Bar Status', 'thecrate'),
                'subtitle' => esc_attr__('Enable or disable "Header Top Small Bar Status".', 'thecrate'),
                'default'  => false,
                'on'       => esc_attr__( 'Enabled', 'thecrate' ),
                'off'      => esc_attr__( 'Disabled', 'thecrate' )
            ),
            array(         
                'id'       => 'thecrate_header_top_small_background',
                'type'     => 'background',
                'title'    => esc_attr__('Header (top-header) - background', 'thecrate'),
                'subtitle' => esc_attr__('Default color: #f26226', 'thecrate'),
                'output'      => array('header .top-header'),
                'default'  => array(
                    'background-color' => '#f26226',
                ),
                'required' => array( 'thecrate_header_top_status', '=', '1' ),
            ),
            array(
                'id'       => 'thecrate_header_top_small_text_color',
                'type'     => 'color',
                'title'    => esc_attr__('Header (top-header) - texts color', 'thecrate'), 
                'subtitle' => esc_attr__('Default color: #ffffff', 'thecrate'),
                'default'  => '#ffffff',
                'validate' => 'color',
                'output'    => array(
                    'color' => 'header .top-header',
                ),
                'required' => array( 'thecrate_header_top_status', '=', '1' ),
            ),
            array(
                'id'       => 'thecrate_header_top_small_text_color',
                'type'     => 'color',
                'title'    => esc_attr__('Header (top-header) - links color', 'thecrate'), 
                'subtitle' => esc_attr__('Default color: #ffffff', 'thecrate'),
                'default'  => '#ffffff',
                'validate' => 'color',
                'output'    => array(
                    'color' => 'header .top-header .left-side a, .top-header .social-links li a',
                ),
                'required' => array( 'thecrate_header_top_status', '=', '1' ),
            ),
            array( 
                'id'       => 'thecrate_header_top_border',
                'type'     => 'border',
                'title'    => esc_attr__('Border', 'thecrate'),
                'subtitle' => esc_attr__('Set a border for the header top bar', 'thecrate'),
                'output'   => array('header .top-header'),
                'all'      => false,
                'default'  => array(
                    'border-color'  => '#dddddd', 
                    'border-style'  => 'solid', 
                    'border-top'    => '0', 
                    'border-right'  => '0', 
                    'border-bottom' => '0', 
                    'border-left'   => '0'
                ),
                'required' => array( 'thecrate_header_top_status', '=', '1' ),
            ),
            array(
                'id'       => 'tslr_enable_phone_number',
                'type'     => 'switch', 
                'title'    => esc_attr__('Enable Phone Number label', 'thecrate'),
                'subtitle' => esc_attr__('Enable or disable Phone Number label', 'thecrate'),
                'default'  => true,
                'required' => array( 'thecrate_header_top_status', '=', '1' ),
            ),
            array(
                'id' => 'thecrate_header_top_small_left_1_text',
                'type' => 'text',
                'title' => esc_attr__('Business Phone Number', 'thecrate'),
                'default' => '+34-2345-3456',
                'required' => array(
                    array( 'thecrate_header_top_status', '=', '1' ),
                    array( 'tslr_enable_phone_number', '=', '1' ),
                ),
            ),
            array(
                'id' => 'thecrate_header_top_small_left_1_url',
                'type' => 'text',
                'title' => esc_attr__('Business Phone Number Link Url', 'thecrate'),
                'default' => '#',
                'required' => array(
                    array( 'thecrate_header_top_status', '=', '1' ),
                    array( 'tslr_enable_phone_number', '=', '1' ),
                ),
            ),
            array(
                'id'       => 'tslr_enable_address',
                'type'     => 'switch', 
                'title'    => esc_attr__('Enable Address label', 'thecrate'),
                'subtitle' => esc_attr__('Enable or disable Address label', 'thecrate'),
                'default'  => true,
                'required' => array( 'thecrate_header_top_status', '=', '1' ),
            ),
            array(
                'id' => 'thecrate_header_top_small_left_2_text',
                'type' => 'text',
                'title' => esc_attr__('Business Address', 'thecrate'),
                'default' => '93L, Str. J. Martin, Rome',
                'required' => array(
                    array( 'thecrate_header_top_status', '=', '1' ),
                    array( 'tslr_enable_address', '=', '1' ),
                ),
            ),
            array(
                'id' => 'thecrate_header_top_small_left_2_url',
                'type' => 'text',
                'title' => esc_attr__('Business Address Link Url', 'thecrate'),
                'default' => '#',
                'required' => array(
                    array( 'thecrate_header_top_status', '=', '1' ),
                    array( 'tslr_enable_address', '=', '1' ),
                ),
            ),
        ),
    ) );
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Header - Main Big', 'thecrate' ),
        'id'         => 'thecrate_header_bottom_main',
        'subsection' => true,
        'fields'     => array(
            array(         
                'id'       => 'thecrate_header_main_background',
                'type'     => 'background',
                'title'    => esc_attr__('Header (main-header) - background', 'thecrate'),
                'subtitle' => esc_attr__('Default color: #ffffff', 'thecrate'),
                'output'      => array('.navbar-default'),
                'default'  => array(
                    'background-color' => '#ffffff',
                )
            ),
            array(
                'id'       => 'thecrate_header_main_text_color',
                'type'     => 'color',
                'title'    => esc_attr__('Main Header texts color', 'thecrate'), 
                'subtitle' => esc_attr__('Default color: #1a1b22', 'thecrate'),
                'default'  => '#1a1b22',
                'validate' => 'color',
                'output'    => array(
                    'color' => 'header',
                ),
            ),
            array(
                'id'       => 'thecrate_header_icons_color',
                'type'     => 'color',
                'title'    => esc_attr__('Color for Search, Account & Cart icons', 'thecrate'), 
                'subtitle' => esc_attr__('Default color: #222222', 'thecrate'),
                'default'  => '#222222',
                'validate' => 'color',
                'output'    => array(
                    'color' => '.header-nav-actions .thecrate-search-icon, .thecrate-account-link i, .thecrate-nav-burger i',
                    'fill' => 'header .cart-contents svg path',
                ),
            ),
            array(
                'id'       => 'thecrate_header_icons_color_hover',
                'type'     => 'color',
                'title'    => esc_attr__('Color for Search, Account & Cart icons (Hover)', 'thecrate'), 
                'subtitle' => esc_attr__('Default color: #000000', 'thecrate'),
                'default'  => '#000000',
                'validate' => 'color',
                'output'    => array(
                    'color' => '.header-nav-actions .thecrate-search-icon:hover i, header .header-nav-actions .thecrate-account-link:hover i, .thecrate-nav-burger:hover i',
                    'fill' => 'body header .cart-contents svg:hover path',
                ),
            ),

            array(
                'id'       => 'thecrate_header_icons_mobile_styling',
                'type'     => 'checkbox', 
                'title'    => esc_html__('Custom Mobile Color?', 'thecrate'),
                'subtitle' => esc_html__('Custom styling for header icons', 'thecrate'),
                'options'   => array(
                    'status'   => esc_html__( 'Yes', 'thecrate' ),
                )
            ),
            array(
                'id'       => 'thecrate_header_mobile_icons_color',
                'type'     => 'color',
                'title'    => esc_attr__('Mobile Color for Search, Account & Cart icons', 'thecrate'), 
                'subtitle' => esc_attr__('Default color: #222222', 'thecrate'),
                'default'  => '#222222',
                'validate' => 'color',
                'required' => array( 'thecrate_header_icons_mobile_styling', '=', 'true' ),
            ),
            array(
                'id'       => 'thecrate_header_mobile_icons_color_hover',
                'type'     => 'color',
                'title'    => esc_attr__('Mobile Color for Search, Account & Cart icons (Hover)', 'thecrate'), 
                'subtitle' => esc_attr__('Default color: #000000', 'thecrate'),
                'default'  => '#000000',
                'validate' => 'color',
            ),
            array(
                'id'       => 'thecrate_header_is_search',
                'type'     => 'switch', 
                'title'    => esc_attr__('Search Icon Status', 'thecrate'),
                'subtitle' => esc_attr__('Enable or Disable Search Icon".', 'thecrate'),
                'default'  => false,
                'on'       => esc_attr__( 'Enabled', 'thecrate' ),
                'off'      => esc_attr__( 'Disabled', 'thecrate' )
            ),
            array(
                'id'       => 'thecrate_header_is_search_mobile',
                'type'     => 'switch', 
                'title'    => esc_attr__('Search Icon Status - Mobile devices', 'thecrate'),
                'subtitle' => esc_attr__('Enable or Disable Search Icon for tablets and smartphones".', 'thecrate'),
                'default'  => false,
                'on'       => esc_attr__( 'Enabled', 'thecrate' ),
                'off'      => esc_attr__( 'Disabled', 'thecrate' )
            ),
            array(
                'id'       => 'thecrate_header_fixed_sidebar_menu_status',
                'type'     => 'switch',
                'title'    => esc_attr__( 'Burger Sidebar Menu Status', 'thecrate' ),
                'subtitle' => esc_attr__( 'Enable/Disable Burger Sidebar Menu Status', 'thecrate' ),
                'desc'     => esc_attr__( 'This Option Will Enable/Disable The Navigation Burger + Sidebar Menu triggered by the burger menu', 'thecrate' ),
                'default'  => 0,
                'on'       => 'Enabled',
                'off'      => 'Disabled',
            ),
            array(
                'id'       => 'thecrate_header_fixed_sidebar_menu_bgs',
                'type'     => 'color_rgba',
                'title'    => esc_attr__( 'Sidebar Menu Background', 'thecrate' ),
                'subtitle' => esc_attr__( 'Default: #ffffff - Opacity: 1', 'thecrate' ),
                'default'   => array(
                    'color'     => '#ffffff',
                    'alpha'     => '1'
                ),
                'output' => array(
                    'background-color' => '.fixed-sidebar-menu'
                ),
                // These options display a fully functional color palette.  Omit this argument
                // for the minimal color picker, and change as desired.
                'options'       => array(
                    'show_input'                => true,
                    'show_initial'              => true,
                    'show_alpha'                => true,
                    'show_palette'              => true,
                    'show_palette_only'         => false,
                    'show_selection_palette'    => true,
                    'max_palette_size'          => 10,
                    'allow_empty'               => true,
                    'clickout_fires_change'     => false,
                    'choose_text'               => 'Choose',
                    'cancel_text'               => 'Cancel',
                    'show_buttons'              => true,
                    'use_extended_classes'      => true,
                    'palette'                   => null,  // show default
                    'input_text'                => 'Select Color'
                ),                        
                'required' => array( 'thecrate_header_fixed_sidebar_menu_status', '=', '1' ),
            ),
            array(
                'id'       => 'thecrate_header_fixed_sidebar_menu_text_color',
                'type'     => 'color',
                'title'    => esc_attr__('Texts Color', 'thecrate'), 
                'default'  => '#000000',
                'validate' => 'color',
                'output'    => array(
                    'color' => '.fixed-sidebar-menu .logo, .fixed-sidebar-menu .widget-title, .fixed-sidebar-menu .widget-title'
                ),
                'required' => array( 'thecrate_header_fixed_sidebar_menu_status', '=', '1' ),
            ),
            array(
                'id'       => 'thecrate_header_fixed_sidebar_menu_select_sidebar',
                'type'     => 'select',
                'data'     => 'sidebars',
                'title'    => esc_attr__( 'Select Sidebar', 'thecrate' ),
                'subtitle' => esc_attr__( 'Select Sidebar to be shown on Sidebar Navigation Menu.', 'thecrate' ),
                'default'   => 'burgersidebar',
                'required' => array( 'thecrate_header_fixed_sidebar_menu_status', '=', '1' ),
            ),
        ),
    ) );
    Redux::setSection( $opt_name, array(
        'title'      => esc_html__( 'Nav Menu', 'thecrate' ),
        'id'         => 'thecrate_styling_nav_menu',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_nav_menu_color',
                'type'     => 'color',
                'title'    => esc_attr__('Nav Menu Link Color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #1a1b22', 'thecrate'),
                'default'  => '#1a1b22',
                'validate' => 'color',
                'output' => array(
                    'color' => '#navbar .menu-item > a,
                                .navbar-nav .search_products a,
                                .navbar-default .navbar-nav > li > a:hover, .navbar-default .navbar-nav > li > a:focus,
                                .navbar-default .navbar-nav > li > a',
                )
            ),
            array(
                'id'       => 'thecrate_nav_menu_color_hover',
                'type'     => 'color',
                'title'    => esc_attr__('Nav Menu Link Color - Hover', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #F26226', 'thecrate'),
                'default'  => '#F26226',
                'validate' => 'color',
                'output' => array(
                    'color' => '#navbar .menu-item.current_page_ancestor.current_page_parent > a, 
                                    #navbar .menu-item.current_page_item.current_page_parent > a, 
                                    #navbar .menu-item:hover > a'
                )
            ),
            array(
                'id'        => 'thecrate_nav_menu_links_size',
                'type'      => 'slider',
                'title'     => esc_attr__('Nav Links Font-Size', 'thecrate'),
                'subtitle'  => esc_attr__('Font-size of the navigation links.', 'thecrate'),
                "default"   => 15,
                "min"       => 1,
                "step"      => 1,
                "max"       => 100,
                'display_value' => 'label',
            ),
            // Submenus background
            array(
                'id'       => 'thecrate_nav_submenu_background',
                'type'     => 'color',
                'title'    => esc_attr__('Nav Submenus Background Color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #ffffff', 'thecrate'),
                'default'  => '#ffffff',
                'validate' => 'color',
                'output' => array(
                    'background-color' => '#navbar .sub-menu, .navbar ul li ul.sub-menu',
                )
            ),
            // Submenus link color
            array(
                'id'       => 'thecrate_nav_submenu_link_color',
                'type'     => 'color',
                'title'    => esc_attr__('Nav Submenus Link Color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #1a1b22', 'thecrate'),
                'default'  => '#1a1b22',
                'validate' => 'color',
                'output' => array(
                    'color' => '#navbar ul.sub-menu li > a',
                )
            ),
            array(
                'id'       => 'thecrate_nav_submenu_link_color_hover',
                'type'     => 'color',
                'title'    => esc_attr__('Nav Submenus Link Color - Hover', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #F26226', 'thecrate'),
                'default'  => '#F26226',
                'validate' => 'color',
                'output' => array(
                    'color' => '#navbar ul.sub-menu li:hover > a',
                )
            ),
            // Submenus link background
            array(
                'id'       => 'thecrate_nav_submenu_link_background',
                'type'     => 'color',
                'title'    => esc_html__('Nav Submenus Link Background', 'thecrate'), 
                'subtitle' => esc_html__('Default: transparent', 'thecrate'),
                'default'  => 'transparent',
                'validate' => 'color',
                'output' => array(
                    'background-color' => '#navbar ul.sub-menu li > a',
                )
            ),
            array(
                'id'       => 'thecrate_nav_submenu_link_background_hover',
                'type'     => 'color',
                'title'    => esc_html__('Nav Submenus Link Background - Hover', 'thecrate'), 
                'subtitle' => esc_html__('Default: transparent', 'thecrate'),
                'default'  => 'transparent',
                'validate' => 'color',
                'output' => array(
                    'background-color' => '#navbar ul.sub-menu li:hover > a',
                )
            ),
            array(
                'id'       => 'thecrate_nav_mobile_custom_styling',
                'type'     => 'checkbox', 
                'title'    => esc_html__('Nav Mobile Custom Styling', 'thecrate'),
                'subtitle' => esc_html__('Custom styling for nav menus', 'thecrate'),
                'options'   => array(
                    'status'   => esc_html__( 'Yes', 'thecrate' ),
                )
            ),
            array(
                'id'       => 'thecrate_nav_mobile_toggled_header',
                'type'     => 'color',
                'title'    => esc_attr__('Nav Mobile: Toggled Header Background', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #ffffff', 'thecrate'),
                'default'  => '#ffffff',
                'validate' => 'color',
                'required' => array( 'thecrate_nav_mobile_custom_styling', '=', 'true' ),
            ),
            array(
                'id'       => 'thecrate_nav_mobile_menu_color',
                'type'     => 'color',
                'title'    => esc_attr__('Nav Mobile: Menu Link Color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #1a1b22', 'thecrate'),
                'default'  => '#1a1b22',
                'validate' => 'color',
                'required' => array( 'thecrate_nav_mobile_custom_styling', '=', 'true' ),
            ),
            array(
                'id'       => 'thecrate_nav_mobile_menu_color_hover',
                'type'     => 'color',
                'title'    => esc_attr__('Nav Mobile: Menu Link Color - Hover', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #F26226', 'thecrate'),
                'default'  => '#F26226',
                'validate' => 'color',
                'required' => array( 'thecrate_nav_mobile_custom_styling', '=', 'true' ),
            ),
            array(
                'id'        => 'thecrate_nav_mobile_menu_links_size',
                'type'      => 'slider',
                'title'     => esc_attr__('Nav Mobile: Links Font-Size', 'thecrate'),
                'subtitle'  => esc_attr__('Font-size of the navigation links.', 'thecrate'),
                "default"   => 15,
                "min"       => 1,
                "step"      => 1,
                "max"       => 100,
                'display_value' => 'label',
                'required' => array( 'thecrate_nav_mobile_custom_styling', '=', 'true' ),
            ),
            // Submenus background
            array(
                'id'       => 'thecrate_nav_mobile_submenu_background',
                'type'     => 'color',
                'title'    => esc_attr__('Nav Mobile: Submenus Background Color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #ffffff', 'thecrate'),
                'default'  => '#ffffff',
                'validate' => 'color',
                'required' => array( 'thecrate_nav_mobile_custom_styling', '=', 'true' ),
            ),
            // Submenus link color
            array(
                'id'       => 'thecrate_nav_mobile_submenu_link_color',
                'type'     => 'color',
                'title'    => esc_attr__('Nav Mobile: Submenus Link Color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #1a1b22', 'thecrate'),
                'default'  => '#1a1b22',
                'validate' => 'color',
                'required' => array( 'thecrate_nav_mobile_custom_styling', '=', 'true' ),
            ),
            array(
                'id'       => 'thecrate_nav_mobile_submenu_link_color_hover',
                'type'     => 'color',
                'title'    => esc_attr__('Nav Mobile: Submenus Link Color - Hover', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #F26226', 'thecrate'),
                'default'  => '#F26226',
                'validate' => 'color',
                'required' => array( 'thecrate_nav_mobile_custom_styling', '=', 'true' ),
            ),
            // Burger Navigation icon
            array(
                'id'       => 'thecrate_burger_nav_color',
                'type'     => 'color',
                'title'    => esc_attr__('Burger Navigation Icon: Color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #000000', 'thecrate'),
                'default'  => '#000000',
                'validate' => 'color',
                'output' => array(
                    'background' => '.navbar-default .navbar-toggle .icon-bar',
                ),
            ),
        ),
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Header Button', 'thecrate' ),
        'id'         => 'thecrate_header_button',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'tslr_header_btn_is_active',
                'type'     => 'switch', 
                'title'    => esc_attr__('Header Button Status', 'thecrate'),
                'subtitle' => esc_attr__('Enable or Disable Header Button".', 'thecrate'),
                'default'  => true,
                'on'       => esc_attr__( 'Enabled', 'thecrate' ),
                'off'      => esc_attr__( 'Disabled', 'thecrate' )
            ),
            array(
                'id'       => 'tslr_header_btn_bg_normal',
                'type'     => 'color',
                'title'    => esc_attr__('Header Button Color', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #2a3cb7', 'thecrate'),
                'default'  => '#2a3cb7',
                'validate' => 'color',
                'output' => array(
                    'background' => '.header-nav-actions .header-btn',
                ),
                'required' => array( 'tslr_header_btn_is_active', '=', '1' ),
            ),
            array(
                'id'       => 'tslr_header_btn_bg_hover',
                'type'     => 'color',
                'title'    => esc_attr__('Header Button Color - hover', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #f26226', 'thecrate'),
                'default'  => '#f26226',
                'validate' => 'color',
                'output' => array(
                    'background' => '.header-nav-actions .header-btn:hover',
                ),
                'required' => array( 'tslr_header_btn_is_active', '=', '1' ),
            ),
            array(
                'id' => 'tslr_header_btn_custom_url',
                'type' => 'text',
                'title' => esc_attr__('Header Button link url', 'thecrate'),
                'subtitle' => esc_attr__('Enter a url for "Header Button"', 'thecrate'),
                'default' => '#',
                'required' => array( 'tslr_header_btn_is_active', '=', '1' ),
            ),
            array(
                'id' => 'tslr_header_btn_custom_text',
                'type' => 'text',
                'title' => esc_attr__('Button Text', 'thecrate'),
                'subtitle' => esc_attr__('Replace the default "Subscribe" button text', 'thecrate'),
                'desc'      => esc_attr__( 'Only for single language websites. For multi-language sites, please override the header template on your child theme.', 'thecrate' ),
                'default' => '',
                'required' => array( 'tslr_header_btn_is_active', '=', '1' ),
            ),
            array(
                'id'       => 'tslr_header_btn_target',
                'type'     => 'checkbox', 
                'title'    => esc_html__('Link Target (New Tab)?', 'thecrate'),
                'subtitle' => esc_html__('Open link in a new tab?', 'thecrate'),
                'options'   => array(
                    'target'   => esc_html__( 'Yes', 'thecrate' ),
                ),
                'default' => array(
                    'target' => '0',
                ),
                'required' => array( 'tslr_header_btn_is_active', '=', '1' ),
            ),
        )
    ));

    /**
    ||-> SECTION: Footer Settings
    */
    Redux::setSection( $opt_name, array(
        'title' => esc_attr__( 'Footer Settings', 'thecrate' ),
        'id'    => 'thecrate_footer2',
        'icon'  => 'el el-icon-arrow-down'
    ) );

    $footer_top_output = array('footer .footer-top');
    if (thecrate_redux('thecrate_footer_bg_on_full_footer') == 1) {
        $footer_top_output = array('');
    }
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Footer General', 'thecrate' ),
        'id'         => 'thecrate_footer_general',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_footer_general_background',
                'type'     => 'background',
                'title'    => esc_attr__('Footer (Top/Bottom)', 'thecrate'),
                'subtitle' => esc_attr__('Will be applied to Footer Top & Bottom (Footer Top & Footer Bottom has to be transparent)', 'thecrate'),
                'output'      => array('footer.footer-main-group'),
            ),
        ),
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Footer Top', 'thecrate' ),
        'id'         => 'thecrate_footer2_top',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_footer_row_1',
                'type'     => 'switch',
                'title'    => esc_attr__( 'Footer Top - Status', 'thecrate' ),
                'subtitle' => esc_attr__( 'Enable/Disable Footer Top', 'thecrate' ),
                'default'  => 1,
                'on'       => 'Enabled',
                'off'      => 'Disabled',
            ),
            array(
                'id'       => 'thecrate_footer_top_background',
                'type'     => 'background',
                'title'    => esc_attr__('Footer (top) - background', 'thecrate'),
                'subtitle' => esc_attr__('Footer background with image or color.', 'thecrate'),
                'output'      => array('footer .footer-top'),
                'default'  => array(
                    'background-color' => '#363636'
                ),
                'required' => array( 'thecrate_footer_row_1', '=', '1' ),
            ),
            array(
                'id'        => 'thecrate_footer_top_texts_color',
                'type'      => 'color_rgba',
                'title'     => esc_attr__( 'Footer Top Text Color', 'thecrate' ),
                'subtitle'  => esc_attr__( 'Set color and alpha channel', 'thecrate' ),
                'desc'      => esc_attr__( 'Set color and alpha channel for footer texts (Especially for widget titles)', 'thecrate' ),
                'output'    => array('color' => 'footer .footer-top h1.widget-title, footer .footer-top h3.widget-title, footer .footer-top .widget-title'),
                'default'   => array(
                    'color'     => '#ffffff',
                    'alpha'     => 1
                ),
                'options'       => array(
                    'show_input'                => true,
                    'show_initial'              => true,
                    'show_alpha'                => true,
                    'show_palette'              => true,
                    'show_palette_only'         => false,
                    'show_selection_palette'    => true,
                    'max_palette_size'          => 10,
                    'allow_empty'               => true,
                    'clickout_fires_change'     => false,
                    'choose_text'               => 'Choose',
                    'cancel_text'               => 'Cancel',
                    'show_buttons'              => true,
                    'use_extended_classes'      => true,
                    'palette'                   => null,  // show default
                    'input_text'                => 'Select Color'
                ),                        
                'required' => array( 'thecrate_footer_row_1', '=', '1' ),
            ),
            array(
                'id'       => 'thecrate_footer_row_1_layout',
                'type'     => 'image_select',
                'title'    => esc_attr__( 'Footer Top - Layout', 'thecrate' ),
                'options'  => array(
                    '1' => array(
                        'alt' => esc_attr__('Footer 1 Column', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_1.png'
                    ),
                    '2' => array(
                        'alt' => esc_attr__('Footer 2 Columns', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_2.png'
                    ),
                    '3' => array(
                        'alt' => esc_attr__('Footer 3 Columns', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_3.png'
                    ),
                    '4' => array(
                        'alt' => esc_attr__('Footer 4 Columns', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_4.png'
                    ),
                    '5' => array(
                        'alt' => esc_attr__('Footer 5 Columns', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_5.png'
                    ),
                    '6' => array(
                        'alt' => esc_attr__('Footer 6 Columns', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_6.png'
                    ),
                    'column_half_sub_half' => array(
                        'alt' => esc_attr__('Footer 6 + 3 + 3', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_half_sub_half.png'
                    ),
                    'column_sub_half_half' => array(
                        'alt' => esc_attr__('Footer 3 + 3 + 6', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_sub_half_half.png'
                    ),
                    'column_sub_fourth_third' => array(
                        'alt' => esc_attr__('Footer 2 + 2 + 2 + 2 + 4', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_sub_fourth_third.png'
                    ),
                    'column_third_sub_fourth' => array(
                        'alt' => esc_attr__('Footer 4 + 2 + 2 + 2 + 2', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_third_sub_fourth.png'
                    ),
                    'column_sub_third_half' => array(
                        'alt' => esc_attr__('Footer 2 + 2 + 2 + 6', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_sub_third_half.png'
                    ),
                    'column_half_sub_third' => array(
                        'alt' => esc_attr__('Footer 6 + 2 + 2 + 2', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_sub_third_half2.png'
                    ),
                    'column_four_two_two_four' => array(
                        'alt' => esc_attr__('Footer 4 + 2 + 2 + 4', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_4_2_2_4.png'
                    ),
                    'column_3_2_2_2_3' => array(
                        'alt' => esc_attr__('Footer 3 + 2 + 2 + 2 + 3', 'thecrate'),
                        'img' => get_template_directory_uri().'/redux-framework/assets/footer_columns/column_3_2_2_2_3.png'
                    ),
                ),
                'default'  => 'column_3_2_2_2_3',
                'required' => array( 'thecrate_footer_row_1', '=', '1' ),
            ),
            array(
                'id'             => 'thecrate_footer_row_1_spacing',
                'type'           => 'spacing',
                'output'         => array('.footer-row-1'),
                'mode'           => 'padding',
                'units'          => array('em', 'px'),
                'units_extended' => 'false',
                'title'          => esc_attr__('Footer Top - Padding', 'thecrate'),
                'subtitle'       => esc_attr__('Choose the spacing for the first row from footer.', 'thecrate'),
                'required' => array( 'thecrate_footer_row_1', '=', '1' ),
                'default'            => array(
                    'padding-top'     => '60px', 
                    'padding-bottom'  => '60px', 
                    'units'          => 'px', 
                )
            ),
            array(
                'id'             => 'thecrate_footer_row_1margin',
                'type'           => 'spacing',
                'output'         => array('.footer-row-1'),
                'mode'           => 'margin',
                'units'          => array('em', 'px'),
                'units_extended' => 'false',
                'title'          => esc_attr__('Footer Top - Margin', 'thecrate'),
                'subtitle'       => esc_attr__('Choose the margin for the first row from footer.', 'thecrate'),
                'required' => array( 'thecrate_footer_row_1', '=', '1' ),
                'default'            => array(
                    'margin-top'     => '0px', 
                    'margin-bottom'  => '0px', 
                    'units'          => 'px', 
                )
            ),
            array( 
                'id'       => 'thecrate_footer_row_1border',
                'type'     => 'border',
                'title'    => esc_attr__('Footer Top - Borders', 'thecrate'),
                'subtitle' => esc_attr__('Only color validation can be done on this field', 'thecrate'),
                'output'   => array('.footer-row-1'),
                'all'      => false,
                'required' => array( 'thecrate_footer_row_1', '=', '1' ),
                'default'  => array(
                    'border-color'  => '#515b5e', 
                    'border-style'  => 'solid', 
                    'border-top'    => '0', 
                    'border-right'  => '0', 
                    'border-bottom' => '0', 
                    'border-left'   => '0'
                )
            ),
        ),
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Footer Bottom (Copyright)', 'thecrate' ),
        'id'         => 'thecrate_footer_bottom',
        'subsection' => true,
        'fields'     => array(
            array(
                'id' => 'thecrate_footer_text_center',
                'type' => 'editor',
                'title' => esc_html__('Footer Center (Copyright)', 'thecrate'),
                'default' => esc_html__('Handcrafted with Love by ThemeSLR.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_footer_text_right',
                'type' => 'editor',
                'title' => esc_html__('Footer Right (Card Icons)', 'thecrate'),
                'default' => '<img width="200" src="'.esc_url(get_template_directory_uri().'/images/card-icons.png').'" alt="'.esc_attr__('card icons', 'thecrate').'" />',
            ),
            array(         
                'id'       => 'thecrate_footer_bottom_background',
                'type'     => 'background',
                'title'    => esc_html__('Footer (bottom) - background', 'thecrate'),
                'subtitle' => esc_html__('Footer background with image or color.', 'thecrate'),
                'output'      => array('footer .footer'),
                'default'  => array(
                    'background-color' => '#363636',
                )
            ),
            array(
                'id'        => 'thecrate_footer_bottom_texts_color',
                'type'      => 'color_rgba',
                'title'     => esc_attr__( 'Footer Bottom Text Color', 'thecrate' ),
                'subtitle'  => esc_attr__( 'Set color and alpha channel', 'thecrate' ),
                'desc'      => esc_attr__( 'Set color and alpha channel for footer texts (Especially for widget titles)', 'thecrate' ),
                'output'    => array('color' => 'footer .footer .widget-title'),
                'default'   => array(
                    'color'     => '#ffffff',
                    'alpha'     => 1
                ),
                'options'       => array(
                    'show_input'                => true,
                    'show_initial'              => true,
                    'show_alpha'                => true,
                    'show_palette'              => true,
                    'show_palette_only'         => false,
                    'show_selection_palette'    => true,
                    'max_palette_size'          => 10,
                    'allow_empty'               => true,
                    'clickout_fires_change'     => false,
                    'choose_text'               => 'Choose',
                    'cancel_text'               => 'Cancel',
                    'show_buttons'              => true,
                    'use_extended_classes'      => true,
                    'palette'                   => null,  // show default
                    'input_text'                => 'Select Color'
                ),                        
            ),
        ),
    ));

    /**
    ||-> SECTION: Contact Settings
    */
    Redux::setSection( $opt_name, array(
        'title' => esc_attr__( 'Contact Settings', 'thecrate' ),
        'id'    => 'thecrate_footer',
        'icon'  => 'el el-icon-map-marker-alt'
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Contact', 'thecrate' ),
        'id'         => 'thecrate_footer_settings',
        'subsection' => true,
        'fields'     => array(
            array(
                'id' => 'thecrate_contact_phone',
                'type' => 'text',
                'title' => esc_attr__('Phone Number', 'thecrate'),
                'subtitle' => esc_attr__('Contact phone number displayed on the contact us page.', 'thecrate'),
                'default' => ' +1 3443 933 223'
            ),
            array(
                'id' => 'thecrate_contact_email',
                'type' => 'text',
                'title' => esc_attr__('Email Address', 'thecrate'),
                'subtitle' => esc_attr__('Contact email displayed on the contact us page., additional info is good in here.', 'thecrate'),
                'default' => 'hello@thecrate.tld'
            ),
            array(
                'id' => 'thecrate_contact_address',
                'type' => 'text',
                'title' => esc_attr__('Address', 'thecrate'),
                'subtitle' => esc_attr__('Enter your contact address', 'thecrate'),
                'default' => 'Crattos Street 246, Alabama, CA, USA'
            )
        ),
    ));
 
    /**
    ||-> SECTION: Shop Settings
    */
    Redux::setSection( $opt_name, array(
        'title' => esc_html__( 'Shop Settings', 'thecrate' ),
        'id'    => 'thecrate_shop',
        'icon'  => 'el el-icon-comment'
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_html__( 'Shop Archive', 'thecrate' ),
        'id'         => 'thecrate_shop_archive',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_shop_layout',
                'type'     => 'image_select',
                'title'    => __( 'Shop List Products Layout', 'thecrate' ),
                'subtitle' => __( 'Select Shop List Products layout.', 'thecrate' ),
                'options'  => array(
                    'thecrate_shop_left_sidebar' => array(
                        'img' => get_template_directory_uri().'/redux-framework/assets/sidebar-left.jpg'
                    ),
                    'thecrate_shop_fullwidth' => array(
                        'img' => get_template_directory_uri().'/redux-framework/assets/sidebar-no.jpg'
                    ),
                    'thecrate_shop_right_sidebar' => array(
                        'img' => get_template_directory_uri().'/redux-framework/assets/sidebar-right.jpg'
                    )
                ),
                'default'  => 'thecrate_shop_left_sidebar'
            ),
            array(
                'id'       => 'thecrate_shop_layout_sidebar',
                'type'     => 'select',
                'data'     => 'sidebars',
                'title'    => __( 'Shop List Sidebar', 'thecrate' ),
                'subtitle' => __( 'Select Shop List Sidebar.', 'thecrate' ),
                'default'   => 'woocommerce',
                'required' => array('thecrate_shop_layout', '!=', 'thecrate_shop_fullwidth'),
            ),
            array(
                'id'        => 'thecrate-shop-columns',
                'type'      => 'select',
                'title'     => __('Number of shop columns', 'thecrate'),
                'subtitle'  => __('Number of products per column to show on shop list template.', 'thecrate'),
                'options'   => array(
                    '2'   => '2 columns',
                    '3'   => '3 columns',
                    '4'   => '4 columns'
                ),
                'default'   => '3',
            ),
            array(
                'id'        => 'thecrate-shop-products-per-page',
                'type'      => 'slider',
                'title'     => __('Products per page', 'thecrate'),
                'subtitle'  => __('Number of products per page. Do not keep this number bigger than 20-30, otherwise your shop will load slowly.', 'thecrate'),
                'desc'      => esc_attr__('Min: 1, max: 100, step: 1, default value: 12', 'thecrate'),
                "default"   => 12,
                "min"       => 1,
                "step"      => 1,
                "max"       => 100,
                'display_value' => 'label',
            ),
            array(
                'id'       => 'thecrate_shop_product_layout',
                'type'     => 'radio',
                'title'    => __( 'Shop Product Layout', 'thecrate' ),
                'subtitle' => __( 'Select Product layout', 'thecrate' ),
                'options'  => array(
                    'woo-grid-no-border'   => 'Without Border',
                    'woo-grid-with-border'   => 'With Border',
                ),
                'default'  => 'woo-grid-no-border'
            ),
            array(
                'id'       => 'thecrate_shop_product_layout_radius',
                'type'     => 'radio',
                'title'    => __( 'Shop Product Layout Radius', 'thecrate' ),
                'subtitle' => __( 'Select Product layout Radius', 'thecrate' ),
                'options'  => array(
                    '0' => esc_attr__('Square (Default)', 'thecrate'),
                    '5' => esc_attr__('Rounded (5px Radius)', 'thecrate'),
                    '30' => esc_attr__('Round (30px Radius)', 'thecrate'),
                ),
                'default'  => '0',
                'required' => array('thecrate_shop_product_layout', 'equals', 'woo-grid-with-border'),
            ),
            array(
                'id'       => 'thecrate_fixed_sidebar_cart',
                'type'     => 'switch', 
                'title'    => esc_attr__('Sidebar Cart *(toggled on add to cart)', 'thecrate'),
                'subtitle' => esc_attr__('The toggled Sidebar Cart on add to cart', 'thecrate'),
                'default'  => true,
                'on'       => esc_attr__( 'On', 'thecrate' ),
                'off'      => esc_attr__( 'Off', 'thecrate' )
            ),
            array(
                'id'       => 'thecrate_shop_sale_badge_background',
                'type'     => 'color',
                'title'    => esc_attr__('Sale Badge Background:', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #2a3cb7', 'thecrate'),
                'default'  => '#2a3cb7',
                'validate' => 'color',
            ),
            array(
                'id'       => 'thecrate_shop_sale_badge_color',
                'type'     => 'color',
                'title'    => esc_attr__('Sale Badge text color:', 'thecrate'), 
                'subtitle' => esc_attr__('Default: #ffffff', 'thecrate'),
                'default'  => '#ffffff',
                'validate' => 'color',
                'output' => array(
                    'color' => '.woocommerce span.onsale, .woocommerce ul.products li.product .onsale',
                )
            ),
        ),
    ));

    /**
    ||-> SECTION: Blog Settings
    */
    Redux::setSection( $opt_name, array(
        'title' => esc_html__( 'Blog Settings', 'thecrate' ),
        'id'    => 'thecrate_blog',
        'icon'  => 'el el-icon-comment'
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_html__( 'Blog Archive', 'thecrate' ),
        'id'         => 'thecrate_blog_archive',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_blog_layout',
                'type'     => 'image_select',
                'compiler' => true,
                'title'    => esc_attr__( 'Blog List Layout', 'thecrate' ),
                'subtitle' => esc_attr__( 'Select Blog List layout.', 'thecrate' ),
                'options'  => array(
                    'thecrate_blog_left_sidebar' => array(
                        'alt' => esc_attr__('2 Columns - Left sidebar', 'thecrate' ),
                        'img' => get_template_directory_uri().'/redux-framework/assets/sidebar-left.jpg'
                    ),
                    'thecrate_blog_fullwidth' => array(
                        'alt' => esc_attr__('1 Column - Full width', 'thecrate' ),
                        'img' => get_template_directory_uri().'/redux-framework/assets/sidebar-no.jpg'
                    ),
                    'thecrate_blog_right_sidebar' => array(
                        'alt' => esc_attr__('2 Columns - Right sidebar', 'thecrate' ),
                        'img' => get_template_directory_uri().'/redux-framework/assets/sidebar-right.jpg'
                    )
                ),
                'default'  => 'thecrate_blog_right_sidebar'
            ),
            array(
                'id'       => 'thecrate_blog_layout_sidebar',
                'type'     => 'select',
                'data'     => 'sidebars',
                'title'    => esc_html__( 'Blog List Sidebar', 'thecrate' ),
                'subtitle' => esc_html__( 'Select Blog List Sidebar.', 'thecrate' ),
                'default'   => 'sidebar-1',
                'required' => array('thecrate_blog_layout', '!=', 'thecrate_blog_fullwidth'),
            ),
            array(
                'id'        => 'thecrate_blog_display_type',
                'type'      => 'select',
                'title'     => esc_attr__('How to display posts', 'thecrate'),
                'subtitle'  => esc_attr__('Select how you want to display post on blog list.', 'thecrate'),
                'options'   => array(
                        'list'   => 'List',
                        'grid'   => 'Grid'
                    ),
                'default'   => 'list',
            ),

            array(
                'id'        => 'thecrate_blog_grid_columns',
                'type'      => 'select',
                'title'     => esc_attr__('Grid columns', 'thecrate'),
                'subtitle'  => esc_attr__('Select how many columns you want.', 'thecrate'),
                'options'   => array(
                        '1'   => '1',
                        '2'   => '2',
                        '3'   => '3',
                        '4'   => '4'
                    ),
                'default'   => '1',
                'required' => array('thecrate_blog_display_type', 'equals', 'grid'),
            )
        ),
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Single Post', 'thecrate' ),
        'id'         => 'thecrate_blog_single_pos',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'thecrate_single_blog_layout',
                'type'     => 'image_select',
                'compiler' => true,
                'title'    => esc_attr__( 'Single Blog List Layout', 'thecrate' ),
                'subtitle' => esc_attr__( 'Select Blog List layout.', 'thecrate' ),
                'options'  => array(
                    'thecrate_single_blog_left_sidebar' => array(
                        'alt' => esc_attr__('2 Columns - Left sidebar', 'thecrate' ),
                        'img' => get_template_directory_uri().'/redux-framework/assets/sidebar-left.jpg'
                    ),
                    'thecrate_single_blog_fullwidth' => array(
                        'alt' => esc_attr__('1 Column - Full width', 'thecrate' ),
                        'img' => get_template_directory_uri().'/redux-framework/assets/sidebar-no.jpg'
                    ),
                    'thecrate_single_blog_right_sidebar' => array(
                        'alt' => esc_attr__('2 Columns - Right sidebar', 'thecrate' ),
                        'img' => get_template_directory_uri().'/redux-framework/assets/sidebar-right.jpg'
                    )
                ),
                'default'  => 'thecrate_single_blog_fullwidth'
            ),
            array(
                'id'       => 'thecrate_single_blog_layout_sidebar',
                'type'     => 'select',
                'data'     => 'sidebars',
                'title'    => esc_attr__( 'Single Blog List Sidebar', 'thecrate' ),
                'subtitle' => esc_attr__( 'Select Blog List Sidebar.', 'thecrate' ),
                'default'   => 'sidebar-1',
                'required' => array('thecrate_single_blog_layout', '!=', 'thecrate_single_blog_fullwidth'),
            ),
            array(
                'id'          => 'thecrate_single_post_typography',
                'type'        => 'typography', 
                'title'       => esc_attr__('Blog Post Font family', 'thecrate'),
                'subtitle'    => esc_attr__( 'Default color: #454646; Font-size: 18px; Line-height: 29px;', 'thecrate' ),
                'google'      => true, 
                'font-size'   => true,
                'line-height' => true,
                'color'       => true,
                'font-backup' => false,
                'text-align'  => false,
                'letter-spacing'  => false,
                'font-weight'  => false,
                'font-style'  => false,
                'subsets'     => false,
                'output'      => array('.single article .article-content p, .search-no-results p.page-title, p'),
                'units'       =>'px',
                'default'     => array(
                    'color' => '#454646', 
                    'font-size' => '16px', 
                    'line-height' => '26px', 
                    'font-family' => 'Work Sans', 
                    'google'      => true
                ),
            ),
            array(
                'id'       => 'thecrate_post_featured_image',
                'type'     => 'switch', 
                'title'    => esc_attr__('Single post featured image.', 'thecrate'),
                'subtitle' => esc_attr__('Show or Hide the featured image from blog post page.".', 'thecrate'),
                'default'  => true,
            ),
            array(
                'id'       => 'thecrate_enable_related_posts',
                'type'     => 'switch', 
                'title'    => esc_attr__('Related Posts', 'thecrate'),
                'subtitle' => esc_attr__('Enable or disable related posts', 'thecrate'),
                'default'  => true,
            ),
            array(
                'id'       => 'thecrate_enable_authorbio',
                'type'     => 'switch', 
                'title'    => esc_attr__('About Author', 'thecrate'),
                'subtitle' => esc_attr__('Enable or disable "About author" section on single post', 'thecrate'),
                'default'  => false,
            ),
        ),
    ));

    /**
    ||-> SECTION: Social Media Settings
    */
    Redux::setSection( $opt_name, array(
        'title' => esc_attr__( 'Social Media Settings', 'thecrate' ),
        'id'    => 'thecrate_social_media',
        'icon'  => 'el el-icon-myspace'
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Social Media', 'thecrate' ),
        'id'         => 'thecrate_social_media_settings',
        'icon' => 'el-icon-map-marker-alt',
        'subsection' => true,
        'fields'     => array(
            array(
                'id' => 'thecrate_social_fb',
                'type' => 'text',
                'title' => esc_html__('Facebook URL', 'thecrate'),
                'subtitle' => esc_html__('Type your Facebook url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_tw',
                'type' => 'text',
                'title' => esc_html__('Twitter username', 'thecrate'),
                'subtitle' => esc_html__('Type your Twitter username.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_pinterest',
                'type' => 'text',
                'title' => esc_html__('Pinterest URL', 'thecrate'),
                'subtitle' => esc_html__('Type your Pinterest url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_skype',
                'type' => 'text',
                'title' => esc_html__('Skype Name', 'thecrate'),
                'subtitle' => esc_html__('Type your Skype username.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_instagram',
                'type' => 'text',
                'title' => esc_html__('Instagram URL', 'thecrate'),
                'subtitle' => esc_html__('Type your Instagram url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_youtube',
                'type' => 'text',
                'title' => esc_html__('YouTube URL', 'thecrate'),
                'subtitle' => esc_html__('Type your YouTube url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_tiktok',
                'type' => 'text',
                'title' => esc_html__('TikTok Channel URL', 'thecrate'),
                'subtitle' => esc_html__('Type your TikTok Channel url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_dribbble',
                'type' => 'text',
                'title' => esc_html__('Dribbble URL', 'thecrate'),
                'subtitle' => esc_html__('Type your Dribbble url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_linkedin',
                'type' => 'text',
                'title' => esc_html__('LinkedIn URL', 'thecrate'),
                'subtitle' => esc_html__('Type your LinkedIn url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_deviantart',
                'type' => 'text',
                'title' => esc_html__('Deviant Art URL', 'thecrate'),
                'subtitle' => esc_html__('Type your Deviant Art url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_digg',
                'type' => 'text',
                'title' => esc_html__('Digg URL', 'thecrate'),
                'subtitle' => esc_html__('Type your Digg url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_flickr',
                'type' => 'text',
                'title' => esc_html__('Flickr URL', 'thecrate'),
                'subtitle' => esc_html__('Type your Flickr url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_stumbleupon',
                'type' => 'text',
                'title' => esc_html__('Stumbleupon URL', 'thecrate'),
                'subtitle' => esc_html__('Type your Stumbleupon url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_tumblr',
                'type' => 'text',
                'title' => esc_html__('Tumblr URL', 'thecrate'),
                'subtitle' => esc_html__('Type your Tumblr url.', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_social_vimeo',
                'type' => 'text',
                'title' => esc_html__('Vimeo URL', 'thecrate'),
                'subtitle' => esc_html__('Type your Vimeo url.', 'thecrate'),
            )
        ),
    ));
    Redux::setSection( $opt_name, array(
        'subsection' => true,
        'icon' => 'el-icon-share',
        'title' => esc_attr__('Social Shares', 'thecrate'),
        'fields' => array(
            array(
                'id'       => 'thecrate_social_share_links',
                'type'     => 'checkbox', 
                'title'    => esc_html__('Social Shares', 'thecrate'),
                'subtitle' => esc_html__('Choose what social share links to be listed (product pages & posts)', 'thecrate'),
                'options'   => array(
                    'twitter'   => esc_html__( 'Twitter', 'thecrate' ),
                    'facebook'   => esc_html__( 'Facebook', 'thecrate' ),
                    'whatsapp'   => esc_html__( 'Whatsapp', 'thecrate' ),
                    // 'messenger'   => esc_html__( 'Messenger', 'thecrate' ),
                    'pinterest'   => esc_html__( 'Pinterest', 'thecrate' ),
                    'linkedin'   => esc_html__( 'LinkedIn', 'thecrate' ),
                    'telegram'   => esc_html__( 'Telegram', 'thecrate' ),
                    'email'   => esc_html__( 'Email', 'thecrate' ),
                ),
                'default' => array(
                    'twitter' => '1', 
                    'facebook' => '1', 
                    'whatsapp' => '1',
                    // 'messenger' => '1',
                    'pinterest' => '1',
                    'linkedin' => '1',
                    'telegram' => '0',
                    'email' => '0',
                )
            ),
            array(
                'id'       => 'thecrate_social_share_locations',
                'type'     => 'checkbox', 
                'title'    => esc_html__('Social Shares Locations', 'thecrate'),
                'subtitle' => esc_html__('Enable or disable social share links on product pages or posts', 'thecrate'),
                'options'   => array(
                    'product'   => esc_html__( 'Product Single', 'thecrate' ),
                    'post'   => esc_html__( 'Post Single', 'thecrate' ),
                ),
                'default' => array(
                    'product' => '1', 
                    'post' => '0',
                )
            ),
        )
    ));
    Redux::setSection( $opt_name, array(
        'title'      => esc_attr__( 'Tweets Feed', 'thecrate' ),
        'icon' => 'el-icon-retweet',
        'id'         => 'thecrate_social_media_tweets_feed',
        'subsection' => true,
        'fields'     => array(
            array(
                'id' => 'thecrate_tw_consumer_key',
                'type' => 'text',
                'title' => esc_attr__('Twitter Consumer Key', 'thecrate'),
                'subtitle' => esc_attr__('Type your Twitter Consumer key.', 'thecrate'),
                'default' => ''
            ),
            array(
                'id' => 'thecrate_tw_consumer_secret',
                'type' => 'text',
                'title' => esc_attr__('Twitter Consumer Secret key', 'thecrate'),
                'subtitle' => esc_attr__('Type your Twitter Consumer Secret key.', 'thecrate'),
                'default' => ''
            ),
            array(
                'id' => 'thecrate_tw_access_token',
                'type' => 'text',
                'title' => esc_attr__('Twitter Access Token', 'thecrate'),
                'subtitle' => esc_attr__('Type your Access Token.', 'thecrate'),
                'default' => ''
            ),
            array(
                'id' => 'thecrate_tw_access_token_secret',
                'type' => 'text',
                'title' => esc_attr__('Twitter Access Token Secret', 'thecrate'),
                'subtitle' => esc_attr__('Type your Twitter Access Token Secret.', 'thecrate'),
                'default' => ''
            )

        ),
    ));


    /**
    ||-> SECTION: 404 Page Settings
    */
    Redux::setSection( $opt_name, array(
        'title' => esc_attr__( '404 Page Settings', 'thecrate' ),
        'id'    => 'thecrate_404',
        'icon'  => 'el el-icon-error',
        'fields'     => array(
            array(
                'id'       => 'thecrate_404_override_status',
                'type'     => 'switch', 
                'title'    => esc_attr__('Override default 404 image/texts?', 'thecrate'),
                'subtitle' => esc_attr__('You can choose to override default 404 image/texts?', 'thecrate'),
                'default'  => false,
                'on'       => esc_attr__( 'Yes', 'thecrate' ),
                'off'      => esc_attr__( 'No', 'thecrate' )
            ),
            array(
                'id' => 'thecrate_404_image',
                'type' => 'media',
                'url' => true,
                'title' => esc_attr__('404 Image', 'thecrate'),
                'default' => array('url' => get_template_directory_uri().'/images/404.png'),
                'required' => array( 'thecrate_404_override_status', '=', '1' ),
            ),
            array(
                'id' => 'thecrate_404_heading',
                'type' => 'text',
                'title' => esc_attr__('404 Heading', 'thecrate'),
                'subtitle' => esc_attr__('Replace the default "Sorry, this page does not exist" heading text', 'thecrate'),
                'required' => array( 'thecrate_404_override_status', '=', '1' ),
                'default' => esc_attr__('Sorry, this page does not exist', 'thecrate'),
            ),
            array(
                'id' => 'thecrate_404_paragraph',
                'type' => 'textarea',
                'title' => esc_attr__('404 Paragraph', 'thecrate'),
                'subtitle' => esc_attr__('Replace the default "The link you clicked might be corrupted, or the page may have been removed." text', 'thecrate'),
                'required' => array( 'thecrate_404_override_status', '=', '1' ),
                'default' => esc_attr__('The link you clicked might be corrupted, or the page may have been removed.', 'thecrate'),
            ),

        ),
    ));