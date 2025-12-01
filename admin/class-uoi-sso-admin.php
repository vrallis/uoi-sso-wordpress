<?php

/**
 * The admin-specific functionality of the plugin.
 */
class Uoi_Sso_Admin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function add_plugin_admin_menu() {
		add_options_page(
			'UOI SSO Settings', 
			'UOI SSO', 
			'manage_options', 
			$this->plugin_name, 
			array( $this, 'display_plugin_setup_page' )
		);
	}

	public function display_plugin_setup_page() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/uoi-sso-admin-display.php';
	}

	public function register_settings() {
		register_setting( $this->plugin_name, 'uoi_sso_cas_url' );
		register_setting( $this->plugin_name, 'uoi_sso_cas_port' );
		register_setting( $this->plugin_name, 'uoi_sso_cas_context' );
		
		// Attribute Mapping Settings
		register_setting( $this->plugin_name, 'uoi_sso_attr_firstname' );
		register_setting( $this->plugin_name, 'uoi_sso_attr_lastname' );
		register_setting( $this->plugin_name, 'uoi_sso_attr_email' );
		register_setting( $this->plugin_name, 'uoi_sso_attr_role' );
		register_setting( $this->plugin_name, 'uoi_sso_role_mapping' );
	}
}
