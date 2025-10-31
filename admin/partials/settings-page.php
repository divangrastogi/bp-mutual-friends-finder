<?php
/**
 * Settings page template for BuddyPress Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 * @subpackage Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap bpmff-settings-wrap">
	<!-- Header Section -->
	<div class="bpmff-header">
		<div class="bpmff-header-content">
			<h1 class="bpmff-title">
				<span class="bpmff-icon">👥</span>
				<?php echo esc_html__( 'Mutual Friends Finder', 'bp-mutual-friends-finder' ); ?>
			</h1>
			<p class="bpmff-subtitle">
				<?php echo esc_html__( 'Display mutual friends on hover - Facebook-style mutual friends tooltip for BuddyPress', 'bp-mutual-friends-finder' ); ?>
			</p>
		</div>
		<div class="bpmff-version">
			<?php echo esc_html__( 'Version 1.0.0', 'bp-mutual-friends-finder' ); ?>
		</div>
	</div>

	<!-- Settings Form -->
	<form method="post" action="options.php" class="bpmff-settings-form">
		<?php settings_fields( 'bpmff_settings_group' ); ?>
		<?php do_settings_sections( 'bpmff_settings_group' ); ?>

		<!-- Action Buttons -->
		<div class="bpmff-actions">
			<div class="bpmff-button-group">
				<?php submit_button( __( 'Save Settings', 'bp-mutual-friends-finder' ), 'primary', 'submit', false ); ?>
				
				<button type="button" id="bpmff-clear-cache-btn" class="button button-secondary bpmff-clear-cache-btn">
					<span class="dashicons dashicons-trash"></span>
					<?php esc_html_e( 'Clear Cache', 'bp-mutual-friends-finder' ); ?>
				</button>
			</div>
		</div>
	</form>

	<!-- Cache Message -->
	<div id="bpmff-cache-message" class="bpmff-cache-message" style="display: none;"></div>

	<!-- Footer Info -->
	<div class="bpmff-footer-info">
		<div class="bpmff-info-box">
			<h3><?php esc_html_e( 'Need Help?', 'bp-mutual-friends-finder' ); ?></h3>
			<p><?php esc_html_e( 'Check the documentation for detailed information about each setting.', 'bp-mutual-friends-finder' ); ?></p>
		</div>
	</div>
</div>
