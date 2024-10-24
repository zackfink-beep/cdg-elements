<?php
// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

// Delete plugin options
delete_option('cdg_elements_settings');
delete_option('cdg_elements_version');
delete_option('cdg_elements_db_version');

// Delete elements table
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cdg_elements");

// Delete all post meta related to the plugin
delete_post_meta_by_key('_cdg_elements_enabled');