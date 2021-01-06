<?php
/**
 * Load deprecated hooks, filters and functions from MMPU merge.
 *
 * @since  3.0
 */
function pmpro_load_deprecated_mmpu_functions() {
	if ( defined( 'PMPROMMPU_VER' ) && version_compare( PMPROMMPU_VER, '0.7', '<=' ) ) {
		// MMPU will load these functions, but we should unhook some since
		// PMPro has the same functionality.
		remove_action( 'pmpro_membership_level_after_other_settings', 'pmprommpu_add_group_to_level_options' );
		remove_action( 'pmpro_save_membership_level', 'pmprommpu_save_group_on_level_edit' );
		remove_action( 'pmpro_delete_membership_level', 'pmprommpu_on_del_level' );
		remove_action( 'wp_ajax_pmprommpu_add_group', 'pmprommpu_add_group' );
		remove_action( 'wp_ajax_pmprommpu_edit_group', 'pmprommpu_edit_group' );
		remove_action( 'wp_ajax_pmprommpu_del_group', 'pmprommpu_del_group' );
		remove_action( 'wp_ajax_pmprommpu_update_level_and_group_order', 'pmprommpu_update_level_and_group_order' );
		remove_filter( 'pmpro_members_list_user', 'pmprommpu_show_multiple_levels_in_memlist', 10, 1 );
		remove_filter( 'pmpro_memberslist_extra_cols', 'pmprommpu_memberslist_extra_cols' );
		remove_filter( 'pmpro_manage_memberslist_custom_column', 'pmprommpu_fill_memberslist_col_member_number', 10, 2 );
		remove_filter( 'init', 'pmprommpu_init_profile_hooks' );
		return;
	}

	//set up wpdb for the tables we need
	function pmprommpu_setDBTables() {
		// Moved to pmpro_setDBTables function.
	}

	// Return an array of all level groups, with the key being the level group id.
	// Groups have an id, name, displayorder, and flag for allow_multiple_selections
	function pmprommpu_get_groups() {
		return pmpro_get_level_groups();
	}

	// Given a name and a true/false flag about whether it allows multiple selections, create a level group.
	function pmprommpu_create_group($inname, $inallowmult = true) {
		return pmpro_create_level_group( $inname, $inallowmult );
	}

	// Set (or move) a membership level into a level group
	function pmprommpu_set_level_for_group($levelid, $groupid) {
		return pmpro_set_level_for_group( $levelid, $groupid );
	}

	// Return an array of the groups and levels in display order - keys are group ID, and values are their levels, in display order
	function pmprommpu_get_levels_and_groups_in_order($includehidden = false) {
		return pmpro_get_levels_and_groups_in_order();
	}

	/**
	 * Checks if a user has any membership level within a certain group
	 */
	function pmprommpu_hasMembershipGroup($groups = NULL, $user_id = NULL) {
		return pmpro_has_membership_group( $groups, $user_id );
	}

	// Given a level ID, this function returns the group ID it belongs to.
	function pmprommpu_get_group_for_level($levelid) {
		return pmpro_get_group_for_level( $levelid );
	}

	// Given a level ID and new group ID, this function sets the group ID for a level. Returns a success flag (true/false).
	function pmprommpu_set_group_for_level($levelid, $groupid) {
		pmpro_set_group_for_level( $levelid, $groupid );
	}

	// Called by AJAX to add a group from the admin-side Membership Levels and Groups page. Incoming parms are name and mult (can users sign up for multiple levels in this group - 0/1).
	function pmprommpu_add_group() {
		pmprom_add_level_group();
	}

	// Called by AJAX to edit a group from the admin-side Membership Levels and Groups page. Incoming parms are group (the ID #), name and mult (can users sign up for multiple levels in this group - 0/1).
	function pmprommpu_edit_group() {
		pmpro_edit_level_group();
	}

	// Called by AJAX to delete an empty group from the admin-side Membership Levels and Groups page. Incoming parm is group (group ID #).
	function pmprommpu_del_group() {
		pmpro_del_level_group();
	}

	// Called by AJAX from the admin-facing levels page when the rows are reordered. Incoming parm (neworder) is an ordered array of objects (with two parms, group (scalar ID) and levels (ordered array of scalar level IDs))
	function pmprommpu_update_level_and_group_order() {
		pmpro_update_level_and_group_order();
	}

	// Given a membership level (required) and a user ID (or current user, if empty), add them. If an admin wants to force
	// the addition even if it's illegal, they can set force_add to true.
	// Checks group constraints to make sure it's legal first, then uses pmpro_changeMembershipLevel from PMPro core
	// to do the heavy lifting. If the addition isn't legal, returns false. Returns true on success.
	function pmprommpu_addMembershipLevel($level = NULL, $user_id = NULL, $force_add = false) {
		if ( ! empty( $level ) ) {
			return pmpro_add_membership_level( $level, $user_id );
		}
		return false;
	}

	// Start functions from overrides.php
	// let's make sure jQuery UI Dialog is present on the admin side.
	function pmprommpu_addin_jquery_dialog( $pagehook ) {
		// Moved to pmpro_admin_enqueue_scripts();
	}
	//add_action( 'admin_enqueue_scripts', 'pmprommpu_addin_jquery_dialog' );

	/**
	 * Change membership levels admin page to show groups.
	 */
	function pmprommpu_pmpro_membership_levels_table( $intablehtml, $inlevelarr ) {
		// Moved into adminpages/membesrhiplevels.php.
		// TODO: Clean up that file.
	}

	//add_filter( 'pmpro_membership_levels_table', 'pmprommpu_pmpro_membership_levels_table', 10, 2 );

	/*
		Add options to edit level page
	*/
	//add options
	function pmprommpu_add_group_to_level_options() {
		// Added to membershiplevels.php.
	}

	//add_action( 'pmpro_membership_level_after_other_settings', 'pmprommpu_add_group_to_level_options' );

	//save options
	function pmprommpu_save_group_on_level_edit( $levelid ) {
		// Added to membershiplevels.php.
	}

	//add_action( 'pmpro_save_membership_level', 'pmprommpu_save_group_on_level_edit' );

	/*
		Delete group data when a level is deleted
	*/
	function pmprommpu_on_del_level( $levelid ) {
		// Moved into adminpages/membershiplevels.php.
	}
	//add_action( 'pmpro_delete_membership_level', 'pmprommpu_on_del_level' );

	// Actual functions are defined in functions.php.
	//add_action( 'wp_ajax_pmprommpu_add_group', 'pmprommpu_add_group' );
	//add_action( 'wp_ajax_pmprommpu_edit_group', 'pmprommpu_edit_group' );
	//add_action( 'wp_ajax_pmprommpu_del_group', 'pmprommpu_del_group' );
	//add_action( 'wp_ajax_pmprommpu_update_level_and_group_order', 'pmprommpu_update_level_and_group_order' );

	function pmprommpu_show_multiple_levels_in_memlist( $inuser ) {
		// Implemented in class-pmpro-members-list-table.php.
	}
	// add_filter( 'pmpro_members_list_user', 'pmprommpu_show_multiple_levels_in_memlist', 10, 1 );

	/*
	* Replaces the default "Level" and "Level ID" columns in Members List
	* with MMPU variants.
	*
	* @since 0.7
	*/
	function pmprommpu_memberslist_extra_cols( $columns ) {
		// Implemented in class-pmpro-members-list-table.php.
	}
	//add_filter( 'pmpro_memberslist_extra_cols', 'pmprommpu_memberslist_extra_cols' );

	/*
	* Fills the MMPU-genereated columns in Members List.
	*
	* @since 0.7
	*/
	function pmprommpu_fill_memberslist_col_member_number( $colname, $user_id ) {
		// Implemented in class-pmpro-members-list-table.php.
	}
	// add_filter( 'pmpro_manage_memberslist_custom_column', 'pmprommpu_fill_memberslist_col_member_number', 10, 2 );

	// From profile.php (deleted)

	/**
	 * Removed default PMPro edit profile functionality and add our own.
	 *
	 * NOTE: Stripe "updates" are not compatible with MMPU
	 */
	function pmprommpu_init_profile_hooks() {
		// No longer needed.
	}

	/**
	 * Show the membership levels section
	 *  add_action( 'show_user_profile', 'pmprommpu_membership_level_profile_fields' );
	 *  add_action( 'edit_user_profile', 'pmprommpu_membership_level_profile_fields' );
	 */
	function pmprommpu_membership_level_profile_fields($user) {
		// Replaced pmpro_membership_level_profile_fields();
	}

	/**
	 * Handle updates
	 *  add_action( 'personal_options_update', 'pmprommpu_membership_level_profile_fields_update' );
	 *  add_action( 'edit_user_profile_update', 'pmprommpu_membership_level_profile_fields_update' );
	*/
	function pmprommpu_membership_level_profile_fields_update() {
		// Replaced pmpro_membership_level_profile_fields_update();
	}

	// From upgrades.php
	//	These functions are run on startup if user is an admin. They check for upgrades -
	//	and if it's a new install, everything is an upgrade!

	function pmprommpu_setup_and_upgrade() {
		// Not needed.
	}

	function pmprommpu_db_delta() {
		// Added in pmpro_db_delta();
	}

	function pmprommpu_setup_v1() {
		// Not needed.
	}
}
add_action( 'init', 'pmpro_load_deprecated_mmpu_functions', 0 );