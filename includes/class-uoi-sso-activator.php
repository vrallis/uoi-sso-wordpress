<?php

/**
 * Fired during plugin activation
 */
class Uoi_Sso_Activator {

	public static function activate() {
		// Set default options if they don't exist
		if ( false === get_option( 'uoi_sso_cas_url' ) ) {
			update_option( 'uoi_sso_cas_url', 'sso.uoi.gr' );
		}
		if ( false === get_option( 'uoi_sso_cas_port' ) ) {
			update_option( 'uoi_sso_cas_port', 443 );
		}
		if ( false === get_option( 'uoi_sso_cas_context' ) ) {
			update_option( 'uoi_sso_cas_context', '/cas' );
		}

		// Attribute Mapping Defaults
		if ( false === get_option( 'uoi_sso_attr_firstname' ) ) {
			update_option( 'uoi_sso_attr_firstname', 'givenName' );
		}
		if ( false === get_option( 'uoi_sso_attr_lastname' ) ) {
			update_option( 'uoi_sso_attr_lastname', 'sn' );
		}
		if ( false === get_option( 'uoi_sso_attr_email' ) ) {
			update_option( 'uoi_sso_attr_email', 'mail' );
		}
		if ( false === get_option( 'uoi_sso_attr_role' ) ) {
			update_option( 'uoi_sso_attr_role', 'eduPersonAffiliation' );
		}
		if ( false === get_option( 'uoi_sso_role_mapping' ) ) {
			// Default mapping: student -> subscriber
			update_option( 'uoi_sso_role_mapping', "student:subscriber\nteacher:editor\nstaff:author" );
		}
	}
}
