<?php
/**
 * Core class for BuddyPress Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BPMFF_Core class
 *
 * Main plugin orchestrator
 */
class BPMFF_Core {

	/**
	 * Instance of the class
	 *
	 * @var BPMFF_Core
	 */
	private static $instance = null;

	/**
	 * Get instance of the class
	 *
	 * @return BPMFF_Core
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the plugin
	 */
	private function init() {
		// Load required classes.
		$this->load_classes();

		// Define hooks.
		$this->define_hooks();

		// Setup AJAX actions.
		$this->setup_ajax_actions();
	}

	/**
	 * Load required classes
	 */
	private function load_classes() {
		require_once BPMFF_PLUGIN_DIR . 'includes/class-bpmff-query.php';
		require_once BPMFF_PLUGIN_DIR . 'includes/class-bpmff-cache.php';
		require_once BPMFF_PLUGIN_DIR . 'includes/class-bpmff-template.php';
		require_once BPMFF_PLUGIN_DIR . 'includes/class-bpmff-ajax-handler.php';
		require_once BPMFF_PLUGIN_DIR . 'includes/class-bpmff-hooks.php';

		// Load admin classes if in admin.
		if ( is_admin() ) {
			require_once BPMFF_PLUGIN_DIR . 'admin/class-bpmff-admin.php';
			require_once BPMFF_PLUGIN_DIR . 'admin/class-bpmff-settings.php';
		}
	}

	/**
	 * Define hooks
	 */
	private function define_hooks() {
		// Enqueue styles and scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// BuddyPress integration.
		add_action( 'bp_init', array( 'BPMFF_Hooks', 'init' ) );

		// Cache invalidation on friendship changes.
		add_action( 'friends_friendship_accepted', array( $this, 'invalidate_friendship_cache' ) );
		add_action( 'friends_friendship_deleted', array( $this, 'invalidate_friendship_cache' ) );
		add_action( 'friends_friendship_withdrawn', array( $this, 'invalidate_friendship_cache' ) );

		// Cron job for cache cleanup.
		add_action( 'bpmff_cleanup_cache', array( 'BPMFF_Cache', 'cleanup_old_cache' ) );

		// Admin hooks.
		if ( is_admin() ) {
			add_action( 'admin_menu', array( 'BPMFF_Admin', 'add_admin_menu' ) );
			add_action( 'admin_init', array( 'BPMFF_Settings', 'register_settings' ) );
		}
	}

	/**
	 * Setup AJAX actions
	 */
	private function setup_ajax_actions() {
		// Frontend AJAX actions (logged-in users)
		add_action( 'wp_ajax_bpmff_get_mutual_friends', array( 'BPMFF_Ajax_Handler', 'get_mutual_friends' ) );
		add_action( 'wp_ajax_bpmff_get_all_mutual_friends', array( 'BPMFF_Ajax_Handler', 'get_all_mutual_friends' ) );
		
		// Admin AJAX actions (logged-in admins only)
		add_action( 'wp_ajax_bpmff_clear_cache', array( 'BPMFF_Ajax_Handler', 'clear_cache' ) );
	}

	/**
	 * Enqueue public assets
	 */
	public function enqueue_public_assets() {
		// Only load on BuddyPress pages.
		if ( ! $this->is_enabled_page() ) {
			return;
		}

		// Check if Friends component is active.
		if ( ! function_exists( 'bp_is_active' ) ) {
			return;
		}
		
		if ( ! bp_is_active( 'friends' ) ) {
			return;
		}

		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Enqueue styles.
		wp_enqueue_style(
			'bpmff-frontend',
			BPMFF_PLUGIN_URL . "assets/css/frontend{$suffix}.css",
			array(),
			BPMFF_VERSION
		);

		// Enqueue scripts.
		wp_enqueue_script(
			'bpmff-frontend',
			BPMFF_PLUGIN_URL . "assets/js/frontend{$suffix}.js",
			array( 'jquery' ),
			BPMFF_VERSION,
			true
		);

		// Localize script.
		wp_localize_script(
			'bpmff-frontend',
			'bpmffData',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'bpmff_ajax_nonce' ),
				'displayCount'    => bpmff_get_option( 'display_count', 3 ),
				'tooltipPosition' => bpmff_get_option( 'tooltip_position', 'auto' ),
				'animationEffect' => bpmff_get_option( 'animation_effect', 'fade' ),
				'i18n'            => array(
					'loading'         => __( 'Loading...', 'bp-mutual-friends-finder' ),
					'error'           => __( 'Failed to load', 'bp-mutual-friends-finder' ),
					'noMutuals'       => __( 'No mutual friends', 'bp-mutual-friends-finder' ),
					'mutualFriends'   => __( 'Mutual Friends', 'bp-mutual-friends-finder' ),
				),
			)
		);
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_assets() {
		// Only on plugin settings page.
		if ( ! isset( $_GET['page'] ) || 'bpmff-settings' !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Enqueue styles.
		wp_enqueue_style(
			'bpmff-admin',
			BPMFF_PLUGIN_URL . "assets/css/admin{$suffix}.css",
			array(),
			BPMFF_VERSION
		);

		// Enqueue scripts.
		wp_enqueue_script(
			'bpmff-admin',
			BPMFF_PLUGIN_URL . "assets/js/admin{$suffix}.js",
			array( 'jquery' ),
			BPMFF_VERSION,
			true
		);

		// Localize script.
		wp_localize_script(
			'bpmff-admin',
			'bpmffAdminData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'bpmff_admin_nonce' ),
				'i18n'    => array(
					'clearingCache' => __( 'Clearing cache...', 'bp-mutual-friends-finder' ),
					'cacheCleared'  => __( 'Cache cleared successfully!', 'bp-mutual-friends-finder' ),
					'error'         => __( 'An error occurred', 'bp-mutual-friends-finder' ),
				),
			)
		);
	}

	/**
	 * Check if plugin is enabled on page
	 *
	 * @return bool
	 */
	private function is_enabled_page() {
		// Check if BuddyPress is active
		if ( ! function_exists( 'buddypress' ) ) {
			return false;
		}

		// Check if plugin is enabled
		if ( ! bpmff_get_option( 'enabled', 1 ) ) {
			return false;
		}

		// Load on all pages - let the hooks handle filtering
		// This ensures the plugin works even if component detection fails
		return true;
	}

	/**
	 * Invalidate friendship cache
	 *
	 * @param int $user_id User ID.
	 */
	public function invalidate_friendship_cache( $user_id ) {
		$user_id = absint( $user_id );

		if ( empty( $user_id ) ) {
			return;
		}

		BPMFF_Cache::invalidate_cache( $user_id );
	}
}
