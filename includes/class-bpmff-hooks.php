<?php
/**
 * BuddyPress integration hooks for Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BPMFF_Hooks class
 *
 * Integrates with BuddyPress hooks and filters
 */
class BPMFF_Hooks {

	/**
	 * Initialize hooks
	 */
	public static function init() {
		// Remove the permalink filter approach - it doesn't work
		// add_filter( 'bp_get_member_permalink', array( __CLASS__, 'add_member_link_data' ), 10, 2 );

		// Use JavaScript to add data attributes after page load
		add_action( 'wp_footer', array( __CLASS__, 'add_data_attributes_script' ) );

		// Add mutual friends indicator to member cards.
		add_action( 'bp_directory_members_item', array( __CLASS__, 'add_member_indicator' ) );

		// Add mutual friends to profile header.
		add_action( 'bp_before_member_header_meta', array( __CLASS__, 'add_profile_indicator' ) );
		
	}

	/**
	 * Add data attributes to member links using JavaScript
	 */
	public static function add_data_attributes_script() {
		// Only add on BuddyPress pages
		if ( ! function_exists( 'buddypress' ) ) {
			return;
		}
		?>
		<script>
			jQuery( document ).ready( function( $ ) {
				// Function to add data attributes to member links
				function addDataAttributesToMemberLinks() {
					// Find all links with /members/ in href that don't already have data-bpmff-user
					var memberLinks = $( 'a[href*="/members/"]:not([data-bpmff-user])' );

					memberLinks.each( function() {
						var link = $( this );
						var href = link.attr( 'href' );

						// Skip if href doesn't contain /members/ or contains other patterns
						if ( href.indexOf( '/members/' ) === -1 ) {
							return;
						}

						// Extract user ID from data-bp-item-id attribute of closest member item
						var memberItem = link.closest( '[data-bp-item-id]' );
						var memberId = memberItem.attr( 'data-bp-item-id' );

						// If no member item found, try to extract from URL
						if ( ! memberId ) {
							var urlParts = href.split( '/members/' );
							if ( urlParts[1] ) {
								var username = urlParts[1].split( '/' )[0];
								// For now, skip if we can't get member ID from data attribute
								return;
							}
						}

						if ( memberId && !link.attr( 'data-bpmff-user' ) ) {
							link.attr( 'data-bpmff-user', memberId );
						}
					} );

					// IMPORTANT: Re-bind events after adding data attributes
					bindEvents();
				}

				// Function to bind events (needs to be available globally)
				function bindEvents() {
					// Remove existing event bindings to avoid duplicates
					$(document).off('mouseenter', '[data-bpmff-user]');

					// Hover events on member links
					$(document).on('mouseenter', '[data-bpmff-user]',
						function(e) {
							var event = new jQuery.Event('mouseenter', { currentTarget: e.currentTarget });
							BPMFF.handleMemberHover.call(BPMFF, event);
						}
					);

					// Modal triggers
					$(document).on('click', '.bpmff-view-all',
						function(e) {
							var event = new jQuery.Event('click', { currentTarget: e.currentTarget });
							BPMFF.openModal.call(BPMFF, event);
						}
					);

					// Click events for mutual friends summary in member directory
					$(document).on('click', '.bpmff-tooltip-trigger',
						function(e) {
							e.preventDefault();
							var $trigger = $(this);
							var $container = $trigger.closest('[data-bpmff-user]');
							var userId = $container.data('bpmff-user');

							if (!userId) {
								return;
							}

							// Load mutual friends data for tooltip
							BPMFF.loadMutualFriendsForTooltip(userId, $trigger);
						}
					);

					// Modal close
					$(document).on('click', '.bpmff-modal-close',
						function(e) {
							BPMFF.closeModal.call(BPMFF);
						}
					);

					// Close modal on overlay click
					$(document).on('click', '.bpmff-modal-overlay',
						function(e) {
							var event = new jQuery.Event('click', { currentTarget: e.currentTarget });
							BPMFF.closeModalOnOverlay.call(BPMFF, event);
						}
					);
				}

				// Make bindEvents available globally for debugging
				window.bindBPMFFEvents = bindEvents;

				// Run immediately
				addDataAttributesToMemberLinks();

				// Set up MutationObserver to watch for dynamically added content
				var observer = new MutationObserver( function( mutations ) {
					var shouldCheck = false;

					mutations.forEach( function( mutation ) {
						// Check if new nodes were added
						if ( mutation.type === 'childList' && mutation.addedNodes.length > 0 ) {
							// Check if any added nodes contain member-related content
							for ( var i = 0; i < mutation.addedNodes.length; i++ ) {
								var node = mutation.addedNodes[i];
								if ( node.nodeType === 1 && ( // Element node
									node.matches && (
										node.matches( '[data-bp-item-id]' ) ||
										node.querySelector && node.querySelector( '[data-bp-item-id]' ) ||
										node.matches( 'a[href*="/members/"]' ) ||
										(node.querySelector && node.querySelector( 'a[href*="/members/"]' ))
									)
								) ) {
									shouldCheck = true;
									break;
								}
							}
						}
					} );

					if ( shouldCheck ) {
						setTimeout( addDataAttributesToMemberLinks, 100 ); // Small delay to ensure DOM is ready
					}
				} );

				// Start observing
				var targetNode = document.body;
				var config = { childList: true, subtree: true };
				observer.observe( targetNode, config );
			} );
		</script>
		<?php
	}

