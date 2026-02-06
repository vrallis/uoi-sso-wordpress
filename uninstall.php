<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Cleans up all plugin options from the database.
 * This file is called by WordPress when the user deletes the plugin
 * from the admin interface.
 */

// If uninstall not called from WordPress, abort.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// Remove all plugin options
$options = array(
	'uoi_sso_cas_url',
	'uoi_sso_cas_port',
	'uoi_sso_cas_context',
	'uoi_sso_attr_firstname',
	'uoi_sso_attr_lastname',
	'uoi_sso_attr_email',
	'uoi_sso_attr_role',
	'uoi_sso_role_mapping',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Clean up any lingering transients (state tokens, ticket hashes)
global $wpdb;
$wpdb->query(
	"DELETE FROM {$wpdb->options}
	 WHERE option_name LIKE '\_transient\_uoi\_sso\_%'
	    OR option_name LIKE '\_transient\_timeout\_uoi\_sso\_%'"
);
