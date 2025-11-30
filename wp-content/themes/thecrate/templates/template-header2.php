<header class="header2">

  <?php get_template_part('templates/header-parts/header', 'top'); ?>

  <!-- BOTTOM BAR -->
  <nav class="navbar navbar-default" id="theme-main-head">
    <div class="container">
        <div class="row">
          <!-- LOGO -->
          <div class="navbar-header col-md-12 text-center">

            <?php if(thecrate_redux('thecrate_logo','url')){ ?>
              <div class="logo">
                  <a href="<?php echo get_site_url(); ?>">
                      <img src="<?php echo esc_url(thecrate_redux('thecrate_logo','url')); ?>" alt="<?php echo esc_attr(get_bloginfo()); ?>" />
                  </a>
              </div>
            <?php }else{ ?>
              <div class="logo no-logo">
                  <a href="<?php echo esc_url(get_site_url()); ?>">
                    <?php echo esc_html(get_bloginfo()); ?>
                  </a>
              </div>
            <?php } ?>

            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
          </div>

          <!-- NAV MENU -->
          <div id="navbar" class="navbar-collapse collapse col-md-12">
            <ul class="menu nav navbar-nav nav-effect nav-menu col-md-8">
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


            <div class="header-nav-actions col-md-4">
              <div class="pull-right">
                <?php //Header icons group: Search, Cart, Account ?>
                <?php get_template_part('templates/header-parts/header', 'icons-group'); ?>

                <?php //Header button ?>
                <?php get_template_part('templates/header-parts/header', 'button'); ?>
              </div>
            </div>
          </div>
        </div>
    </div>
  </nav>
</header>
