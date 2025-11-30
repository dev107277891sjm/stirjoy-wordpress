<?php
/**
 * The admin settings page specific functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src/admin
 */
namespace Themehigh\WoocommerceMultipleAddressesPro\admin;

use \Themehigh\WoocommerceMultipleAddressesPro\includes\utils\THWMA_Utils;
use \Themehigh\WoocommerceMultipleAddressesPro\includes\THWMA_i18n;

if(!defined('WPINC')) {
	die;
}

if(!class_exists('THWMA_Admin_Settings')) :
	/**
     * The Admin settings class.
     */
	abstract class THWMA_Admin_Settings {
		protected $page_id = '';
		protected $section_id = '';
		protected $tabs = '';
		protected $sections = '';

		/**
         * Constructor.
         *
         * @param string $page The page id
         * @param string $section The secdtion info
         */
        public  function __construct($page, $section = '') {
			THWMA_Admin_Utils::prepare_sections_and_fields();
			$this->page_id = $page;
			if(is_plugin_active('woocommerce-checkout-field-editor-pro/woocommerce-checkout-field-editor-pro.php')) {
				$this->tabs = array(
				'general_settings' => esc_html__('General Settings', 'woocommerce-multiple-addresses-pro'),
				'advanced_settings' => esc_html__('Advanced Settings', 'woocommerce-multiple-addresses-pro'),
				'custom_section_settings' =>esc_html__('Custom Section Settings', 'woocommerce-multiple-addresses-pro'),
				'license_settings' => esc_html__('Plugin License', 'woocommerce-multiple-addresses-pro')
				);
			} else {
				$this->tabs = array(
				'general_settings' => esc_html__('General Settings', 'woocommerce-multiple-addresses-pro'),
				'advanced_settings' => esc_html__('Advanced Settings', 'woocommerce-multiple-addresses-pro'),
				'license_settings' => esc_html__('Plugin License', 'woocommerce-multiple-addresses-pro')
				);
			}
			$sections_data = get_option(THWMA_Utils::OPTION_KEY_CUSTOM_SECTIONS);
			$sections_values = array();
			if(!empty($sections_data) && is_array($sections_data)) {
				foreach($sections_data as $values) {
					$section_id = $values['id'];
					$title = $values['title'];
					$sections_values[$section_id] = $title;
				}
			}
			$this->sections = $sections_values;
		}

		/**
         * Function for get tabs.
         *
         * @return array
         */
        public  function get_tabs() {
			return $this->tabs;
		}

		/**
         * Function for gwt current tab.
         *
         * @return string
         */
        public  function get_current_tab() {
			return $this->page_id;
		}

		/**
         * Function for sget sections
         *
         * @return array
         */
        public  function get_sections() {
			return $this->sections;
		}

		/**
         * Function for get current section
         *
         * @return string
         */
        public  function get_current_section() {
			return isset($_GET['section']) ? esc_attr($_GET['section']) : $this->section_id;
		}

		/**
         * Function for set current section.
         *
         * @param string $section_id The section id
         */
        public  static function set_current_section($section_id) {
			if($section_id) {
				self::$section_id = $section_id;
			}
		}

		/**
         * Function for set the first section active (open the plugin menu).
         */
        public  static function set_first_section_as_current() {
			$sections = THWMA_Admin_Utils::get_sections();
			if($sections && is_array($sections)) {
				$array_keys = array_keys($sections);
				if($array_keys && is_array($array_keys) && isset($array_keys[0])) {
					self::set_current_section($array_keys[0]);
				}
			}
		}

		/**
         * Function for set render tabs.
         *
         * @return void
         */
        public  function render_tabs() {
			$current_tab = $this->get_current_tab();
			$tabs = $this->get_tabs();
			if(empty($tabs)) {
				return;
			}
			echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';
			if(!empty($tabs) && is_array($tabs)) {
				foreach($tabs as $id => $label) {
					$active = ($current_tab == $id) ? 'nav-tab-active' : '';
					$label = THWMA_i18n::t($label);
					echo '<a class="nav-tab '.esc_attr($active).'" href="'. esc_url_raw($this->get_admin_url($id)) .'">'.$label.'</a>';
				}
			}
			echo '</h2>';
		}

		/**
         * The render section function.
         *
         * @return void
         */
        public  function render_sections() {
			$current_section = $this->get_current_section();
			$sections = $this->get_sections();
			if(empty($sections)) {
				return;
			}
			$array_keys = array_keys($sections);
			$section_html = '';
			if(!empty($sections) && is_array($sections)) {
				foreach($sections as $id => $label) {
					$label = THWMA_i18n::t($label);
					$url   = $this->get_admin_url($this->page_id, sanitize_title($id));
					$section_html .= '<li><a href="'. esc_url_raw($url) .'" class="'.($current_section == $id ? 'current' : '').'">'.$label.'</a> '.(end($array_keys) == $id ? '' : '|').' </li>';
				}
			}
			if($section_html) {
				echo '<ul class="thpladmin-sections">';
				echo $section_html;
				echo '</ul>';
			}
		}

		/**
         * Function for get admin url.
         *
         * @param string $tab The tab name
         * @param string $section The section name
         *
         * @return string
         */
        public  function get_admin_url($tab = false, $section = false) {
			$url = 'admin.php?&page=th_multiple_addresses_pro';
			if($tab && !empty($tab)) {
				$url .= '&tab='. $tab;
			}
			if($section && !empty($section)) {
				$url .= '&section='. $section;
			}
			return admin_url($url);
		}

		/**
         * The render form field elements.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         * @param array $render_cell The cell
         */
    public  function render_form_field_element($field, $atts = array(), $render_cell = true) {
			if($field && is_array($field)) {
				$args = shortcode_atts(array(
					'label_cell_props' => '',
					'input_cell_props' => '',
					'label_cell_colspan' => '',
					'input_cell_colspan' => '',
				), $atts);
				$ftype     = isset($field['type']) ? $field['type'] : 'text';
				$flabel    = isset($field['label']) && !empty($field['label']) ? THWMA_i18n::t($field['label']) : '';
				$sub_label = isset($field['sub_label']) && !empty($field['sub_label']) ? THWMA_i18n::t($field['sub_label']) : '';
				$tooltip   = isset($field['hint_text']) && !empty($field['hint_text']) ? THWMA_i18n::t($field['hint_text']) : '';
				$field_html = '';
				if($ftype == 'text') {
					$field_html = $this->render_form_field_element_inputtext($field, $atts);
				} else if($ftype == 'number') {
					$field_html = $this->render_form_field_element_inputnumber($field, $atts);
				} else if($ftype == 'textarea') {
					$field_html = $this->render_form_field_element_textarea($field, $atts);
				} else if($ftype == 'select') {
					$field_html = $this->render_form_field_element_select($field, $atts);
				} else if($ftype == 'multiselect') {
					$field_html = $this->render_form_field_element_multiselect($field, $atts);
				} else if($ftype == 'colorpicker') {
					$field_html = $this->render_form_field_element_colorpicker($field, $atts);
	            } else if($ftype == 'checkbox') {
					$field_html = $this->render_form_field_element_checkbox($field, $atts, $render_cell);
					$flabel 	= '&nbsp;';
				} else if($ftype == 'time') {
					$field_html = $this->render_form_field_element_inputtime($field, $atts);
				} else if($ftype == 'hidden') {
					$field_html = $this->render_form_field_element_inputhidden($field, $atts);
				}else if($ftype == 'select_woo'){
					$field_html = $this->render_form_field_element_select_woo($field, $atts);
				}
				if($render_cell) {
					$required_html = isset($field['required']) && $field['required'] ? '<abbr class="required" title="required">*</abbr>' : '';
					$label_cell_props = !empty($args['label_cell_props']) ? $args['label_cell_props'] : '';
					$input_cell_props = !empty($args['input_cell_props']) ? $args['input_cell_props'] : '';
					if($flabel) { ?>
						<td <?php if($label_cell_props) {echo $label_cell_props;} ?> >
							<?php echo $flabel; echo $required_html;
							if($sub_label) { ?>
								<br/><span class="thpladmin-subtitle"><?php echo $sub_label; ?></span>
							<?php } ?>
						</td>
						<?php }
						$this->render_form_fragment_tooltip($tooltip); ?>
						<td <?php if($field_html) { echo $input_cell_props;} ?> ><?php echo $field_html; ?></td>
					<?php } else {
						echo $field_html;
				}
			}
		}

		/**
         * Function for prepare form field props.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return array
         */
		private function prepare_form_field_props($field, $atts = array()) {
			$field_props = '';
			$args = shortcode_atts(array(
				'input_width' => '',
				'input_name_prefix' => 'i_',
				'input_name_suffix' => '',
			), $atts);
			$ftype = isset($field['type']) ? $field['type'] : 'text';
			if($ftype == 'multiselect') {
				$args['input_name_suffix'] = $args['input_name_suffix'].'[]';
			}
			$fname  = $args['input_name_prefix'].$field['name'].$args['input_name_suffix'];
			$fvalue = isset($field['value']) ? $field['value'] : '';
			$fvalue = htmlspecialchars($fvalue);
			$input_width  = $args['input_width'] ? 'width:'.$args['input_width'].';' : '';
			$fid=isset($field['id']) ? $field['id'] : '';
			$frequired=isset($field['required']) ? 'required' : '';
			$field_props  = 'name="'. $fname .'" value="'. $fvalue .'" style="'. $input_width .'"id="'.$fid.'"'.$frequired.' ';
			$field_props .= (isset($field['placeholder']) && !empty($field['placeholder'])) ? ' placeholder="'.$field['placeholder'].'"' : '';
			$field_props .= (isset($field['onchange']) && !empty($field['onchange'])) ? ' onchange="'.$field['onchange'].'"' : '';
			if($ftype == 'number') {
				$fmin=isset($field['min']) ? $field['min'] : '';
				$fmax=isset($field['max']) ? $field['max'] : '';
				$field_props .= 'min="'. $fmin .'"max="'.$fmax.'"';
			}
			return $field_props;
		}

		/**
         * Function for set render form field elements (Advanced).
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         * @param array $render_cell The render cells
         *
         * @return array
         */
        public  function render_form_field_element_advanced($field, $atts=array(), $render_cell=true) {
			if($field && is_array($field)) {
				$ftype = isset($field['type']) ? $field['type'] : 'text';
				if($ftype == 'checkbox') {
					$this->render_form_field_element_checkbox($field, $atts, $render_cell);
					return true;
				}
				$args = shortcode_atts(array(
					'label_cell_props' => '',
					'input_cell_props' => '',
					'label_cell_th' => false,
					'input_width' => '',
					'input_float' => '',
					'rows' => '5',
					'cols' => '100',
					'input_name_prefix' => 'i_'
				), $atts);
				$fname  = $args['input_name_prefix'].$field['name'];
				$flabel = THWMA_i18n::t($field['label']);
				$fvalue = isset($field['value']) ? $field['value'] : '';
				if($ftype == 'multiselect' && is_array($fvalue)) {
					$fvalue = !empty($fvalue) ? implode(',', $fvalue) : $fvalue;
				}
				if($ftype == 'select_woo' && is_array($fvalue)) {
					$fvalue = !empty($fvalue) ? implode(',', $fvalue) : $fvalue;
				}
				$input_width  = $args['input_width'] ? 'width:'.$args['input_width'].';' : '';
				$field_props  = 'name="'. $fname .'" value="'. $fvalue .'" style="'. $input_width .'"';
				$field_props .= (isset($field['placeholder']) && !empty($field['placeholder'])) ? ' placeholder="'.$field['placeholder'].'"' : '';
				$required_html = (isset($field['required']) && $field['required']) ? '<abbr class="required" title="required">*</abbr>' : '';

				if(isset($field['onchange']) && !empty($field['onchange'])) {
					$field_props .= ' onchange="'.$field['onchange'].'"';
				}
				if($ftype == 'multiselect' || $ftype == 'select_woo') {
					$field_props  = 'name="'. $fname .'[]" data-value="'. $fvalue .'" style="'. $input_width .'"';
					$field_props .= (isset($field['placeholder']) && !empty($field['placeholder'])) ? ' placeholder="'.$field['placeholder'].'"' : '';
				}
				return $field_props;

			}
		}

		/**
         * Function for prepare input text field.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return array
         */
		private function render_form_field_element_inputtext($field, $atts = array()) {
			$field_html = '';
			if($field && is_array($field)) {
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<input type="text" '. $field_props .' />';
			}
			return $field_html;
		}

		/**
         * Function for prepare input date field.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return array
         */
		private function render_form_field_element_inputdate($field, $atts = array()) {
			$field_html = '';
			if($field && is_array($field)) {
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<input type="text" '. $field_props .' />';
			}
			return $field_html;
		}

		/**
         * Function for prepare input time field.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return array
         */
		private function render_form_field_element_inputtime($field, $atts = array()) {
			$field_html = '';
			if($field && is_array($field)) {
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<input type="time" '. $field_props .' />';
			}
			return $field_html;
		}

		/**
         * Function for prepare input number field.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return array
         */
		private function render_form_field_element_inputnumber($field, $atts = array()) {
			$field_html = '';
			if($field && is_array($field)) {
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<input type="number" '. $field_props .' />';
			}
			return $field_html;
		}

		/**
         * Function for prepare textarea field.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return array
         */
		private function render_form_field_element_textarea($field, $atts = array()) {
			$field_html = '';
			if($field && is_array($field)) {
				$args = shortcode_atts(array(
					'rows' => '5',
					'cols' => '100',
				), $atts);
				$fvalue = isset($field['value']) ? $field['value'] : '';
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<textarea '. $field_props .' rows="'.$args['rows'].'" cols="'.$args['cols'].'" >'.$fvalue.'</textarea>';
			}
			return $field_html;
		}

		/**
         * Function for prepare select field.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return array
         */
		private function render_form_field_element_select($field, $atts = array()) {
			$field_html = '';
			if($field && is_array($field)) {
				$fvalue = isset($field['value']) ? $field['value'] : '';
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<select '. $field_props .' >';
				if(!empty($field['options']) && is_array($field['options'])) {
					foreach($field['options'] as $value => $label) {
						$selected = $value === $fvalue ? 'selected' : '';
						$field_html .= '<option value="'. trim($value) .'" '.$selected.'>'. THWMA_i18n::t($label) .'</option>';
					}
				}
				$field_html .= '</select>';
			}
			return $field_html;
		}

		/**
         * Function for prepare select2 field using woocommerce selectWoo.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return array
         */
		private function render_form_field_element_select_woo($field, $atts=array()) {
			$field_html = '';
			if($field && is_array($field)) {
				$fvalue = isset($field['value']) ? $field['value'] : '';

				$field_props = $this->render_form_field_element_advanced($field, $atts);
				$field_class = isset($field['class']) ? $field['class'] : '';
				$field_html .= '<select '. $field_props .' class="'.$field_class.'" multiple="multiple" >';
				if(!empty($fvalue) && is_array($fvalue)) {
					foreach($fvalue as $key => $value) {
						$product = get_page_by_path( $value, OBJECT, 'product' );
						$field_html .= '<option value="'. trim($value).'" selected>'. THWMA_i18n::t( get_the_title( $product )) .'</option>';
					}
				}
				$field_html .= '</select>';
			}

			return $field_html;
		}

		/**
         * Function for prepare multiselect field.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return array
         */
		private function render_form_field_element_multiselect($field, $atts = array()) {
			$field_html = '';
			if($field && is_array($field)) {
				$fvalue = isset($field['value']) ? $field['value'] : '';
				//$field_props = $this->prepare_form_field_props($field, $atts);
				$field_props = $this->render_form_field_element_advanced($field, $atts);
				$field_html = '<select multiple="multiselect" '. $field_props .' class="thpladmin-enhanced-multi-select" >';
				if(!empty($field['options']) && is_array($field['options'])) {
					foreach($field['options'] as $value => $label) {
						$field_html .= '<option value="'. trim($value) .'" >'. THWMA_i18n::t($label) .'</option>';
					}
				}
				$field_html .= '</select>';
			}
			return $field_html;
		}

		/**
         * Function for prepareinput radio field.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return array
         */
		private function render_form_field_element_radio($field, $atts = array()) {
			$field_html = '';
			if($field && is_array($field)) {
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<select '. $field_props .' >';
				if(!empty($field['options']) && is_array($field['options'])) {
					foreach($field['options'] as $value => $label) {
						$selected = $value === $fvalue ? 'selected' : '';
						$field_html .= '<option value="'. trim($value) .'" '.$selected.'>'. THWMA_i18n::t($label) .'</option>';
					}
				}
				$field_html .= '</select>';
			}
			return $field_html;
		}

		// private function render_form_field_element_checkbox($field, $atts = array(), $render_cell = true) {
		// 	$field_html = '';
		// 	if($field && is_array($field)) {
		// 		$args = shortcode_atts(array(
		// 			'label_props' => '',
		// 			'cell_props'  => 3,
		// 			'render_input_cell' => false,
		// 		), $atts);

		// 		$fid 	= 'a_f'. $field['name'];
		// 		$flabel = isset($field['label']) && !empty($field['label']) ? THWMA_i18n::t($field['label']) : '';
		// 		$field_props  = $this->prepare_form_field_props($field, $atts);
		// 		$field_props .= $field['checked'] ? ' checked' : '';

		// 		$field_html  = '<input type="checkbox" id="'. $fid .'" '. $field_props .' />';
		// 		$field_html .= '<label for="'. $fid .'" '. $args['label_props'] .' > '. $flabel .'</label>';
		// 	}
		// 	if(!$render_cell && $args['render_input_cell']) {
		// 		return  $args['cell_props'] .' >'. $field_html ;
		// 	} else {
		// 		return $field_html;
		// 	}
		// }

		/**
         * Function for prepare checkbox field.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         * @param array $render_cell The cell style
         *
         * @return string
         */
    public  function render_form_field_element_checkbox($field, $atts=array(), $render_cell=false) {
			$args = shortcode_atts(array('cell_props'  => '', 'input_props' => '', 'label_props' => '', 'name_prefix' => 'i_', 'id_prefix' => 'a_f'), $atts);
			$fid    = $args['id_prefix'].$field['name'];
			$fname  = $args['name_prefix'].$field['name'];
			$fvalue = isset($field['value']) ? $field['value'] : '';
			$flabel = THWMA_i18n::t($field['label']);

			$field_props  = 'id="'. $fid .'" name="'. $fname .'"';
			$field_props .= !empty($fvalue) ? ' value="'. $fvalue .'"' : '';
			$field_props .= $field['checked'] ? ' checked' : '';
			$field_props .= $args['input_props'];
			$field_props .= isset($field['onchange']) && !empty($field['onchange']) ? ' onchange="'.$field['onchange'].'"' : '';

			$field_html  = '<input type="checkbox" '. $field_props .' />';
			$field_html .= '<label for="'. $fid .'" '. $args['label_props'] .' > '. esc_html__($flabel, 'woocommerce-multiple-addresses-pro') .'</label>';

			if($render_cell) { ?>
				<td <?php echo $args['cell_props']; ?> ><?php echo $field_html; ?></td>
			<?php } else { ?>
				<?php echo $field_html; ?>
			<?php }
		}

		/**
         * Function for prepare color-picker field.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return string
         */
		private function render_form_field_element_colorpicker($field, $atts = array()) {
			$field_html = '';
			if($field && is_array($field)) {
				$field_props = $this->prepare_form_field_props($field, $atts);

				$field_html  = '<span class="thpladmin-colorpickpreview '.$field['name'].'_preview" style=""></span>';
        $field_html .= '<input type="text" '. $field_props .' class="thpladmin-colorpick"/>';
			}
			return $field_html;
		}

		/**
         * Function for prepare input hidden field.
         *
         * @param array $field The field datas
         * @param array $atts The attributes data
         *
         * @return string
         */
		public  function render_form_field_element_inputhidden($field, $atts = array()) {
			$field_html = '';
			if($field && is_array($field)) {
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<input type="hidden" '. $field_props .' />';
			}
			return $field_html;
		}

		/**
         * Function for set tooltip fragment.
         *
         * @param string $tooltip The tooltip text
         */
        public  function render_form_fragment_tooltip($tooltip = false) {
			if($tooltip) { ?>
				<td style="width: 26px; padding:0px;" class="thwma-tooltip">
					<a href="javascript:void(0)" title="<?php echo $tooltip; ?>" class="thpladmin_tooltip"><img src="<?php echo THWMA_ASSETS_URL_ADMIN; ?>/img/help.png" title=""/></a>
				</td>
			<?php } else { ?>
				<td style="width: 26px; padding:0px;" class="thwma-tooltip"></td>
			<?php }
		}

		/**
         * Function for set heading seperator fragments.
         *
         * @param array $atts The attributes
         */
        public  function render_form_fragment_h_separator($atts = array()) {
			$args = shortcode_atts(array(
				'colspan' 	   => 6,
				'padding-top'  => '5px',
				'border-style' => 'dashed',
	    		'border-width' => '1px',
				'border-color' => '#e6e6e6',
				'content'	   => '',
			), $atts);

			$style  = $args['padding-top'] ? 'padding-top:'.$args['padding-top'].';' : '';
			$style .= $args['border-style'] ? ' border-bottom:'.$args['border-width'].' '.$args['border-style'].' '.$args['border-color'].';' : ''; ?>
	        <tr><td colspan="<?php echo $args['colspan']; ?>" style="<?php echo $style; ?>"><?php echo $args['content']; ?></td></tr>
	    <?php }

		/*private function output_h_separator($show_line = true) {
			$style = $show_line ? 'margin: 5px 0; border-bottom: 1px dashed #ccc' : '';
			echo '<tr><td colspan="6" style="'.$style.'">&nbsp;</td></tr>';
		}*/

		/**
         * Function for set field spacing fragments.
         *
         * @param int $padding The padding details
         */
        public  function render_field_form_fragment_h_spacing($padding = 5) {
			$style = $padding ? 'padding-top:'.$padding.'px;' : ''; ?>
	        <tr><td colspan="6" style="<?php echo $style ?>"></td></tr>
	    <?php }

		/**
         * Function for set blank.
         *
         * @param int $colspan The colspan data
         */
        public  function render_form_field_blank($colspan = 3) { ?>
	        <td colspan="<?php echo $colspan; ?>">&nbsp;</td>
	    <?php }

		/**
         * Function for set render form section separator.
         *
         * @param array $props The style details
         * @param array $atts The attribute details
         *
         * @return string
         */
        public  function render_form_section_separator($props, $atts=array()) { ?>
			<tr valign="top"><td colspan="<?php echo $props['colspan']; ?>" style="height:10px;"></td></tr>
			<tr valign="top"><td colspan="<?php echo $props['colspan']; ?>" class="thpladmin-form-section-title" >
				<?php echo $props['title']; ?></td></tr>
			<tr valign="top"><td colspan="<?php echo $props['colspan']; ?>" style="height:0px;"></td></tr>
		<?php }
	}
endif;
