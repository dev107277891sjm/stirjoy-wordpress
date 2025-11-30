<?php if (thecrate_redux('thecrate_header_top_status') == true) { ?>
  <!-- TOP HEADER -->
  <div class="top-header row no-margin">
    <?= esc_html(thecrate_redux('thecrate_header_top_small_left_2_text')) ?>

    <?php /*<div class="col-md-6 col-sm-6 text-left left-side">
      <div class="top-header-col-inner">
        <?php
          if (thecrate_redux('tslr_enable_phone_number') == 1) {
            echo '<a target="_blank" href="'.esc_url(thecrate_redux('thecrate_header_top_small_left_1_url')).'">
                    <i class="fas fa-phone-alt"></i> '.esc_html(thecrate_redux('thecrate_header_top_small_left_1_text')).'
                  </a>';
          }

          if (thecrate_redux('tslr_enable_address') == 1) {
            echo '<a target="_blank" href="'.esc_url(thecrate_redux('thecrate_header_top_small_left_2_url')).'">
                    <i class="fas fa-map-marked-alt"></i> '.esc_html(thecrate_redux('thecrate_header_top_small_left_2_text')).'
                  </a>';
          }
        ?>
      </div>
    </div>

    <div class="col-md-6 col-sm-6 text-right right-side">
      <div class="top-header-col-inner">
        <?php echo thecrate_get_top_right(); ?>
      </div>
    </div>*/ ?>
  </div>
<?php } ?>