<?php

/**
 * CAS Auth Provider
 */
class Uoi_Sso_Cas_Provider implements Uoi_Sso_Provider_Interface {

	private $cas_url;
	private $cas_port;
	private $cas_context;

	/**
	 * Log a message to the WordPress debug log.
	 *
	 * @param string $message Log message.
	 * @param string $level   'error', 'warning', or 'info'.
	 */
	private static function log( $message, $level = 'error' ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( '[UOI SSO] [%s] %s', strtoupper( $level ), $message ) );
		}
	}

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

	public function get_logout_url( $service_url ) {
		$base = "https://{$this->cas_url}:{$this->cas_port}{$this->cas_context}";
		return $base . '/logout?service=' . urlencode( $service_url );
	}

	public function authenticate() {
		if ( ! $this->is_callback() ) {
			return null;
		}

		$ticket = sanitize_text_field( $_GET['ticket'] );

		$ticket_hash = 'uoi_sso_ticket_' . hash( 'sha256', $ticket );
		if ( false !== get_transient( $ticket_hash ) ) {
			self::log( 'Ticket replay attempt detected.' );
			return new WP_Error( 'cas_ticket_replayed', __( 'This SSO ticket has already been used.', 'uoi-sso' ) );
		}
		set_transient( $ticket_hash, 1, 5 * MINUTE_IN_SECONDS );

		$service_url = $this->get_current_url_without_ticket();

		$validation_url = "https://{$this->cas_url}:{$this->cas_port}{$this->cas_context}/serviceValidate?service=" . urlencode( $service_url ) . "&ticket=" . $ticket;

		$response = wp_remote_get( $validation_url, array(
			'timeout'   => 15,
			'sslverify' => true,
		) );

		if ( is_wp_error( $response ) ) {
			self::log( 'CAS validation HTTP error: ' . $response->get_error_message() );
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code !== 200 ) {
			self::log( sprintf( 'CAS server returned HTTP %d.', $status_code ) );
			return new WP_Error(
				'cas_http_error',
				sprintf(
					__( 'CAS server returned HTTP %d.', 'uoi-sso' ),
					$status_code
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			self::log( 'Empty response body from CAS server.' );
			return new WP_Error( 'cas_empty_response', __( 'Empty response from CAS server.', 'uoi-sso' ) );
		}

		return $this->parse_cas_response( $body );
	}

	/**
	 * Parse a CAS 2.0 XML serviceValidate response using DOMDocument.
	 *
	 * @param string $body Raw XML response body.
	 * @return WP_User|WP_Error
	 */
	private function parse_cas_response( $body ) {
		// Disable external entity loading to prevent XXE attacks
		$previous_value = libxml_disable_entity_loader( true );
		$internal_errors = libxml_use_internal_errors( true );

		$doc = new DOMDocument();
		$loaded = $doc->loadXML( $body, LIBXML_NONET | LIBXML_NOENT );

		// Restore previous libxml settings
		libxml_disable_entity_loader( $previous_value );
		libxml_clear_errors();
		libxml_use_internal_errors( $internal_errors );

		if ( ! $loaded ) {
			self::log( 'Failed to parse CAS XML response.' );
			return new WP_Error( 'cas_xml_error', __( 'Failed to parse CAS response XML.', 'uoi-sso' ) );
		}

		$xpath = new DOMXPath( $doc );
		$xpath->registerNamespace( 'cas', 'http://www.yale.edu/tp/cas' );

		// Check for authentication failure
		$failure_nodes = $xpath->query( '//cas:authenticationFailure' );
		if ( $failure_nodes->length > 0 ) {
			$code = $failure_nodes->item( 0 )->getAttribute( 'code' );
			self::log( sprintf( 'CAS authentication failure, code: %s', $code ) );
			return new WP_Error(
				'cas_auth_failed',
				/* translators: %s: CAS failure code */
				sprintf( __( 'CAS Authentication failed: %s', 'uoi-sso' ), sanitize_text_field( $code ) )
			);
		}

		// Check for authentication success
		$success_nodes = $xpath->query( '//cas:authenticationSuccess' );
		if ( $success_nodes->length === 0 ) {
			self::log( 'CAS response contained neither success nor failure element.' );
			return new WP_Error( 'cas_auth_failed', __( 'CAS Authentication failed: unexpected response.', 'uoi-sso' ) );
		}

		// Extract username
		$user_nodes = $xpath->query( '//cas:authenticationSuccess/cas:user' );
		if ( $user_nodes->length === 0 || empty( trim( $user_nodes->item( 0 )->textContent ) ) ) {
			self::log( 'CAS response did not contain a username.' );
			return new WP_Error( 'cas_no_user', __( 'CAS response did not contain a username.', 'uoi-sso' ) );
		}

		$username = sanitize_user( trim( $user_nodes->item( 0 )->textContent ) );
		self::log( sprintf( 'CAS authentication successful for user: %s', $username ), 'info' );

		// Extract attributes
		$attributes = array();
		$attr_nodes = $xpath->query( '//cas:authenticationSuccess/cas:attributes/*' );
		if ( $attr_nodes->length > 0 ) {
			foreach ( $attr_nodes as $node ) {
				// Use local name to strip the namespace prefix
				$key = $node->localName;
				$value = sanitize_text_field( $node->textContent );
				$attributes[ $key ] = $value;
			}
		}

		return $this->get_or_create_user( $username, $attributes );
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

		if ( $user ) {
			// User found by username — this is the authoritative match for SSO
			self::log( sprintf( 'Existing user found: %s (ID: %d)', $username, $user->ID ), 'info' );
			$userdata = array(
				'ID'         => $user->ID,
				'first_name' => $first_name,
				'last_name'  => $last_name,
			);

			// Only update role if it's not an admin
			if ( ! in_array( 'administrator', $user->roles ) ) {
				$userdata['role'] = $role;
			}

			// Only update email if no other user owns it
			if ( is_email( $email ) && $email !== $user->user_email ) {
				$email_owner = get_user_by( 'email', $email );
				if ( ! $email_owner || $email_owner->ID === $user->ID ) {
					$userdata['user_email'] = $email;
				}
			}

			wp_update_user( $userdata );
			return $user;
		}

		if ( is_email( $email ) ) {
			$email_owner = get_user_by( 'email', $email );
			if ( $email_owner ) {
				// Email belongs to a different WP user
				self::log( sprintf( 'Email conflict: CAS user %s has email %s which belongs to WP user ID %d.', $username, $email, $email_owner->ID ) );
				return new WP_Error(
					'cas_email_conflict',
					sprintf(
						__( 'The email address %2$s is already associated with a different account. Please contact the administrator. (CAS user: %1$s)', 'uoi-sso' ),
						$username,
						$email
					)
				);
			}
		}

		// Auto-provisioning: create new user
		$userdata = array(
			'user_login' => $username,
			'user_pass'  => wp_generate_password(),
			'user_email' => $email,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'role'       => $role,
		);

		$user_id = wp_insert_user( $userdata );

		if ( is_wp_error( $user_id ) ) {
			self::log( sprintf( 'Failed to create user %s: %s', $username, $user_id->get_error_message() ) );
			return $user_id;
		}

		self::log( sprintf( 'Auto-provisioned new user: %s (ID: %d)', $username, $user_id ), 'info' );
		return get_user_by( 'id', $user_id );
	}
}
