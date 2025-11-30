<?php
namespace Elementor;

class Themeslr_Isolated_Element_Widget extends Widget_Base {
 
  public function get_name() {
    return 'tslr-isolated-element';
  }
  
  public function get_title() {
    return esc_html__('ThemeSLR - Isolated Element', 'themeslr');
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
    'attach_image',
        [
            'label' => esc_html__( 'Upload Image', 'themeslr' ),
            'type' => Controls_Manager::MEDIA,
        ]
    );
    $this->add_control(
    'left_percent',
        [
            'label' => esc_html__('Left (%)', 'themeslr'),
            'type' => Controls_Manager::NUMBER,
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'default' => 15,
        ]
    );
    $this->add_control(
    'top_percent',
        [
            'label' => esc_html__('Top (%)', 'themeslr'),
            'type' => Controls_Manager::NUMBER,
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'default' => 15,
        ]
    );
    $this->add_control(
    'width',
        [
            'label' => esc_html__('Width (px)', 'themeslr'),
            'type' => Controls_Manager::NUMBER,
            'default' => 100,
        ]
    );
    $this->end_controls_section();
  }
  
  protected function render() {
    $settings           = $this->get_settings_for_display();

    $attach_image           = $settings['attach_image'];
    $left_percent           = $settings['left_percent'];
    $top_percent           = $settings['top_percent'];
    $width           = $settings['width'];

    $image_attributes = wp_get_attachment_image_src( $attach_image['id'], 'full' );
    if ($image_attributes) {
        echo '<img class="absolute" width="'.$width.'" height="auto" style="left: '.$left_percent.'%; top: '.$top_percent.'%;" src="'.$image_attributes[0].'" alt="absolute element" />';
    }
  }
  protected function content_template() {

  }
}