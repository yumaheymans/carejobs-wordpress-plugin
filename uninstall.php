<?php
/**
 * Removes the plugin's cached job lists (transients) when it is deleted.
 *
 * The plugin stores nothing else.
 *
 * @package CareJobs_Healthcare_Jobs
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Deletes this plugin's transients (and their timeouts) on the current site.
 */
function carejobs_hj_uninstall_delete_transients() {
	global $wpdb;

	$patterns = array(
		$wpdb->esc_like( '_transient_carejobs_hj_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_carejobs_hj_' ) . '%',
	);

	foreach ( $patterns as $pattern ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $pattern ) );
	}
}

if ( is_multisite() ) {
	$carejobs_hj_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $carejobs_hj_site_ids as $carejobs_hj_site_id ) {
		switch_to_blog( (int) $carejobs_hj_site_id );
		carejobs_hj_uninstall_delete_transients();
		restore_current_blog();
	}
} else {
	carejobs_hj_uninstall_delete_transients();
}
