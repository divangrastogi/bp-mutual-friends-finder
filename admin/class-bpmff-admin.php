<?php
/**
 * Admin interface for BuddyPress Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 * @subpackage Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BPMFF_Admin class
 *
 * Handles admin interface
 */
class BPMFF_Admin {

	/**
	 * Add admin menu
	 */
	public static function add_admin_menu() {
		// Add submenu under BuddyPress.
		add_submenu_page(
			'bp-general-settings',
			__( 'Mutual Friends Finder', 'bp-mutual-friends-finder' ),
			__( '👥 Mutual Friends', 'bp-mutual-friends-finder' ),
			'manage_options',
			'bpmff-settings',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Render settings page
	 */
	public static function render_settings_page() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'bp-mutual-friends-finder' ) );
		}

		// Include settings template.
		include BPMFF_PLUGIN_DIR . 'admin/partials/settings-page.php';
	}
}
