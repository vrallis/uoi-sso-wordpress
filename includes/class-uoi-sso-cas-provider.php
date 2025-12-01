<?php

/**
 * CAS Auth Provider
 */
class Uoi_Sso_Cas_Provider implements Uoi_Sso_Provider_Interface {

	private $cas_url;
	private $cas_port;
	private $cas_context;

	public function __construct() {
		$this->cas_url = get_option( 'uoi_sso_cas_url', 'sso.uoi.gr' );
		$this->cas_port = get_option( 'uoi_sso_cas_port', 443 );
		$this->cas_context = get_option( 'uoi_sso_cas_context', '/cas' );
	}

	public function get_login_url( $service_url ) {
		$base = "https://{$this->cas_url}:{$this->cas_port}{$this->cas_context}";
		return $base . '/login?service=' . urlencode( $service_url );
	}

	public function is_callback() {
		return isset( $_GET['ticket'] );
	}

	public function authenticate() {
		if ( ! $this->is_callback() ) {
			return null;
		}

		$ticket = sanitize_text_field( $_GET['ticket'] );
		// Clean the service URL to remove the ticket parameter for validation
		$service_url = $this->get_current_url_without_ticket();

		$validation_url = "https://{$this->cas_url}:{$this->cas_port}{$this->cas_context}/serviceValidate?service=" . urlencode( $service_url ) . "&ticket=" . $ticket;

		$response = wp_remote_get( $validation_url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return new WP_Error( 'cas_empty_response', __( 'Empty response from CAS server.', 'uoi-sso' ) );
		}

		// Simple XML parsing for CAS 2.0 response
		// Success format: <cas:serviceResponse><cas:authenticationSuccess><cas:user>username</cas:user><cas:attributes>...</cas:attributes></cas:authenticationSuccess></cas:serviceResponse>
		
		if ( strpos( $body, '<cas:authenticationSuccess>' ) !== false ) {
			preg_match( '/<cas:user>(.*?)<\/cas:user>/', $body, $matches );
			if ( ! empty( $matches[1] ) ) {
				$username = $matches[1];
				
				// Parse attributes
				$attributes = array();
				if ( preg_match( '/<cas:attributes>(.*?)<\/cas:attributes>/s', $body, $attr_matches ) ) {
					// This is a very basic XML parser for the flat list of attributes usually returned by CAS
					// It might need to be more robust for complex XML structures
					preg_match_all( '/<cas:(.*?)>(.*?)<\/cas:\1>/', $attr_matches[1], $kv_matches, PREG_SET_ORDER );
					foreach ( $kv_matches as $kv ) {
						$attributes[ $kv[1] ] = $kv[2];
					}
				}

				return $this->get_or_create_user( $username, $attributes );
			}
		}

		return new WP_Error( 'cas_auth_failed', __( 'CAS Authentication failed.', 'uoi-sso' ) );
	}

	private function get_current_url_without_ticket() {
		$url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		return remove_query_arg( 'ticket', $url );
	}

	private function get_or_create_user( $username, $attributes = array() ) {
		// Get configured attribute keys
		$attr_email = get_option( 'uoi_sso_attr_email', 'mail' );
		$attr_first = get_option( 'uoi_sso_attr_firstname', 'givenName' );
		$attr_last  = get_option( 'uoi_sso_attr_lastname', 'sn' );
		$attr_role  = get_option( 'uoi_sso_attr_role', 'eduPersonAffiliation' );

		// Extract values from attributes
		$email = isset( $attributes[ $attr_email ] ) ? $attributes[ $attr_email ] : $username . '@uoi.gr';
		$first_name = isset( $attributes[ $attr_first ] ) ? $attributes[ $attr_first ] : '';
		$last_name = isset( $attributes[ $attr_last ] ) ? $attributes[ $attr_last ] : '';
		
		// Determine Role
		$role = 'subscriber'; // Default
		$role_mapping_str = get_option( 'uoi_sso_role_mapping', '' );
		$cas_role_value = isset( $attributes[ $attr_role ] ) ? $attributes[ $attr_role ] : '';

		if ( ! empty( $role_mapping_str ) && ! empty( $cas_role_value ) ) {
			$lines = explode( "\n", $role_mapping_str );
			foreach ( $lines as $line ) {
				$parts = explode( ':', trim( $line ) );
				if ( count( $parts ) === 2 ) {
					$map_cas_val = trim( $parts[0] );
					$map_wp_role = trim( $parts[1] );
					
					// CAS value might be an array string like "[student]" or just "student"
					// Simple check: if the mapped value appears in the attribute value
					if ( stripos( $cas_role_value, $map_cas_val ) !== false ) {
						$role = $map_wp_role;
						break; // Stop at first match
					}
				}
			}
		}

		// Try to find user by login
		$user = get_user_by( 'login', $username );

		if ( ! $user ) {
			// Try to find by email
			if ( is_email( $email ) ) {
				$user = get_user_by( 'email', $email );
			}
		}

		if ( ! $user ) {
			// Auto-provisioning
			$userdata = array(
				'user_login' => $username,
				'user_pass'  => wp_generate_password(),
				'user_email' => $email,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'role'       => $role
			);

			$user_id = wp_insert_user( $userdata );

			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}

			$user = get_user_by( 'id', $user_id );
		} else {
			// Update existing user information (Sync)
			$userdata = array(
				'ID'         => $user->ID,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				// Optional: Update email if changed? Maybe safer not to.
			);
			
			// Only update role if it's not an admin (safety)
			if ( ! in_array( 'administrator', $user->roles ) ) {
				$userdata['role'] = $role;
			}

			wp_update_user( $userdata );
		}

		return $user;
	}
}