	/**
	 * Add mutual friends indicator to member cards
	 */
	public static function add_member_indicator() {
		// Only on members directory.
		if ( ! function_exists( 'bp_is_members_directory' ) || ! bp_is_members_directory() ) {
			return;
		}

		// Check if enabled on members directory.
		if ( ! bpmff_get_option( 'enable_members_dir', 1 ) ) {
			return;
		}

		global $members_template;

		if ( empty( $members_template->member ) ) {
			return;
		}

		$member = $members_template->member;
		$current_user_id = get_current_user_id();

		// Skip own profile.
		if ( $member->id === $current_user_id ) {
			return;
		}

		// Get mutual friends count.
		$mutuals = BPMFF_Query::get_mutual_friends(
			$current_user_id,
			$member->id,
			array(
				'limit'     => 3,
				'use_cache' => true,
			)
		);

		if ( $mutuals['count'] > 0 ) {
			// Display mutual friends underneath username
			echo '<div class="bpmff-member-mutual-friends" data-bpmff-user="' . absint( $member->id ) . '">';
			echo '<div class="bpmff-mutual-summary bpmff-tooltip-trigger">';
			echo '<span class="bpmff-mutual-count">' . absint( $mutuals['count'] ) . ' ' . esc_html__( 'mutual friends', 'bp-mutual-friends-finder' ) . '</span>';
			echo '<span class="bpmff-tooltip-arrow">▼</span>';
			echo '</div>';
			echo '</div>';
		}
	}

	/**
	 * Add mutual friends to profile header
	 */
	public static function add_profile_indicator() {
		// Only on profile pages.
		if ( ! function_exists( 'bp_is_user' ) || ! bp_is_user() ) {
			return;
		}

		// Check if enabled on profile pages.
		if ( ! bpmff_get_option( 'enable_profile_pages', 1 ) ) {
			return;
		}

		$current_user_id = get_current_user_id();
		
		// Check if bp_displayed_user_id function exists.
		if ( ! function_exists( 'bp_displayed_user_id' ) ) {
			return;
		}
		
		$profile_user_id = bp_displayed_user_id();

		// Skip own profile.
		if ( $profile_user_id === $current_user_id ) {
			return;
		}

		// Get mutual friends.
		$mutuals = BPMFF_Query::get_mutual_friends(
			$current_user_id,
			$profile_user_id,
			array(
				'limit'     => 5,
				'use_cache' => true,
			)
		);

		if ( $mutuals['count'] > 0 ) {
			// Display mutual friends under username
			echo '<div class="bpmff-profile-mutual-friends" data-bpmff-user="' . absint( $profile_user_id ) . '">';
			echo '<div class="bpmff-mutual-summary bpmff-tooltip-trigger">';
			echo '<span class="bpmff-mutual-count">' . absint( $mutuals['count'] ) . ' ' . esc_html__( 'mutual friends', 'bp-mutual-friends-finder' ) . '</span>';
			echo '<span class="bpmff-tooltip-arrow">▼</span>';
			echo '</div>';
			echo '</div>';
		}
	}
}
