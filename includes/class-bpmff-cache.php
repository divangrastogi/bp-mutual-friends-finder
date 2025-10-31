<?php
/**
 * Cache handler for BuddyPress Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BPMFF_Cache class
 *
 * Handles multi-layer caching for mutual friends data
 */
class BPMFF_Cache {

	/**
	 * Get cached mutual friends data
	 *
	 * @param int $user_id Current user ID.
	 * @param int $target_id Target user ID.
	 * @return mixed Cached data or false.
	 */
	public static function get_cache( $user_id, $target_id ) {
		$user_id = absint( $user_id );
		$target_id = absint( $target_id );

		if ( empty( $user_id ) || empty( $target_id ) ) {
			return false;
		}

		$cache_key = self::get_cache_key( $user_id, $target_id );
		$group = 'bpmff_mutuals';

		// Layer 1: Object cache (WordPress cache).
		$data = wp_cache_get( $cache_key, $group );
		if ( false !== $data ) {
			return $data;
		}

		// Layer 2: Transient (database cache).
		$transient_key = "bpmff_mutual_{$user_id}_{$target_id}";
		$data = get_transient( $transient_key );

		if ( false !== $data ) {
			wp_cache_set( $cache_key, $data, $group, 3600 );
			return $data;
		}

		return false;
	}

	/**
	 * Set cache for mutual friends
	 *
	 * @param int   $user_id Current user ID.
	 * @param int   $target_id Target user ID.
	 * @param mixed $data Data to cache.
	 * @return bool True if set, false otherwise.
	 */
	public static function set_cache( $user_id, $target_id, $data ) {
		$user_id = absint( $user_id );
		$target_id = absint( $target_id );

		if ( empty( $user_id ) || empty( $target_id ) ) {
			return false;
		}

		$cache_key = self::get_cache_key( $user_id, $target_id );
		$group = 'bpmff_mutuals';

		/**
		 * Filter: bpmff_cache_expiration
		 * Modify cache expiration time
		 */
		$expiration = apply_filters( 'bpmff_cache_expiration', 3600 );

		// Set in object cache.
		wp_cache_set( $cache_key, $data, $group, $expiration );

		// Set in transient.
		$transient_key = "bpmff_mutual_{$user_id}_{$target_id}";
		set_transient( $transient_key, $data, $expiration );

		return true;
	}

	/**
	 * Get cache key
	 *
	 * @param int $user_id Current user ID.
	 * @param int $target_id Target user ID.
	 * @return string Cache key.
	 */
	private static function get_cache_key( $user_id, $target_id ) {
		$user_id = absint( $user_id );
		$target_id = absint( $target_id );

		$key = "bpmff_mutual_{$user_id}_{$target_id}";

		/**
		 * Filter: bpmff_cache_key
		 * Modify cache key
		 */
		return apply_filters( 'bpmff_cache_key', $key, $user_id, $target_id );
	}

	/**
	 * Invalidate cache on friendship changes
	 *
	 * @param int $user_id User ID.
	 */
	public static function invalidate_cache( $user_id ) {
		$user_id = absint( $user_id );

		if ( empty( $user_id ) ) {
			return;
		}

		// Clear transients for this user.
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_bpmff_mutual_' . $user_id . '_%'
			)
		);

		/**
		 * Action: bpmff_cache_invalidated
		 * Fired when cache is invalidated
		 */
		do_action( 'bpmff_cache_invalidated', $user_id );
	}

	/**
	 * Clear all cache
	 */
	public static function clear_all_cache() {
		// Clear all transients.
		global $wpdb;
		$wpdb->query(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_bpmff_mutual_%'"
		);

		/**
		 * Action: bpmff_cache_cleared
		 * Fired when all cache is cleared
		 */
		do_action( 'bpmff_cache_cleared' );
	}

	/**
	 * Cleanup old cache entries (cron job)
	 */
	public static function cleanup_old_cache() {
		global $wpdb;

		// Delete old transients.
		$wpdb->query(
			"DELETE FROM {$wpdb->options} 
			WHERE option_name LIKE '_transient_bpmff_mutual_%' 
			AND option_value < DATE_SUB(NOW(), INTERVAL 24 HOUR)"
		);

		/**
		 * Action: bpmff_cache_cleanup
		 * Fired after cache cleanup
		 */
		do_action( 'bpmff_cache_cleanup' );
	}
}
