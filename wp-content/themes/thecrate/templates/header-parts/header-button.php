<?php 
  if (thecrate_redux('tslr_header_btn_is_active') == 1) {
    if (thecrate_redux('tslr_header_btn_custom_url') != '') {
      $header_btn_url = thecrate_redux('tslr_header_btn_custom_url');
      // header button redux option
      $header_btn_custom_text = thecrate_redux('tslr_header_btn_custom_text');
      // header button custom field
      $header_btn_custom_text_meta = get_post_meta( get_the_ID(), 'header_btn_custom_text_meta', true);
      if (isset($header_btn_custom_text_meta) && !empty($header_btn_custom_text_meta)) {
        $button_text = $header_btn_custom_text_meta;
      }else{
        if (isset($header_btn_custom_text) && !empty($header_btn_custom_text)) {
          $button_text = $header_btn_custom_text;
        }else{
          $button_text = esc_html__('Subscribe', 'thecrate');
        }
      }

      $link_target = "";
      $redux_target = thecrate_redux('tslr_header_btn_target');
      if ($redux_target != '' && $redux_target != 0) {
        if ($redux_target['target'] == '1') {
          $link_target = "_blank";
        }
      }

      echo '<a target="'.esc_attr($link_target).'" href="'.esc_url($header_btn_url).'" class="header-btn">'.esc_html($button_text).'</a>';
    }
  }
?>