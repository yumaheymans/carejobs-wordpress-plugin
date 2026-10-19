<?php
/**
 * Plugin Name:       CareJobs Healthcare Jobs
 * Plugin URI:        https://carejobs.now/apps#wordpress
 * Description:       Show a live list of open US healthcare jobs, with posted pay, filtered by kind of job, state and keywords. Free, no account, no API key.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            CareJobs.Now
 * Author URI:        https://carejobs.now
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       carejobs-healthcare-jobs
 *
 * @package CareJobs_Healthcare_Jobs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CAREJOBS_HJ_VERSION', '1.0.0' );
define( 'CAREJOBS_HJ_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAREJOBS_HJ_URL', plugin_dir_url( __FILE__ ) );

// The public CareJobs.Now API (no key, no account). See readme.txt, "External services".
define( 'CAREJOBS_HJ_ENDPOINT', 'https://carejobs.now/api/mcp' );
define( 'CAREJOBS_HJ_CACHE_PREFIX', 'carejobs_hj_v1_' );

require_once CAREJOBS_HJ_DIR . 'includes/data.php';
require_once CAREJOBS_HJ_DIR . 'includes/api.php';
require_once CAREJOBS_HJ_DIR . 'includes/render.php';

/**
 * Registers the stylesheet and editor script, then the block.
 *
 * The stylesheet is only enqueued when a shortcode, block or widget renders.
 */
function carejobs_hj_init() {
	wp_register_style(
		'carejobs-healthcare-jobs',
		CAREJOBS_HJ_URL . 'assets/css/carejobs-healthcare-jobs.css',
		array(),
		CAREJOBS_HJ_VERSION
	);

	wp_register_script(
		'carejobs-hj-block-editor',
		CAREJOBS_HJ_URL . 'blocks/carejobs-jobs/edit.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
		CAREJOBS_HJ_VERSION,
		true
	);

	wp_add_inline_script(
		'carejobs-hj-block-editor',
		'window.carejobsHjEditor = ' . wp_json_encode( carejobs_hj_editor_data(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';',
		'before'
	);

	wp_set_script_translations( 'carejobs-hj-block-editor', 'carejobs-healthcare-jobs' );

	register_block_type(
		CAREJOBS_HJ_DIR . 'blocks/carejobs-jobs',
		array(
			'render_callback' => 'carejobs_hj_render_block',
		)
	);
}
add_action( 'init', 'carejobs_hj_init' );

add_shortcode( 'carejobs_jobs', 'carejobs_hj_shortcode' );

/**
 * Registers the classic widget.
 */
function carejobs_hj_register_widget() {
	require_once CAREJOBS_HJ_DIR . 'includes/class-carejobs-hj-widget.php';
	register_widget( 'CareJobs_HJ_Widget' );
}
add_action( 'widgets_init', 'carejobs_hj_register_widget' );
