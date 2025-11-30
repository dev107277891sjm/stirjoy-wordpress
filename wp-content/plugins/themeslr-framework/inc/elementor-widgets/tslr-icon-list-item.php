<?php
namespace Elementor;

class Themeslr_List_Items_With_Icon_Widget extends Widget_Base {
   
 
  public function get_name() {
    return 'icon-list-item';
  }
  
  public function get_title() {
    return esc_html__('ThemeSLR - List Items with Icons', 'themeslr');
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
          'label' => esc_html__( 'General Options', 'themeslr' ),
      ]
    );

    $repeater = new \Elementor\Repeater(); 
    $repeater->add_control(
      'title',
      [
        'label'         => esc_html__( 'Text (Label)', 'themeslr' ),
        'label_block'       => true,
        'type'          => Controls_Manager::TEXT,
        'default'         => 'New Products Added Monthly',
      ]
    );
    $repeater->add_control( 
        'icon_fontawesome',
        [
          'label'       => esc_html__( 'Icon', 'themeslr' ),
          'type'        => \Elementor\Controls_Manager::ICONS,
          'default'       => [
              'value'       => 'fas fa-star',
              'library'       => 'solid',
          ]
        ]
    );
    $repeater->add_control(
      'url',
      [
        'label'         => esc_html__( 'Link', 'themeslr' ),
        'label_block'       => true,
        'type'          => Controls_Manager::TEXT,
      ]
    );
    $this->add_control(
      'full_list',
      [
        'label'         => esc_html__( 'Items List', 'themeslr' ),
        'type'          => \Elementor\Controls_Manager::REPEATER,
        'fields'        => $repeater->get_controls(),
        'default'         => [ 
          [ 
            'title'     => 'Full Satisfaction Guarantee',
            'icon_fontawesome' => 'fas fa-mug-hot',
            'url' => '',
          ],
          [ 
            'title'     => 'World-Class Selected Coffees',
            'icon_fontawesome' => 'fas fa-mug-hot',
            'url' => '',
          ],
          [ 
            'title'     => 'Get Your Monthly Coffee Crates Now',
            'icon_fontawesome' => 'fas fa-mug-hot',
            'url' => '',
          ],
        ],
        'title_field' => '{{{ title }}}',
      ]
    );


    $this->add_control(
        'alignment',
        [
            'label' => esc_html__('Alignment', 'themeslr'),
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'left' => [
                    'title' => esc_html__('Left', 'themeslr'),
                    'icon' => 'eicon-text-align-left',
                ],
                'center' => [
                    'title' => esc_html__('Center', 'themeslr'),
                    'icon' => 'eicon-text-align-center',
                ],
                'right' => [
                    'title' => esc_html__('Right', 'themeslr'),
                    'icon' => 'eicon-text-align-right',
                ],
            ],
            'default' => 'left',
            'toggle' => true,
            'selectors' => [
                '{{WRAPPER}} .tslr-icon-list-items-holder' => 'text-align: {{VALUE}};',
            ],
        ]
    );
    $this->add_control(
      'list_icon_title_size',
      [
        'label' => esc_html__( 'Text Font size', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'default' => '18',
        'selectors' => [
            '{{WRAPPER}} .tslr-icon-list-text' => 'font-size: {{VALUE}}px;',
        ],
      ]
    );
    $this->add_control(
      'list_icon_title_line_height',
      [
        'label' => esc_html__( 'Text Line Height', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'default' => '18',
        'selectors' => [
            '{{WRAPPER}} .tslr-icon-list-text' => 'line-height: {{VALUE}}px;',
        ],
      ]
    );
    $this->add_control(
      'list_icon_title_color',
      [
        'label' => esc_html__( 'Text Color', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#000',
        'selectors' => [
            '{{WRAPPER}} .tslr-icon-list-text' => 'color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
      'list_icon_size',
      [
        'label' => esc_html__( 'Icon Size (px)', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'default' => '18',
        'selectors' => [
            '{{WRAPPER}} .tslr-icon-list-icon-holder-inner i' => 'font-size: {{VALUE}}px;',
        ],
      ]
    );
    $this->add_control(
      'list_icon_color',
      [
        'label' => esc_html__( 'Icon Color', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::COLOR,
        'default' => '#000',
        'selectors' => [
            '{{WRAPPER}} .tslr-icon-list-icon-holder-inner i' => 'color: {{VALUE}};',
        ],
      ]
    );
    $this->add_control(
        'padding',
        [
            'label' => esc_html__( 'Margin', 'themeslr' ),
            'type'  => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => [
                '{{WRAPPER}} .tslr-icon-list-icon-holder-inner i' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );
    $this->add_control(
      'target',
      [
        'label'         => esc_html__( 'Open Link in a new tab?', 'themeslr' ),
        'type'          => \Elementor\Controls_Manager::SWITCHER,
        'label_on'        => esc_html__( 'Yes', 'themeslr' ),
        'label_off'       => esc_html__( 'No', 'themeslr' ),
        'return_value'      => 'yes',
        'default'         => 'no',
      ]
    );
    $this->add_control(
      'icon_position',
      [
        'label'         => esc_html__( 'Icon Position', 'themeslr' ),
        'type'          => \Elementor\Controls_Manager::SELECT,
        'options' => [
            'left' => esc_html__('Left', 'themeslr'),
            'right' => esc_html__('Right', 'themeslr'),
        ],
        'default'         => 'left',
      ]
    );


    $this->end_controls_section();
  
  }
  
  protected function render() {
    $settings           = $this->get_settings_for_display();
    $full_list           = $settings['full_list'];
    $target           = $settings['target'];
    $alignment =   ((array_key_exists('alignment', $settings))?$settings['alignment']:"left");
    $icon_position           = $settings['icon_position'];
    if ($target == 'yes') {
      $target = 'target="_blank"';
    }else{
      $target = '';
    }

    if ($alignment) {
      $block_alignment = 'tslr-block-'.$alignment;
    }
    ?>

    <?php if($full_list){ ?>
      <div class="tslr-icon-list-items-holder <?php echo esc_attr($block_alignment); ?>">
        <?php foreach ($full_list as $list) { ?>
          <div class="tslr-icon-list-item">
              <?php if (!empty($list['url'])){ ?>
                  <a <?php echo $target; ?> href="<?php echo esc_url($list['url']); ?>">
              <?php } ?>

                <?php if($icon_position == 'left'){ ?>
                <div class="tslr-icon-list-icon-holder">
                    <div class="tslr-icon-list-icon-holder-inner clearfix">
                        <i class="<?php echo esc_attr($list['icon_fontawesome']['value']); ?>"></i>
                    </div>
                </div>
                <?php } ?>

                <div class="tslr-icon-list-text"><?php echo esc_html($list['title']); ?></div>

                <?php if($icon_position == 'right'){ ?>
                  <div class="tslr-icon-list-icon-holder">
                      <div class="tslr-icon-list-icon-holder-inner clearfix">
                          <i class="<?php echo esc_attr($list['icon_fontawesome']['value']); ?>"></i>
                      </div>
                  </div>
                <?php } ?>

              <?php if (!empty($list['url'])){ ?>
                  </a>
              <?php } ?>
          </div>
        <?php } ?>
      </div>
    <?php }
  }
  protected function content_template() {

  }
}