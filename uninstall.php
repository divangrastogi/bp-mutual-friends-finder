<?php
/**
 * Uninstall handler for BuddyPress Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'bpmff_settings' );

// Delete all transients.
global $wpdb;
$wpdb->query(
	"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_bpmff_%'"
);

// Unschedule cron jobs.
$timestamp = wp_next_scheduled( 'bpmff_cleanup_cache' );
if ( $timestamp ) {
	wp_unschedule_event( $timestamp, 'bpmff_cleanup_cache' );
}
