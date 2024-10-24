<?php
/**
 * Handle AJAX requests for the admin interface
 */
class CDG_Elements_Admin_Ajax {
	
	public function __construct() {
		// Constructor can be empty as we're using dependency injection
	}

	/**
	 * Get elements for current post
	 */
	public function get_elements() {
		try {
			check_ajax_referer('cdg_elements_nonce', 'nonce');
			
			if (!current_user_can('edit_posts')) {
				wp_send_json_error('Insufficient permissions');
				return;
			}

			$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
			
			global $wpdb;
			$table_name = $wpdb->prefix . 'cdg_elements';
			
			$elements = $wpdb->get_results($wpdb->prepare(
				"SELECT * FROM $table_name WHERE post_id = %d AND is_active = 1 ORDER BY id ASC",
				$post_id
			));

			if ($elements === false) {
				error_log('CDG Elements - Database error: ' . $wpdb->last_error);
				wp_send_json_error('Database error occurred');
				return;
			}

			error_log('CDG Elements - Retrieved elements: ' . print_r($elements, true));
			wp_send_json_success($elements);

		} catch (Exception $e) {
			error_log('CDG Elements - Error getting elements: ' . $e->getMessage());
			wp_send_json_error('Server error occurred');
		}
	}

/**
	 * Create a new element
	 */
	public function create_element() {
		try {
			error_log('CDG Elements - Starting element creation');
			
			// Check nonce and permissions
			check_ajax_referer('cdg_elements_nonce', 'nonce');
			
			if (!current_user_can('edit_posts')) {
				error_log('CDG Elements - Permission denied');
				wp_send_json_error('Insufficient permissions');
				return;
			}
	
			// Verify element data
			if (!isset($_POST['element'])) {
				error_log('CDG Elements - No element data in POST: ' . print_r($_POST, true));
				wp_send_json_error('No element data received');
				return;
			}
	
			$element_data = $_POST['element'];
			error_log('CDG Elements - Raw element data: ' . print_r($element_data, true));
	
			// Get post ID
			$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : get_the_ID();
			error_log('CDG Elements - Post ID: ' . $post_id);
	
			// Verify database
			global $wpdb;
			$table_name = $wpdb->prefix . 'cdg_elements';
	
			// Check if table exists
			$table_exists = $wpdb->get_var($wpdb->prepare(
				"SHOW TABLES LIKE %s",
				$table_name
			));
	
			if (!$table_exists) {
				error_log('CDG Elements - Table does not exist: ' . $table_name);
				wp_send_json_error('Database table not found');
				return;
			}
	
			// Prepare data for insertion
			$data = array(
				'post_id'     => $post_id,
				'element_text' => sanitize_text_field($element_data['text']),
				'font_family' => sanitize_text_field($element_data['font']),
				'font_url'    => !empty($element_data['font_url']) ? esc_url_raw($element_data['font_url']) : '',
				'color'       => sanitize_hex_color_no_hash($element_data['color']),
				'size'        => sanitize_text_field($element_data['size']),
				'rotation'    => intval($element_data['rotation']),
				'position_x'  => intval($element_data['position_x']),
				'position_y'  => intval($element_data['position_y']),
				'is_active'   => 1
			);
	
			error_log('CDG Elements - Data to insert: ' . print_r($data, true));
			error_log('CDG Elements - Table name: ' . $table_name);
	
			// Insert the data
			$result = $wpdb->insert($table_name, $data);
			
			if ($result === false) {
				error_log('CDG Elements - Database error during insert: ' . $wpdb->last_error);
				error_log('CDG Elements - Last query: ' . $wpdb->last_query);
				wp_send_json_error('Database error: ' . $wpdb->last_error);
				return;
			}
	
			$element_id = $wpdb->insert_id;
			error_log('CDG Elements - New element ID: ' . $element_id);
	
			// Get the inserted element
			$new_element = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $table_name WHERE id = %d",
				$element_id
			));
	
			if (!$new_element) {
				error_log('CDG Elements - Could not retrieve new element');
				wp_send_json_error('Failed to retrieve new element');
				return;
			}
	
