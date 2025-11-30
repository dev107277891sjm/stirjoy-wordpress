<?php

if (!function_exists('thecrate_header_title_breadcrumbs_include')) {
	function thecrate_header_title_breadcrumbs_include(){
		$breadcrumbs_on_off = get_post_meta( get_the_ID(), 'breadcrumbs_on_off', true );
		if ( function_exists('themeslr_framework')) {
			if (is_page() || is_singular('post')) {
				if (is_cart() || is_shop() || is_checkout() || is_account_page()) {
					if ((isset($breadcrumbs_on_off) && $breadcrumbs_on_off == 'yes') || (isset($breadcrumbs_on_off) && $breadcrumbs_on_off == '')) {
						echo thecrate_header_title_breadcrumbs();
					}
				}else{
					if ((isset($breadcrumbs_on_off) && $breadcrumbs_on_off == 'yes') || (isset($breadcrumbs_on_off) && $breadcrumbs_on_off == '')) {
						echo thecrate_header_title_breadcrumbs();
					}
				}
			}else{
				echo thecrate_header_title_breadcrumbs();
			}
		}else{
			echo thecrate_header_title_breadcrumbs();
		}
	}
	add_action('thecrate_before_primary_area', 'thecrate_header_title_breadcrumbs_include');
}

// GET SITE BREADCRUMBS
if (!function_exists('thecrate_header_title_breadcrumbs')) {
	function thecrate_header_title_breadcrumbs(){
		?>
	    <div class="header-title-breadcrumb relative">
	    	<div class="header-title-breadcrumb-overlay">
            	<div class="container">
                    <div class="header-group row">
                        <div class="col-md-12">
                        	<?php if (!is_singular('product')) { ?><h1><?php } ?>
                                <?php 
                                if (is_single()) {
                                	if (class_exists( 'WooCommerce' )) {
                            			if (is_product()) {
                            			}else{
                                    		echo get_the_title();
                            			}
                                	}else{
                                		echo get_the_title();
                                	}
                                }elseif (is_search()) {
                                    echo esc_html__( 'Search Results for: "', 'thecrate' ) . get_search_query();
                                }elseif (is_category()) {
                                    echo esc_html__( 'Category: ', 'thecrate' ).' '.single_cat_title( '', false );
                                }elseif (is_category()) {
                                    echo esc_html__( 'Tag: ', 'thecrate' ) . single_tag_title( '', false );
                                }elseif (is_author() || is_archive()) {
                                	if (class_exists( 'WooCommerce' )) {
                                		if (is_shop()) {
	                                    	echo esc_html__( 'Our Shop', 'thecrate' );
                                		}else{
	                                    	echo get_the_archive_title() . get_the_archive_description();
                                		}
                                	}else{
                                    	echo get_the_archive_title() . get_the_archive_description();
                                	}
                                }elseif (is_home()) {
                                    echo esc_html__( 'Latest Posts', 'thecrate' );
                                }elseif (is_page()) {
                                    echo get_the_title();
                                }else{
                                    echo get_the_title();
                                } ?>
	                        <?php if (!is_singular('product')) { ?></h1><?php } ?>

		                    <?php if(!function_exists('bcn_display')){ ?>
	                            <ol class="breadcrumb">
	                                <?php echo thecrate_breadcrumbs(); ?>
	                            </ol>
		                    <?php } else { ?>
	                            <div class="breadcrumbs breadcrumbs-navxt" typeof="BreadcrumbList" vocab="https://schema.org/">
	                                <?php echo bcn_display(); ?>
	                            </div>
		                    <?php } ?>
	                                  
                        </div>
                    </div>
                </div>
            </div>
	    </div>
	    <div class="clearfix"></div><?php
	}
}


