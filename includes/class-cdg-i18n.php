<?php
/**
 * Define the internationalization functionality.
 */
class CDG_Elements_i18n {

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'cdg-elements',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}