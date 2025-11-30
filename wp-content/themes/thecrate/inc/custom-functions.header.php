<?php
/**
CUSTOM HEADER FUNCTIONS
*/

/**
||-> FUNCTION: GET DYNAMIC CSS
*/
if (!function_exists('thecrate_dynamic_css')) {
	function thecrate_dynamic_css(){

	    $html = '';

		wp_enqueue_style(
		   'thecrate-custom-style',
		    get_template_directory_uri() . '/css/custom-editor-style.css'
		);

	    $html .= thecrate_redux('tslr_custom_css');


	    //PAGE PRELOADER BACKGROUND COLOR
	    if (thecrate_redux('thecrate_preloader_status')) {
	        $html .= 'body .thecrate_preloader_holder{
						background-color: '.thecrate_redux('thecrate_preloader_bg_color', 'background-color').';
	        		}';
	    }

		// HEADER SEMITRANSPARENT - METABOX
		$themeslr_header_custom_bg_color = get_post_meta( get_the_ID(), 'themeslr_header_custom_bg_color', true );
		$thecrate_header_semitransparent = get_post_meta( get_the_ID(), 'themeslr_header_semitransparent', true );
	    if (isset($thecrate_header_semitransparent) && $thecrate_header_semitransparent == 'enabled') {
			$themeslr_header_semitransparentr_rgba_value = get_post_meta( get_the_ID(), 'themeslr_header_semitransparentr_rgba_value', true );
			$thecrate_header_semitransparentr_rgba_value_scroll = get_post_meta( get_the_ID(), 'thecrate_header_semitransparentr_rgba_value_scroll', true );
			if (isset($themeslr_header_custom_bg_color)) {
				list($r, $g, $b) = sscanf($themeslr_header_custom_bg_color, "#%02x%02x%02x");
			}else{
				$hexa = '#ffffff'; //Theme Options Color
				list($r, $g, $b) = sscanf($hexa, "#%02x%02x%02x");
			}

			$html .= '
				.is_header_semitransparent .navbar-default {
				    background: rgba('.esc_html($r).', '.esc_html($g).', '.esc_html($b).', '.esc_html($themeslr_header_semitransparentr_rgba_value).') none repeat scroll 0 0;
				    position: absolute;
				    box-shadow: none;
				    -webkit-box-shadow: none;
				}
				.is_header_semitransparent .sticky-wrapper.is-sticky .navbar-default {
				    background: rgba('.esc_html($r).', '.esc_html($g).', '.esc_html($b).', '.esc_html($thecrate_header_semitransparentr_rgba_value_scroll).') none repeat scroll 0 0;
				}';
	    }

	    // THEME OPTIONS STYLESHEET
	    if(class_exists( 'ReduxFrameworkPlugin' )){

		    if (thecrate_redux('thecrate_backtotop_status') == true) {
		    	if (thecrate_redux('thecrate_backtotop_bg_color','background-color') != '') {
				 	$html .= '.back-to-top {
							background: '.thecrate_redux('thecrate_backtotop_bg_color','background-color').' url('.get_template_directory_uri().'/images/svg/back-to-top-arrow.svg) no-repeat center center;
							height: '.thecrate_redux('thecrate_backtotop_height_width').'px;
							width: '.thecrate_redux('thecrate_backtotop_height_width').'px;
						}';
		    	}
		    }

		    $breadcrumbs_delimitator = thecrate_redux('thecrate_breadcrumbs_delimitator');

	    }else{
		    $breadcrumbs_delimitator = '/';

			$html .= '.header-title-breadcrumb-overlay {
					    background-image: none;
					    background-color: #f4f4f4;
					}';
	    }


		// Skin Background
		$themeslr_skin_color_meta = get_post_meta( get_the_ID(), 'themeslr_skin_color_meta', true );
		if (isset($themeslr_skin_color_meta) && !empty($themeslr_skin_color_meta)) {
			$skin_bg_color = $themeslr_skin_color_meta;

			$html .= "html body .thecrate_preloader_holder {
					    background-color: ".$skin_bg_color.";
					}";

		}else{
	    	if(class_exists( 'ReduxFrameworkPlugin' )){
				$skin_bg_color = thecrate_redux("thecrate_style_main_backgrounds_color");
	    	}else{
				$skin_bg_color = '#F26226';
	    	}
		}
		// Skin Background - hover
		$themeslr_skin_color_meta_hover = get_post_meta( get_the_ID(), 'themeslr_skin_color_meta_hover', true );
		if (isset($themeslr_skin_color_meta_hover) && !empty($themeslr_skin_color_meta_hover)) {
			$skin_bg_color_hover = $themeslr_skin_color_meta_hover;

			$html .= "
					body .header-nav-actions .header-btn:hover{
						background: ".$skin_bg_color_hover.";
					}";

		}else{
	    	if(class_exists( 'ReduxFrameworkPlugin' )){
				$skin_bg_color_hover = thecrate_redux('thecrate_style_main_backgrounds_color_hover');
	    	}else{
				$skin_bg_color_hover = '#2A3CB7';
	    	}
		}
		// Skin Color
		$themeslr_skin_text_color_meta = get_post_meta( get_the_ID(), 'themeslr_skin_text_color_meta', true );
		if (isset($themeslr_skin_text_color_meta) && !empty($themeslr_skin_text_color_meta)) {
			$skin_color = $themeslr_skin_text_color_meta;

			$html .= "
					body .woocommerce .star-rating span:before,
					body .woocommerce .star-rating::before,
					body #navbar ul.sub-menu li:hover > a,
					body #navbar .menu-item.current_page_ancestor.current_page_parent > a, 
					body #navbar .menu-item.current_page_item.current_page_parent > a, 
					body #navbar .menu-item:hover > a{
						color: ".$skin_color.";
					}
					body .header-nav-actions .header-btn{
						background: ".$skin_color.";
					}
					body .back-to-top {
						background-color: ".$skin_color.";
					}";

		}else{
	    	if(class_exists( 'ReduxFrameworkPlugin' )){
				$skin_color = thecrate_redux("thecrate_style_main_texts_color");
	    	}else{
				$skin_color = '#F26226';
	    	}
		}

		$header_custom_logo_width = get_post_meta( get_the_ID(), 'header_custom_logo_width', true );
		if (isset($header_custom_logo_width) && !empty($header_custom_logo_width)) {
			$html .= '
						.logo img,
					    .navbar-header .logo img {
					        width: '.esc_attr($header_custom_logo_width).'px;
					    }';
		}else{
			$html .= '
						.logo img,
					    .navbar-header .logo img {
					        width: '.thecrate_redux('thecrate_logo_max_width').'px;
					    }';
		}

	    if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
			$thecrate_text_selection_color = thecrate_redux('thecrate_text_selection_color');
			$thecrate_text_selection_background_color = thecrate_redux('thecrate_text_selection_background_color');
			$thecrate_global_buttons = thecrate_redux('thecrate_global_buttons_radius');
			$thecrate_global_nav_lnks_font_size = thecrate_redux('thecrate_nav_menu_links_size');
			$thecrate_global_breadcrumbs_title_size = thecrate_redux('thecrate_breadcrumbs_title_size');
			$thecrate_breadcrumbs_alignment = thecrate_redux('thecrate_title_breadcrumbs_alignment');
			$thecrate_shop_product_layout_radius = thecrate_redux('thecrate_shop_product_layout_radius');
			$buttons_shadow = thecrate_redux('thecrate_buttons_shadow');
			$thecrate_breadcrumbs_tb_spacing = thecrate_redux('thecrate_breadcrumbs_tb_spacing');
			if ($buttons_shadow['shadow'] == 1) {
				$thecrate_buttons_shadow = '0 0 15px rgb(0 0 0 / 20%)';
			}else{
				$thecrate_buttons_shadow = 'none';
			}
		}else{
			$thecrate_text_selection_color = '#FFFFFF';
			$thecrate_text_selection_background_color = '#F26226';
			$thecrate_global_buttons = '0';
			$thecrate_global_nav_lnks_font_size = '15';
			$thecrate_global_breadcrumbs_title_size = '15';
			$thecrate_breadcrumbs_alignment = 'center';
			$thecrate_shop_product_layout_radius = '0';
			$thecrate_buttons_shadow = '0 0 15px rgb(0 0 0 / 20%)';
			$thecrate_breadcrumbs_tb_spacing = '150';
		}

		if (function_exists('themeslr_framework')) {
			// custom footer background image
			$themeslr_footer_custom_bg_image = get_post_meta( get_the_ID(), 'themeslr_footer_custom_bg_image', true );
      		if(isset($themeslr_footer_custom_bg_image) && !empty($themeslr_footer_custom_bg_image)){
      			$html .= 'body footer .footer-top, body footer .footer{
      							background: transparent;
      						}
							body footer {
							    background-image: url('.esc_url($themeslr_footer_custom_bg_image).');
							    background-size: cover;
							    background-repeat: no-repeat;
							}';
      		}
			// custom footer top padding
			$themeslr_footer_top_padding_top = get_post_meta( get_the_ID(), 'themeslr_footer_top_padding_top', true );
			$themeslr_footer_top_padding_bottom = get_post_meta( get_the_ID(), 'themeslr_footer_top_padding_bottom', true );
			if (!empty($themeslr_footer_top_padding_top) && !empty($themeslr_footer_top_padding_bottom)) {
      			$html .= 'body .footer-row-1{
      							padding-top: '.esc_html($themeslr_footer_top_padding_top).';
      							padding-bottom: '.esc_html($themeslr_footer_top_padding_bottom).';
      						}';
			}

		}

		if ( class_exists( 'WooCommerce' )) {
			// WooCommerce sale badge background
			if (thecrate_redux('thecrate_shop_sale_badge_background') != '') {
				$thecrate_shop_sale_badge_background = thecrate_redux('thecrate_shop_sale_badge_background');
				$html .= '.woocommerce span.onsale, .woocommerce ul.products li.product .onsale{
						    background-image: url("data:image/svg+xml,%3Csvg width=\'50\' height=\'50\' viewBox=\'0 0 50 50\' fill=\'none\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M25 0L42.6777 7.32233L50 25L42.6777 42.6777L25 50L7.32233 42.6777L0 25L7.32233 7.32233L25 0Z\' fill=\'%23'.str_replace('#', '', $thecrate_shop_sale_badge_background).'\'/%3E%3C/svg%3E");
						}';
			}

			$html .= '.header2 .logo, .header3 .logo {
					        margin-bottom: '.thecrate_redux('thecrate_header_2_space_between_logo_menu').'px;
					    }';

			if (is_singular('product')) {
				$_product = wc_get_product( get_the_ID() );
				if( !$_product->is_type( 'bundle' ) ) {
					$html .= '.woocommerce .quantity input::-webkit-outer-spin-button,
						.woocommerce .quantity input::-webkit-inner-spin-button {
						    -webkit-appearance: none;
						    margin: 0;
						}';
				}
			}else{
				$html .= '.woocommerce .quantity input::-webkit-outer-spin-button,
					.woocommerce .quantity input::-webkit-inner-spin-button {
					    -webkit-appearance: none;
					    margin: 0; 
					}';
			}
		}


		$html .= '.woo-grid-with-border ul.products li.product a.woocommerce-LoopProduct-link.woocommerce-loop-product__link{border-radius: '.esc_html($thecrate_shop_product_layout_radius).'px;}';
		if ($thecrate_shop_product_layout_radius != '0') {
			$html .= '.woo-grid-with-border ul.products li.product a.woocommerce-LoopProduct-link.woocommerce-loop-product__link{overflow: hidden;}';
		}
		$html .= '.header-title-breadcrumb-overlay h1,.header-title-breadcrumb-overlay h1 span{font-size: '.esc_html($thecrate_global_breadcrumbs_title_size).'px;}';
		$html .= '#navbar .menu-item > a{font-size: '.esc_html($thecrate_global_nav_lnks_font_size).'px;}';
		$html .= '.header-title-breadcrumb-overlay, .header-title-breadcrumb-overlay .breadcrumb{text-align: '.esc_html(str_replace('text-', '', $thecrate_breadcrumbs_alignment)).';}';

		$header_custom_breadcrumbs = get_post_meta( get_the_ID(), 'header_custom_breadcrumbs', true );
		if ($header_custom_breadcrumbs) {
			$html .= 'body .header-title-breadcrumb-overlay {
					    background-image: url('.esc_url($header_custom_breadcrumbs).');
					}';
		}


	    // NAV MENU (Mobile) THEME PANEL STYLING
	    if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
			$nav_styling = thecrate_redux('thecrate_nav_mobile_custom_styling');
			if ($nav_styling != '' && $nav_styling != 0) {
				if ($nav_styling['status'] == '1') {

					$thecrate_nav_mobile_toggled_header = thecrate_redux('thecrate_nav_mobile_toggled_header');
					$thecrate_nav_mobile_menu_color = thecrate_redux('thecrate_nav_mobile_menu_color');
					$thecrate_nav_mobile_menu_color_hover = thecrate_redux('thecrate_nav_mobile_menu_color_hover');
					$thecrate_nav_mobile_menu_links_size = thecrate_redux('thecrate_nav_mobile_menu_links_size');
					$thecrate_nav_mobile_submenu_background = thecrate_redux('thecrate_nav_mobile_submenu_background');
					$thecrate_nav_mobile_submenu_link_color = thecrate_redux('thecrate_nav_mobile_submenu_link_color');
					$thecrate_nav_mobile_submenu_link_color_hover = thecrate_redux('thecrate_nav_mobile_submenu_link_color_hover');

					$nav_mobile_styling = '';
					$nav_mobile_styling .= 'header #navbar{background:'.esc_html($thecrate_nav_mobile_toggled_header).';}';
					$nav_mobile_styling .= 'header #navbar .menu-item > a, header .navbar-nav .search_products a, header .navbar-default .navbar-nav > li > a:hover, header .navbar-default .navbar-nav > li > a:focus, header .navbar-default .navbar-nav > li > a{color:'.esc_html($thecrate_nav_mobile_menu_color).';}';
					$nav_mobile_styling .= 'header #navbar .menu-item.current_page_ancestor.current_page_parent > a, header #navbar .menu-item.current_page_item.current_page_parent > a, header #navbar .menu-item:hover > a{color:'.esc_html($thecrate_nav_mobile_menu_color_hover).';}';
					$nav_mobile_styling .= 'header #navbar .menu-item > a{font-size: '.esc_html($thecrate_nav_mobile_menu_links_size).'px;}';
					$nav_mobile_styling .= 'header #navbar .sub-menu, header .navbar ul li ul.sub-menu{background-color: '.esc_html($thecrate_nav_mobile_submenu_background).';}';
					$nav_mobile_styling .= 'header #navbar ul.sub-menu li > a{color: '.esc_html($thecrate_nav_mobile_submenu_link_color).' !important;}';
					$nav_mobile_styling .= 'header #navbar ul.sub-menu li:hover > a{color: '.esc_html($thecrate_nav_mobile_submenu_link_color_hover).';}';

			    	$html .= '@media only screen and (max-width: 767px) {'.$nav_mobile_styling.'}';
			    }
			}
	    }
	    // Header Icons (Mobile) THEME PANEL STYLING
	    if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
			$header_icons_styling = thecrate_redux('thecrate_header_icons_mobile_styling');
			if ($header_icons_styling != '' && $header_icons_styling != 0) {
				if ($header_icons_styling['status'] == '1') {

					$thecrate_header_mobile_icons_color = thecrate_redux('thecrate_header_mobile_icons_color');
					$thecrate_header_mobile_icons_color_hover = thecrate_redux('thecrate_header_mobile_icons_color_hover');
				
					$nav_mobile_styling = '';
					$nav_mobile_styling .= 'header .header-nav-actions .thecrate-search-icon, header .thecrate-account-link i, header .thecrate-nav-burger i{color:'.esc_html($thecrate_header_mobile_icons_color).';}';
					$nav_mobile_styling .= 'body header .cart-contents svg path{fill:'.esc_html($thecrate_header_mobile_icons_color).';}';
					$nav_mobile_styling .= 'header .header-nav-actions .thecrate-search-icon:hover i, header .header-nav-actions .thecrate-account-link:hover i, header .thecrate-nav-burger:hover i{color:'.esc_html($thecrate_header_mobile_icons_color_hover).' !important;}';
					$nav_mobile_styling .= 'body header .cart-contents svg:hover path{fill:'.esc_html($thecrate_header_mobile_icons_color_hover).';}';
			
			    	$html .= '@media only screen and (max-width: 767px) {'.$nav_mobile_styling.'}';
			    }
		    }
	    }

	    // THEME OPTIONS STYLESHEET
	    $html .= '
	    .wp-block-tag-cloud .tag-cloud-link, .tagcloud > a,
	    .widget_thecrate_address_social_icons .social-links a,
	    .top-header .social-links > li,
	    li.product .woosc-btn, li.product .woosq-btn, li.product .woosw-btn,
	    	.single-product div.product .summary .cart .woosc-btn, .single-product div.product .summary .cart .woosw-btn, .single-product div.product .summary .cart .woosq-btn,
	    	.woosw-copy-btn input[type="button"], .btn-theme-default, .woocommerce table.my_account_orders .button, .woocommerce-account .woocommerce-MyAccount-content a.button, .themeslr_button a.button-winona, .header-nav-actions .header-btn, .list-view .post-details .post-excerpt .more-link, .woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt, .woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .form-submit input[type="submit"], button[type="submit"], .wp-block-search .wp-block-search__button, form input[type="submit"], .button{
	    		border-radius: '.esc_html($thecrate_global_buttons).'px;
	    		-webkit-border-radius: '.esc_html($thecrate_global_buttons).'px;
	    		box-shadow: '.esc_html($thecrate_buttons_shadow).';
	    		-webkit-box-shadow: '.esc_html($thecrate_buttons_shadow).';
	    	}
	    	.header-title-breadcrumb-overlay{
	    		padding-top: '.esc_html($thecrate_breadcrumbs_tb_spacing).'px;
	    		padding-bottom: '.esc_html($thecrate_breadcrumbs_tb_spacing).'px;
	    	}
			@media only screen and (min-width: 767px) {
				nav#theme-main-head {
				    padding: 27px 0;
				}
			}

	    	.container{width: 1300px; max-width: 100%;}
	    		.breadcrumb a::after {
		        	content: "'.esc_attr($breadcrumbs_delimitator).'";
		    	}
			    ::selection{
			        color: '.esc_attr($thecrate_text_selection_color).';
			        background: '.esc_attr($thecrate_text_selection_background_color).';
			    }
			    ::-moz-selection { /* Code for Firefox */
			        color: '.esc_attr($thecrate_text_selection_color).';
			        background: '.esc_attr($thecrate_text_selection_background_color).';
			    }
			    /*COLOR*/
			    a, 
			    a:hover, 
			    a:focus,
			    span.amount,
			    .widget_popular_recent_tabs .nav-tabs li.active a,
			    .widget_product_categories .cat-item:hover,
			    .widget_product_categories .cat-item a:hover,
			    .widget_archive li:hover,
			    .widget_archive li a:hover,
			    .widget_categories .cat-item:hover,
			    .widget_categories li a:hover,
			    .pricing-table.recomended .button.solid-button, 
			    .pricing-table .table-content:hover .button.solid-button,
			    .pricing-table.Recommended .button.solid-button, 
			    .pricing-table.recommended .button.solid-button, 
			    #sync2 .owl-item.synced .post_slider_title,
			    #sync2 .owl-item:hover .post_slider_title,
			    #sync2 .owl-item:active .post_slider_title,
			    .pricing-table.recomended .button.solid-button, 
			    .pricing-table .table-content:hover .button.solid-button,
			    .testimonial-author,
			    .testimonials-container blockquote::before,
			    .testimonials-container blockquote::after,
			    .post-author > a,
			    h2 span,
			    label.error,
			    .author-name,
			    .comment_body .author_name,
			    .prev-next-post a:hover,
			    .prev-text,
			    .wpb_button.btn-filled:hover,
			    .next-text,
			    .social ul li a:hover i,
			    .wpcf7-form span.wpcf7-not-valid-tip,
			    .text-dark .statistics .stats-head *,
			    .wpb_button.btn-filled,
			    footer ul.menu li.menu-item a:hover,
			    .widget_meta a:hover,
			    .widget_pages a:hover,
				.comment-author-link a:hover,
			    .simple_sermon_content_top h4,
			    .widget_recent_entries_with_thumbnail li:hover a,
			    .widget_recent_entries li a:hover,
			    .thecrate-single-post-meta .thecrate-meta-post-comments a:hover,
				.list-view .post-details .post-category-comment-date i,
			    #navbar .tslr-icon-list-item:hover,
			    #navbar .menu-item:hover .sub-menu .tslr-icon-list-item .tslr-icon-list-text
				.list-view .post-details .post-name a,	
				.thecrate-single-post-meta .thecrate-meta-post-author a, 
				header .header-nav-actions .thecrate-account-link:hover i,  
				.header-nav-actions .thecrate-search-icon:hover i,  
				.woocommerce ul.products li.product .woocommerce-loop-product__title:hover,
				.product_meta a:hover,
				.tslr-blog-posts-shortcode h4 a:hover,
				.header-group .breadcrumb a:hover,
				.widget_block a.wp-block-latest-comments__comment-link:hover, 
				.widget_block a.wp-block-latest-comments__comment-author:hover,
				.wp-block-tag-cloud .tag-cloud-link,
				.list-view .post-details .post-category-comment-date a:hover,
				.single .post-category-comment-date a:hover,
				.comment-navigation > div a:hover,
				.woocommerce .star-rating span:before,
				.related-posts .post-name:hover,
				.list-view .post-details .post-name a:hover,
				.wp-block-button.is-style-outline a.wp-block-button__link,
				.woocommerce .star-rating::before,
				.header_mini_cart .woocommerce ul.cart_list li a:hover, .header_mini_cart .woocommerce ul.product_list_widget li a:hover,
				.sidebar-content .widget_nav_menu li a:hover{
			        color: '.esc_attr($skin_color).';
			    }
				header .cart-contents svg:hover path{
			        fill: '.esc_attr($skin_color).';

				}
			    #navbar .menu-item:hover .sub-menu .tslr-icon-list-item:hover .tslr-icon-list-icon-holder-inner i,
			    #navbar .menu-item:hover .sub-menu .tslr-icon-list-item:hover .tslr-icon-list-text{
			        color: '.esc_attr($skin_color).' !important; /*Color: Main blue*/
				}
			    /*BACKGROUND + BACKGROUND-COLOR*/
				header .cart-contents span{
			        background: '.esc_attr($skin_color).';
				}
				.wp-block-group h2:before,
				.tagcloud > a:hover,
				.theme-icon-search,
				.wpb_button::after,
				.rotate45,
				.latest-posts .post-date-day,
				.latest-posts h3,
				.latest-tweets h3,
				.latest-videos h3,
				.button.solid-button,
				button.vc_btn,
				.pricing-table.Recommended .table-content,
				.pricing-table.recommended .table-content,
				.pricing-table.recomended .table-content,
				.pricing-table .table-content:hover,
				.block-triangle,
				.owl-theme .owl-controls .owl-page span,
				body .vc_btn.vc_btn-blue,
				body a.vc_btn.vc_btn-blue,
				body button.vc_btn.vc_btn-blue,
				.pagination .page-numbers.current,
				.pagination .page-numbers:hover,
				#subscribe > button[type=\'submit\'],
				.social-sharer > li:hover,
				.prev-next-post a:hover .rotate45,
				.masonry_banner.default-skin,
				.form-submit input,
				.member-header::before,
				.member-header::after,
				.member-footer .social::before,
				.member-footer .social::after,
				.subscribe > button[type=\'submit\'],
				.no-results input[type=\'submit\'],
				h3#reply-title::after,
				.newspaper-info,
				.widget-title:after,
				h2.heading-bottom:after,
				.wpb_content_element .wpb_accordion_wrapper .wpb_accordion_header.ui-state-active,
				#primary .main-content ul li:not(.rotate45)::before,
				.wpcf7-form .wpcf7-submit,
				ul.ecs-event-list li span,
				#contact_form2 .solid-button.button,
				.details-container > div.details-item .amount, .details-container > div.details-item ins,
				.theme-search .search-submit,
				.pricing-table.recommended .table-content .title-pricing,
				.pricing-table .table-content:hover .title-pricing,
				.pricing-table.recommended .button.solid-button,
				.post-category-date a[rel="tag"],
				.is_sticky,
				.fixed-sidebar-menu h3#reply-title::before,
				.fixed-sidebar-menu h2.heading-bottom::before,
				.fixed-sidebar-menu .widget-title::before,
				.sidebar-content h3#reply-title::before,
				.sidebar-content h2.heading-bottom::before,
				.sidebar-content .widget-title::before,
				.single .label-info.edit-t:hover,
				.read-more-overlay i,
				footer .footer-top .widget_wysija_cont .wysija-submit,
				.woocommerce div.product form.cart .button,
				.give-btn,
				#wp-calendar td#today,
				.pricing-table .table-content:hover .button.solid-button,
				footer .footer-top .menu .menu-item a::before,
				.theme-pagination.pagination .page-numbers.current,
				.woocommerce #respond input#submit:hover,
				.woocommerce div.product form.cart .button:hover,
				.woocommerce a.button:hover,
				.woocommerce button.button:hover,
				.woocommerce input.button:hover,
				.read-more-overlay i:hover,
				.mpc-mailchimp.mpc-submit--small input[type="submit"],
				.author-bio,
				.widget_thecrate_address_social_icons .social-links a,
				.themeslr_button a.button-winona,
				.header-nav-actions .header-btn,
				.list-view .post-details .post-excerpt .more-link,
				.woocommerce #respond input#submit.alt,
				.woocommerce a.button.alt,
				.woocommerce button.button.alt,
				.woocommerce input.button.alt,
				.woocommerce #respond input#submit,
				.woocommerce a.button,
				.woocommerce button.button,
				.woocommerce input.button,
				.form-submit input[type="submit"],
				button[type="submit"],
				a.wp-block-button__link,
				.btn-theme-default,
				form input[type="submit"],
				.woosw-copy-btn input,
				.wp-block-search .wp-block-search__button,
				.button,
				.post-password-form input[type=\'submit\'] {
			        background: '.esc_attr($skin_bg_color).';
			    }

				.no-js .theme-search .theme-icon-search,
				a.wp-block-button__link:hover,
				.theme-icon-search:hover,
				.latest-posts .post-date-month,
				.button.solid-button:hover,
				body .vc_btn.vc_btn-blue:hover,
				body a.vc_btn.vc_btn-blue:hover,
				.post-category-date a[rel="tag"]:hover,
				.single-post-tags > a:hover,
				body button.vc_btn.vc_btn-blue:hover,
				a.col-2.sbtn:hover,
				#contact_form2 .solid-button.button:hover,
				.subscribe > button[type=\'submit\']:hover,
				.no-results input[type=\'submit\']:hover,
				ul.ecs-event-list li span:hover,
				.pricing-table.recommended .table-content .price_circle,
				.pricing-table .table-content:hover .price_circle,
				#modal-search-form .modal-content input.search-input,
				.wpcf7-form .wpcf7-submit:hover,
				.form-submit input:hover,
				.pricing-table.recommended .button.solid-button:hover,
				.pricing-table .table-content:hover .button.solid-button:hover,
				footer .footer-top .widget_wysija_cont .wysija-submit:hover,
				.widget_thecrate_address_social_icons .social-links a:hover,
				.fixed-search-inside .search-submit:hover,
				.slider_navigation .btn:hover,
				.mpc-mailchimp.mpc-submit--small input[type="submit"]:hover,
				.post-password-form input[type=\'submit\']:hover,
				.list-view .post-details .post-excerpt .more-link:hover,
				.woocommerce input.button:hover,
				.form-submit input[type="submit"]:hover,
				button[type="submit"]:hover,
				form input[type="submit"]:hover,
				.button:hover,
				.woocommerce .woocommerce-error .button:hover,
				.woocommerce .woocommerce-info .button:hover,
				.woocommerce .woocommerce-message .button:hover,
				.woocommerce-page .woocommerce-error .button:hover,
				.woocommerce-page .woocommerce-info .button:hover,
				.woocommerce-page .woocommerce-message .button:hover,
				.woocommerce #respond input#submit.alt:hover,
				.woocommerce a.button.alt:hover,
				.woocommerce button.button.alt:hover,
				.woocommerce input.button.alt:hover,
				.woocommerce #respond input#submit:hover,
				.wp-block-tag-cloud a:hover,
				.woosw-copy-btn input:hover,
				.woocommerce a.button:hover,
				.btn-theme-default:hover,
				.wp-block-button.is-style-outline a.wp-block-button__link:hover,
				.woocommerce div.product form.cart .button:hover,
				.woocommerce button.button:hover,
				.read-more-overlay i:hover,
				.woocommerce input.button:hover{
			        background: '.esc_attr($skin_bg_color_hover).';
			    }
			    .tagcloud > a:hover{
			        background: '.esc_attr($skin_bg_color_hover).' !important;
			    }

			    /*BORDER-COLOR*/
			    .author-bio,
			    blockquote,
			    .widget_popular_recent_tabs .nav-tabs > li.active,
			    body .left-border, 
			    body .right-border,
			    body .member-header,
			    body .member-footer .social,
			    .navbar ul li ul.sub-menu,
			    .wpb_content_element .wpb_tabs_nav li.ui-tabs-active,
			    #contact-us .form-control:focus,
				.wp-block-tag-cloud .tag-cloud-link,
			    .sale_banner_holder:hover,
			    .testimonial-img,
				.widget_price_filter .ui-slider .ui-slider-handle,
			    #navbar .menu-item.current_page_item > a,
				#navbar .menu-item:hover > a,
				.wp-block-button.is-style-outline a.wp-block-button__link,
			    .navbar-default .navbar-toggle:hover, 
			    .header_search_form,
				.tslr-carousel-everything-shortcode-group.owl-theme .owl-controls .owl-page.active span,
				.testimonials-container-1.owl-theme .owl-controls .owl-page.active span,
			    .navbar-default .navbar-toggle{
			        border-color: '.esc_attr($skin_color).'; /*Color: Main blue */
			    }
			    body .vc_tta-color-white.vc_tta-style-outline .vc_tta-tab.vc_active>a{
			        border-bottom-color: '.esc_attr($skin_color).'; /*Color: Main blue */
			    }';

	    wp_add_inline_style( 'thecrate-custom-style', thecrate_minify_css($html) );
	}
	add_action('wp_enqueue_scripts', 'thecrate_dynamic_css' );
}

/**
||-> FUNCTION: GET HEADER-TOP RIGHT PART
*/
if (!function_exists('thecrate_get_top_right')) {
	function thecrate_get_top_right(){

	    // CONTENT
	    $html = '';
	    $html .= '<ul class="social-links">';
	                if ( thecrate_redux('thecrate_social_fb') && thecrate_redux('thecrate_social_fb') != '' ) {
	                    $html .= '<li class="facebook"><a href="'.esc_attr( thecrate_redux('thecrate_social_fb') ).'"><i class="fab fa-facebook"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_tw') && thecrate_redux('thecrate_social_tw') != '' ) {
	                    $html .= '<li class="twitter"><a href="https://twitter.com/'.esc_attr( thecrate_redux('thecrate_social_tw') ).'"><i class="fab fa-twitter"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_tiktok') && thecrate_redux('thecrate_social_tiktok') != '' ) {
	                    $html .= '<li class="tiktok"><a href="'.esc_attr( thecrate_redux('thecrate_social_tiktok') ).'"><i class="fab fa-tiktok"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_youtube') && thecrate_redux('thecrate_social_youtube') != '' ) {
	                    $html .= '<li class="youtube"><a href="'.esc_attr( thecrate_redux('thecrate_social_youtube') ).'"><i class="fab fa-youtube"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_pinterest') && thecrate_redux('thecrate_social_pinterest') != '' ) {
	                    $html .= '<li class="pinterest"><a href="'.esc_attr( thecrate_redux('thecrate_social_pinterest') ).'"><i class="fab fa-pinterest"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_linkedin') && thecrate_redux('thecrate_social_linkedin') != '' ) {
	                    $html .= '<li class="linkedin"><a href="'.esc_attr( thecrate_redux('thecrate_social_linkedin') ).'"><i class="fab fa-linkedin"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_skype') && thecrate_redux('thecrate_social_skype') != '' ) {
	                    $html .= '<li class="skype"><a href="'.esc_attr( thecrate_redux('thecrate_social_skype') ).'"><i class="fab fa-skype"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_instagram') && thecrate_redux('thecrate_social_instagram') != '' ) {
	                    $html .= '<li class="instagram"><a href="'.esc_attr( thecrate_redux('thecrate_social_instagram') ).'"><i class="fab fa-instagram"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_dribbble') && thecrate_redux('thecrate_social_dribbble') != '' ) {
	                    $html .= '<li class="dribbble"><a href="'.esc_attr( thecrate_redux('thecrate_social_dribbble') ).'"><i class="fab fa-dribbble"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_deviantart') && thecrate_redux('thecrate_social_deviantart') != '' ) {
	                    $html .= '<li class="deviantart"><a href="'.esc_attr( thecrate_redux('thecrate_social_deviantart') ).'"><i class="fab fa-deviantart"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_digg') && thecrate_redux('thecrate_social_digg') != '' ) {
	                    $html .= '<li class="digg"><a href="'.esc_attr( thecrate_redux('thecrate_social_digg') ).'"><i class="fab fa-digg"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_flickr') && thecrate_redux('thecrate_social_flickr') != '' ) {
	                    $html .= '<li class="flickr"><a href="'.esc_attr( thecrate_redux('thecrate_social_flickr') ).'"><i class="fab fa-flickr"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_stumbleupon') && thecrate_redux('thecrate_social_stumbleupon') != '' ) {
	                    $html .= '<li class="stumbleupon"><a href="'.esc_attr( thecrate_redux('thecrate_social_stumbleupon') ).'"><i class="fab fa-stumbleupon"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_tumblr') && thecrate_redux('thecrate_social_tumblr') != '' ) {
	                    $html .= '<li class="tumblr"><a href="'.esc_attr( thecrate_redux('thecrate_social_tumblr') ).'"><i class="fab fa-tumblr"></i></a></li>';
	                }
	                if ( thecrate_redux('thecrate_social_vimeo') && thecrate_redux('thecrate_social_vimeo') != '' ) {
	                    $html .= '<li class="vimeo"><a href="'.esc_attr( thecrate_redux('thecrate_social_vimeo') ).'"><i class="fab fa-vimeo-square"></i></a></li>';
	                }
	    $html .= '</ul>';

	    return $html;
	}
}


