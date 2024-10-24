<?php
if (!defined('WPINC')) {
	die;
}
?>
<div class="wrap cdg-elements-manager">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

	<div class="cdg-elements-workspace">
		<!-- Element Management Panel -->
		<div class="element-controls">
			<div class="element-form-container">
				<h2><?php _e('Add/Edit Element', 'cdg-elements'); ?></h2>
				<form id="cdg-element-form">
					<input type="hidden" id="element-id" name="element-id" value="">
					
					<!-- Text Content -->
					<div class="form-field">
						<label for="element-text"><?php _e('Text Content', 'cdg-elements'); ?></label>
						<input type="text" id="element-text" name="element-text" required>
					</div>

					<!-- Font Settings -->
					<div class="form-row">
						<div class="form-field">
							<label for="element-font"><?php _e('Font Family Name', 'cdg-elements'); ?></label>
							<input type="text" 
								   id="element-font" 
								   name="element-font" 
								   placeholder="e.g., 'Roboto'" 
								   required>
						</div>
						<div class="form-field">
							<label for="element-font-url"><?php _e('Font CSS URL', 'cdg-elements'); ?></label>
							<input type="url" 
								   id="element-font-url" 
								   name="element-font-url" 
								   placeholder="https://fonts.googleapis.com/css2?family=Roboto">
						</div>
					</div>

					<!-- Color and Size -->
					<div class="form-row">
						<div class="form-field">
							<label for="element-color"><?php _e('Text Color', 'cdg-elements'); ?></label>
							<input type="text" id="element-color" name="element-color" class="color-picker" value="#000000">
						</div>

						<div class="form-field">
							<label for="element-size"><?php _e('Text Size', 'cdg-elements'); ?></label>
							<select id="element-size" name="element-size">
								<option value="x-small"><?php _e('Extra Small', 'cdg-elements'); ?></option>
								<option value="small"><?php _e('Small', 'cdg-elements'); ?></option>
								<option value="medium" selected><?php _e('Medium', 'cdg-elements'); ?></option>
								<option value="large"><?php _e('Large', 'cdg-elements'); ?></option>
							</select>
						</div>
					</div>

					<!-- Rotation and Position -->
					<div class="form-row">
						<div class="form-field">
							<label for="element-rotation"><?php _e('Rotation', 'cdg-elements'); ?></label>
							<select id="element-rotation" name="element-rotation">
								<option value="-5">-5°</option>
								<option value="-3">-3°</option>
								<option value="0" selected>0°</option>
								<option value="3">3°</option>
								<option value="5">5°</option>
							</select>
						</div>
					</div>

					<!-- Position Controls -->
					<div class="form-row">
						<div class="form-field">
							<label for="element-position-x"><?php _e('X Position', 'cdg-elements'); ?></label>
							<input type="number" id="element-position-x" name="element-position-x" value="0">
						</div>

						<div class="form-field">
							<label for="element-position-y"><?php _e('Y Position', 'cdg-elements'); ?></label>
							<input type="number" id="element-position-y" name="element-position-y" value="0">
						</div>
					</div>

					<!-- Form Actions -->
					<div class="form-actions">
						<button type="submit" class="button button-primary" id="save-element">
							<?php _e('Save Element', 'cdg-elements'); ?>
						</button>
						<button type="button" class="button" id="clear-form">
							<?php _e('Clear Form', 'cdg-elements'); ?>
						</button>
					</div>
				</form>
			</div>

			<!-- Elements List -->
			<div class="elements-list">
				<h2><?php _e('Existing Elements', 'cdg-elements'); ?></h2>
				<div id="elements-container"></div>
			</div>
		</div>

		<!-- Preview Panel -->
		<div class="preview-panel">
			<div class="preview-header">
				<h2><?php _e('Preview', 'cdg-elements'); ?></h2>
			</div>

			<!-- Preview Canvas -->
			<div class="preview-container">
				<div id="preview-canvas">
					<div class="sample-content cdg-below">
						<h2>Sample Content</h2>
						<p>This is sample content to demonstrate element positioning.</p>
					</div>
					<div id="preview-elements"></div>
				</div>
			</div>
		</div>
	</div>
</div>
