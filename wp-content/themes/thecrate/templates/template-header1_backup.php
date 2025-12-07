<header class="header1">

  <?php //Header Top Row ?>
  <?php get_template_part('templates/header-parts/header', 'top'); ?>

  <!-- Header Bottom -->
  <nav class="navbar navbar-default" id="theme-main-head">
    <div class="navbar-header col-md-2">
      <!-- Responsive Burger Nav -->
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
      </button>

      <!-- Logo -->
      <?php $header_custom_logo = get_post_meta( get_the_ID(), 'header_custom_logo', true ); ?>
      <?php if(isset($header_custom_logo) && !empty($header_custom_logo)){ ?>
        <?php $logo = '<img src="'.esc_url($header_custom_logo).'" alt="'.esc_attr(get_bloginfo()).'" />'; ?>
        <?php $logo_class = ''; ?>
      <?php }else{ ?>
        <?php if(thecrate_redux('thecrate_logo','url')){ ?>
          <?php $logo = '<img src="'.esc_url(thecrate_redux('thecrate_logo','url')).'" alt="'.esc_attr(get_bloginfo()).'" />'; ?>
          <?php $logo_class = ''; ?>
        <?php }else{ ?>
          <?php $logo = get_bloginfo(); ?>
          <?php $logo_class = 'no-logo'; ?>
        <?php } ?>
      <?php } ?>

      <div class="logo <?php echo esc_attr($logo_class); ?>">
        <a href="<?php echo esc_url(get_site_url()); ?>">
          <?php echo wp_kses($logo, 'link'); ?>
        </a>
      </div>
    </div>

    <!-- NAV MENU -->
    <div id="navbar" class="navbar-collapse collapse col-md-10">

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


      <ul class="menu nav navbar-nav nav-effect nav-menu pull-right">
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
  </nav>
</header>