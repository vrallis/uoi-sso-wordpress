<?php
/**
 * Plugin Name:       UOI SSO
 * Plugin URI:        https://dit.uoi.gr
 * Description:       Single Sign-On for University of Ioannina using CAS. Contact: vrallis@orailab.gr, contact@bill.gr
 * Version:           1.0.4
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Vasileios Rallis
 * Author URI:        https://bill.gr
 * License:           GPL v2 or later
 * Text Domain:       uoi-sso
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'UOI_SSO_VERSION', '1.0.4' );

/**
 * Plugin filesystem path (with trailing slash).
 */
define( 'UOI_SSO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin URL (with trailing slash).
 */
define( 'UOI_SSO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin main file path.
 */
define( 'UOI_SSO_PLUGIN_FILE', __FILE__ );

/**
 * The code that runs during plugin activation.
 */
function activate_uoi_sso() {
	require_once UOI_SSO_PLUGIN_DIR . 'includes/class-uoi-sso-activator.php';
	Uoi_Sso_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_uoi_sso() {
	require_once UOI_SSO_PLUGIN_DIR . 'includes/class-uoi-sso-deactivator.php';
	Uoi_Sso_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_uoi_sso' );
register_deactivation_hook( __FILE__, 'deactivate_uoi_sso' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require UOI_SSO_PLUGIN_DIR . 'includes/class-uoi-sso.php';

/**
 * Begins execution of the plugin.
 */
function run_uoi_sso() {
	$plugin = new Uoi_Sso();
	$plugin->run();
}
run_uoi_sso();