/**
Function name: 				thecrate_current_header_template()			
Function description:		Gets the current header variant from theme options. If page has custom options, theme options will be overwritten.
*/
if (!function_exists('thecrate_current_header_template')) {
	function thecrate_current_header_template(){

		global  $thecrate_redux;

		// the_post_thumbnail( $size, $attr );

	    // PAGE METAS
	    $header_v = get_post_meta( get_the_ID(), 'themeslr_header_custom_variant', true );

		$html = '';

	    if (is_page() && $header_v) {
	        if ($header_v && $header_v != '') {
	        	$html .= get_template_part( 'templates/template-'.esc_attr($header_v) );
	        }
	    }else{
	    	if (isset($thecrate_redux['thecrate_header_layout'])) {
    			$html .= get_template_part( 'templates/template-'.esc_attr($thecrate_redux['thecrate_header_layout']) );
	    	}else{
	    		$html .= get_template_part( 'templates/template-header1' );
	    	}
	    }
	    return $html;
	}
}



/**
||-> FUNCTION: GET GOOGLE FONTS FROM THEME OPTIONS PANEL
*/
if (!function_exists('thecrate_get_site_fonts')) {
	function thecrate_get_site_fonts(){
	    global  $thecrate_redux;
	    $fonts_string = 'Jost:regular,300,400,500,600,700,bold%7C';
	    if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
		    if (isset($thecrate_redux['thecrate_google_fonts_select'])) {
		        $i = 0;
		        $len = count($thecrate_redux['thecrate_google_fonts_select']);
		        foreach(array_keys($thecrate_redux['thecrate_google_fonts_select']) as $key){
		            $font_url = str_replace(' ', '+', $thecrate_redux['thecrate_google_fonts_select'][$key]);
		            
		            if ($i == $len - 1) {
		                // last
		                $fonts_string .= $font_url;
		            }else{
		                $fonts_string .= $font_url . '%7C';
		            }
		            $i++;
		        }
		        // fonts url
		        $fonts_url = add_query_arg( 'family', $fonts_string, "//fonts.googleapis.com/css" );
		        // enqueue fonts
		        wp_enqueue_style( 'thecrate-fonts', $fonts_url, array(), '1.0.0' );
		    }
		} else {
	        $font_url = str_replace(' ', '+', 'Work+Sans:100,200,300,regular,500,600,700,800,900,latin-ext,latin%7CJost:regular,300,400,500,600,700,bold');
	        $fonts_url = add_query_arg( 'family', $font_url, "//fonts.googleapis.com/css" );
	        wp_enqueue_style( 'thecrate-fonts-fallback', $fonts_url, array(), '1.0.0' );
	    }
	}
	add_action('wp_enqueue_scripts', 'thecrate_get_site_fonts');
}



