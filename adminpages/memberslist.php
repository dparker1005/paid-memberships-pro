<?php

global $user_list_table;
// query, filter, and sort the data
$user_list_table = new PMPro_Members_List_Table();
$user_list_table->prepare_items();
require_once dirname( __DIR__ ) . '/adminpages/admin_header.php';
// render the List Table
?>
	<h2><?php _e( 'PMPro Members List Table', 'paid-memberships-pro' ); ?>
	<a target="_blank" href="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=memberslist_csv" class="add-new-h2"><?php _e( 'Export to CSV', 'paid-memberships-pro' ); ?></a>
	</h2>
		<div id="member-list-table-demo">			
			<div id="pbrx-post-body">		
				<form id="member-list-form" method="get">
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
					<?php
						$user_list_table->search_box( __( 'Find Member', 'paid-memberships-pro' ), 'pbrx-user-find' );
						$user_list_table->display();
					?>
			</form>
		</div>			
	</div>
<?php
	require_once dirname( __DIR__ ) . '/adminpages/admin_footer.php';
?>
