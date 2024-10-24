<?php
/**
 * The admin-specific functionality of the plugin.
 */
class CDG_Elements_Admin {
	private $plugin_name;
	private $version;
	private $ajax_handler;

	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		// Initialize AJAX handler
		require_once plugin_dir_path(__FILE__) . 'class-cdg-admin-ajax.php';
		$this->ajax_handler = new CDG_Elements_Admin_Ajax();

		// Register AJAX handlers
		add_action('wp_ajax_cdg_create_element', array($this->ajax_handler, 'create_element'));
		add_action('wp_ajax_cdg_update_element', array($this->ajax_handler, 'update_element'));
		add_action('wp_ajax_cdg_delete_element', array($this->ajax_handler, 'delete_element'));
		add_action('wp_ajax_cdg_get_elements', array($this->ajax_handler, 'get_elements'));
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		if (!$this->is_plugin_page()) {
			return;
		}

		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'css/admin-style.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		if (!$this->is_plugin_page()) {
			return;
		}

		wp_enqueue_script('wp-color-picker');

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'js/elements-manager.js',
			array('jquery', 'wp-color-picker'),
			$this->version,
			false
		);

		wp_localize_script($this->plugin_name, 'cdgElements', array(
			'ajaxurl' => admin_url('ajax.php'),
			'nonce' => wp_create_nonce('cdg_elements_nonce'),
			'postId' => get_the_ID(),
			'debug' => true
		));
	}

	/**
	 * Add menu item
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			'CDG Elements',
			'CDG Elements',
			'manage_options',
			$this->plugin_name,
			array($this, 'display_plugin_admin_page'),
			'dashicons-editor-textcolor',
			65
		);
	}

	/**
	 * Display the admin page
	 */
	public function display_plugin_admin_page() {
		include_once('views/elements-page.php');
	}

	/**
	 * Check if current page is plugin page
	 */
	private function is_plugin_page() {
		if (!function_exists('get_current_screen')) {
			return false;
		}

		$screen = get_current_screen();
		return is_object($screen) && strpos($screen->id, $this->plugin_name) !== false;
	}
}
