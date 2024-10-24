<?php
/**
 * The core plugin class.
 */
class CDG_Elements {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->version = CDG_ELEMENTS_VERSION;
		$this->plugin_name = 'cdg-elements';
		
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cdg-loader.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cdg-i18n.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-cdg-admin.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-cdg-admin-ajax.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-cdg-frontend.php';

		$this->loader = new CDG_Elements_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new CDG_Elements_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	private function define_admin_hooks() {
		// Initialize admin class
		$plugin_admin = new CDG_Elements_Admin($this->plugin_name, $this->version);
		
		// Add basic admin hooks
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
	}

	private function define_public_hooks() {
		$plugin_public = new CDG_Elements_Frontend($this->plugin_name, $this->version);
		$plugin_public->initialize();
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}
}