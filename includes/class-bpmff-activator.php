<?php
/**
 * Activation handler for BuddyPress Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BPMFF_Activator class
 *
 * Handles plugin activation tasks
 */
class BPMFF_Activator {

	/**
	 * Activate the plugin
	 *
	 * @static
	 */
	public static function activate() {
		// Check WordPress version.
		if ( version_compare( get_bloginfo( 'version' ), '6.0', '<' ) ) {
			wp_die(
				esc_html__( 'BuddyPress Mutual Friends Finder requires WordPress 6.0 or higher.', 'bp-mutual-friends-finder' ),
				esc_html__( 'Plugin Activation Error', 'bp-mutual-friends-finder' ),
				array( 'back_link' => true )
			);
		}

		// Check PHP version.
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			wp_die(
				esc_html__( 'BuddyPress Mutual Friends Finder requires PHP 7.4 or higher.', 'bp-mutual-friends-finder' ),
				esc_html__( 'Plugin Activation Error', 'bp-mutual-friends-finder' ),
				array( 'back_link' => true )
			);
		}

		// Check BuddyPress is active.
		if ( ! function_exists( 'buddypress' ) ) {
			wp_die(
				esc_html__( 'BuddyPress Mutual Friends Finder requires BuddyPress to be installed and activated.', 'bp-mutual-friends-finder' ),
				esc_html__( 'Plugin Activation Error', 'bp-mutual-friends-finder' ),
				array( 'back_link' => true )
			);
		}

		// Check BuddyPress version.
		$bp = buddypress();
		if ( isset( $bp->version ) && version_compare( $bp->version, '10.0', '<' ) ) {
			wp_die(
				esc_html__( 'BuddyPress Mutual Friends Finder requires BuddyPress 10.0 or higher.', 'bp-mutual-friends-finder' ),
				esc_html__( 'Plugin Activation Error', 'bp-mutual-friends-finder' ),
				array( 'back_link' => true )
			);
		}

		// Check Friends component is active.
		if ( ! bp_is_active( 'friends' ) ) {
			wp_die(
				esc_html__( 'BuddyPress Mutual Friends Finder requires BuddyPress Friends component to be active.', 'bp-mutual-friends-finder' ),
				esc_html__( 'Plugin Activation Error', 'bp-mutual-friends-finder' ),
				array( 'back_link' => true )
			);
		}

		// Set default plugin options.
		self::set_default_options();

		// Schedule cron jobs.
		self::schedule_cron_jobs();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Set default plugin options
	 *
	 * @static
	 */
	private static function set_default_options() {
		$defaults = array(
			'enabled'              => 1,
			'enable_members_dir'   => 1,
			'enable_profile_pages' => 1,
			'enable_activity'      => 1,
			'enable_groups'        => 1,
			'display_mode'         => 'both', // 'tooltip', 'modal', 'both'
			'display_count'        => 3,
			'show_count_badge'     => 1,
			'avatar_size'          => 'thumb',
			'animation_effect'     => 'fade',
			'tooltip_position'     => 'auto',
			'enable_caching'       => 1,
			'cache_duration'       => 3600,
			'max_cached_entries'   => 1000,
			'preload_enabled'      => 0,
			'respect_privacy'      => 1,
			'hide_private_friends' => 1,
			'min_mutual_threshold' => 0,
			'exclude_roles'        => array(),
			'debug_mode'           => 0,
		);

		$existing = get_option( 'bpmff_settings', array() );
		$options = wp_parse_args( $existing, $defaults );

		update_option( 'bpmff_settings', $options );
	}

	/**
	 * Schedule cron jobs
	 *
	 * @static
	 */
	private static function schedule_cron_jobs() {
		// Schedule cache cleanup job.
		if ( ! wp_next_scheduled( 'bpmff_cleanup_cache' ) ) {
			wp_schedule_event( time(), 'daily', 'bpmff_cleanup_cache' );
		}
	}
}
