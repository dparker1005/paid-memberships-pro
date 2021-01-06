<?php
/**
 * Deprecated hooks, filters and functions
 *
 * @since  2.0
 */

/**
 * Check for deprecated filters.
 */
function pmpro_init_check_for_deprecated_filters() {
	global $wp_filter;
	
	$pmpro_map_deprecated_filters = array(
		'pmpro_getfile_extension_blocklist'    => 'pmpro_getfile_extension_blacklist',
	);
	
	foreach ( $pmpro_map_deprecated_filters as $new => $old ) {
		if ( has_filter( $old ) ) {
			/* translators: 1: the old hook name, 2: the new or replacement hook name */
			trigger_error( sprintf( esc_html__( 'The %1$s hook has been deprecated in Paid Memberships Pro. Please use the %2$s hook instead.', 'paid-memberships-pro' ), $old, $new ) );
			
			// Add filters back using the new tag.
			foreach( $wp_filter[$old]->callbacks as $priority => $callbacks ) {
				foreach( $callbacks as $callback ) {
					add_filter( $new, $callback['function'], $priority, $callback['accepted_args'] ); 
				}
			}
		}
	}
}
add_action( 'init', 'pmpro_init_check_for_deprecated_filters', 99 );

/**
 * Previously used function for class definitions for input fields to see if there was an error.
 *
 * To filter field values, we now recommend using the `pmpro_element_class` filter.
 *
 */
function pmpro_getClassForField( $field ) {
	pmpro_get_element_class( '', $field );
}

/**
 * Redirect some old menu items to their new location
 */
function pmpro_admin_init_redirect_old_menu_items() {	
	if ( is_admin()
		&& ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'pmpro_license_settings'
		&& basename( $_SERVER['SCRIPT_NAME'] ) == 'options-general.php' ) {
		wp_safe_redirect( admin_url( 'admin.php?page=pmpro-license' ) );
		exit;
	}
}
add_action( 'init', 'pmpro_admin_init_redirect_old_menu_items' );

/**
 * Create, add, remove or updates the membership level of the given user to the given level.
 *
 * $level may either be the ID or name of the desired membership_level.
 * If $user_id is omitted, the value will be retrieved from $current_user.
 *
 * @param int    $level ID of level to set as new level, use 0 to cancel membership
 * @param int    $user_id ID of the user to change levels for
 * @param string $old_level_status The status to set for the row in the memberships users table. (e.g. inactive, cancelled, admin_cancelled, expired) Defaults to 'inactive'.
 * $param int $cancel_level If set cancel just this one level instead of all active levels (to support Multiple Memberships per User)
 *
 * Return values:
 *      Success returns boolean true.
 *      Failure returns boolean false.
 */
function pmpro_changeMembershipLevel( $level, $user_id = null, $old_level_status = 'inactive', $cancel_level = null ) {
	if ( empty( $level ) ) {
		$return = pmpro_cancel_all_membership_levels( $user_id, $old_level_status );
	} else {
		if ( ! empty( $cancel_level ) ) {
			pmpro_cancel_membership_levels( $cancel_level, $user_id, $old_level_status );
		}
		$return = pmpro_add_membership_level( $level, $user_id );
	}
	return $return;
}

function pmpro_cancelMembershipLevel( $cancel_level, $user_id = null, $old_level_status = 'inactive' ) {
	return pmpro_cancel_membership_level( $cancel_level, $user_id, $old_level_status );
}