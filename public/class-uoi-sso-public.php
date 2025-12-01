<?php

/**
 * The public-facing functionality of the plugin.
 */
class Uoi_Sso_Public {

	private $plugin_name;
	private $version;
	private $auth_provider;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		// Initialize the provider (Currently CAS, but could be dynamic later)
		$this->auth_provider = new Uoi_Sso_Cas_Provider();
	}

	public function display_sso_button() {
		$login_url = $this->auth_provider->get_login_url( wp_login_url() );
		include plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/uoi-sso-public-display.php';
	}

	public function sso_button_shortcode() {
		ob_start();
		$this->display_sso_button();
		return ob_get_clean();
	}

	public function handle_sso_response() {
		if ( is_user_logged_in() ) {
			return;
		}

		// Check if this is a callback from the provider
		if ( $this->auth_provider->is_callback() ) {
			
			$user = $this->auth_provider->authenticate();

			if ( is_wp_error( $user ) ) {
				wp_die( $user->get_error_message(), 'SSO Error' );
			}

			if ( $user instanceof WP_User ) {
				wp_set_current_user( $user->ID, $user->user_login );
				wp_set_auth_cookie( $user->ID );
				do_action( 'wp_login', $user->user_login, $user );
				
				// Redirect to home or dashboard
				wp_safe_redirect( home_url() );
				exit;
			}
		}
	}
}