if (!function_exists('thecrate_cart_checkout_progress')) {
	function thecrate_cart_checkout_progress(){ ?>
		<?php if (is_cart() || is_checkout()) { ?>
		  <ul class="cart-checkout-progress hide-for-large below-main-header">
		    <li class="active">
		      <?php if (is_checkout() && empty( is_wc_endpoint_url('order-received'))){ ?>
		        <a href="<?php echo esc_url(wc_get_cart_url()); ?>"><?php esc_html_e('My Cart', 'thecrate'); ?></a>
		      <?php }else{ ?>
		        <?php esc_html_e('My Cart', 'thecrate'); ?>
		      <?php } ?>
		    </li>
		    <li class="<?php if (is_checkout()){echo 'active';} ?>"><?php esc_html_e('Place Order', 'thecrate'); ?></li>
		    <li class="<?php if (is_checkout() && !empty( is_wc_endpoint_url('order-received'))){echo 'active';} ?>"><?php esc_html_e('Order Summary', 'thecrate'); ?></li>
		  </ul>
		<?php }
	}
}


/**
||-> BREADCRUMBS
*/
if (!function_exists('thecrate_breadcrumbs')) {
	function thecrate_breadcrumbs() {
	    
	    global  $thecrate_redux;
	    if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
	        if ( !$thecrate_redux['thecrate_enable_breadcrumbs'] ) {
	            return false;
	        }
	    }

	    $delimiter = '';
	    $html =  '';
	    //text for the 'Home' link
	    $name = esc_html__("Home", "thecrate");
	    $currentBefore = '<li class="active">';
	    $currentAfter = '</li>';

	        if (!is_home() && !is_front_page() || is_paged()) {
	            global  $post;
	            $home = esc_url(home_url('/'));
	            $html .= '<li><a href="' . $home . '">' . $name . '</a></li> ' . $delimiter . '';
	        
	        if (is_category()) {
	            global  $wp_query;
	            $cat_obj = $wp_query->get_queried_object();
	            $thisCat = $cat_obj->term_id;
	            $thisCat = get_category($thisCat);
	            $parentCat = get_category($thisCat->parent);
	                if ($thisCat->parent != 0)
	            $html .= (get_category_parents($parentCat, true, '' . $delimiter . ''));
	            $html .= $currentBefore . single_cat_title('', false) . $currentAfter;
	        }elseif (is_tax()) {
	            global  $wp_query;
	            $html .= $currentBefore . single_cat_title('', false) . $currentAfter;
	        }

	        if (is_day()) {
	            $html .= '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li> ' . $delimiter . '';
	            $html .= '<li><a href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '">' . get_the_time('F') . '</a></li> ' . $delimiter . ' ';
	            $html .= $currentBefore . get_the_time('d') . $currentAfter;
	        }

	        if (is_month()) {
	            $html .= '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li> ' . $delimiter . '';
	            $html .= $currentBefore . get_the_time('F') . $currentAfter;
	        } elseif (is_year()) {
	            $html .= $currentBefore . get_the_time('Y') . $currentAfter;
	        } elseif (is_attachment()) {
	            $html .= $currentBefore;
	            $html .= get_the_title();
	            $html .= $currentAfter;
	        } elseif (class_exists( 'WooCommerce' ) && is_shop()) {
	            $html .= $currentBefore;
	            $html .= esc_attr__('Shop','thecrate');
	            $html .= $currentAfter;
	        }elseif (class_exists( 'WooCommerce' ) && is_product()) {

	            global  $post;
	            $cat = get_the_terms( $post->ID, 'product_cat' );
	            if ($cat && ! is_wp_error( $cat ) ) {
	                foreach ($cat as $categoria) {
	                    if($categoria->parent == 0){

	                        // Get the ID of a given category
	                        $category_id = get_cat_ID( $categoria->name );

	                        // Get the URL of this category
	                        $category_link = get_term_link($categoria->term_id, 'product_cat');

	                        $html .= '<li><a href="'.esc_url($category_link).'">
	                        ' . $categoria->name . '</a></li>';
	                    }
	                }
	            }

	            $html .= $currentBefore;
	            $html .= get_the_title();
	            $html .= $currentAfter;

	        } elseif (is_single()) {
	            if (get_the_category()) {
	                $cat = get_the_category();
	                $cat = $cat[0];
	                $html .= '<li>' . get_category_parents($cat, true, ' ' . $delimiter . '') . '</li>';
	            }
	            $html .= $currentBefore;
	            $html .= get_the_title();
	            $html .= $currentAfter;
	        } elseif (is_page() && !$post->post_parent) {
	            $html .= $currentBefore;
	            $html .= get_the_title();
	            $html .= $currentAfter;
	        } elseif (is_page() && $post->post_parent) {
	            $parent_id = $post->post_parent;
	            $breadcrumbs = array();
	            while ($parent_id) {
	                $page = get_page($parent_id);
	                $breadcrumbs[] = '<li><a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a></li>';
	                $parent_id = $page->post_parent;
	            }
	            $breadcrumbs = array_reverse($breadcrumbs);
	            foreach ($breadcrumbs as $crumb)
	                $html .= $crumb . ' ' . $delimiter . ' ';
	            $html .= $currentBefore;
	            $html .= get_the_title();
	            $html .= $currentAfter;
	        } elseif (is_search()) {
	            $html .= $currentBefore . esc_html__('Search Results for: "','thecrate') .get_search_query() . '"'. $currentAfter;
	        } elseif (is_tag()) {
	            $html .= $currentBefore . single_tag_title( '', false ) . $currentAfter;
	        } elseif (is_author()) {
	            global  $author;
	            $userdata = get_userdata($author);
	            $html .= $currentBefore . $userdata->display_name . $currentAfter;
	        } elseif (is_404()) {
	            $html .= $currentBefore . esc_attr__('404 Not Found','thecrate') . $currentAfter;
	        }
	        if (get_query_var('paged')) {
	            if (is_home() || is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author())
	                $html .= $currentBefore;
	            $html .= esc_attr__('Page','thecrate') . ' ' . get_query_var('paged');
	            if (is_home() || is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author())
	                $html .= $currentAfter;
	        }
	    }

	    return $html;
	}
}

