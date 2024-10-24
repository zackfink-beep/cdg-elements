<?php
/**
 * The public-facing functionality of the plugin.
 */
class CDG_Elements_Frontend {
	private $plugin_name;
	private $version;
	private $elements;

	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->elements = array();
	}

	/**
	 * Initialize the class
	 */
	public function initialize() {
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('wp_footer', array($this, 'render_elements'));
		add_filter('body_class', array($this, 'add_body_class'));
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles() {
		if (!$this->should_load_resources()) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'css/public.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts() {
		if (!$this->should_load_resources()) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'js/public.js',
			array('jquery'),
			$this->version,
			true
		);
	}

	/**
	 * Add body class when elements are present
	 */
	public function add_body_class($classes) {
		if ($this->should_load_resources()) {
			$classes[] = 'has-cdg-elements';
		}
		return $classes;
	}

	/**
	 * Check if elements should be loaded
	 */
	private function should_load_resources() {
		if (!is_singular()) {
			return false;
		}

		$post_id = get_the_ID();
		$enabled = get_post_meta($post_id, '_cdg_elements_enabled', true);
		
		if ($enabled !== '1') {
			return false;
		}

		// Check if there are any elements for this post
		global $wpdb;
		$table_name = $wpdb->prefix . 'cdg_elements';
		
		$count = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND is_active = 1",
			$post_id
		));

		return $count > 0;
	}

	/**
	 * Load elements for current page
	 */
	private function load_elements() {
		if (!empty($this->elements)) {
			return;
		}

		global $wpdb;
		$post_id = get_the_ID();
		$table_name = $wpdb->prefix . 'cdg_elements';

		$this->elements = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $table_name 
			WHERE post_id = %d AND is_active = 1 
			ORDER BY id ASC",
			$post_id
		));

		// Load fonts
		$this->load_custom_fonts();
	}

	/**
	 * Load custom fonts for elements
	 */
	private function load_custom_fonts() {
		$loaded_fonts = array();

		foreach ($this->elements as $element) {
			if (!empty($element->font_url) && !in_array($element->font_url, $loaded_fonts)) {
				wp_enqueue_style(
					'cdg-font-' . md5($element->font_url),
					$element->font_url,
					array(),
					null
				);
				$loaded_fonts[] = $element->font_url;
			}
		}
	}

	/**
	 * Render elements on the page
	 */
	public function render_elements() {
		if (!$this->should_load_resources()) {
			return;
		}

		$this->load_elements();

		if (empty($this->elements)) {
			return;
		}

		echo '<div id="cdg-elements-container" class="cdg-elements-container" aria-hidden="true">';
		foreach ($this->elements as $element) {
			$this->render_single_element($element);
		}
		echo '</div>';
	}

	/**
	 * Render a single element
	 */
	private function render_single_element($element) {
		$element_id = 'cdg-element-' . esc_attr($element->id);
		$classes = array('cdg-element');
		$styles = $this->get_element_styles($element);
		?>
		<div id="<?php echo $element_id; ?>"
			 class="<?php echo esc_attr(implode(' ', $classes)); ?>"
			 <?php echo $this->build_style_attribute($styles); ?>>
			<?php echo esc_html($element->element_text); ?>
		</div>
		<?php
	}

	/**
	 * Get element styles
	 */
	private function get_element_styles($element) {
		return array(
			'left' => $element->position_x . 'px',
			'top' => $element->position_y . 'px',
			'font-family' => $element->font_family,
			'color' => $element->color,
			'font-size' => $this->get_size_value($element->size),
			'transform' => 'rotate(' . intval($element->rotation) . 'deg)',
			'filter' => $this->get_blur_filter($element->size)
		);
	}

	/**
	 * Build style attribute
	 */
	private function build_style_attribute($styles) {
		$style_string = '';
		foreach ($styles as $property => $value) {
			if (!empty($value)) {
				$style_string .= $property . ': ' . $value . '; ';
			}
		}
		return 'style="' . esc_attr(trim($style_string)) . '"';
	}

	/**
	 * Get size value in pixels
	 */
	private function get_size_value($size) {
		$sizes = array(
			'x-small' => '12px',
			'small' => '16px',
			'medium' => '24px',
			'large' => '36px'
		);
		return isset($sizes[$size]) ? $sizes[$size] : $sizes['medium'];
	}

	/**
	 * Get blur filter value
	 */
	private function get_blur_filter($size) {
		$blur_values = array(
			'x-small' => 3,
			'small' => 2,
			'medium' => 1,
			'large' => 0
		);
		$blur = isset($blur_values[$size]) ? $blur_values[$size] : 0;
		return $blur > 0 ? "blur({$blur}px)" : 'none';
	}
}
