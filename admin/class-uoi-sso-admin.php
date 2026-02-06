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
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		require_once UOI_SSO_PLUGIN_DIR . 'admin/partials/uoi-sso-admin-display.php';
	}

	public function register_settings() {
		register_setting( $this->plugin_name, 'uoi_sso_cas_url', array(
			'type'              => 'string',
			'sanitize_callback' => array( $this, 'sanitize_cas_url' ),
			'default'           => 'sso.uoi.gr',
		) );
		register_setting( $this->plugin_name, 'uoi_sso_cas_port', array(
			'type'              => 'integer',
			'sanitize_callback' => array( $this, 'sanitize_cas_port' ),
			'default'           => 443,
		) );
		register_setting( $this->plugin_name, 'uoi_sso_cas_context', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '/cas',
		) );

		// Attribute Mapping Settings
		register_setting( $this->plugin_name, 'uoi_sso_attr_firstname', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'givenName',
		) );
		register_setting( $this->plugin_name, 'uoi_sso_attr_lastname', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'sn',
		) );
		register_setting( $this->plugin_name, 'uoi_sso_attr_email', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'mail',
		) );
		register_setting( $this->plugin_name, 'uoi_sso_attr_role', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'eduPersonAffiliation',
		) );
		register_setting( $this->plugin_name, 'uoi_sso_role_mapping', array(
			'type'              => 'string',
			'sanitize_callback' => array( $this, 'sanitize_role_mapping' ),
			'default'           => "student:subscriber\nteacher:editor\nstaff:author",
		) );
	}

	/**
	 * Sanitize CAS URL: strip protocol, slashes, and dangerous characters.
	 */
	public function sanitize_cas_url( $value ) {
		$value = sanitize_text_field( $value );
		// Remove any protocol prefix
		$value = preg_replace( '#^https?://#', '', $value );
		// Remove trailing slashes
		$value = rtrim( $value, '/' );
		return $value;
	}

	/**
	 * Sanitize CAS port: must be an integer between 1 and 65535.
	 */
	public function sanitize_cas_port( $value ) {
		$port = absint( $value );
		if ( $port < 1 || $port > 65535 ) {
			add_settings_error(
				'uoi_sso_cas_port',
				'invalid_port',
				__( 'CAS port must be between 1 and 65535. Reverted to 443.', 'uoi-sso' ),
				'error'
			);
			return 443;
		}
		return $port;
	}

	/**
	 * Sanitize role mapping: validate each line matches "value:role" format
	 * and that the WordPress role actually exists.
	 */
	public function sanitize_role_mapping( $value ) {
		$value = sanitize_textarea_field( $value );
		$lines = explode( "\n", $value );
		$clean_lines = array();

		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( empty( $line ) ) {
				continue;
			}
			$parts = explode( ':', $line, 2 );
			if ( count( $parts ) !== 2 ) {
				continue; // Skip malformed lines
			}
			$cas_val = sanitize_text_field( trim( $parts[0] ) );
			$wp_role = sanitize_text_field( trim( $parts[1] ) );

			// Verify the WordPress role exists
			if ( ! empty( $cas_val ) && ! empty( $wp_role ) && wp_roles()->is_role( $wp_role ) ) {
				$clean_lines[] = $cas_val . ':' . $wp_role;
			}
		}

		return implode( "\n", $clean_lines );
	}
}
