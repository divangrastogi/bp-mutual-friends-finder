<?php
/**
 * Deactivation handler for BuddyPress Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BPMFF_Deactivator class
 *
 * Handles plugin deactivation tasks
 */
class BPMFF_Deactivator {

	/**
	 * Deactivate the plugin
	 *
	 * @static
	 */
	public static function deactivate() {
		// Unschedule cron jobs.
		self::unschedule_cron_jobs();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Unschedule cron jobs
	 *
	 * @static
	 */
	private static function unschedule_cron_jobs() {
		// Unschedule cache cleanup job.
		$timestamp = wp_next_scheduled( 'bpmff_cleanup_cache' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'bpmff_cleanup_cache' );
		}
	}
}
