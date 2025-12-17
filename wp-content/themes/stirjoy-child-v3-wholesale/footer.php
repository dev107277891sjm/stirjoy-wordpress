<?php
/**
 * The template for displaying the footer.
 *
*/
?>

    <!-- FOOTER -->
    <footer class="footer-main-group stirjoy-footer">
        <div class="container">
            <!-- Footer Top Section - Three Columns -->
            <div class="stirjoy-footer-top">
                <div class="row">
                    <!-- Left Column - Navigation Links -->
                    <div class="col-md-3 stirjoy-footer-col">
                        <p class="stirjoy-footer-item"><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">SHOP MEALS</a></p>
                        <p class="stirjoy-footer-item"><a href="<?php echo esc_url( wc_get_page_permalink( 'home' ) ); ?>/#how-it-works">HOW IT WORKS</a></p>
                        <p class="stirjoy-footer-item"><a href="<?php echo esc_url( wc_get_page_permalink( 'home' ) ); ?>/#our-story">OUR STORY</a></p>
                        <p class="stirjoy-footer-item"><a href="<?php echo esc_url( wc_get_page_permalink( 'my account' ) ); ?>">MY ACCOUNT</a></p>
                    </div>
                    
                    <!-- Middle Column - Information Links -->
                    <div class="col-md-3 stirjoy-footer-col">
                        <p class="stirjoy-footer-item"><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ?: '#' ); ?>">CONTACT US</a></p>
                        <p class="stirjoy-footer-item"><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'delivery' ) ) ?: '#' ); ?>">FAQs</a></p>
                        <p class="stirjoy-footer-item"><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'terms-conditions' ) ) ?: wc_get_page_permalink( 'terms-conditions' ) ); ?>">TERMS & CONDITIONS</a></p>
                        <p class="stirjoy-footer-item"><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'privacy-policy' ) ) ?: get_privacy_policy_url() ); ?>">PRIVACY POLICY</a></p>
                        <p class="stirjoy-footer-item"><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'cookie-policy' ) ) ?: wc_get_page_permalink( 'cookie-policy' ) ); ?>">COOKIE POLICY</a></p>
                    </div>
                    
                    <!-- Right Column - Newsletter & Social -->
                    <div class="col-md-6 stirjoy-footer-col">
                        <div class="stirjoy-footer-newsletter-container">
                            <div class="stirjoy-footer-newsletter">
                                <h3 class="stirjoy-newsletter-title">Join our newsletter and get first dibs on seasonal bundles and exclusive deals!</h3>
                                <form class="stirjoy-newsletter-form" method="post" action="">
                                    <div class="stirjoy-newsletter-input-group">
                                        <input type="email" name="email" placeholder="Your email" class="stirjoy-email-input" required>
                                        <button type="submit" class="stirjoy-submit-btn">SUBMIT</button>
                                    </div>
                                </form>
                            </div>
                        
                            <div class="stirjoy-footer-social">
                                <a href="https://www.tiktok.com/@stirjoy" target="_blank" rel="noopener" class="stirjoy-social-link">
                                    <img src="<?php echo esc_url(stirjoy_get_image_url('Group (1).png')); ?>" alt="TIKTOK">
                                    <span>TIKTOK</span>
                                </a>
                                <a href="https://www.instagram.com/stirjoy" target="_blank" rel="noopener" class="stirjoy-social-link">
                                    <img src="<?php echo esc_url(stirjoy_get_image_url('Group (2).png')); ?>" alt="INSTAGRAM">
                                    <span>INSTAGRAM</span>
                                </a>
                            </div>
                        </div>
                        <p class="stirjoy-copyright">© STIRJOY 2025</p>
                    </div>
                </div>
            </div>
            
        </div>
        <!-- Footer Bottom - Large STIRJOY Logo -->
        <div class="stirjoy-footer-brand">
            <img src="<?php echo esc_url(stirjoy_get_image_url('Calque_1.png')); ?>" alt="STIRJOY" class="stirjoy-footer-brand-img-desktop">
            <img src="<?php echo esc_url(stirjoy_get_image_url('Calque_1 (1).png')); ?>" alt="STIRJOY" class="stirjoy-footer-brand-img-mobile">
        </div>
    </footer>
</div>

<?php if ( class_exists( 'ReduxFrameworkPlugin' ) ) { ?>
    <?php if (thecrate_redux('thecrate_backtotop_status') == true) { ?>
        <?php 
        if (thecrate_redux('thecrate_backtotop_border_status') == 0) {
            $border_status = 'has-no-border';
        }else{
            $border_status = 'has-border';
        } ?>
        <!-- BACK TO TOP BUTTON -->
        <a class="back-to-top themeslr-is-visible themeslr-fade-out <?php echo esc_attr($border_status); ?>" href="#0">
            <span></span>
        </a>
    <?php } ?>
<?php } ?>

<?php wp_footer(); ?>
</body>
</html>