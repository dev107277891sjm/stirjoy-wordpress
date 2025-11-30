<?php
namespace Elementor;

class Themeslr_Clients_Widget extends Widget_Base {
   
    public function get_script_depends() {
        wp_register_script( 'tslr-custom', THEMESLR_FRAMEWORK_DIR.'js/tslr-custom.js');
        wp_register_script( 'owl-carousel', THEMESLR_FRAMEWORK_DIR.'js/owl.carousel.js');
        return [ 'jquery', 'elementor-frontend', 'owl-carousel', 'tslr-custom' ];
    }   

  public function get_name() {
    return 'clients';
  }
  
  public function get_title() {
    return esc_html__('ThemeSLR - Clients', 'themeslr');
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
      'visible_items_clients',
      [
        'label' => esc_html__( 'Visible Clients per slide', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => '5',
        'options' => [
          '1' => '1', 
          '2' => '2', 
          '3' => '3', 
          '4' => '4', 
          '5' => '5', 
          '6' => '6'
        ]
      ]
    );

    $this->add_control(
      'number',
      [
        'label' => esc_html__( 'Number', 'mt-addons' ),
        'description' => esc_html__( 'Number of clients logos to show', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'default' => '10',
      ]
    );

    $this->add_control(
      'background_color_overlay',
      [
        'label' => esc_html__( 'Logo Background Overlay', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '',
        'selectors' => [
            '{{WRAPPER}} .clients_image_holder_inside' => 'background-color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'background_color_overlay',
      [
        'label' => esc_html__( 'Logo Background Overlay', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '',
        'selectors' => [
            '{{WRAPPER}} .clients_image_holder_inside' => 'background-color: {{VALUE}};',
        ],
      ]
    );
    // $this->add_group_control(
    //   \Elementor\Group_Control_Border::get_type(),
    //   [
    //     'name' => 'border',
    //     'selector' => '{{WRAPPER}} .themeslr_clients_slider.owl-carousel .owl-wrapper-outer',
    //   ]
    // );

    $this->end_controls_section();
  
  }
  
  protected function render() {
    $settings           = $this->get_settings_for_display();

    $visible_items_clients           = $settings['visible_items_clients'];
    $number           = $settings['number'];

    $args_clients = array(
      'posts_per_page'   => $number,
      'orderby'          => 'post_date',
      'order'            => 'DESC',
      'post_type'        => 'clients',
      'post_status'      => 'publish' 
    );
    ?>

    <div class="row">
      <div class="themeslr_clients_slider col-md-12 clients_container_shortcode-<?php echo esc_attr($visible_items_clients); ?> owl-carousel owl-theme">
        <?php $clients = get_posts($args_clients); ?>
        <?php if($clients) { ?>
          <?php foreach ($clients as $client) { ?>
            <?php $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $client->ID ),'full' ); ?>
            <div class="clients_image_holder post">
              <div class="item col-md-12">
                <div class="clients_image_holder_inside post">
                  <?php if($thumbnail_src) { ?> 
                    <img class="client_image" src="<?php echo esc_url($thumbnail_src[0]); ?>" alt="<?php echo esc_attr($client->post_title); ?>" />
                  <?php }else{ ?> 
                    <img src="http://placehold.it/160x100" alt="<?php echo esc_attr($client->post_title); ?>" /> 
                  <?php } ?>
                </div>
              </div>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
    </div>

    <?php if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) { ?>
      <script type="text/javascript">
        jQuery( document ).ready(function() {
          jQuery(".clients_container_shortcode-1").owlCarousel({
              navigation      : false, // Show next and prev buttons
              navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
              pagination      : false,
              autoPlay        : false,
              slideSpeed      : 700,
              paginationSpeed : 700,
              autoPlay : true,
              autoPlayTimeout:10000,
              autoPlayHoverPause:true,
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
          jQuery(".clients_container_shortcode-2").owlCarousel({
              navigation      : false, // Show next and prev buttons
              navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
              pagination      : false,
              autoPlay        : false,
              slideSpeed      : 700,
              paginationSpeed : 700,
              autoPlay : true,
              autoPlayTimeout:10000,
              autoPlayHoverPause:true,
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
          jQuery(".clients_container_shortcode-3").owlCarousel({
              navigation      : false, // Show next and prev buttons
              navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
              pagination      : false,
              autoPlay        : false,
              slideSpeed      : 700,
              paginationSpeed : 700,
              autoPlay : true,
              autoPlayTimeout:10000,
              autoPlayHoverPause:true,
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

          jQuery(".clients_container_shortcode-4").owlCarousel({
              navigation      : false, // Show next and prev buttons
              navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
              pagination      : false,
              autoPlay        : false,
              slideSpeed      : 700,
              paginationSpeed : 700,
              autoPlay : true,
              autoPlayTimeout:10000,
              autoPlayHoverPause:true,
              itemsCustom : [
                  [0,     1],
                  [450,   1],
                  [600,   2],
                  [700,   3],
                  [1000,  4],
                  [1200,  4],
                  [1400,  4],
                  [1600,  4]
              ]
          });


          jQuery(".clients_container_shortcode-5").owlCarousel({
              navigation      : false, // Show next and prev buttons
              navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
              pagination      : false,
              autoPlay        : false,
              slideSpeed      : 700,
              paginationSpeed : 700,
              autoPlay : true,
              autoPlayTimeout:10000,
              autoPlayHoverPause:true,
              itemsCustom : [
                  [0,     1],
                  [450,   1],
                  [600,   2],
                  [700,   3],
                  [1000,  4],
                  [1200,  5],
                  [1400,  5],
                  [1600,  5]
              ]
          });

          jQuery(".clients_container_shortcode-6").owlCarousel({
              navigation      : false, // Show next and prev buttons
              navigationText      : ["<i class='fa fa-long-arrow-left'></i>","<i class='fa fa-long-arrow-right'></i>"],
              pagination      : false,
              autoPlay        : false,
              slideSpeed      : 700,
              paginationSpeed : 700,
              autoPlay : true,
              autoPlayTimeout:10000,
              autoPlayHoverPause:true,
              itemsCustom : [
                  [0,     2],
                  [450,   2],
                  [600,   3],
                  [700,   3],
                  [1000,  4],
                  [1200,  6],
                  [1400,  6],
                  [1600,  6]
              ]
          });
        });
      </script>
    <?php }
  }
  protected function content_template() {

  }
}