// Add specific CSS class by filter
if (!function_exists('thecrate_body_classes')) {
	function thecrate_body_classes( $classes ) {

		global  $thecrate_redux;

	    $post_featured_image = '';
	    $is_nav_sticky = '';
	    $tslr_site_layout = '';
	    $tslr_shop_variant_class = '';
	    $thecrate_fixed_sidebar_cart = 'thecrate_fixed_sidebar_cart_on';

	    if(class_exists( 'ReduxFrameworkPlugin' )){
	    	// CHECK IF FEATURED IMAGE IS FALSE(Disabled)
		    if (is_singular('post')) {
		        if ($thecrate_redux['thecrate_post_featured_image'] == false) {
		            $post_featured_image = 'hide_post_featured_image';
		        }
		    }

	    	// CHECK IF THE NAV IS STICKY
		    if ($thecrate_redux['thecrate_is_nav_sticky'] == true) {
		        // If is sticky
		        $is_nav_sticky = 'is_nav_sticky';
		    }
		    
		    // SITE LAYOUT - THEME OPTION
		    if (thecrate_redux('tslr_site_layout') == true && thecrate_redux('tslr_site_layout') == 'layout_boxed') {
		        $tslr_site_layout = 'layout_boxed';
		    }
		    
		    // Shop Variant Class
		    if (thecrate_redux('thecrate_shop_product_layout') != '') {
		        $tslr_shop_variant_class = thecrate_redux('thecrate_shop_product_layout');
		    }
		    
		    // Shop Variant Class
		    if (thecrate_redux('thecrate_fixed_sidebar_cart') == false) {
		        $thecrate_fixed_sidebar_cart = '';
		    }
	    }

	    // SITE LAYOUT - METABOX
	    $tslr_site_layout_meta = '';
	    $tslr_site_layout_meta = get_post_meta( get_the_ID(), 'tslr_site_layout_meta', true );
	    if ($tslr_site_layout_meta == true) {
	        $tslr_site_layout_meta = 'layout_boxed';
	    }
	    // HIDE FOOTER ON CURRENT PAGE - METABOX
	    $tslr_site_hide_footer = '';
	    $tslr_site_hide_footer = get_post_meta( get_the_ID(), 'tslr_site_hide_footer', true );
	    if ($tslr_site_hide_footer == true) {
	        $tslr_site_hide_footer = 'layout_hide_footer';
	    }
	    // HIDE HEADER ON CURRENT PAGE - METABOX
	    $tslr_site_hide_header = '';
	    $tslr_site_hide_header = get_post_meta( get_the_ID(), 'tslr_site_hide_header', true );
	    if ($tslr_site_hide_header == true) {
	        $tslr_site_hide_header = 'layout_hide_header';
	    }


	    // CHECK IF HEADER IS SEMITRANSPARENT
	    $semitransparent_header_meta = get_post_meta( get_the_ID(), 'themeslr_header_semitransparent', true );
	    $semitransparent_header = '';
	    if ($semitransparent_header_meta == 'enabled') {
	        // If is semitransparent
	        $semitransparent_header = 'is_header_semitransparent';
	    }

	    // DIFFERENT HEADER LAYOUT TEMPLATES
	    $header_custom_variant = get_post_meta( get_the_ID(), 'themeslr_header_custom_variant', true );
	    $header_version = 'header1';
	    if(class_exists( 'ReduxFrameworkPlugin' )){
		    if (isset($header_custom_variant) && $header_custom_variant != '') {
		    	$header_version = $header_custom_variant;
		    }else{
			    $header_version = 'header1';
			    if ($thecrate_redux['thecrate_header_layout']) {
			        // Header Layout #1
			        $header_version = $thecrate_redux['thecrate_header_layout'];
			    }
		    }
	    }

	    // add 'class-name' to the $classes array
	    $classes[] = $thecrate_fixed_sidebar_cart . ' ' . $tslr_shop_variant_class . ' ' . $tslr_site_hide_footer . ' ' . $tslr_site_hide_header . ' ' . $tslr_site_layout_meta . ' ' . $tslr_site_layout . ' ' . $post_featured_image . ' ' . $is_nav_sticky . ' ' . $header_version . ' ' . $semitransparent_header . ' ';
	    // return the $classes array
	    return $classes;

	}
	add_filter( 'body_class', 'thecrate_body_classes' );
}