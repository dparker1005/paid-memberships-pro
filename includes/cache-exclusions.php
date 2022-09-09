<?php

/**
 * Build a list of URLs that should not be cached.
 *
 * @since TBD
 *
 * @return array List of URLs that should not be cached.
 */
function pmpro_build_cache_exclusions() {
	global $pmpro_pages, $wpdb;

	$cache_exclusions  = array();
	$excluded_post_ids = array();

	// Exclude all PMPro pages.
	if ( ! empty( $pmpro_pages ) && is_array( $pmpro_pages ) ) {
		$excluded_post_ids = array_merge( $excluded_post_ids, array_values( $pmpro_pages ) );
	}

	// Exclude all posts that are restricted by level.
	$restricted_posts = $wpdb->get_col( "SELECT DISTINCT page_id FROM $wpdb->pmpro_memberships_pages" );
	if ( ! empty( $restricted_posts ) && is_array( $restricted_posts ) ) {
		$excluded_post_ids = array_merge( $excluded_post_ids, $restricted_posts );
	}

	// Add excluded posts to the list of URLs that should not be cached.
	if ( ! empty( $excluded_post_ids ) && is_array( $excluded_post_ids ) ) {
		foreach ( $excluded_post_ids as $post_id ) {
			$cache_exclusions[] = str_replace( home_url(), '', get_permalink( $post_id ) );
		}
	}

	// Exclude all categories that are restricted by level.
	$restricted_categories = $wpdb->get_col( "SELECT DISTINCT category_id FROM $wpdb->pmpro_memberships_categories" );
	if ( ! empty( $restricted_categories ) && is_array( $restricted_categories ) ) {
		foreach ( $restricted_categories as $category_id ) {
			$cache_exclusions[] = str_replace(  home_url(), '', get_category_link( $category_id ) );
		}
	}

	// Filter the list of cache exclusions.
	$cache_exclusions = apply_filters( 'pmpro_cache_exclusions', $cache_exclusions );

	// Save cache exclusions to a WP option.
	update_option( 'pmpro_cache_exclusions', $cache_exclusions );

	return $cache_exclusions;
}
add_action( 'save_post', 'pmpro_build_cache_exclusions' );

/**
 * Add "no-cache" headers to pages that should not be cached.
 *
 * @since TBD
 */
function pmpro_add_no_cache_headers() {
	// Get the list of URLs that should not be cached.
	$cache_exclusions = get_option( 'pmpro_cache_exclusions', array() );

	// Check whether the current page should be cached.
	if ( ! empty( $cache_exclusions ) && is_array( $cache_exclusions ) ) {
		foreach ( $cache_exclusions as $excluded_url ) {
			if ( strpos( $_SERVER['REQUEST_URI'], $excluded_url ) === 0 ) {
				// Add "no-cache" headers.
				nocache_headers();
				return;
			}
		}
	}
}
add_action( 'send_headers', 'pmpro_add_no_cache_headers' );

/**
 * Test caching by adding time to the wp hook.
 */
function pmpro_add_time_to_wp_hook() {
	echo time();
}
// add_action( 'wp', 'pmpro_add_time_to_wp_hook' );