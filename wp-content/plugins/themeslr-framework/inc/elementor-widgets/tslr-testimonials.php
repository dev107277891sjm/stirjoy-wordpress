<?php
namespace Elementor;

class Themeslr_Testimonials_Widget extends Widget_Base {
   
    public function get_script_depends() {
        wp_register_script( 'tslr-custom', THEMESLR_FRAMEWORK_DIR.'js/tslr-custom.js');
        wp_register_script( 'owl-carousel', THEMESLR_FRAMEWORK_DIR.'js/owl.carousel.js');
        return [ 'jquery', 'elementor-frontend', 'owl-carousel', 'tslr-custom' ];
    }   

  public function get_name() {
    return 'testimonials';
  }
  
  public function get_title() {
    return esc_html__('ThemeSLR - Testimonials', 'themeslr');
  }
  
  public function get_icon() {
    return 'eicon-post-list';
  }
  
  public function get_categories() {
    return [ 'themeslr-widgets' ];
  }
  
  public function register_controls() {
    $this->start_controls_section(
      'section_content',
      [
          'label' => esc_html__( 'Content', 'themeslr' ),
      ]
    );
    $this->add_control(
      'visible_items',
      [
        'label' => esc_html__( 'Visible Testimonials per slide', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => '2',
        'options' => [
          '1' => '1', 
          '2' => '2', 
          '3' => '3', 
        ]
      ]
    );

    $this->add_control(
      'number',
      [
        'label' => esc_html__( 'Number', 'mt-addons' ),
        'description' => esc_html__( 'Number of testimonials show', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'default' => '10',
      ]
    );

    $this->add_control(
      'testimonial01_color',
      [
        'label' => esc_html__( 'Testimonial Content - Text Color', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '',
        'selectors' => [
            '{{WRAPPER}} .tslr-testimonials-shortcode-holder .testimonial01_item' => 'color: {{VALUE}};',
            '{{WRAPPER}} .tslr-testimonials-shortcode-holder .testimonail01-content' => 'border-color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'testimonial01_bg',
      [
        'label' => esc_html__( 'Testimonial Content - Background Color', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '',
        'selectors' => [
            '{{WRAPPER}} .tslr-testimonials-shortcode-holder' => 'background-color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'testimonial01_bg_image',
      [
        'label' => esc_html__( 'Testimonial Content - Background Image', 'mt-addons' ),
        'type'      => \Elementor\Controls_Manager::MEDIA,
        'selectors' => [
            '{{WRAPPER}} .tslr-testimonials-shortcode-holder' => 'background-image: url({{URL}}); background-position: center; background-repeat: no-repeat; background-size: 80%;',
        ],
      ]
    );
    $this->add_control(
        'padding',
        [
            'label' => esc_html__( 'Padding', 'mt-addons' ),
            'type'  => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => [
                '{{WRAPPER}} .tslr-testimonials-shortcode-holder' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

    $this->end_controls_section();
  
  }
  
  protected function render() {
    $settings           = $this->get_settings_for_display();
    $visible_items          = $settings['visible_items'];
    $number                         = $settings['number'];
    ?>
    <div class="tslr-testimonials-shortcode-holder">
      <div class="tslr-testimonials-shortcode testimonials-container-<?php echo esc_attr($visible_items); ?> owl-carousel owl-theme">
        <?php 
        $args_testimonials = array(
          'posts_per_page'   => $number,
          'orderby'          => 'post_date',
          'order'            => 'DESC',
          'post_type'        => 'testimonial',
          'post_status'      => 'publish' 
        );

        $testimonials = get_posts($args_testimonials);
          $i=0;
          foreach ($testimonials as $testimonial) {
            ++$i;
            #metaboxes
            $metabox_job_position = get_post_meta( $testimonial->ID, 'job-position', true );
            $metabox_company = get_post_meta( $testimonial->ID, 'company', true );
            $testimonial_id = $testimonial->ID;
            $content_post   = get_post($testimonial_id);
            $content        = $content_post->post_content;
            $content        = apply_filters('the_content', $content);
            $content        = str_replace(']]>', ']]&gt;', $content);
            #thumbnail
            $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $testimonial->ID ),'connection_testimonials_150x150' );
            
            if($i==1){ ?>
              <div class="item">
                <div class="vc_col-md-12 relative testimonial01_item_parent">
                  <div class="testimonial01_item text-left">
                    <svg width="96" height="18" viewBox="0 0 96 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 0H17.9381V17.9381H0V0ZM19.4332 0H37.3713V17.9381H19.4332V0ZM38.8655 0H56.8036V17.9381H38.8655V0ZM58.2987 0H76.2368V17.9381H58.2987V0ZM77.7319 0H95.67V17.9381H77.7319V0Z" fill="#00B67A"/><path d="M8.96949 12.0893L11.6979 11.3969L12.8369 14.9101L8.96949 12.0884V12.0893ZM15.2478 7.54831H10.4458L8.96949 3.02612L7.49318 7.54831H2.69116L6.57745 10.3511L5.10114 14.8724L8.98832 12.0696L11.3795 10.3511L15.2478 7.54831ZM28.4027 12.0893L31.1302 11.3969L32.2701 14.9101L28.4027 12.0884V12.0893ZM34.681 7.54831H29.8781L28.4027 3.02612L26.9264 7.54831H22.1244L26.0107 10.3511L24.5344 14.8724L28.4215 12.0696L30.8127 10.3511L34.681 7.54831ZM47.835 12.0893L50.5634 11.3969L51.7025 14.9101L47.835 12.0884V12.0893ZM54.1133 7.54831H49.3122L47.8359 3.02612L46.3605 7.54831H41.5576L45.4448 10.3511L43.9685 14.8724L47.8547 12.0696L50.2468 10.3511L54.1142 7.54831H54.1133ZM67.2682 12.0893L69.9966 11.3969L71.1357 14.9101L67.2682 12.0884V12.0893ZM73.5465 7.54831H68.7445L67.2682 3.02612L65.7919 7.54831H60.9899L64.8762 10.3511L63.4008 14.8724L67.2871 12.0696L69.6782 10.3511L73.5465 7.54831ZM86.7014 12.0893L89.4289 11.3969L90.5689 14.9101L86.7014 12.0884V12.0893ZM92.9798 7.54831H88.1768L86.7014 3.02612L85.2251 7.54831H80.4231L84.3094 10.3511L82.8331 14.8724L86.7203 12.0696L89.1114 10.3511L92.9798 7.54831Z" fill="white"/></svg>
                    <div class="testimonail01-content"><?php echo strip_tags(themeslr_excerpt_limit($content,18)); ?></div>
                    <h4><strong><?php echo $testimonial->post_title; ?></strong> - <?php echo $metabox_job_position; ?></h4>
                  </div> 
                </div>
            <?php } ?>
            <?php if($i==2){ ?>
                <div class="vc_col-md-12 relative testimonial01_item_parent">
                  <div class="testimonial01_item text-left">
                    <svg width="96" height="18" viewBox="0 0 96 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 0H17.9381V17.9381H0V0ZM19.4332 0H37.3713V17.9381H19.4332V0ZM38.8655 0H56.8036V17.9381H38.8655V0ZM58.2987 0H76.2368V17.9381H58.2987V0ZM77.7319 0H95.67V17.9381H77.7319V0Z" fill="#00B67A"/><path d="M8.96949 12.0893L11.6979 11.3969L12.8369 14.9101L8.96949 12.0884V12.0893ZM15.2478 7.54831H10.4458L8.96949 3.02612L7.49318 7.54831H2.69116L6.57745 10.3511L5.10114 14.8724L8.98832 12.0696L11.3795 10.3511L15.2478 7.54831ZM28.4027 12.0893L31.1302 11.3969L32.2701 14.9101L28.4027 12.0884V12.0893ZM34.681 7.54831H29.8781L28.4027 3.02612L26.9264 7.54831H22.1244L26.0107 10.3511L24.5344 14.8724L28.4215 12.0696L30.8127 10.3511L34.681 7.54831ZM47.835 12.0893L50.5634 11.3969L51.7025 14.9101L47.835 12.0884V12.0893ZM54.1133 7.54831H49.3122L47.8359 3.02612L46.3605 7.54831H41.5576L45.4448 10.3511L43.9685 14.8724L47.8547 12.0696L50.2468 10.3511L54.1142 7.54831H54.1133ZM67.2682 12.0893L69.9966 11.3969L71.1357 14.9101L67.2682 12.0884V12.0893ZM73.5465 7.54831H68.7445L67.2682 3.02612L65.7919 7.54831H60.9899L64.8762 10.3511L63.4008 14.8724L67.2871 12.0696L69.6782 10.3511L73.5465 7.54831ZM86.7014 12.0893L89.4289 11.3969L90.5689 14.9101L86.7014 12.0884V12.0893ZM92.9798 7.54831H88.1768L86.7014 3.02612L85.2251 7.54831H80.4231L84.3094 10.3511L82.8331 14.8724L86.7203 12.0696L89.1114 10.3511L92.9798 7.54831Z" fill="white"/></svg>
                    <div class="testimonail01-content"><?php echo strip_tags(themeslr_excerpt_limit($content,18)); ?></div>
                    <h4><strong><?php echo $testimonial->post_title; ?></strong> - <?php echo $metabox_job_position; ?></h4>
                  </div> 
                </div>
              </div>
              <?php $i=0; ?>
            <?php } ?>
        <?php } ?>
      </div>
    </div>
    <?php if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) { ?>
      <script type="text/javascript">
        jQuery( document ).ready(function() {
          setTimeout(function() {
              jQuery(".testimonials-container-1").owlCarousel({
                  navigation      : false, // Show next and prev buttons
                  pagination      : true,
                  autoPlay        : true,
                  slideSpeed      : 700,
                  paginationSpeed : 700,
                  itemsCustom : [
                      [0,     1],
                      [450,   1],
                      [600,   1],
                      [700,   1],
                      [1000,  1],
                      [1200,  1],
                      [1400,  1],
                      [1600,  1]
                  ]
              });
          }, 1000);
          jQuery(".testimonials-container-2").owlCarousel({
              navigation      : false, // Show next and prev buttons
              pagination      : false,
              autoPlay        : false,
              slideSpeed      : 700,
              paginationSpeed : 700,
              itemsCustom : [
                  [0,     1],
                  [450,   1],
                  [600,   2],
                  [700,   2],
                  [1000,  2],
                  [1200,  2],
                  [1400,  2],
                  [1600,  2]
              ]
          });
          jQuery(".testimonials-container-3").owlCarousel({
              navigation      : false, // Show next and prev buttons
              pagination      : false,
              autoPlay        : false,
              slideSpeed      : 700,
              paginationSpeed : 700,
              itemsCustom : [
                  [0,     1],
                  [450,   1],
                  [600,   2],
                  [700,   2],
                  [1000,  3],
                  [1200,  3],
                  [1400,  3],
                  [1600,  3]
              ]
          });
        });
      </script>
    <?php } ?>

    <?php
  }
  protected function content_template() {

  }
}