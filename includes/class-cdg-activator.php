<?php
/**
 * Fired during plugin activation.
 */
class CDG_Elements_Activator {

	public static function activate() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'cdg_elements';

		error_log('CDG Elements - Starting activation');

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			post_id bigint(20) NOT NULL,
			element_text text NOT NULL,
			font_family varchar(100) NOT NULL,
			font_url text DEFAULT NULL,
			color varchar(20) NOT NULL,
			size varchar(20) NOT NULL,
			rotation smallint(6) NOT NULL,
			position_x int(11) NOT NULL,
			position_y int(11) NOT NULL,
			is_active tinyint(1) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY post_id (post_id)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		// Verify table creation
		$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
		if ($table_exists) {
			error_log('CDG Elements - Table created successfully');
		} else {
			error_log('CDG Elements - Table creation failed');
		}
	}
}
