<?php
/**
 * Plugin Activation Tests
 *
 * @package BP_Mutual_Friends_Finder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test plugin activation and basic functionality
 */
class BPMFF_Activation_Test {

	/**
	 * Test that plugin file exists
	 */
	public static function test_plugin_file_exists() {
		$plugin_file = BPMFF_PLUGIN_DIR . 'bp-mutual-friends-finder.php';
		return file_exists( $plugin_file );
	}

	/**
	 * Test that all required classes can be loaded
	 */
	public static function test_classes_loadable() {
		$classes = array(
			'BPMFF_Activator',
			'BPMFF_Deactivator',
			'BPMFF_Core',
			'BPMFF_Query',
			'BPMFF_Cache',
			'BPMFF_Template',
			'BPMFF_Ajax_Handler',
			'BPMFF_Hooks',
		);

		foreach ( $classes as $class ) {
			if ( ! class_exists( $class ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Test that core functions exist
	 */
	public static function test_core_functions_exist() {
		$functions = array(
			'bpmff_get_option',
			'bpmff_update_option',
			'bpmff_delete_option',
		);

		foreach ( $functions as $function ) {
			if ( ! function_exists( $function ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Test that templates exist
	 */
	public static function test_templates_exist() {
		$templates = array(
			'tooltip.php',
			'modal.php',
			'mutual-list.php',
		);

		foreach ( $templates as $template ) {
			$path = BPMFF_PLUGIN_DIR . 'templates/' . $template;
			if ( ! file_exists( $path ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Test that assets exist
	 */
	public static function test_assets_exist() {
		$assets = array(
			'assets/css/frontend.css',
			'assets/css/admin.css',
			'assets/js/frontend.js',
			'assets/js/admin.js',
		);

		foreach ( $assets as $asset ) {
			$path = BPMFF_PLUGIN_DIR . $asset;
			if ( ! file_exists( $path ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Run all tests
	 */
	public static function run_all_tests() {
		$tests = array(
			'Plugin file exists' => self::test_plugin_file_exists(),
			'Classes loadable' => self::test_classes_loadable(),
			'Core functions exist' => self::test_core_functions_exist(),
			'Templates exist' => self::test_templates_exist(),
			'Assets exist' => self::test_assets_exist(),
		);

		$results = array();
		foreach ( $tests as $test_name => $result ) {
			$results[ $test_name ] = $result ? 'PASS' : 'FAIL';
		}

		return $results;
	}
}

// Run tests if this file is accessed directly.
if ( basename( $_SERVER['PHP_SELF'] ) === 'test-plugin-activation.php' ) {
	$results = BPMFF_Activation_Test::run_all_tests();

	echo '<h2>BuddyPress Mutual Friends Finder - Activation Tests</h2>';
	echo '<table border="1" cellpadding="10">';
	echo '<tr><th>Test</th><th>Result</th></tr>';

	foreach ( $results as $test_name => $result ) {
		$color = 'PASS' === $result ? '#90EE90' : '#FFB6C6';
		echo '<tr><td>' . esc_html( $test_name ) . '</td><td style="background-color: ' . esc_attr( $color ) . ';">' . esc_html( $result ) . '</td></tr>';
	}

	echo '</table>';
}
