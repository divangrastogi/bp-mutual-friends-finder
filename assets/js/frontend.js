/**
 * BuddyPress Mutual Friends Finder Frontend Script
 */

(function($) {
	'use strict';

	// Check if bpmffData is available
	if (typeof bpmffData === 'undefined') {
		console.error('BPMFF: bpmffData is not defined');
		return;
	}

	const BPMFF = {
		// Configuration
		config: {
			ajaxUrl: bpmffData.ajaxUrl,
			nonce: bpmffData.nonce,
			displayCount: parseInt(bpmffData.displayCount) || 3,
			tooltipDelay: 500,
			cacheTimeout: 300000 // 5 minutes
		},

		// Local cache for AJAX responses
		cache: new Map(),

		/**
		 * Initialize plugin
		 */
		init: function() {
			// Event binding is now handled in PHP after data attributes are added
		},

		/**
		 * Handle member link hover
		 */
		handleMemberHover: function(e) {
			const $target = $(e.currentTarget);
			const userId = $target.data('bpmff-user');
			
			if (!userId) {
				return;
			}

			// Clear existing timeout
			if (this.hoverTimeout) {
				clearTimeout(this.hoverTimeout);
			}

			// Delay before showing tooltip
			this.hoverTimeout = setTimeout(() => {
				this.loadMutualFriends(userId, $target);
			}, this.config.tooltipDelay);

			// Clear on mouse leave
			$target.one('mouseleave', () => {
				clearTimeout(this.hoverTimeout);
			});
		},

		/**
		 * Load mutual friends for tooltip display (for click triggers)
		 */
		loadMutualFriendsForTooltip: function(userId, $trigger) {
			// Check cache first
			const cacheKey = `mutual_${userId}`;
			const cached = this.cache.get(cacheKey);
			
			if (cached && Date.now() - cached.timestamp < this.config.cacheTimeout) {
				this.showTooltip($trigger, cached.data);
				return;
			}

			// Show loading state
			this.showLoadingTooltip($trigger);

			// AJAX request
			$.ajax({
				url: this.config.ajaxUrl,
				type: 'POST',
				data: {
					action: 'bpmff_get_mutual_friends',
					nonce: this.config.nonce,
					target_user_id: userId,
					display_count: this.config.displayCount,
					format: 'tooltip'
				},
				success: (response) => {
					if (response.success) {
						// Cache response
						this.cache.set(cacheKey, {
							data: response.data,
							timestamp: Date.now()
						});
						
						this.showTooltip($trigger, response.data);
					} else {
						this.showErrorTooltip($trigger, response.data.message);
					}
				},
				error: (xhr, status, error) => {
					this.showErrorTooltip($trigger, 'Failed to load mutual friends');
				}
			});
		},

		/**
		 * Display tooltip with mutual friends
		 */
		showTooltip: function($target, data) {
			// Remove existing tooltips
			$('.bpmff-tooltip').remove();

			// No mutual friends
			if (data.count === 0) {
				return;
			}

			// Create tooltip element
			const $tooltip = $(data.html);
			
			// Position tooltip
			this.positionTooltip($tooltip, $target);
			
			// Add to DOM with animation
			$('body').append($tooltip);
			
			setTimeout(() => {
				$tooltip.addClass('bpmff-tooltip-visible');
			}, 10);

			// Check if this is a click-triggered tooltip
			const isClickTriggered = $target.hasClass('bpmff-tooltip-trigger');

			// Handle closing differently for click vs hover triggers
			if (isClickTriggered) {
				// For click triggers, close on outside click or ESC
				const closeTooltip = () => {
					$tooltip.removeClass('bpmff-tooltip-visible');
					setTimeout(() => $tooltip.remove(), 300);
				};

				// Close on document click (outside tooltip)
				$(document).one('click', (e) => {
					if (!$(e.target).closest('.bpmff-tooltip').length && !$(e.target).closest('.bpmff-tooltip-trigger').length) {
						closeTooltip();
					}
				});

				// Close on ESC key
				$(document).one('keydown', (e) => {
					if (e.keyCode === 27) { // ESC
						closeTooltip();
					}
				});

				// Keep tooltip visible when hovering over it
				$tooltip.on('mouseenter', () => {
					$(document).off('click', closeTooltip);
					$(document).off('keydown', closeTooltip);
				});

				$tooltip.on('mouseleave', () => {
					// Re-bind the close handlers
					setTimeout(() => {
						$(document).one('click', (e) => {
							if (!$(e.target).closest('.bpmff-tooltip').length && !$(e.target).closest('.bpmff-tooltip-trigger').length) {
								closeTooltip();
							}
						});
						$(document).one('keydown', (e) => {
							if (e.keyCode === 27) {
								closeTooltip();
							}
						});
					}, 100);
				});
			} else {
				// Original hover behavior
				// Remove on mouse leave
				$target.one('mouseleave', () => {
					$tooltip.removeClass('bpmff-tooltip-visible');
					setTimeout(() => $tooltip.remove(), 300);
				});

				// Keep tooltip visible when hovering over it
				$tooltip.on('mouseenter', () => {
					$target.off('mouseleave');
				});

				$tooltip.on('mouseleave', () => {
					$tooltip.removeClass('bpmff-tooltip-visible');
					setTimeout(() => $tooltip.remove(), 300);
				});
			}
		},

		/**
		 * Show loading tooltip
		 */
		showLoadingTooltip: function($target) {
			// Remove existing tooltips
			$('.bpmff-tooltip').remove();

			const $tooltip = $('<div class="bpmff-tooltip bpmff-loading" role="status"><div class="bpmff-spinner"></div><span>' + bpmffData.i18n.loading + '</span></div>');
			
			this.positionTooltip($tooltip, $target);
			$('body').append($tooltip);
			
			setTimeout(() => {
				$tooltip.addClass('bpmff-tooltip-visible');
			}, 10);
		},

		/**
		 * Show error tooltip
		 */
		showErrorTooltip: function($target, message) {
			// Remove existing tooltips
			$('.bpmff-tooltip').remove();

			const $tooltip = $('<div class="bpmff-tooltip" role="alert"><div class="bpmff-error-message">' + message + '</div></div>');
			
			this.positionTooltip($tooltip, $target);
			$('body').append($tooltip);
			
			setTimeout(() => {
				$tooltip.addClass('bpmff-tooltip-visible');
			}, 10);

			// Auto-remove after 3 seconds
			setTimeout(() => {
				$tooltip.removeClass('bpmff-tooltip-visible');
				setTimeout(() => $tooltip.remove(), 300);
			}, 3000);
		},

		/**
		 * Calculate and set tooltip position
		 */
		positionTooltip: function($tooltip, $target) {
			const targetOffset = $target.offset();
			const targetWidth = $target.outerWidth();
			const targetHeight = $target.outerHeight();
			const tooltipWidth = $tooltip.outerWidth();
			const tooltipHeight = $tooltip.outerHeight();
			const windowWidth = $(window).width();
			const windowHeight = $(window).height();
			const scrollTop = $(window).scrollTop();

			let top, left;
			let position = bpmffData.tooltipPosition || 'auto';

			// Auto-position if needed
			if (position === 'auto') {
				const spaceTop = targetOffset.top - scrollTop;
				const spaceBottom = windowHeight - (targetOffset.top - scrollTop + targetHeight);
				position = spaceBottom > tooltipHeight ? 'bottom' : 'top';
			}

			// Calculate position
			switch(position) {
				case 'bottom':
					top = targetOffset.top + targetHeight + 10;
					left = targetOffset.left + (targetWidth / 2) - (tooltipWidth / 2);
					$tooltip.addClass('bpmff-tooltip-bottom');
					break;
				case 'left':
					top = targetOffset.top + (targetHeight / 2) - (tooltipHeight / 2);
					left = targetOffset.left - tooltipWidth - 10;
					$tooltip.addClass('bpmff-tooltip-left');
					break;
				case 'right':
					top = targetOffset.top + (targetHeight / 2) - (tooltipHeight / 2);
					left = targetOffset.left + targetWidth + 10;
					$tooltip.addClass('bpmff-tooltip-right');
					break;
				default: // top
					top = targetOffset.top - tooltipHeight - 10;
					left = targetOffset.left + (targetWidth / 2) - (tooltipWidth / 2);
					$tooltip.addClass('bpmff-tooltip-top');
			}

			// Keep within viewport
			left = Math.max(10, Math.min(left, windowWidth - tooltipWidth - 10));
			top = Math.max(scrollTop + 10, top);

			$tooltip.css({top: top, left: left});
		},

		/**
		 * Open modal with all mutual friends
		 */
		openModal: function(e) {
			e.preventDefault();
			const userId = $(e.currentTarget).data('user-id');
			this.loadModalContent(userId, 1);
		},

		/**
		 * Load modal content via AJAX
		 */
		loadModalContent: function(userId, page) {
			page = page || 1;

			$.ajax({
				url: this.config.ajaxUrl,
				type: 'POST',
				data: {
					action: 'bpmff_get_all_mutual_friends',
					nonce: this.config.nonce,
					target_user_id: userId,
					page: page
				},
				success: (response) => {
					if (response.success) {
						this.showModal(userId, response.data);
					}
				},
				error: () => {
					alert('Failed to load mutual friends');
				}
			});
		},

		/**
		 * Show modal
		 */
		showModal: function(userId, data) {
			// Remove existing modal
			$('.bpmff-modal-overlay').remove();

			const $overlay = $('<div class="bpmff-modal-overlay" role="dialog" aria-modal="true"></div>');
			const $modal = $('<div class="bpmff-modal"></div>');
			const $header = $('<div class="bpmff-modal-header"><h3>' + bpmffData.i18n.mutualFriends + '</h3><button type="button" class="bpmff-modal-close" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
			const $body = $('<div class="bpmff-modal-body"><div class="bpmff-modal-content">' + data.html + '</div></div>');
			const $footer = $('<div class="bpmff-modal-footer"><div class="bpmff-modal-pagination"></div></div>');

			// Add pagination
			if (data.total_pages > 1) {
				let pagination = '';
				for (let i = 1; i <= data.total_pages; i++) {
					if (i === data.page) {
						pagination += '<span class="current">' + i + '</span>';
					} else {
						pagination += '<a href="#" data-page="' + i + '" data-user-id="' + userId + '">' + i + '</a>';
					}
				}
				$footer.find('.bpmff-modal-pagination').html(pagination);
			}

			$modal.append($header).append($body).append($footer);
			$overlay.append($modal);
			$('body').append($overlay);

			// Show with animation
			setTimeout(() => {
				$overlay.addClass('bpmff-modal-visible');
			}, 10);

			// Bind pagination
			$overlay.on('click', '.bpmff-modal-pagination a', (e) => {
				e.preventDefault();
				const page = $(e.currentTarget).data('page');
				this.loadModalContent(userId, page);
			});
		},

		/**
		 * Close modal
		 */
		closeModal: function() {
			const $overlay = $('.bpmff-modal-overlay');
			$overlay.removeClass('bpmff-modal-visible');
			setTimeout(() => $overlay.remove(), 300);
		},

		/**
		 * Close modal on overlay click
		 */
		closeModalOnOverlay: function(e) {
			if ($(e.target).hasClass('bpmff-modal-overlay')) {
				this.closeModal();
			}
		}
	};

	// Initialize on document ready
	$(document).ready(() => BPMFF.init());

	// Expose to global scope for debugging
	window.BPMFF = BPMFF;

})(jQuery);