			error_log('CDG Elements - Successfully created element: ' . print_r($new_element, true));
			wp_send_json_success($new_element);
	
		} catch (Exception $e) {
			error_log('CDG Elements - Exception: ' . $e->getMessage());
			error_log('CDG Elements - Stack trace: ' . $e->getTraceAsString());
			wp_send_json_error('Server error occurred');
		}
	}

	/**
	 * Update an existing element
	 */
	public function update_element() {
		try {
			check_ajax_referer('cdg_elements_nonce', 'nonce');
			
			if (!current_user_can('edit_posts')) {
				wp_send_json_error('Insufficient permissions');
				return;
			}

			if (!isset($_POST['element'])) {
				wp_send_json_error('No element data received');
				return;
			}

			$element_data = $_POST['element'];
			$element_id = isset($element_data['id']) ? intval($element_data['id']) : 0;

			if (!$element_id) {
				wp_send_json_error('Invalid element ID');
				return;
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'cdg_elements';

			$data = array(
				'element_text' => sanitize_text_field($element_data['text']),
				'font_family' => sanitize_text_field($element_data['font']),
				'font_url' => esc_url_raw($element_data['font_url']),
				'color' => sanitize_hex_color($element_data['color']),
				'size' => sanitize_text_field($element_data['size']),
				'rotation' => intval($element_data['rotation']),
				'position_x' => intval($element_data['position_x']),
				'position_y' => intval($element_data['position_y'])
			);

			$result = $wpdb->update(
				$table_name,
				$data,
				array('id' => $element_id)
			);

			if ($result === false) {
				wp_send_json_error('Failed to update element');
				return;
			}

			$updated_element = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $table_name WHERE id = %d",
				$element_id
			));

			wp_send_json_success($updated_element);

		} catch (Exception $e) {
			error_log('CDG Elements - Error: ' . $e->getMessage());
			wp_send_json_error('Server error occurred');
		}
	}

	/**
	 * Delete an element
	 */
	public function delete_element() {
		try {
			check_ajax_referer('cdg_elements_nonce', 'nonce');
			
			if (!current_user_can('edit_posts')) {
				wp_send_json_error('Insufficient permissions');
				return;
			}

			$element_id = isset($_POST['element_id']) ? intval($_POST['element_id']) : 0;

			if (!$element_id) {
				wp_send_json_error('Invalid element ID');
				return;
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'cdg_elements';

			$result = $wpdb->delete(
				$table_name,
				array('id' => $element_id)
			);

			if ($result === false) {
				wp_send_json_error('Failed to delete element');
				return;
			}

			wp_send_json_success(array(
				'message' => 'Element deleted successfully',
				'element_id' => $element_id
			));

		} catch (Exception $e) {
			error_log('CDG Elements - Error: ' . $e->getMessage());
			wp_send_json_error('Server error occurred');
		}
	}

	/**
	 * Sanitize element data
	 */
	private function sanitize_element_data($data) {
		return array(
			'text' => sanitize_text_field(isset($data['text']) ? $data['text'] : ''),
			'font' => sanitize_text_field(isset($data['font']) ? $data['font'] : ''),
			'font_url' => esc_url_raw(isset($data['font_url']) ? $data['font_url'] : ''),
			'color' => sanitize_hex_color(isset($data['color']) ? $data['color'] : '#000000'),
			'size' => $this->sanitize_size(isset($data['size']) ? $data['size'] : 'medium'),
			'rotation' => $this->sanitize_rotation(isset($data['rotation']) ? $data['rotation'] : 0),
			'position' => array(
				'x' => intval(isset($data['position']['x']) ? $data['position']['x'] : 0),
				'y' => intval(isset($data['position']['y']) ? $data['position']['y'] : 0)
			)
		);
	}

	/**
	 * Sanitize size value
	 */
	private function sanitize_size($size) {
		$allowed_sizes = array('x-small', 'small', 'medium', 'large');
		return in_array($size, $allowed_sizes) ? $size : 'medium';
	}

	/**
	 * Sanitize rotation value
	 */
	private function sanitize_rotation($rotation) {
		$allowed_rotations = array(-5, -3, 0, 3, 5);
		$rotation = intval($rotation);
		return in_array($rotation, $allowed_rotations) ? $rotation : 0;
	}
}
