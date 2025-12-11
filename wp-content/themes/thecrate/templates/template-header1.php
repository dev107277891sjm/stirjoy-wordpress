<header class="header1">

  <?php //Header Top Row ?>
  <?php get_template_part('templates/header-parts/header', 'top'); ?>

  <!-- Header Bottom -->
  <nav class="navbar navbar-default" id="theme-main-head">
    <div class="container">
      <!-- Logo -->
      <div class="navbar-header">
        <!-- Responsive Burger Nav -->
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar1" aria-expanded="false" aria-controls="navbar1">
            <span class="sr-only"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>

        <?php if(thecrate_redux('thecrate_logo','url')){ ?>
          <?php $logo = '<img src="'.esc_url(thecrate_redux('thecrate_logo','url')).'" alt="'.esc_attr(get_bloginfo()).'" />'; ?>
          <?php $logo_class = ''; ?>
        <?php }else{ ?>
          <?php $logo = get_bloginfo(); ?>
          <?php $logo_class = 'no-logo'; ?>
        <?php } ?>

        <div class="logo <?php echo esc_attr($logo_class); ?>">
          <a href="<?php echo esc_url(get_site_url()); ?>">
            <?php echo wp_kses($logo, 'link'); ?>
          </a>
        </div>
      </div>

      <!-- NAV MENU -->
      <div id="navbar" class="navbar-collapse collapse">
        <ul class="menu nav navbar-nav nav-effect nav-menu">
          <?php
            if ( has_nav_menu( 'primary' ) ) {
              $defaults = array(
                'menu'            => '',
                'container'       => false,
                'container_class' => '',
                'container_id'    => '',
                'menu_class'      => 'menu',
                'menu_id'         => '',
                'echo'            => true,
                'fallback_cb'     => false,
                'before'          => '',
                'after'           => '',
                'link_before'     => '',
                'link_after'      => '',
                'items_wrap'      => '%3$s',
                'depth'           => 0,
                'walker'          => ''
              );

              $defaults['theme_location'] = 'primary';

              wp_nav_menu( $defaults );
            }else{
              if( current_user_can( 'administrator' ) ){
                echo thecrate_no_menu_set_notice('right');
              }
            }
          ?>
        </ul>
      </div>

      <!-- MOBILE NAV MENU -->
      <div id="navbar1" class="navbar-collapse collapse col-md-5">
        <?php if ( !class_exists( 'ReduxFrameworkPlugin' ) ) { ?>
          <?php $header_nav_status = 'redux-missing'; ?>
        <?php }else{ ?>
          <?php $header_nav_status = ''; ?>
        <?php } ?>

        <div class="header-nav-actions <?php echo esc_attr($header_nav_status); ?>">
          <?php //Header icons group: Search, Cart, Account ?>
          <?php get_template_part('templates/header-parts/header', 'icons-group'); ?>

          <?php //Header button ?>
          <?php get_template_part('templates/header-parts/header', 'button'); ?>
        </div>

        <ul class="menu nav navbar-nav nav-effect nav-menu mobile-menu pull-left">
          <?php
            if ( has_nav_menu( 'primary' ) ) {
              $defaults = array(
                'menu'            => '',
                'container'       => false,
                'container_class' => '',
                'container_id'    => '',
                'menu_class'      => 'menu',
                'menu_id'         => '',
                'echo'            => true,
                'fallback_cb'     => false,
                'before'          => '',
                'after'           => '',
                'link_before'     => '',
                'link_after'      => '',
                'items_wrap'      => '%3$s',
                'depth'           => 0,
                'walker'          => ''
              );

              $defaults['theme_location'] = 'primary';

              wp_nav_menu( $defaults );
            }else{
              if( current_user_can( 'administrator' ) ){
                echo thecrate_no_menu_set_notice('right');
              }
            }
          ?>
        </ul>
      </div>
    </div>  

    <?php if ( class_exists( 'WooCommerce' ) && thecrate_redux('thecrate_fixed_sidebar_cart') == true && is_user_logged_in() ) { ?>
      <!-- Fixed Sidebar Menu -->
      <div class="relative fixed-sidebar-menu-holder header7">
          <div class="fixed-sidebar-menu fixed-sidebar-menu-minicart">
              <!-- Close Cart Button (styled like cart bar toggle) -->
              <button type="button" class="cart-box-close-button" aria-label="Close cart">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <line x1="18" y1="6" x2="6" y2="18"></line>
                      <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
              </button>
              <!-- Sidebar Menu Holder -->
              <div class="header7">
                  <!-- RIGHT SIDE -->
                  <div class="left-side container">
                      <div class="header_mini_cart">
                          <?php the_widget( 'WC_Widget_Cart', array( 'title' => esc_html__('Your Box', 'thecrate') ) ); ?>
                      </div>
                  </div>
              </div>
          </div>
      </div>
    <?php } ?>
  </nav>
</header>