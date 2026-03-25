<?php
/**
 * Plugin Name: Clean-Sweep
 * Plugin URI:  https://afashah.com
 * Description: A modular, high-performance database utility to audit and optimize WordPress.
 * Version:     0.2
 * Author:      Ashar Fazail
 * Author URI:  https://afashah.com
 * License:     GPL2
 * * @package CleanSweep
 */

// Prevent direct file access for security
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define Core Constants
 */
define( 'CS_VERSION', '0.2' );
define( 'CS_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Requirements: Load Modular Classes
 */
require_once CS_PATH . 'includes/class-clean-sweep-engine.php';
require_once CS_PATH . 'includes/class-clean-sweep-admin.php';

/**
 * Main Execution Entry Point.
 * Instantiates the engine and passes it to the admin handler (Dependency Injection).
 */
function run_clean_sweep() {
	$engine = new CleanSweep_Engine();
	$admin  = new CleanSweep_Admin( $engine );
}
run_clean_sweep();

/**
 * Activation Logic: Flushes cache to ensure the UI updates immediately.
 */
register_activation_hook( __FILE__, [ 'CleanSweep_Admin', 'activate' ] );