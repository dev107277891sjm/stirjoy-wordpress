<?php
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}

if ( class_exists('Elementor\Core\Admin\Admin') ) {

	class ThemeSLR_Elementor_Widgets {

		protected static $instance = null;

		public static function get_instance() {
			if ( ! isset( static::$instance ) ) {
				static::$instance = new static;
			}

			return static::$instance;
		}

		protected function __construct() {
			require_once( 'tslr-button.php' );
			require_once( 'tslr-blogpost01.php' );
			require_once( 'tslr-title-subtitle.php' );
			require_once( 'tslr-clients.php' );
			require_once( 'tslr-testimonials.php' );
			require_once( 'tslr-isolated-element.php' );
			require_once( 'tslr-icon-list-item.php' );
			require_once( 'tslr-pricing-tables.php' );
			
			add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
		}

		public function register_widgets() {
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\Themeslr_Button_Widget() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\Themeslr_Blogpost01_Widget() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\Themeslr_Heading_Title_Subtitle_Widget() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\Themeslr_Clients_Widget() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\Themeslr_Testimonials_Widget() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\Themeslr_Isolated_Element_Widget() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\Themeslr_List_Items_With_Icon_Widget() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\Themeslr_Pricing_Table_Widget() );
		}

	}

	add_action( 'init', 'themeslr_elementor_init' );
	function themeslr_elementor_init() {
		ThemeSLR_Elementor_Widgets::get_instance();
	}

	function themeslr_elementor_widget_categories( $elements_manager ) {

		$elements_manager->add_category(
			'themeslr-widgets',
			[
				'title' => __( 'ThemeSLR Widgets', 'themeslr' ),
				'icon' => 'eicon-gallery-grid',
			]
		);

	}
	add_action( 'elementor/elements/categories_registered', 'themeslr_elementor_widget_categories' );

}