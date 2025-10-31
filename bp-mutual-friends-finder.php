<?php
/**
 * Plugin Name: BuddyPress Mutual Friends Finder
 * Plugin URI: https://example.com/bp-mutual-friends-finder
 * Description: Display mutual friends on hover - Facebook-style mutual friends tooltip for BuddyPress
 * Version: 1.0.0
 * Author: BuddyPress Community
 * Author URI: https://buddypress.org
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: bp-mutual-friends-finder
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: buddypress
 *
 * @package BP_Mutual_Friends_Finder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
if ( ! defined( 'BPMFF_VERSION' ) ) {
	define( 'BPMFF_VERSION', '1.0.0' );
}

if ( ! defined( 'BPMFF_PLUGIN_DIR' ) ) {
	define( 'BPMFF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'BPMFF_PLUGIN_URL' ) ) {
	define( 'BPMFF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'BPMFF_PLUGIN_FILE' ) ) {
	define( 'BPMFF_PLUGIN_FILE', __FILE__ );
}

/**
 * Check if BuddyPress is active before initializing plugin
 */
function bpmff_check_buddypress() {
	if ( ! function_exists( 'buddypress' ) ) {
		add_action( 'admin_notices', 'bpmff_buddypress_missing_notice' );
		return false;
	}

	$bp = buddypress();

	if ( ! isset( $bp->friends ) || ! bp_is_active( 'friends' ) ) {
		add_action( 'admin_notices', 'bpmff_friends_component_missing_notice' );
		return false;
	}

	return true;
}

/**
 * Display notice if BuddyPress is missing
 */
function bpmff_buddypress_missing_notice() {
	?>
	<div class="notice notice-error is-dismissible">
		<p>
			<?php
			printf(
				/* translators: %s: plugin name */
				esc_html__( '%s requires BuddyPress to be installed and activated.', 'bp-mutual-friends-finder' ),
				'<strong>BuddyPress Mutual Friends Finder</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Display notice if Friends component is missing
 */
function bpmff_friends_component_missing_notice() {
	?>
	<div class="notice notice-error is-dismissible">
		<p>
			<?php
			printf(
				/* translators: %s: plugin name */
				esc_html__( '%s requires BuddyPress Friends component to be active.', 'bp-mutual-friends-finder' ),
				'<strong>BuddyPress Mutual Friends Finder</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Initialize the plugin
 */
function bpmff_init() {
	// Check BuddyPress dependency.
	if ( ! bpmff_check_buddypress() ) {
		return;
	}

	// Load plugin core class.
	require_once BPMFF_PLUGIN_DIR . 'includes/class-bpmff-core.php';

	// Initialize core plugin class.
	BPMFF_Core::instance();

	// Load text domain.
	load_plugin_textdomain( 'bp-mutual-friends-finder', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	/**
	 * Hook: bpmff_init
	 * Fires after plugin initialization
	 */
	do_action( 'bpmff_init' );
}

// Load activation/deactivation classes immediately (before plugins_loaded).
require_once BPMFF_PLUGIN_DIR . 'includes/class-bpmff-activator.php';
require_once BPMFF_PLUGIN_DIR . 'includes/class-bpmff-deactivator.php';

// Initialize plugin on plugins_loaded hook.
add_action( 'plugins_loaded', 'bpmff_init', 20 );

// Register activation hook.
register_activation_hook( BPMFF_PLUGIN_FILE, array( 'BPMFF_Activator', 'activate' ) );

// Register deactivation hook.
register_deactivation_hook( BPMFF_PLUGIN_FILE, array( 'BPMFF_Deactivator', 'deactivate' ) );

/**
 * Get plugin option with default value
 *
 * @param string $option Option name.
 * @param mixed  $default Default value.
 * @return mixed Option value or default.
 */
function bpmff_get_option( $option, $default = false ) {
	$options = get_option( 'bpmff_settings', array() );
	return isset( $options[ $option ] ) ? $options[ $option ] : $default;
}

/**
 * Update plugin option
 *
 * @param string $option Option name.
 * @param mixed  $value Option value.
 * @return bool True if updated, false otherwise.
 */
function bpmff_update_option( $option, $value ) {
	$options = get_option( 'bpmff_settings', array() );
	$options[ $option ] = $value;
	return update_option( 'bpmff_settings', $options );
}

/**
 * Delete plugin option
 *
 * @param string $option Option name.
 * @return bool True if deleted, false otherwise.
 */
function bpmff_delete_option( $option ) {
	$options = get_option( 'bpmff_settings', array() );
	if ( isset( $options[ $option ] ) ) {
		unset( $options[ $option ] );
		return update_option( 'bpmff_settings', $options );
	}
	return false;
}
