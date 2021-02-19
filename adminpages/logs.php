<?php
//only admins can get this
if (!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("pmpro_pagesettings"))) {
	die(__("You do not have permissions to perform this action.", 'paid-memberships-pro' ));
}

// Get all log types.
$logs = pmpro_get_logs();

// Which log is the user trying to view?
if ( isset( $_REQUEST['log'] ) && array_key_exists( $_REQUEST['log'], $logs ) ) {
	$selected_log = $_REQUEST['log'];

	// Check if user updated the log's settings...
	if ( isset( $_REQUEST['log_settings'] ) && is_array( $_REQUEST['log_settings'] ) ) {
		if ( ! isset( $_REQUEST['log_settings']['enabled'] ) ) {
			$_REQUEST['log_settings']['enabled'] = false;
		}
		// Save new log settings.
		pmpro_set_settings_for_log( $selected_log, $_REQUEST['log_settings'] );
	}
}

require_once(dirname(__FILE__) . "/admin_header.php");

if ( empty( $selected_log ) ) {
	// Show list of all logs.
	?>
		<table class="widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Slug', 'paid-memberships-pro' ); ?></th>
				<th><?php esc_html_e( 'Enabled', 'paid-memberships-pro' ); ?></th>
				<th><?php esc_html_e( 'Max Entries', 'paid-memberships-pro' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ( $logs as $log_slug => $value ) {
					$log_settings = pmpro_get_settings_for_log( $log_slug );
					$count = 0;
					?>
						<tr class="<?php if($count++ % 2 == 1) { ?>alternate<?php } ?>">
							<td><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'pmpro-logs', 'log' => $log_slug ), get_admin_url(null, 'admin.php' ) ) ); ?>"><?php esc_html_e( $log_slug ); ?></a></td>
							<td><?php echo empty( $log_settings['enabled'] ) ? esc_html_e( 'No', 'paid-memberships-pro' ) : esc_html_e( 'Yes', 'paid-memberships-pro' ); ?></td>
							<td><?php echo empty( $log_settings['max_entries'] ) ? esc_html_e( '-', 'paid-memberships-pro' ) : esc_html_e( $log_settings['max_entries'] ); ?></td>
						</tr>
					<?php
				}
			?>
		</tbody>
		</table>
	<?php
} else {
	$selected_log_obj = new PMPro_Log( $selected_log );
	?>
	<h2><?php _e('Log Settings', 'paid-memberships-pro' );?></h2>
	<form action="<?php echo admin_url('admin.php?page=pmpro-logs&log=' . $selected_log );?>" method="post" enctype="multipart/form-data">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top"><label><?php _e('Slug', 'paid-memberships-pro' );?>:</label></th>
					<td>
						<p id='log_slug'><?php echo $selected_log?><p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="log_settings[enabled]"><?php _e('Enabled', 'paid-memberships-pro' );?>:</label></th>
					<td><input name="log_settings[enabled]" type="checkbox" <?php checked( ! empty( $selected_log_obj->enabled ) ) ?>></td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="log_settings[max_entries]"><?php _e('Maximum Entries', 'paid-memberships-pro' );?>:</label></th>
					<td><input name="log_settings[max_entries]" type="number" value=<?php echo intval( $selected_log_obj->max_entries ) ?>></td>
				</tr>
		</table>
		<p class="submit topborder">
			<input name="save" type="submit" class="button button-primary" value="<?php _e('Save Settings', 'paid-memberships-pro' ); ?>" />
			<input name="cancel" type="button" class="button" value="<?php _e('Cancel', 'paid-memberships-pro' ); ?>" onclick="location.href='<?php echo add_query_arg( 'page', 'pmpro-logs' , get_admin_url(NULL, '/admin.php') ); ?>';" />
		</p>
	</form>
	<hr/>
	<h2><?php _e('Log Contents', 'paid-memberships-pro' );?></h2>
	<select id="log_filter">
		<option value=""><?php _e('All Tags', 'paid-memberships-pro' );?></option>
		<?php
		foreach ( $logs[$selected_log]['valid_tags'] as $tag ) {
			?><option value="<?php esc_html_e( $tag ); ?>"><?php esc_html_e( $tag );?></option><?php
		}
		?>
	</select>
	<textarea readonly style='width:100%; height: 300px;' id='log_output'></textarea>
	<input type="button" class="button" id="log_refresh" value="<?php _e('Refresh Log', 'paid-memberships-pro' ); ?>" />
	<input type="button" class="button" id="log_clear" value="<?php _e('Clear Log', 'paid-memberships-pro' ); ?>" />
	<?php
}

require_once(dirname(__FILE__) . "/admin_footer.php");
?>
