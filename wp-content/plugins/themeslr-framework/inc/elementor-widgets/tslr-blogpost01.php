<?php
namespace Elementor;

class Themeslr_Blogpost01_Widget extends Widget_Base {
  
  public function get_name() {
    return 'blogpost01';
  }
  
  public function get_title() {
    return esc_html__('ThemeSLR - Blog Posts', 'themeslr');
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
      'number',
      [
        'label' => esc_html__( 'Number of posts to show', 'themeslr' ),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'default' => 3,
      ]
    );
    $post_category_tax = get_terms('category');
    $post_category = array();
    if ($post_category_tax) {
      $post_category[''] = esc_html__( '-Select a Category-', 'themeslr' );
      foreach ( $post_category_tax as $term ) {
        $post_category[$term->slug] = $term->name;
      }
    }
    $this->add_control(
      'category',
      [
        'label' => esc_html__( 'Blog Category (Optional)', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'options' => $post_category,
        'default' => '',
      ]
    );
    $this->add_control(
      'columns',
      [
        'label' => esc_html__( 'Posts per row', 'themeslr' ),
        'label_block' => true,
        'type' => Controls_Manager::SELECT,
        'default' => '',
        'options' => [
          '1'     => '1',
          '2'     => '2',
          '3'     => '3',
          '4'     => '4',
        ]
      ]
    );

    $this->end_controls_section();
  }
  
  protected function render() {
    $settings           = $this->get_settings_for_display();

    $number           = $settings['number'];
    $category           = $settings['category'];
    $columns           = $settings['columns'];


    $shortcode_content = '[blogpost01 number="'.esc_attr($number).'" category="'.esc_attr($category).'" columns="'.esc_attr($columns).'"]';

    echo do_shortcode($shortcode_content);
  }
  protected function content_template() {

  }
}