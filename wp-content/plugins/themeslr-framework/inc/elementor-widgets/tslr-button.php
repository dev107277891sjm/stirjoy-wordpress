<?php
namespace Elementor;

class Themeslr_Button_Widget extends Widget_Base {
  
  public function get_name() {
    return 'tslr_button';
  }
  
  public function get_title() {
    return esc_html__('ThemeSLR - Button', 'themeslr');
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
          'label' => esc_html__( 'Content', 'themeslr' ),
      ]
    );  

    $this->add_control(
      'btn_text',
      [
        'label' => esc_html__( 'Button text', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => 'JOIN US NOW',
      ]
    );
    $this->add_control(
      'btn_url',
      [
        'label' => esc_html__( 'Link', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => '#',
      ]
    );
    $this->add_control(
      'btn_size',
      [
        'label' => esc_html__( 'Size', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => 'btn btn-medium',
        'options' => array(
          'btn btn-sm' => __('Small', 'themeslr'),
          'btn btn-medium' => __('Medium', 'themeslr'),
          'btn btn-lg' => __('Large', 'themeslr'),
          'extra-large' => __('Extra-Large', 'themeslr')
        ),
      ]
    );
    $this->add_control(
      'btn_style',
      [
        'label' => esc_html__( 'Style', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => 'btn-square',
        'options' => array(
          'btn-square' => __('Square (Default)', 'themeslr'),
          'btn-rounded' => __('Rounded (5px Radius)', 'themeslr'),
          'btn-round' => __('Round (30px Radius)', 'themeslr'),
        ),
      ]
    );
    $this->add_control(
      'align',
      [
        'label' => esc_html__( 'Alignment', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => 'text-left',
        'options' => array(
          'text-left' => __('Left', 'themeslr'),
          'text-center' => __('Center', 'themeslr'),
          'text-right' => __('Right', 'themeslr')
        ),
      ]
    );
    $this->add_control(
      'display_type',
      [
        'label'        => __( 'Inline?', 'text-domain' ),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __( 'On', 'text-domain' ),
        'label_off'    => __( 'Off', 'text-domain' ),
        'return_value' => 'yes',
        'default'      => 'no',
      ]
    );
    $this->add_control(
      'background_color',
      [
        'label' => __( 'Background Color', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#f26226',
        'selectors' => [
            '{{WRAPPER}} .themeslr_button .themeslr-primary-btn' => 'background: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'background_color_hover',
      [
        'label' => __( 'Background Color: Hover', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#000000',
        'selectors' => [
            '{{WRAPPER}} .themeslr_button .themeslr-primary-btn:hover' => 'background: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'text_color',
      [
        'label' => __( 'Text Color', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#ffffff',
        'selectors' => [
            '{{WRAPPER}} .themeslr_button .themeslr-primary-btn' => 'color: {{VALUE}};',
            '{{WRAPPER}} .themeslr_button .themeslr-primary-btn i' => 'color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'text_color_hover',
      [
        'label' => __( 'Text Color: Hover', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#ffffff',
        'selectors' => [
            '{{WRAPPER}} .themeslr_button .themeslr-primary-btn:hover' => 'color: {{VALUE}};',
            '{{WRAPPER}} .themeslr_button .themeslr-primary-btn:hover i' => 'color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'box_shadow_status',
      [
        'label'        => __( 'Box Shadow', 'text-domain' ),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __( 'On', 'text-domain' ),
        'label_off'    => __( 'Off', 'text-domain' ),
        'return_value' => 'yes',
        'default'      => 'no',
      ]
    );
    $this->add_control(
      'box_shadow',
      [
        'label'     => __( 'Box Shadow', 'text-domain' ),
        'type'      => Controls_Manager::BOX_SHADOW,
        'default'   => [
          'horizontal' => 2,
          'vertical'   => 4,
          'blur'       => 8,
          'spread'     => 0,
          'color'      => 'rgba(0, 0, 0, 0.15)',
        ],
        'selectors' => [
            '{{WRAPPER}} .themeslr_button .themeslr-primary-btn' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}};',
        ],
        'condition' => [
            'box_shadow_status' => 'yes', // Show only if switcher is 'yes'
        ],
      ]
    );
    $this->add_control(
      'icon_status',
      [
        'label'        => __( 'Button Icon', 'text-domain' ),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __( 'On', 'text-domain' ),
        'label_off'    => __( 'Off', 'text-domain' ),
        'return_value' => 'yes',
        'default'      => 'no',
      ]
    );
    $this->add_control(
      'icon',
      [
        'label'   => __( 'Icon', 'text-domain' ),
        'type'    => Controls_Manager::ICONS,
        'default' => [
            'value'   => 'fas fa-star',
            'library' => 'fa-solid',
        ],
        'condition' => [
            'icon_status' => 'yes', // Show only if switcher is 'yes'
        ]
      ]
    );
    $this->add_control(
      'icon_placement',
      [
        'label' => esc_html__( 'Icon Placement', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => 'before',
        'options' => array(
          'before' => __('Before Text', 'themeslr'),
          'after' => __('After Text', 'themeslr'),
        ),
        'condition' => [
            'icon_status' => 'yes', // Show only if switcher is 'yes'
        ],
      ]
    );

    $this->end_controls_section();
  }
  
  protected function render() {
    $settings           = $this->get_settings_for_display();

    $btn_text           = $settings['btn_text'];
    $btn_url           = $settings['btn_url'];
    $btn_size           = $settings['btn_size'];
    $btn_style           = $settings['btn_style'];
    $align           = $settings['align'];
    $display_type           = $settings['display_type'];
    $icon           = $settings['icon'];
    $icon_status           = $settings['icon_status'];
    $icon_placement           = $settings['icon_placement'];

    if ($display_type) {
      $display_type = 'inline-block';
    }



    ?>


    <div class="<?php echo esc_attr($align); ?> <?php echo esc_attr($btn_style); ?>  <?php echo esc_attr($display_type); ?> themeslr_button themeslr_button_shortcode">
      <a href="<?php echo esc_url($btn_url); ?>" class="themeslr-primary-btn button-winona <?php echo esc_attr($btn_size); ?>">
        <?php if($icon_placement == 'before' && $icon_status == 'yes'){ ?>
          <span><i class="<?php echo esc_attr($icon['value']); ?>"></i></span>
        <?php } ?>
        <?php echo esc_html($btn_text); ?>
        <?php if($icon_placement == 'after' && $icon_status == 'yes'){ ?>
          <span><i class="<?php echo esc_attr($icon['value']); ?>"></i></span>
        <?php } ?>
      </a>
    </div>
    <?php
  }
  protected function content_template() {

  }
}