<?php
/*
	Loading a service?
*/
/*
	Note: The applydiscountcode goes through the site_url() instead of admin-ajax to avoid HTTP/HTTPS issues.
*/
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "applydiscountcode")
{		
	function pmpro_applydiscountcode_init()
	{
		require_once(dirname(__FILE__) . "/../services/applydiscountcode.php");	
		exit;
	}
	add_action("init", "pmpro_applydiscountcode_init", 11);
}
function pmpro_wp_ajax_authnet_silent_post()
{		
	require_once(dirname(__FILE__) . "/../services/authnet-silent-post.php");	
	exit;	
}
add_action('wp_ajax_nopriv_authnet_silent_post', 'pmpro_wp_ajax_authnet_silent_post');
add_action('wp_ajax_authnet_silent_post', 'pmpro_wp_ajax_authnet_silent_post');
function pmpro_wp_ajax_getfile()
{
	require_once(dirname(__FILE__) . "/../services/getfile.php");	
	exit;	
}
add_action('wp_ajax_nopriv_getfile', 'pmpro_wp_ajax_getfile');
add_action('wp_ajax_getfile', 'pmpro_wp_ajax_getfile');
function pmpro_wp_ajax_ipnhandler()
{
	require_once(dirname(__FILE__) . "/../services/ipnhandler.php");	
	exit;	
}
add_action('wp_ajax_nopriv_ipnhandler', 'pmpro_wp_ajax_ipnhandler');
add_action('wp_ajax_ipnhandler', 'pmpro_wp_ajax_ipnhandler');
function pmpro_wp_ajax_stripe_webhook()
{
	require_once(dirname(__FILE__) . "/../services/stripe-webhook.php");	
	exit;	
}
add_action('wp_ajax_nopriv_stripe_webhook', 'pmpro_wp_ajax_stripe_webhook');
add_action('wp_ajax_stripe_webhook', 'pmpro_wp_ajax_stripe_webhook');
function pmpro_wp_ajax_braintree_webhook()
{
	require_once(dirname(__FILE__) . "/../services/braintree-webhook.php");	
	exit;	
}
add_action('wp_ajax_nopriv_braintree_webhook', 'pmpro_wp_ajax_braintree_webhook');
add_action('wp_ajax_braintree_webhook', 'pmpro_wp_ajax_braintree_webhook');
function pmpro_wp_ajax_twocheckout_ins()
{
	require_once(dirname(__FILE__) . "/../services/twocheckout-ins.php");	
	exit;	
}
add_action('wp_ajax_nopriv_twocheckout-ins', 'pmpro_wp_ajax_twocheckout_ins');
add_action('wp_ajax_twocheckout-ins', 'pmpro_wp_ajax_twocheckout_ins');
function pmpro_wp_ajax_memberlist_csv()
{
	require_once(dirname(__FILE__) . "/../adminpages/memberslist-csv.php");	
	exit;	
}
add_action('wp_ajax_memberslist_csv', 'pmpro_wp_ajax_memberlist_csv');
function pmpro_wp_ajax_orders_csv()
{
	require_once(dirname(__FILE__) . "/../adminpages/orders-csv.php");	
	exit;	
}
add_action('wp_ajax_orders_csv', 'pmpro_wp_ajax_orders_csv');

/**
 * Load the Orders print view.
 *
 * @since 1.8.6
 */
function pmpro_orders_print_view() {
	require_once(dirname(__FILE__) . "/../adminpages/orders-print.php");
	exit;
}
add_action('wp_ajax_pmpro_orders_print_view', 'pmpro_orders_print_view');

/**
 * Get order JSON.
 *
 * @since 1.8.6
 */
function pmpro_get_order_json() {
	$order_id = $_REQUEST['order_id'];
	$order = new MemberOrder($order_id);
	echo json_encode($order);
	exit;
}
add_action('wp_ajax_pmpro_get_order_json', 'pmpro_get_order_json');

function pmpro_update_level_order() {
	
	$level_order = null;
	
	if ( isset( $_REQUEST['level_order'] ) && is_array( $_REQUEST['level_order'] ) ) {
		$level_order = array_map( 'intval', $_REQUEST['level_order'] );
		$level_order = implode(',', $level_order );
	} else if ( isset( $_REQUEST['level_order'] ) ) {
		$level_order = sanitize_text_field( $_REQUEST['level_order'] );
	}
	
	echo pmpro_setOption('level_order', $level_order);
    exit;
}
add_action('wp_ajax_pmpro_update_level_order', 'pmpro_update_level_order');

