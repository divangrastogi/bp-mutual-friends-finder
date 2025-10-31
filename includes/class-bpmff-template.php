<?php
/**
 * Template handler for BuddyPress Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BPMFF_Template class
 *
 * Handles template loading and rendering
 */
class BPMFF_Template {

	/**
	 * Load template with theme override support
	 *
	 * @param string $template_name Template name.
	 * @param array  $args Template arguments.
	 * @return string Template HTML.
	 */
	public static function get_template( $template_name, $args = array() ) {
		$template_name = sanitize_file_name( $template_name );

		// Check in theme first.
		$theme_template = self::locate_template( $template_name );

		if ( $theme_template ) {
			return self::render_template( $theme_template, $args );
		}

		// Check in plugin.
		$plugin_template = BPMFF_PLUGIN_DIR . 'templates/' . $template_name . '.php';

		if ( file_exists( $plugin_template ) ) {
			return self::render_template( $plugin_template, $args );
		}

		return '';
	}

	/**
	 * Locate template in theme
	 *
	 * @param string $template_name Template name.
	 * @return string|false Template path or false.
	 */
	private static function locate_template( $template_name ) {
		$template_name = sanitize_file_name( $template_name );

		// Check in theme/buddypress/mutual-friends/
		$theme_template = get_template_directory() . '/buddypress/mutual-friends/' . $template_name . '.php';
		if ( file_exists( $theme_template ) ) {
			return $theme_template;
		}

		// Check in theme/mutual-friends/
		$theme_template = get_template_directory() . '/mutual-friends/' . $template_name . '.php';
		if ( file_exists( $theme_template ) ) {
			return $theme_template;
		}

		// Check in child theme.
		if ( is_child_theme() ) {
			$child_template = get_stylesheet_directory() . '/buddypress/mutual-friends/' . $template_name . '.php';
			if ( file_exists( $child_template ) ) {
				return $child_template;
			}

			$child_template = get_stylesheet_directory() . '/mutual-friends/' . $template_name . '.php';
			if ( file_exists( $child_template ) ) {
				return $child_template;
			}
		}

		return false;
	}

	/**
	 * Render template with arguments
	 *
	 * @param string $template_path Template file path.
	 * @param array  $args Template arguments.
	 * @return string Template HTML.
	 */
	private static function render_template( $template_path, $args = array() ) {
		if ( ! file_exists( $template_path ) ) {
			return '';
		}

		// Extract arguments to variables.
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		// Start output buffering.
		ob_start();

		// Include template.
		include $template_path;

		// Get output.
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Render tooltip HTML
	 *
	 * @param array $data Tooltip data.
	 * @return string Tooltip HTML.
	 */
	public static function render_tooltip( $data ) {
		$html = self::get_template(
			'tooltip',
			array(
				'count'   => isset( $data['count'] ) ? $data['count'] : 0,
				'friends' => isset( $data['friends'] ) ? $data['friends'] : array(),
				'user_id' => isset( $data['user_id'] ) ? $data['user_id'] : 0,
			)
		);

		/**
		 * Filter: bpmff_tooltip_html
		 * Modify tooltip HTML
		 */
		return apply_filters( 'bpmff_tooltip_html', $html, $data );
	}

	/**
	 * Render modal HTML
	 *
	 * @param array $data Modal data.
	 * @return string Modal HTML.
	 */
	public static function render_modal( $data ) {
		$html = self::get_template(
			'modal',
			array(
				'count'   => isset( $data['count'] ) ? $data['count'] : 0,
				'friends' => isset( $data['friends'] ) ? $data['friends'] : array(),
				'user_id' => isset( $data['user_id'] ) ? $data['user_id'] : 0,
			)
		);

		/**
		 * Filter: bpmff_modal_html
		 * Modify modal HTML
		 */
		return apply_filters( 'bpmff_modal_html', $html, $data );
	}

	/**
	 * Render friends list
	 *
	 * @param array $friends Friends data.
	 * @param array $args Render arguments.
	 * @return string Friends list HTML.
	 */
	public static function render_friends_list( $friends, $args = array() ) {
		$html = self::get_template(
			'mutual-list',
			array(
				'friends' => $friends,
				'args'    => $args,
			)
		);

		/**
		 * Filter: bpmff_friends_list_html
		 * Modify friends list HTML
		 */
		return apply_filters( 'bpmff_friends_list_html', $html, $friends, $args );
	}
}
