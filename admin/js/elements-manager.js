(function($) {
	'use strict';

	class ElementsManager {
		constructor() {
			this.elements = [];
			this.currentElement = null;
			this.init();
		}

		init() {
			this.initColorPicker();
			this.initFormHandling();
			this.loadElements();
		}

		initColorPicker() {
			$('.color-picker').wpColorPicker();
		}

		initFormHandling() {
			$('#cdg-element-form').on('submit', (e) => {
				e.preventDefault();
				this.handleFormSubmit();
			});

			$('#clear-form').on('click', () => {
				this.clearForm();
			});

			// Handle element selection from list
			$('#elements-container').on('click', '.element-item', (e) => {
				const elementId = $(e.currentTarget).data('id');
				this.editElement(elementId);
			});

			// Handle element deletion
			$('#elements-container').on('click', '.delete-element', (e) => {
				e.stopPropagation();
				const elementId = $(e.currentTarget).closest('.element-item').data('id');
				if (confirm('Are you sure you want to delete this element?')) {
					this.deleteElement(elementId);
				}
			});
		}

		loadElements() {
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				dataType: 'json',
				data: {
					action: 'cdg_get_elements',
					nonce: cdgElements.nonce,
					post_id: cdgElements.postId
				},
				success: (response) => {
					console.log('Loaded elements:', response);
					if (response.success) {
						this.elements = response.data || [];
						this.renderElementsList();
						this.renderPreviewElements();
					} else {
						this.showNotice('Failed to load elements', 'error');
					}
				},
				error: (jqXHR, textStatus, errorThrown) => {
					console.error('Failed to load elements:', textStatus);
					this.showNotice('Failed to load elements', 'error');
				}
			});
		}

		handleFormSubmit() {
			const formData = this.getFormData();
			console.log('Form data:', formData);

			if (formData.id) {
				this.updateElement(formData);
			} else {
				this.createElement(formData);
			}
		}

		getFormData() {
			return {
				id: $('#element-id').val(),
				text: $('#element-text').val(),
				font: $('#element-font').val(),
				font_url: $('#element-font-url').val(),
				color: $('#element-color').val(),
				size: $('#element-size').val(),
				rotation: $('#element-rotation').val(),
				position_x: parseInt($('#element-position-x').val()) || 0,
				position_y: parseInt($('#element-position-y').val()) || 0
			};
		}

		createElement(elementData) {
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				dataType: 'json',
				data: {
					action: 'cdg_create_element',
					nonce: cdgElements.nonce,
					post_id: cdgElements.postId,
					element: elementData
				},
				success: (response) => {
					console.log('Create response:', response);
					if (response.success) {
						this.elements.push(response.data);
						this.renderElementsList();
						this.renderPreviewElements();
						this.clearForm();
						this.showNotice('Element created successfully', 'success');
					} else {
						this.showNotice(response.message || 'Error creating element', 'error');
					}
				},
				error: (jqXHR, textStatus, errorThrown) => {
					console.error('Failed to create element:', textStatus);
					this.showNotice('Failed to create element', 'error');
				}
			});
		}

		updateElement(elementData) {
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				dataType: 'json',
				data: {
					action: 'cdg_update_element',
					nonce: cdgElements.nonce,
					element: elementData
				},
				success: (response) => {
					if (response.success) {
						const index = this.elements.findIndex(el => el.id === elementData.id);
						if (index !== -1) {
							this.elements[index] = response.data;
						}
						this.renderElementsList();
						this.renderPreviewElements();
						this.clearForm();
						this.showNotice('Element updated successfully', 'success');
					} else {
						this.showNotice(response.message || 'Error updating element', 'error');
					}
				},
				error: (jqXHR, textStatus, errorThrown) => {
					console.error('Failed to update element:', textStatus);
					this.showNotice('Failed to update element', 'error');
				}
			});
		}

		deleteElement(elementId) {
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				dataType: 'json',
				data: {
					action: 'cdg_delete_element',
					nonce: cdgElements.nonce,
					element_id: elementId
				},
				success: (response) => {
					if (response.success) {
						this.elements = this.elements.filter(el => el.id !== elementId);
						this.renderElementsList();
						this.renderPreviewElements();
						this.clearForm();
						this.showNotice('Element deleted successfully', 'success');
					} else {
						this.showNotice(response.message || 'Error deleting element', 'error');
					}
				},
				error: (jqXHR, textStatus, errorThrown) => {
					console.error('Failed to delete element:', textStatus);
					this.showNotice('Failed to delete element', 'error');
				}
			});
		}

		renderElementsList() {
			const container = $('#elements-container');
			container.empty();

			this.elements.forEach(element => {
				container.append(`
					<div class="element-item" data-id="${element.id}">
						<div class="element-preview" style="color: ${element.color};">
							${element.element_text || element.text}
						</div>
						<div class="element-actions">
							<button type="button" class="button edit-element">Edit</button>
							<button type="button" class="button delete-element">Delete</button>
						</div>
					</div>
				`);
			});
		}

		renderPreviewElements() {
			const container = $('#preview-elements');
			container.empty();

			this.elements.forEach(element => {
				const styles = {
					left: `${element.position_x || 0}px`,
					top: `${element.position_y || 0}px`,
					fontFamily: element.font_family || element.font || 'Arial',
					color: element.color || '#000000',
					fontSize: this.getSizeValue(element.size),
					transform: `rotate(${element.rotation || 0}deg)`
				};

				const styleString = Object.entries(styles)
					.map(([key, value]) => `${this.camelToKebab(key)}: ${value}`)
					.join('; ');

				container.append(`
					<div id="preview-element-${element.id}"
						 class="preview-element"
						 data-id="${element.id}"
						 style="${styleString}">
						${element.element_text || element.text}
					</div>
				`);
			});
		}

		editElement(elementId) {
			const element = this.elements.find(el => el.id === elementId);
			if (!element) return;

			// Populate form
			$('#element-id').val(element.id);
			$('#element-text').val(element.element_text || element.text);
			$('#element-font').val(element.font_family || element.font);
			$('#element-font-url').val(element.font_url);
			$('#element-color').wpColorPicker('color', element.color);
			$('#element-size').val(element.size);
			$('#element-rotation').val(element.rotation);
			$('#element-position-x').val(element.position_x);
			$('#element-position-y').val(element.position_y);

			// Update UI
			$('#save-element').text('Update Element');
			$('.element-item').removeClass('active');
			$(`.element-item[data-id="${elementId}"]`).addClass('active');
		}

		clearForm() {
			$('#cdg-element-form')[0].reset();
			$('#element-id').val('');
			$('#element-color').wpColorPicker('color', '#000000');
			$('#save-element').text('Save Element');
			$('.element-item').removeClass('active');
		}

		getSizeValue(size) {
			const sizes = {
				'x-small': '12px',
				'small': '16px',
				'medium': '24px',
				'large': '36px'
			};
			return sizes[size] || sizes.medium;
		}

		camelToKebab(string) {
			return string.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase();
		}

		showNotice(message, type = 'success') {
			const notice = $(`
				<div class="notice notice-${type} is-dismissible">
					<p>${message}</p>
				</div>
			`);

			$('.wrap > h1').after(notice);
			setTimeout(() => {
				notice.fadeOut(() => notice.remove());
			}, 3000);
		}
	}

	// Initialize when document is ready
	$(document).ready(() => {
		window.CDGElementsManager = new ElementsManager();
	});

})(jQuery);
