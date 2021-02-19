<?php

function pmpro_get_logs() {
	static $logs;
	if ( empty( $logs ) ) {
		$default_logs = array(
			
		);
		$logs = apply_filters( 'pmpro_logs', $default_logs );
	}
	return $logs;
}

function pmpro_get_settings_for_all_logs( $force_update = false ) {
	static $all_log_settings;
	if ( empty( $all_log_settings ) || $force_update ) {
		$all_log_settings = get_option( 'pmpro_logs_settings', array() );
	}
	return $all_log_settings;
}

function pmpro_get_settings_for_log( $log_slug ) {
	// Get current data from database.
	$all_log_settings = pmpro_get_settings_for_all_logs();
	$log_settings     = array();
	if ( array_key_exists( $log_slug, $all_log_settings ) ) {
		$log_settings = array_merge( $all_log_settings[ $log_slug ], $log_settings );
	}

	// Merge settings with defaults set for log.
	$all_logs = pmpro_get_logs();
	if ( array_key_exists( $log_slug, $all_logs ) ) {
		$log_settings = array_merge( $all_logs[ $log_slug ]['defaults'], $log_settings  );
	}

	// Merge settings with global defults.
	$global_defaults = array(
		'defaults'   => array(
			'enabled'     => false,
			'max_entries' => 0,
		),
	);
	$log_settings = array_merge( $all_logs[ $log_slug ]['defaults'], $log_settings );
	return $log_settings;
}

function pmpro_set_settings_for_log( $log_slug, $settings ) {
	$all_logs                      = pmpro_get_logs();
	$all_log_settings              = pmpro_get_settings_for_all_logs();
	$current_log_settings          = pmpro_get_settings_for_log( $log_slug );
	$all_log_settings[ $log_slug ] = array_merge( $current_log_settings, $settings );
	update_option( 'pmpro_logs_settings', $all_log_settings );
	pmpro_get_settings_for_all_logs( true );
}

/**
 * AJAX callback to get log entries.
 */
function pmpro_wp_ajax_pmpro_log_get_entries_json() {
	$log_slug = sanitize_text_field( $_REQUEST['log_slug'] );
	$log = new PMPro_Log( $log_slug );
	echo $log->get_entries_json();
	exit;
}
add_action( 'wp_ajax_pmpro_log_get_entries_json', 'pmpro_wp_ajax_pmpro_log_get_entries_json' );

/**
 * AJAX callback to clear a log.
 */
function pmpro_wp_ajax_pmpro_log_clear() {
	$log_slug = sanitize_text_field( $_REQUEST['log_slug'] );
	$log = new PMPro_Log( $log_slug );
	$log->clear_log();
	echo $log->get_entries_json();
	exit;
}
add_action( 'wp_ajax_pmpro_log_clear', 'pmpro_wp_ajax_pmpro_log_clear' );