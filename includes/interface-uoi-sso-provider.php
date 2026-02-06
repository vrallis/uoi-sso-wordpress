<?php

/**
 * Interface for Auth Providers (CAS, SAML, etc.)
 */
interface Uoi_Sso_Provider_Interface {
	
	/**
	 * Get the URL to redirect the user to for login.
	 *
	 * @param string $service_url The URL to redirect back to after successful login.
	 * @return string
	 */
	public function get_login_url( $service_url );

	/**
	 * Authenticate the user based on the provider's response.
	 *
	 * @return WP_User|WP_Error|null User object on success, Error on failure, null if no auth attempt.
	 */
	public function authenticate();
	
	/**
	 * Check if the current request is a callback from the provider.
	 * 
	 * @return bool
	 */
	public function is_callback();

	/**
	 * Get the URL to log the user out of the SSO provider.
	 *
	 * @param string $service_url The URL to redirect back to after logout.
	 * @return string
	 */
	public function get_logout_url( $service_url );
}
