<?php
/**
 * AJAX handler for BuddyPress Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BPMFF_Ajax_Handler class
 *
 * Handles AJAX requests for mutual friends
 */
class BPMFF_Ajax_Handler {

	/**
	 * Get mutual friends via AJAX
	 */
	public static function get_mutual_friends() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'bpmff_ajax_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed', 'bp-mutual-friends-finder' ),
				)
			);
		}

		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'message' => __( 'You must be logged in', 'bp-mutual-friends-finder' ),
				)
			);
		}

		// Validate and sanitize input.
		$target_user_id = isset( $_POST['target_user_id'] ) ? absint( $_POST['target_user_id'] ) : 0;

		if ( empty( $target_user_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid user ID', 'bp-mutual-friends-finder' ),
				)
			);
		}

		// Verify user exists.
		$user = get_userdata( $target_user_id );
		if ( ! $user ) {
			wp_send_json_error(
				array(
					'message' => __( 'User not found', 'bp-mutual-friends-finder' ),
				)
			);
		}

		// Rate limiting.
		$transient_key = 'bpmff_rate_limit_' . get_current_user_id();
		$request_count = get_transient( $transient_key );

		if ( $request_count && $request_count > 30 ) {
			wp_send_json_error(
				array(
					'message' => __( 'Too many requests. Please try again later.', 'bp-mutual-friends-finder' ),
				)
			);
		}

		set_transient( $transient_key, ( $request_count ? $request_count + 1 : 1 ), 60 );

		// Get display count.
		$display_count = isset( $_POST['display_count'] ) ? absint( $_POST['display_count'] ) : 3;
		$display_count = max( 1, min( 5, $display_count ) );

		// Get format.
		$format = isset( $_POST['format'] ) ? sanitize_text_field( wp_unslash( $_POST['format'] ) ) : 'tooltip';
		$format = in_array( $format, array( 'tooltip', 'modal' ), true ) ? $format : 'tooltip';

		// Get mutual friends.
		$mutuals = BPMFF_Query::get_mutual_friends(
			get_current_user_id(),
			$target_user_id,
			array(
				'limit'     => $display_count,
				'use_cache' => true,
				'random'    => true,
			)
		);

		// Prepare response data.
		$response_data = array(
			'count'   => absint( $mutuals['count'] ),
			'mutuals' => array_map(
				function( $friend ) {
					return array(
						'id'     => absint( $friend['id'] ),
						'name'   => sanitize_text_field( $friend['name'] ),
						'avatar' => esc_url( $friend['avatar'] ),
						'link'   => esc_url( $friend['link'] ),
					);
				},
				$mutuals['friends']
			),
		);

		// Render template.
		if ( 'tooltip' === $format ) {
			$response_data['html'] = BPMFF_Template::render_tooltip(
				array(
					'count'   => $mutuals['count'],
					'friends' => $mutuals['friends'],
					'user_id' => $target_user_id,
				)
			);
		} else {
			$response_data['html'] = BPMFF_Template::render_modal(
				array(
					'count'   => $mutuals['count'],
					'friends' => $mutuals['friends'],
					'user_id' => $target_user_id,
				)
			);
		}

		// Cache the result.
		BPMFF_Cache::set_cache( get_current_user_id(), $target_user_id, $response_data );

		wp_send_json_success( $response_data );
	}

	/**
	 * Get all mutual friends via AJAX (paginated)
	 */
	public static function get_all_mutual_friends() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'bpmff_ajax_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed', 'bp-mutual-friends-finder' ),
				)
			);
		}

		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'message' => __( 'You must be logged in', 'bp-mutual-friends-finder' ),
				)
			);
		}

		// Validate input.
		$target_user_id = isset( $_POST['target_user_id'] ) ? absint( $_POST['target_user_id'] ) : 0;
		$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

		if ( empty( $target_user_id ) || $page < 1 ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid parameters', 'bp-mutual-friends-finder' ),
				)
			);
		}

		// Get mutual friends (all).
		$mutuals = BPMFF_Query::get_mutual_friends(
			get_current_user_id(),
			$target_user_id,
			array(
				'limit'     => 999,
				'use_cache' => true,
				'random'    => false,
			)
		);

		// Paginate results.
		$per_page = 20;
		$total_pages = ceil( $mutuals['count'] / $per_page );
		$offset = ( $page - 1 ) * $per_page;
		$paginated_friends = array_slice( $mutuals['friends'], $offset, $per_page );

		// Prepare response.
		$response_data = array(
			'count'       => absint( $mutuals['count'] ),
			'page'        => absint( $page ),
			'total_pages' => absint( $total_pages ),
			'friends'     => array_map(
				function( $friend ) {
					return array(
						'id'     => absint( $friend['id'] ),
						'name'   => sanitize_text_field( $friend['name'] ),
						'avatar' => esc_url( $friend['avatar'] ),
						'link'   => esc_url( $friend['link'] ),
					);
				},
				$paginated_friends
			),
			'html'        => BPMFF_Template::render_friends_list( $paginated_friends ),
		);

		wp_send_json_success( $response_data );
	}

	/**
	 * Clear cache via AJAX
	 */
	public static function clear_cache() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'bpmff_admin_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed', 'bp-mutual-friends-finder' ),
				)
			);
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action', 'bp-mutual-friends-finder' ),
				)
			);
		}

		// Clear cache.
		BPMFF_Cache::clear_all_cache();

		wp_send_json_success(
			array(
				'message' => __( 'Cache cleared successfully', 'bp-mutual-friends-finder' ),
			)
		);
	}
}
