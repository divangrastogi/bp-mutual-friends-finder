<?php
/**
 * Query handler for BuddyPress Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BPMFF_Query class
 *
 * Handles database queries for mutual friends
 */
class BPMFF_Query {

	/**
	 * Get mutual friends between current user and target
	 *
	 * @param int   $user_id Current user ID.
	 * @param int   $target_id Target user ID.
	 * @param array $args Optional arguments.
	 * @return array {
	 *     @type int   $count   Total mutual friends count.
	 *     @type array $friends Array of mutual friend objects.
	 * }
	 */
	public static function get_mutual_friends( $user_id, $target_id, $args = array() ) {
		// Validate user IDs.
		$user_id = absint( $user_id );
		$target_id = absint( $target_id );

		if ( empty( $user_id ) || empty( $target_id ) ) {
			return array(
				'count'   => 0,
				'friends' => array(),
			);
		}

		// Parse arguments.
		$defaults = array(
			'limit'     => 3,
			'random'    => true,
			'use_cache' => true,
			'order'     => 'random',
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * Filter: bpmff_query_args
		 * Modify query arguments
		 */
		$args = apply_filters( 'bpmff_query_args', $args, $user_id, $target_id );

		// Check privacy permissions.
		if ( ! self::can_view_friends( $user_id, $target_id ) ) {
			return array(
				'count'   => 0,
				'friends' => array(),
			);
		}

		/**
		 * Action: bpmff_before_query
		 * Fired before querying mutual friends
		 */
		do_action( 'bpmff_before_query', $user_id, $target_id );

		// Get friend IDs for both users.
		$user_friends = self::get_friend_ids( $user_id, $args['use_cache'] );
		$target_friends = self::get_friend_ids( $target_id, $args['use_cache'] );

		// Calculate mutual friends.
		$mutual_ids = self::calculate_mutuals( $user_friends, $target_friends );

		// Get total count.
		$total_count = count( $mutual_ids );

		// Handle limiting and randomization.
		if ( $args['random'] ) {
			shuffle( $mutual_ids );
		}

		$mutual_ids = array_slice( $mutual_ids, 0, $args['limit'] );

		// Get full user data.
		$friends = self::get_friend_data( $mutual_ids, $args );

		$result = array(
			'count'   => $total_count,
			'friends' => $friends,
		);

		/**
		 * Filter: bpmff_mutual_friends_data
		 * Modify mutual friends data before return
		 */
		$result = apply_filters( 'bpmff_mutual_friends_data', $result, $user_id, $target_id );

		return $result;
	}

	/**
	 * Get friend IDs for a user with caching
	 *
	 * @param int  $user_id User ID.
	 * @param bool $use_cache Use cached results.
	 * @return array Array of friend IDs.
	 */
	private static function get_friend_ids( $user_id, $use_cache = true ) {
		$user_id = absint( $user_id );

		if ( empty( $user_id ) ) {
			return array();
		}

		$cache_key = "bpmff_friends_{$user_id}";
		$group = 'bpmff';

		// Try to get from cache.
		if ( $use_cache ) {
			$friend_ids = wp_cache_get( $cache_key, $group );
			if ( false !== $friend_ids ) {
				return $friend_ids;
			}
		}

		// Get from BuddyPress API.
		$friend_ids = friends_get_friend_user_ids( $user_id );

		if ( empty( $friend_ids ) ) {
			$friend_ids = array();
		}

		// Cache the result.
		if ( $use_cache ) {
			wp_cache_set( $cache_key, $friend_ids, $group, 3600 );
		}

		return array_map( 'absint', $friend_ids );
	}

	/**
	 * Calculate intersection of friend lists
	 *
	 * @param array $user_friends User's friend IDs.
	 * @param array $target_friends Target's friend IDs.
	 * @return array Array of mutual friend IDs.
	 */
	private static function calculate_mutuals( $user_friends, $target_friends ) {
		if ( empty( $user_friends ) || empty( $target_friends ) ) {
			return array();
		}

		return array_intersect( $user_friends, $target_friends );
	}

	/**
	 * Get full friend data objects
	 *
	 * @param array $friend_ids Array of friend IDs.
	 * @param array $args Query arguments.
	 * @return array Array of friend data.
	 */
	private static function get_friend_data( $friend_ids, $args = array() ) {
		if ( empty( $friend_ids ) ) {
			return array();
		}

		$friends = array();

		foreach ( $friend_ids as $friend_id ) {
			$friend_id = absint( $friend_id );

			$friend_data = array(
				'id'     => $friend_id,
				'name'   => bp_core_get_user_displayname( $friend_id ),
				'avatar' => bp_core_fetch_avatar(
					array(
						'item_id' => $friend_id,
						'type'    => isset( $args['avatar_size'] ) ? $args['avatar_size'] : 'thumb',
						'html'    => false,
					)
				),
				'link'   => bp_core_get_user_domain( $friend_id ),
			);

			/**
			 * Filter: bpmff_friend_data
			 * Modify individual friend data
			 */
			$friend_data = apply_filters( 'bpmff_friend_data', $friend_data, $friend_id );

			$friends[] = $friend_data;
		}

		return $friends;
	}

	/**
	 * Check privacy settings
	 *
	 * @param int $user_id Current user ID.
	 * @param int $target_id Target user ID.
	 * @return bool True if can view friends, false otherwise.
	 */
	private static function can_view_friends( $user_id, $target_id ) {
		$user_id = absint( $user_id );
		$target_id = absint( $target_id );

		// Check if respect privacy is enabled.
		if ( ! bpmff_get_option( 'respect_privacy', 1 ) ) {
			return true;
		}

		// Check if target user's friends list is public.
		$friend_list_privacy = bp_get_user_meta( $target_id, 'bp_profile_completion_status', true );

		// Use BuddyPress privacy check if available.
		if ( function_exists( 'bp_core_get_user_meta' ) ) {
			$privacy = bp_core_get_user_meta( $target_id, 'bp_friends_visibility', true );

			if ( 'private' === $privacy && $user_id !== $target_id ) {
				return false;
			}
		}

		/**
		 * Filter: bpmff_can_view_friends
		 * Check if mutual friends can be viewed
		 */
		return apply_filters( 'bpmff_can_view_friends', true, $user_id, $target_id );
	}

	/**
	 * Invalidate friend cache for a user
	 *
	 * @param int $user_id User ID.
	 */
	public static function invalidate_friend_cache( $user_id ) {
		$user_id = absint( $user_id );

		if ( empty( $user_id ) ) {
			return;
		}

		$cache_key = "bpmff_friends_{$user_id}";
		wp_cache_delete( $cache_key, 'bpmff' );
	}
}
