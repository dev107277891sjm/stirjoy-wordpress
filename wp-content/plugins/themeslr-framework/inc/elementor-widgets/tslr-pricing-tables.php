<?php
namespace Elementor;

class Themeslr_Pricing_Table_Widget extends Widget_Base {
  
  public function get_name() {
    return 'tslr_pricing';
  }
  
  public function get_title() {
    return esc_html__('ThemeSLR - Pricing Table', 'themeslr');
  }
  
  public function get_icon() {
    return 'eicon-post-list';
  }
  
  public function get_categories() {
    return [ 'themeslr-widgets' ];
  }
  
  protected function register_controls() {
    $this->section_title();
  }

  private function section_title() {
    $this->start_controls_section(
      'section_title',
      [
          'label' => esc_html__( 'Title & Subtitle', 'themeslr' ),
      ]
    );  

    $this->add_control(
      'package_name',
      [
        'label' => esc_html__( 'Title', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => 'Monthly',
      ]
    );
    $this->add_control(
      'title_color',
      [
        'label' => __( 'Title Color', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#2a3cb7',
        'selectors' => [
            '{{WRAPPER}} .themeslr-pricing-table .pricing__title span' => 'color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'title_bg',
      [
        'label' => __( 'Title Background', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#ffffff',
        'selectors' => [
            '{{WRAPPER}} .themeslr-pricing-table .pricing__title span' => 'background: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'package_subtitle',
      [
        'label' => esc_html__( 'Subtitle', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => 'Mega Discount',
      ]
    );
    $this->add_control(
      'subtitle_color',
      [
        'label' => __( 'Subtitle Color', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#666666',
        'selectors' => [
            '{{WRAPPER}} .themeslr-pricing-table .pricing__sentence' => 'color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'package_recommended',
      [
        'label' => esc_html__( 'Featured', 'themeslr' ),
        'label_block' => true,
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => 'pricing__item--nofeatured',
        'options' => array(
          'pricing__item--nofeatured' => __('Basic', 'themeslr'),
          'pricing__item--featured' => __('Recommended', 'themeslr'),
        ),
      ]
    );
    $this->end_controls_section();

    $this->start_controls_section(
        'pricing_bubble_group',
        [
            'label' => __( 'Price Bubble', 'text-domain' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]
    );
    $this->add_control(
      'package_price',
      [
        'label' => esc_html__( 'Price', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => '99',
      ]
    );
    $this->add_control(
      'package_currency',
      [
        'label' => esc_html__( 'Currency', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => '$',
      ]
    );
    $this->add_control(
      'package_period',
      [
        'label' => esc_html__( 'Period', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => '/month',
      ]
    );
    $this->add_control(
      'price_bubble_bg_fill',
      [
        'label' => __( 'Bubble Background', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#F9DBD9',
        'selectors' => [
            '{{WRAPPER}} .themeslr-pricing-table svg path' => 'fill: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'price_bubble_color',
      [
        'label' => __( 'Color', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#454545',
        'selectors' => [
            '{{WRAPPER}} .themeslr-pricing-table .pricing__period strong' => 'color: {{VALUE}};',
            '{{WRAPPER}} .themeslr-pricing-table .pricing__bubble_content' => 'color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
        'border_radius',
        [
            'label'     => __( 'Border Radius', 'text-domain' ),
            'type'      => Controls_Manager::DIMENSIONS,
            'default'   => [
                'top'    => '0',
                'right'  => '0',
                'bottom' => '0',
                'left'   => '0',
                'unit'   => 'px',
            ],
            'selectors' => [
                '{{WRAPPER}} .pricing__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );
    $this->end_controls_section();


    $this->start_controls_section(
        'pricing_image_group',
        [
            'label' => __( 'Image', 'text-domain' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]
    );
    $this->add_control(
      'package_image_status',
      [
        'label'        => __( 'Image', 'text-domain' ),
        'type'         => \Elementor\Controls_Manager::SWITCHER,
        'label_on'     => __( 'On', 'text-domain' ),
        'label_off'    => __( 'Off', 'text-domain' ),
        'return_value' => 'yes',
        'default'      => 'no',
      ]
    );
    $this->add_control(
      'package_image_style',
      [
        'label' => esc_html__( 'Image Style', 'themeslr' ),
        'label_block' => true,
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => '',
        'options' => array(
          '' => __('Square (Default)', 'themeslr'),
          'img-rounded' => __('Rounded', 'themeslr'),
          'img-circle' => __('Circle', 'themeslr'),
        ),
        'condition' => [
            'package_image_status' => 'yes', // Show only if switcher is 'yes'
        ],
      ]
    );
    $this->add_control(
        'package_image',
        [
          'label'   => __( 'Choose Image', 'text-domain' ),
          'type'    => \Elementor\Controls_Manager::MEDIA,
          'default' => [
              'url' => \Elementor\Utils::get_placeholder_image_src(),
          ],
          'condition' => [
              'package_image_status' => 'yes', // Show only if switcher is 'yes'
          ],
        ]
    );
    $this->end_controls_section();


    $this->start_controls_section(
        'pricing_features_group',
        [
            'label' => __( 'Pricing Features', 'text-domain' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]
    );
    $this->add_control(
      'features_color',
      [
        'label' => __( 'Features Color', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#606060',
        'selectors' => [
            '{{WRAPPER}} .pricing__feature' => 'color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'features_border_color',
      [
        'label' => __( 'Features Border Color', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#E8E8E8',
        'selectors' => [
            '{{WRAPPER}} .pricing__feature' => 'border-color: {{VALUE}};',
        ],
      ]
    );
    $repeater = new Repeater();
    $repeater->add_control(
        'name',
        [
            'label'   => __( 'Feature', 'text-domain' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => __( 'Re-bills every month', 'text-domain' ),
        ]
    );

    $this->add_control(
        'features',
        [
            'label'       => __( 'Features list', 'text-domain' ),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'default'     => [
                [ 'name' => __( 'Re-bills every month', 'text-domain' ) ],
                [ 'name' => __( 'Cancel Anytime', 'text-domain' ) ],
                [ 'name' => __( 'Get Free Shipping', 'text-domain' ) ],
            ],
            'title_field' => '{{{ name }}}', // Shows field value in the UI
        ]
    );
    $this->end_controls_section();


    $this->start_controls_section(
        'button_section',
        [
            'label' => __( 'Button', 'text-domain' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]
    );
    $this->add_control(
      'button_text',
      [
        'label' => esc_html__( 'Button text', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => 'Select Box',
      ]
    );
    $this->add_control(
      'button_url',
      [
        'label' => esc_html__( 'Link', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => '#',
      ]
    );
    $this->add_control(
      'btn_style',
      [
        'label' => esc_html__( 'Style', 'themeslr' ),
        'label_block' => true,
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => 'btn-square',
        'options' => array(
          'btn-square' => __('Square (Default)', 'themeslr'),
          'btn-rounded' => __('Rounded (5px Radius)', 'themeslr'),
          'btn-round' => __('Round (30px Radius)', 'themeslr'),
        ),
      ]
    );
    $this->add_control(
      'button_bg',
      [
        'label' => __( 'Background', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#2a3cb7',
        'selectors' => [
            '{{WRAPPER}} .button' => 'background-color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'button_bg_hover',
      [
        'label' => __( 'Background Hover', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#2a3cb7',
        'selectors' => [
            '{{WRAPPER}} .button:hover' => 'background-color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'button_color',
      [
        'label' => __( 'Color', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#ffffff',
        'selectors' => [
            '{{WRAPPER}} .button' => 'color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'button_color_hover',
      [
        'label' => __( 'Color Hover', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#ffffff',
        'selectors' => [
            '{{WRAPPER}} .button:hover' => 'color: {{VALUE}};',
        ],
      ]
    );
    $this->end_controls_section();


    $this->start_controls_section(
        'general_styling_section',
        [
            'label' => __( 'Other Styling Options', 'text-domain' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]
    );

    $this->add_control(
      'pricing_bg',
      [
        'label' => __( 'Pricing Table background', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#ffffff',
        'selectors' => [
            '{{WRAPPER}} .pricing__item' => 'background: {{VALUE}};',
        ],
      ]
    );
    $this->add_group_control(
      \Elementor\Group_Control_Border::get_type(),
      [
        'name' => 'border',
        'selector' => '{{WRAPPER}} .pricing__item',
      ]
    );
    $this->end_controls_section();
  }
  
  protected function render() {
    $settings           = $this->get_settings_for_display();
    $package_recommended           = $settings['package_recommended'];
    $package_subtitle           = $settings['package_subtitle'];
    $package_name           = $settings['package_name'];
    // price
    $package_currency           = $settings['package_currency'];
    $package_period           = $settings['package_period'];
    $package_price           = $settings['package_price'];
    // img
    $package_image_status           = $settings['package_image_status'];
    $package_image           = $settings['package_image'];
    $package_image_style           = $settings['package_image_style'];
    // button
    $btn_style           = $settings['btn_style'];
    $button_text           = $settings['button_text'];
    $button_url           = $settings['button_url'];

    $price_bubble_bg_svg = '<svg width="87" height="98" viewBox="0 0 87 98" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M58.4408 -32.4016L96.1635 -25.8422L122.747 1.47392L128.037 39.1131L110.012 72.6984L75.5591 89.4014L37.8364 82.8421L11.2532 55.5259L5.96338 17.8867L23.9874 -15.6986L58.4408 -32.4016Z" fill="#F9DBD9"/></svg>';

    ?>


    <div class="themeslr-pricing-table" id="<?php echo 'themeslr_pricing--'.uniqid(); ?>">
        <div class="pricing pricing--pema">
            <div class="pricing__item <?php echo esc_attr($package_recommended); ?>">
                <div class="pricing__title">
                    <span><?php echo esc_html($package_name); ?></span>
                </div>
                <div class="pricing__period">
                    <?php echo $price_bubble_bg_svg; ?>
                    <div class="pricing__bubble_content">
                        <strong><?php echo esc_attr($package_currency . $package_price); ?></strong>
                        <br /><?php echo esc_html($package_period); ?>
                    </div>
                </div>
                <p class="pricing__sentence"><?php echo esc_html($package_subtitle); ?></p>

                <?php if ($package_image) : ?>
                    <div class="themeslr-pricing-image--holder text-center">
                        <img class="<?php echo $package_image_style; ?>" src="<?php echo esc_url(wp_get_attachment_url($package_image['id'])); ?>" alt="<?php echo esc_attr($package_name); ?>" />
                    </div>
                <?php endif; ?>

                <ul class="pricing__feature-list">
                    <?php if ( ! empty( $settings['features'] ) ) { ?>
                      <?php foreach ( $settings['features'] as $item ) { ?>
                          <li class="pricing__feature"><?php echo esc_html($item['name']); ?></li>
                      <?php } ?>
                    <?php } ?>
                </ul>

                <div class="themeslr-pricing-button--holder themeslr_button_shortcode text-center <?php echo $btn_style; ?>">
                  <a class="button" 
                     href="<?php echo esc_url($button_url); ?>">
                     <?php echo esc_html($button_text); ?>
                  </a>
                </div>

            </div>
        </div>
    </div>
    <?php
  }
  protected function content_template() {

  }
}