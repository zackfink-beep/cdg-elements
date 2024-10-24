(function($) {
	'use strict';

	class CDGElementsManager {
		constructor() {
			this.init();
		}

		init() {
			this.handleElementVisibility();
		}

		handleElementVisibility() {
			if ('IntersectionObserver' in window) {
				const observer = new IntersectionObserver(
					(entries) => {
						entries.forEach(entry => {
							const element = entry.target;
							if (entry.isIntersecting) {
								$(element).addClass('is-visible');
							} else {
								$(element).removeClass('is-visible');
							}
						});
					},
					{
						threshold: 0.1
					}
				);

				$('.cdg-element').each(function() {
					observer.observe(this);
				});
			} else {
				// Fallback for browsers that don't support IntersectionObserver
				$('.cdg-element').addClass('is-visible');
			}
		}
	}

	// Initialize when document is ready
	$(document).ready(() => {
		window.cdgElementsManager = new CDGElementsManager();
	});

})(jQuery);
