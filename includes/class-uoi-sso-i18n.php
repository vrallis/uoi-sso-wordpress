<?php

/**
 * Define the internationalization functionality.
 */
class Uoi_Sso_I18n {

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'uoi-sso',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
