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

		/**
		 * Filter the SSO auth provider instance.
		 *
		 * Allows developers to replace the default CAS provider with a custom
		 * implementation (e.g., SAML) by returning an object that implements
		 * Uoi_Sso_Provider_Interface.
		 *
		 * @param Uoi_Sso_Provider_Interface $provider The auth provider instance.
		 */
		$this->auth_provider = apply_filters( 'uoi_sso_auth_provider', new Uoi_Sso_Cas_Provider() );
	}

	/**
	 * Enqueue public-facing styles and scripts on the login page.
	 */
	public function enqueue_login_assets() {
		wp_enqueue_style(
			$this->plugin_name,
			UOI_SSO_PLUGIN_URL . 'public/css/uoi-sso-public.css',
			array(),
			$this->version
		);
		wp_enqueue_script(
			$this->plugin_name,
			UOI_SSO_PLUGIN_URL . 'public/js/uoi-sso-public.js',
			array(),
			$this->version,
			true
		);
	}

	public function display_sso_button() {
		// Generate a CSRF state token and store it in a transient (10 min expiry)
		$state = wp_generate_password( 32, false );

		// Capture redirect_to
		$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( $_REQUEST['redirect_to'] ) : '';
		set_transient( 'uoi_sso_state_' . $state, $redirect_to, 10 * MINUTE_IN_SECONDS );

		$service_url = add_query_arg( 'uoi_sso_state', $state, wp_login_url() );
		$login_url = $this->auth_provider->get_login_url( $service_url );
		include UOI_SSO_PLUGIN_DIR . 'public/partials/uoi-sso-public-display.php';
	}

	public function sso_button_shortcode() {
		ob_start();
		$this->display_sso_button();
		return ob_get_clean();
	}

	/**
	 * Handle WordPress logout by also logging out of the CAS server.
	 * Redirects to the CAS logout endpoint so the SSO session is terminated.
	 */
	public function handle_sso_logout() {
		$redirect_url = wp_login_url();
		$logout_url = $this->auth_provider->get_logout_url( $redirect_url );

		wp_redirect( $logout_url );
		exit;
	}

	public function handle_sso_response() {
		if ( is_user_logged_in() ) {
			return;
		}

		// Check if this is a callback from the provider
		if ( $this->auth_provider->is_callback() ) {

			// Verify CSRF state token before processing the ticket
			$state = isset( $_GET['uoi_sso_state'] ) ? sanitize_text_field( $_GET['uoi_sso_state'] ) : '';
			$state_value = get_transient( 'uoi_sso_state_' . $state );
			if ( empty( $state ) || false === $state_value ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( '[UOI SSO] [ERROR] Invalid or expired SSO state token on callback.' );
				}
				wp_die(
					__( 'Invalid or expired SSO state. Please try logging in again.', 'uoi-sso' ),
					__( 'SSO Error', 'uoi-sso' ),
					array( 'response' => 403, 'back_link' => true )
				);
			}

			// Retrieve redirect_to from state and delete the transient (single use)
			$redirect_to = ! empty( $state_value ) ? esc_url_raw( $state_value ) : '';
			delete_transient( 'uoi_sso_state_' . $state );

			$user = $this->auth_provider->authenticate();

			if ( is_wp_error( $user ) ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( '[UOI SSO] [ERROR] SSO authentication failed: ' . $user->get_error_message() );
				}
				wp_die(
					__( 'Authentication failed. Please try again or contact the administrator.', 'uoi-sso' ),
					__( 'SSO Error', 'uoi-sso' ),
					array( 'response' => 403, 'back_link' => true )
				);
			}

			if ( $user instanceof WP_User ) {
				wp_set_current_user( $user->ID, $user->user_login );
				wp_set_auth_cookie( $user->ID );
				do_action( 'wp_login', $user->user_login, $user );

				// Redirect to the original destination, or fall back to home
				$redirect_url = ! empty( $redirect_to ) ? $redirect_to : home_url();
				wp_safe_redirect( $redirect_url );
				exit;
			}
		}
	}
}
