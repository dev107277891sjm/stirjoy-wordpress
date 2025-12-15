<div class="thecrate-header-icons-group"> 

  <?php if(thecrate_redux('thecrate_header_fixed_sidebar_menu_status') == true){ ?>
    <!-- NAV BURGER -->
    <a href="#" class="thecrate-nav-burger">
      <i class="fas fa-ellipsis-v"></i>
    </a>
  <?php } ?>

  <?php if ( class_exists( 'ReduxFrameworkPlugin' ) ) { ?>
    <?php if(thecrate_redux('thecrate_header_is_search') == true){ ?>
      <?php 
        if(thecrate_redux('thecrate_header_is_search_mobile') == false){
          $search_status = 'hidden_on_mobile';
        }else{
          $search_status = '';
        }
      ?>
      <a href="#" class="thecrate-search-icon <?php echo esc_attr($search_status); ?>">
        <i class="fas fa-search" aria-hidden="true"></i>
      </a>
    <?php } ?>
  <?php }else{ ?>
    <a href="#" class="thecrate-search-icon">
      <i class="fas fa-search" aria-hidden="true"></i>
    </a>
  <?php } ?>

  <?php if ( class_exists( 'WooCommerce' ) ) { ?>
    <?php if ( is_user_logged_in() ) { ?>
      <a href="<?php echo esc_url(get_permalink( get_option('woocommerce_myaccount_page_id') )); ?>" class="thecrate-account-link thecrate-account-link-loggedin">
        <!-- <i class="far fa-user-circle"></i> -->
        <span class="log-in-text">MY ACCOUNT</span>
      </a>
      <a class="cart-contents-custom" href="<?php echo esc_url(wc_get_cart_url()); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'thecrate'); ?>">
        <img src="<?php echo esc_url(stirjoy_get_image_url('shopping-basket.png')); ?>" alt="Cart">
        <span>(<?php echo WC()->cart->get_cart_contents_count(); ?>)</span>
      </a>
    <?php }else{ ?>
      <a href="<?php echo esc_url(get_permalink( get_option('woocommerce_myaccount_page_id') )); ?>" class="thecrate-account-link thecrate-account-link-loggedout">
        <!-- <i class="far fa-user-circle"></i> -->
         <span class="log-in-text">LOG IN</span>
      </a>
    <?php } ?>


    <?php if ( is_user_logged_in() ) { ?>
      <div class="woocommerce-MyAccount-navigation thecrate-woocommerce-account-tabs">
        <ul>
          <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
            <li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
              <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php } ?>
  <?php } ?>
  
</div>



<?php if ( class_exists( 'ReduxFrameworkPlugin' ) ) { ?>
  <?php if(thecrate_redux('thecrate_header_is_search') == true){ ?>
  <!-- Search Form -->
  <div class="fixed-search-overlay">
      <!-- INSIDE SEARCH OVERLAY -->
      <div class="fixed-search-inside">
          <?php echo thecrate_custom_search_form(); ?>
      </div>
  </div>
  <?php } ?>
<?php } ?>

