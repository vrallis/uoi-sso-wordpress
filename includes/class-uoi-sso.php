<?php

/**
 * The core plugin class.
 */
class Uoi_Sso {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		if ( defined( 'UOI_SSO_VERSION' ) ) {
			$this->version = UOI_SSO_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'uoi-sso';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( __FILE__ ) . 'class-uoi-sso-loader.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-uoi-sso-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-uoi-sso-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-uoi-sso-public.php';
		
		// Auth Providers
		require_once plugin_dir_path( __FILE__ ) . 'interface-uoi-sso-provider.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-uoi-sso-cas-provider.php';

		$this->loader = new Uoi_Sso_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new Uoi_Sso_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	private function define_admin_hooks() {
		$plugin_admin = new Uoi_Sso_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
	}

	private function define_public_hooks() {
		$plugin_public = new Uoi_Sso_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'login_enqueue_scripts', $plugin_public, 'enqueue_login_assets' );
		$this->loader->add_action( 'login_form', $plugin_public, 'display_sso_button' );
		$this->loader->add_action( 'init', $plugin_public, 'handle_sso_response' );
		$this->loader->add_action( 'wp_logout', $plugin_public, 'handle_sso_logout' );
		$this->loader->add_shortcode( 'uoi_sso_button', $plugin_public, 'sso_button_shortcode' );
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
