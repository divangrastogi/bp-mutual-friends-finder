<?php
/**
 * Modal template for all mutual friends
 *
 * @package BP_Mutual_Friends_Finder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="bpmff-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="bpmff-modal-title">
	<div class="bpmff-modal">
		<div class="bpmff-modal-header">
			<h3 id="bpmff-modal-title">
				<?php esc_html_e( 'Mutual Friends', 'bp-mutual-friends-finder' ); ?>
			</h3>
			<button type="button" class="bpmff-modal-close" aria-label="<?php esc_attr_e( 'Close', 'bp-mutual-friends-finder' ); ?>">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		
		<div class="bpmff-modal-body">
			<div class="bpmff-modal-content">
				<!-- Content loaded via AJAX -->
			</div>
		</div>
		
		<div class="bpmff-modal-footer">
			<div class="bpmff-modal-pagination">
				<!-- Pagination loaded via AJAX -->
			</div>
		</div>
	</div>
</div>
