<?php
/**
 * Settings handler for BuddyPress Mutual Friends Finder
 *
 * @package BP_Mutual_Friends_Finder
 * @subpackage Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BPMFF_Settings class
 *
 * Handles plugin settings
 */
class BPMFF_Settings {

	/**
	 * Register settings
	 */
	public static function register_settings() {
		// Register setting.
		register_setting(
			'bpmff_settings_group',
			'bpmff_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			)
		);

		// Add settings sections.
		add_settings_section(
			'bpmff_general_section',
			__( 'General Settings', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_general_section' ),
			'bpmff_settings_group'
		);

		add_settings_section(
			'bpmff_display_section',
			__( 'Display Settings', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_display_section' ),
			'bpmff_settings_group'
		);

		add_settings_section(
			'bpmff_performance_section',
			__( 'Performance Settings', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_performance_section' ),
			'bpmff_settings_group'
		);

		add_settings_section(
			'bpmff_privacy_section',
			__( 'Privacy Settings', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_privacy_section' ),
			'bpmff_settings_group'
		);

		// Add settings fields.
		self::add_settings_fields();
	}

	/**
	 * Add settings fields
	 */
	private static function add_settings_fields() {
		// General fields.
		add_settings_field(
			'bpmff_enabled',
			__( 'Enable Plugin', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'bpmff_settings_group',
			'bpmff_general_section',
			array( 'name' => 'enabled' )
		);

		add_settings_field(
			'bpmff_enable_members_dir',
			__( 'Enable on Members Directory', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'bpmff_settings_group',
			'bpmff_general_section',
			array( 'name' => 'enable_members_dir' )
		);

		add_settings_field(
			'bpmff_enable_profile_pages',
			__( 'Enable on Profile Pages', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'bpmff_settings_group',
			'bpmff_general_section',
			array( 'name' => 'enable_profile_pages' )
		);

		// Display fields.
		add_settings_field(
			'bpmff_display_count',
			__( 'Number of Avatars to Display', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_number_field' ),
			'bpmff_settings_group',
			'bpmff_display_section',
			array(
				'name' => 'display_count',
				'min'  => 1,
				'max'  => 5,
			)
		);

		add_settings_field(
			'bpmff_tooltip_position',
			__( 'Tooltip Position', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_select_field' ),
			'bpmff_settings_group',
			'bpmff_display_section',
			array(
				'name'    => 'tooltip_position',
				'options' => array(
					'auto'   => __( 'Auto', 'bp-mutual-friends-finder' ),
					'top'    => __( 'Top', 'bp-mutual-friends-finder' ),
					'bottom' => __( 'Bottom', 'bp-mutual-friends-finder' ),
					'left'   => __( 'Left', 'bp-mutual-friends-finder' ),
					'right'  => __( 'Right', 'bp-mutual-friends-finder' ),
				),
			)
		);

		add_settings_field(
			'bpmff_animation_effect',
			__( 'Animation Effect', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_select_field' ),
			'bpmff_settings_group',
			'bpmff_display_section',
			array(
				'name'    => 'animation_effect',
				'options' => array(
					'fade'  => __( 'Fade', 'bp-mutual-friends-finder' ),
					'slide' => __( 'Slide', 'bp-mutual-friends-finder' ),
					'none'  => __( 'None', 'bp-mutual-friends-finder' ),
				),
			)
		);

		// Performance fields.
		add_settings_field(
			'bpmff_enable_caching',
			__( 'Enable Caching', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'bpmff_settings_group',
			'bpmff_performance_section',
			array( 'name' => 'enable_caching' )
		);

		add_settings_field(
			'bpmff_cache_duration',
			__( 'Cache Duration (seconds)', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_number_field' ),
			'bpmff_settings_group',
			'bpmff_performance_section',
			array(
				'name' => 'cache_duration',
				'min'  => 300,
				'max'  => 86400,
			)
		);

		// Privacy fields.
		add_settings_field(
			'bpmff_respect_privacy',
			__( 'Respect BuddyPress Privacy Settings', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'bpmff_settings_group',
			'bpmff_privacy_section',
			array( 'name' => 'respect_privacy' )
		);

		add_settings_field(
			'bpmff_hide_private_friends',
			__( 'Hide for Private Friend Lists', 'bp-mutual-friends-finder' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'bpmff_settings_group',
			'bpmff_privacy_section',
			array( 'name' => 'hide_private_friends' )
		);
	}

	/**
	 * Render general section
	 */
	public static function render_general_section() {
		echo '<p>' . esc_html__( 'Configure where mutual friends are displayed.', 'bp-mutual-friends-finder' ) . '</p>';
	}

	/**
	 * Render display section
	 */
	public static function render_display_section() {
		echo '<p>' . esc_html__( 'Customize the appearance and behavior of tooltips.', 'bp-mutual-friends-finder' ) . '</p>';
	}

	/**
	 * Render performance section
	 */
	public static function render_performance_section() {
		echo '<p>' . esc_html__( 'Optimize plugin performance with caching.', 'bp-mutual-friends-finder' ) . '</p>';
	}

	/**
	 * Render privacy section
	 */
	public static function render_privacy_section() {
		echo '<p>' . esc_html__( 'Control privacy and visibility of mutual friends data.', 'bp-mutual-friends-finder' ) . '</p>';
	}

	/**
	 * Render checkbox field
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_checkbox_field( $args ) {
		$options = get_option( 'bpmff_settings', array() );
		$value = isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : 0;
		?>
		<input type="checkbox" name="bpmff_settings[<?php echo esc_attr( $args['name'] ); ?>]" value="1" <?php checked( $value, 1 ); ?> />
		<?php
	}

	/**
	 * Render number field
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_number_field( $args ) {
		$options = get_option( 'bpmff_settings', array() );
		$value = isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : 0;
		$min = isset( $args['min'] ) ? $args['min'] : 0;
		$max = isset( $args['max'] ) ? $args['max'] : 100;
		?>
		<input type="number" name="bpmff_settings[<?php echo esc_attr( $args['name'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" />
		<?php
	}

	/**
	 * Render select field
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_select_field( $args ) {
		$options = get_option( 'bpmff_settings', array() );
		$value = isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : '';
		?>
		<select name="bpmff_settings[<?php echo esc_attr( $args['name'] ); ?>]">
			<?php foreach ( $args['options'] as $option_value => $option_label ) : ?>
				<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?>>
					<?php echo esc_html( $option_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $input Settings input.
	 * @return array Sanitized settings.
	 */
	public static function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$sanitized = array();

		foreach ( $input as $key => $value ) {
			$key = sanitize_key( $key );

			switch ( $key ) {
				case 'enabled':
				case 'enable_members_dir':
				case 'enable_profile_pages':
				case 'enable_activity':
				case 'enable_groups':
				case 'show_count_badge':
				case 'enable_caching':
				case 'preload_enabled':
				case 'respect_privacy':
				case 'hide_private_friends':
				case 'debug_mode':
					$sanitized[ $key ] = absint( $value );
					break;

				case 'display_count':
				case 'cache_duration':
				case 'max_cached_entries':
				case 'min_mutual_threshold':
					$sanitized[ $key ] = absint( $value );
					break;

				case 'display_mode':
				case 'avatar_size':
				case 'animation_effect':
				case 'tooltip_position':
					$sanitized[ $key ] = sanitize_text_field( $value );
					break;

				case 'exclude_roles':
					$sanitized[ $key ] = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : array();
					break;

				default:
					$sanitized[ $key ] = sanitize_text_field( $value );
					break;
			}
		}

		return $sanitized;
	}
}
