<?php
/**
 * The template for displaying the footer.
 *
*/
?>

    <!-- FOOTER -->
    <footer class="footer-main-group">
        <?php if ( class_exists( 'ReduxFrameworkPlugin' ) ) { ?>
            <?php if (thecrate_redux('thecrate_footer_row_1') == true) { ?>
                <?php if (is_active_sidebar('footer_row_1_1') || is_active_sidebar('footer_row_1_2') || is_active_sidebar('footer_row_1_3') || is_active_sidebar('footer_row_1_4') || is_active_sidebar('footer_row_1_5')) { ?>
                    <!-- FOOTER TOP -->
                    <div class="row footer-top">
                        <div class="container">
                        <?php
                            //FOOTER WIDGETS ROW 1
                            echo thecrate_footer_row1();
                        ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        <?php } ?>

        <!-- FOOTER BOTTOM -->
        <div class="row footer">
            <div class="container">
                <div class="row">
                    <?php if ( class_exists( 'ReduxFrameworkPlugin' ) ) { ?>
                        <div class="col-md-6 text-left footer-bottom-col-2">
                            <p class="copyright"><?php echo thecrate_redux('thecrate_footer_text_center'); ?></p>
                        </div>
                        <div class="col-md-6 text-right footer-bottom-col-3">
                            <p class="card-icons"><?php echo thecrate_redux('thecrate_footer_text_right'); ?></p>
                        </div>
                    <?php }else{ ?>
                        <div class="col-md-6 text-left footer-bottom-col-2">
                            <p class="copyright"><?php echo esc_html__('Handcrafted with Love by ThemeSLR.','thecrate'); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
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