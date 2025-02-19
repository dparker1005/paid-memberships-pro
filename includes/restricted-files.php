<?php

/**
 * Set up restriction directories.
 *
 * @since TBD
 */
function pmpro_restricted_files_set_up_directories() {
	// Create wp-content/uploads/pmpro folder if it doesn't exist.
	$restricted_file_directory = pmpro_get_restricted_file_path();
	if ( ! file_exists( $restricted_file_directory ) ) {
		wp_mkdir_p( $restricted_file_directory );
	}

	// Create/update .htaccess file for apache servers.
	$htaccess = '<FilesMatch ".*">' . "\n" .
		'  <IfModule !mod_authz_core.c>' . "\n" .
		'    Order allow,deny' . "\n" .
		'    Deny from all' . "\n" .
		'  </IfModule>' . "\n" .
		'  <IfModule mod_authz_core.c>' . "\n" .
		'    Require all denied' . "\n" .
		'  </IfModule>' . "\n" .
		'</FilesMatch>';
	file_put_contents( trailingslashit( $restricted_file_directory ) . '.htaccess', $htaccess );
}
add_action( 'admin_init', 'pmpro_restricted_files_set_up_directories' );

/**
 * If a restricted file is requested, check if the user has access.
 * If so, serve the file.
 *
 * @since TBD
 */
function pmpro_restricted_files_check_request() {
	if ( empty( $_REQUEST['pmpro_restricted_file'] ) || empty( $_REQUEST['pmpro_restricted_file_dir'] ) ) {
		return;
	}

	// Get the requested file.
	$file = basename( sanitize_text_field( wp_unslash( $_REQUEST['pmpro_restricted_file'] ) ) );
	$file_dir = basename( sanitize_text_field( wp_unslash( $_REQUEST['pmpro_restricted_file_dir'] ) ) );

	/**
	 * Filter to check if a user can access a restricted file.
	 *
	 * @since TBD
	 *
	 * @param bool   $can_access Whether the user can access the file.
	 * @param string $file_dir   Directory of the restricted file.
	 * @param string $file       Name of the restricted file.
	 */
	if ( empty( apply_filters( 'pmpro_can_access_restricted_file', false, $file_dir, $file ) ) ) {
		wp_die( __( 'You do not have permission to access this file.', 'paid-memberships-pro' ), 403 );
	}

	// Serve the file.
	$file_path = pmpro_get_restricted_file_path( $file_dir, $file );
	if ( file_exists( $file_path ) ) {
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$content_type = finfo_file( $finfo, $file_path );
		finfo_close( $finfo );
		header( 'Content-Type: ' . $content_type );
		header( 'Content-Disposition: attachment; filename="' . $file . '"' );
		readfile( $file_path );
		exit;
	} else {
		wp_die( __( 'File not found.', 'paid-memberships-pro' ), 404 );
	}
}
add_action( 'init', 'pmpro_restricted_files_check_request' );

/**
 * Add a filter to allow access to restricted files for core use-cases.
 *
 * @since TBD
 *
 * @param  bool   $can_access Whether the user can access the file.
 * @param  string $file_dir   Directory of the restricted file.
 * @return bool               Whether the user can access the file.
 */
function pmpro_can_access_restricted_file( $can_access, $file_dir ) {
	if ( 'logs' === $file_dir ) {
		return current_user_can( 'manage_options' );
	}

	return $can_access;
}
add_filter( 'pmpro_can_access_restricted_file', 'pmpro_can_access_restricted_file', 10, 2 );

/**
 * Get the path to a restricted file.
 *
 * @since TBD
 *
 * @param  string $file_dir Directory of the restricted file.
 * @param  string $file     Name of the restricted file.
 * @return string           Path to the restricted file.
 */
function pmpro_get_restricted_file_path( $file_dir = '', $file = '' ) {
	$uploads_dir = trailingslashit( wp_upload_dir()['basedir'] );
	$restricted_file_path = $uploads_dir . 'pmpro/';
	// Get the directory path.
	if ( ! empty( $file_dir ) ) {
		$restricted_file_path .= $file_dir . '/';

		// Create the directory if it doesn't exist.
		if ( ! file_exists( $restricted_file_path ) ) {
			wp_mkdir_p( $restricted_file_path );
		}

		// Get the file path.
		if ( ! empty( $file ) ) {
			$restricted_file_path .= $file;
		}
	}
	return $restricted_file_path;
}