/*
 * Called by AJAX to add a group from the admin-side Membership Levels and Groups page.
 * Incoming parms are name and mult (can users sign up for multiple levels in this group - 0/1).
 */
function pmpro_add_level_group() {
	global $wpdb;

	$displaynum = $wpdb->get_var("SELECT MAX(displayorder) FROM {$wpdb->pmpro_groups}");
	if(! $displaynum || intval($displaynum)<1) { $displaynum = 1; } else { $displaynum = intval($displaynum); $displaynum++; }

	if(array_key_exists("name", $_REQUEST)) {
		$allowmult = 0;
		if(array_key_exists("mult", $_REQUEST) && intval($_REQUEST["mult"])>0) { $allowmult = 1; }
		$wpdb->insert($wpdb->pmpro_groups,
			array(	'name' => $_REQUEST["name"],
					'allow_multiple_selections' => $allowmult,
					'displayorder' => $displaynum),
			array(	'%s',
					'%d',
					'%d')
			);
	}

	wp_die();
}
add_action( 'wp_ajax_pmpro_add_level_group', 'pmpro_add_level_group' );

/*
 * Called by AJAX to edit a group from the admin-side Membership Levels and Groups page.
 * Incoming parms are group (the ID #), name and mult (can users sign up for multiple levels in this group - 0/1).
 */
function pmpro_edit_level_group() {
	global $wpdb;

	if(array_key_exists("name", $_REQUEST) && array_key_exists("group", $_REQUEST) && intval($_REQUEST["group"])>0) {
		$allowmult = 0;
		if(array_key_exists("mult", $_REQUEST) && intval($_REQUEST["mult"])>0) { $allowmult = 1; }
		$grouptoedit = intval($_REQUEST["group"]);

		// TODO: Error checking would be smart.
		$wpdb->update($wpdb->pmpro_groups,
			array(	'name' => $_REQUEST["name"],
					'allow_multiple_selections' => $allowmult
			), // SET
			array(	'id' => $grouptoedit), // WHERE
			array(	'%s',
					'%d',
					'%d'
			), // SET FORMAT
			array(	'%d' ) // WHERE format
		);
	}

	wp_die();
}
add_action( 'wp_ajax_pmpro_edit_level_group', 'pmpro_edit_level_group' );

/*
 * Called by AJAX to delete an empty group from the admin-side Membership Levels and Groups page.
 * Incoming parm is group (group ID #).
 */
function pmpro_delete_level_group() {
	global $wpdb;

	if(array_key_exists("group", $_REQUEST) && intval($_REQUEST["group"])>0) {
		$groupid = intval($_REQUEST["group"]);

		// TODO: Error checking would be smart.
		$wpdb->delete( $wpdb->pmpro_membership_levels_groups, array('group' => $groupid ) );
		$wpdb->delete( $wpdb->pmpro_groups, array( 'id' => $groupid) );
	}

	wp_die();
}
add_action( 'wp_ajax_pmpro_delete_level_group', 'pmpro_delete_level_group' );

/*
 * Called by AJAX from the admin-facing levels page when the rows are reordered.
 * Incoming parm (neworder) is an ordered array of objects (with two parms, group (scalar ID) and levels (ordered array of scalar level IDs))
 */
function pmpro_update_level_and_group_order() {
	global $wpdb;

	$grouparr = array();
	$levelarr = array();

	if(array_key_exists("neworder", $_REQUEST) && is_array($_REQUEST["neworder"])) {
		foreach($_REQUEST["neworder"] as $curgroup) {
			$grouparr[] = $curgroup["group"];
			foreach($curgroup["levels"] as $curlevel) {
				$levelarr[] = $curlevel;
			}
		}
		$ctr = 1;

		// Inefficient for large groups/large numbers of groups
		foreach($grouparr as $orderedgroup) {

			// TODO: Error checking would be smart.
			$wpdb->update( $wpdb->pmpro_groups, array ( 'displayorder' => $ctr ), array( 'id' => $orderedgroup ) );
			$ctr++;
		}
		pmpro_setOption('level_order', $levelarr);
	}

	wp_die();
}
add_action( 'wp_ajax_pmpro_update_level_and_group_order', 'pmpro_update_level_and_group_order' );