if (!function_exists('thecrate_sharer')) {
	function thecrate_sharer($tooltip_placement){

		$html = '';
		$html .= '<div class="article-social">
	                <ul class="social-sharer">
	                    <li class="facebook">
	                        <a data-toggle="tooltip" title="'.esc_attr__('Share on Facebook','thecrate').'" data-placement="'.esc_attr($tooltip_placement).'" href="http://www.facebook.com/share.php?u='.get_permalink().'&amp;title='.get_the_title().'"><i class="icon-social-facebook"></i></a>
	                    </li>
	                    <li class="twitter">
	                        <a data-toggle="tooltip" title="'.esc_attr__('Tweet on Twitter','thecrate').'" data-placement="'.esc_attr($tooltip_placement).'" href="http://twitter.com/home?status='.get_the_title().'+'.get_permalink().'"><i class="icon-social-twitter"></i></a>
	                    </li>
	                    <li class="pinterest">
	                        <a data-toggle="tooltip" title="'.esc_attr__('Pin on Pinterest','thecrate').'" data-placement="'.esc_attr($tooltip_placement).'" href="http://pinterest.com/pin/create/bookmarklet/?media='.get_permalink().'&url='.get_permalink().'&is_video=false&description='.get_permalink().'"><i class="icon-social-pinterest"></i></a>
	                    </li>
	                    <li class="linkedin">
	                        <a data-toggle="tooltip" title="'.esc_attr__('Share on LinkedIn','thecrate').'" data-placement="'.esc_attr($tooltip_placement).'" href="http://www.linkedin.com/shareArticle?mini=true&amp;url='.get_permalink().'&amp;title='.get_the_title().'&amp;source='.get_permalink().'"><i class="icon-social-linkedin"></i></a>
	                    </li>
	                    <li class="reddit">
	                        <a data-toggle="tooltip" title="'.esc_attr__('Share on Reddit','thecrate').'" data-placement="'.esc_attr($tooltip_placement).'" href="http://www.reddit.com/submit?url='.get_permalink().'&amp;title='.get_the_title().'"><i class="icon-social-reddit"></i></a>
	                    </li>
	                    <li class="tumblr">
	                        <a data-toggle="tooltip" title="'.esc_attr__('Share on Tumblr','thecrate').'" data-placement="'.esc_attr($tooltip_placement).'" href="http://www.tumblr.com/share?v=3&amp;u='.get_permalink().'&amp;t='.get_the_title().'"><i class="icon-social-tumblr"></i></a>
	                    </li>
	                </ul>
	            </div>';

		return $html;
	}
}