/**
 * BuddyPress Mutual Friends Finder Admin Script
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		$('#bpmff-clear-cache-btn').on('click', function(e) {
			e.preventDefault();

			var $btn = $(this);
			var $message = $('#bpmff-cache-message');

			$btn.prop('disabled', true);
			$message.text(bpmffAdminData.i18n.clearingCache).removeClass('error success').show();

			$.ajax({
				url: bpmffAdminData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'bpmff_clear_cache',
					nonce: bpmffAdminData.nonce
				},
				success: function(response) {
					if (response.success) {
						$message.text(response.data.message).addClass('success').show();
					} else {
						$message.text(response.data.message || bpmffAdminData.i18n.error).addClass('error').show();
					}
				},
				error: function() {
					$message.text(bpmffAdminData.i18n.error).addClass('error').show();
				},
				complete: function() {
					$btn.prop('disabled', false);
				}
			});
		});
	});

})(jQuery);
