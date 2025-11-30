<?php
namespace Elementor;

class Themeslr_Heading_Title_Subtitle_Widget extends Widget_Base {
  
  public function get_name() {
    return 'heading_title_subtitle';
  }
  
  public function get_title() {
    return esc_html__('ThemeSLR - Heading with Title and Subtitle', 'themeslr');
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
      'title_light',
        [
          'label' => esc_attr__( "Section title (Light font-weight)", "themeslr" ),
          'type' => Controls_Manager::TEXTAREA,
          'default' => 'Get Box',
        ]
    );
    $this->add_control(
      'title_subtitle_alignment',
      [
        'label' => esc_html__( 'Title / Subtitle Alignment', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => 'text-center',
        'options' => [
          'text-left'     => 'Left',
          'text-center'     => 'Center',
          'text-right'     => 'Right',
        ]
      ]
    );
    $this->add_control(
      'title_color',
      [
        'label' => esc_html__( 'Title Color', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => 'dark_title',
        'options' => [
          'light_title'     => 'Light Style',
          'dark_title'     => 'Dark Style',
        ]
      ]
    );
    $this->add_control(
    'line_break',
      [
        'label' => esc_html__( 'Line Break?', 'mt-addons' ),
        'description' => esc_html__( 'If checked, a line break will be added between the light and the bold texts.', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'label_on' => esc_html__( 'Yes', 'mt-addons' ),
        'label_off' => esc_html__( 'No', 'mt-addons' ),
        'return_value' => 'yes',
        'default' => 'no',
      ]
    );
    $this->add_control(
      'title_bold',
        [
          'label' => esc_attr__( "Section title (Bold font-weight)", "themeslr" ),
          'type' => Controls_Manager::TEXTAREA,
          'default' => 'Products Separately',
        ]
    );
    $this->add_control(
      'title_font_size',
      [
        'label' => esc_html__( 'Title Font Size', 'mt-addons' ),
        'description' => esc_html__( '(Example: 50) For the default size, leave this empty', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'default' => '40',
      ]
    );
    $this->add_control(
      'subtitle',
        [
          'label' => esc_attr__( "Section subtitle", "themeslr" ),
          'type' => Controls_Manager::TEXTAREA,
          'default' => 'Subscribe to our month crates',
        ]
    );

    $this->add_control(
      'subtitle_color',
      [
        'label' => esc_html__( 'Subtitle Color', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => 'dark_subtitle',
        'options' => [
          'light_subtitle'     => 'Light Style',
          'dark_subtitle'     => 'Dark Style',
        ]
      ]
    );
    $this->add_control(
      'subtitle_font_size',
      [
        'label' => esc_html__( 'Subitle Font Size', 'mt-addons' ),
        'description' => esc_html__( '(Example: 18) For the default size, leave this empty', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'default' => '18',
      ]
    );
    $this->add_control(
    'subtitle_fullwidth',
      [
        'label' => esc_html__( 'Subtitle Full-width?', 'mt-addons' ),
        'description' => esc_html__( 'If checked, the subtitle will override the default option (70% width) and will become full-width.', 'mt-addons' ),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'label_on' => esc_html__( 'Yes', 'mt-addons' ),
        'label_off' => esc_html__( 'No', 'mt-addons' ),
        'return_value' => 'yes',
        'default' => 'no',
      ]
    );

    $this->add_control(
      'space_top',
      [
        'label' => esc_html__( 'Space Top', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => 'No Space',
        'options' => [
          'No Space' => 'No Space',
          '5' => '5', 
          '10' => '10', 
          '15' => '15', 
          '20' => '20', 
          '25' => '25', 
          '30' => '30', 
          '40' => '40', 
          '50' => '50', 
          '60' => '60', 
          '70' => '70', 
          '80' => '80', 
          '90' => '90', 
          '100' => '100', 
          '110' => '110', 
          '120' => '120', 
          '150' => '150'
        ]
      ]
    );
    $this->add_control(
      'space_bottom',
      [
        'label' => esc_html__( 'Space Bottom', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => 'No Space',
        'options' => [
          'No Space' => 'No Space',
          '5' => '5', 
          '10' => '10', 
          '15' => '15', 
          '20' => '20', 
          '25' => '25', 
          '30' => '30', 
          '40' => '40', 
          '50' => '50', 
          '60' => '60', 
          '70' => '70', 
          '80' => '80', 
          '90' => '90', 
          '100' => '100', 
          '110' => '110', 
          '120' => '120', 
          '150' => '150'
        ]
      ]
    );
    $this->end_controls_section();
  
  }
  
  protected function render() {
    $settings           = $this->get_settings_for_display();

    $title_light           = $settings['title_light'];
    $line_break           = $settings['line_break'];
    $title_bold           = $settings['title_bold'];
    $title_font_size           = $settings['title_font_size'];
    $subtitle           = $settings['subtitle'];
    $subtitle_font_size           = $settings['subtitle_font_size'];
    $subtitle_fullwidth           = $settings['subtitle_fullwidth'];
    $title_subtitle_alignment           = $settings['title_subtitle_alignment'];
    $title_color           = $settings['title_color'];
    $subtitle_color           = $settings['subtitle_color'];
    $space_top           = $settings['space_top'];
    $space_bottom           = $settings['space_bottom'];


    $shortcode_content = '[heading_title_subtitle space_bottom="'.esc_attr($space_bottom).'"  space_top="'.esc_attr($space_top).'" subtitle_color="'.esc_attr($subtitle_color).'" title_color="'.esc_attr($title_color).'" title_subtitle_alignment="'.esc_attr($title_subtitle_alignment).'" subtitle_fullwidth="'.esc_attr($subtitle_fullwidth).'" subtitle_font_size="'.esc_attr($subtitle_font_size).'"  subtitle="'.esc_attr($subtitle).'" title_font_size="'.esc_attr($title_font_size).'" title_light="'.esc_attr($title_light).'" title_bold="'.esc_attr($title_bold).'" line_break="'.esc_attr($line_break).'" ]';

    echo do_shortcode($shortcode_content);
  }
  protected function content_template() {

  }
}