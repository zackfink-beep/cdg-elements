<?php
/**
 * Fired during plugin deactivation.
 */
class CDG_Elements_Deactivator {

	public static function deactivate() {
		// Clear any scheduled hooks if needed
		wp_clear_scheduled_hook('cdg_elements_daily_cleanup');
		
		// Clear rewrite rules
		flush_rewrite_rules();
	}
}