<?php

class PMPro_Member_Edit_Panel_Subscriptions extends PMPro_Member_Edit_Panel {
	/**
	 * Set up the panel.
	 */
	public function __construct() {
		$this->slug = 'subscriptions';
		$this->title = __( 'Subscriptions', 'paid-memberships-pro' );

		// Get the user's Stripe Customer if they have one.
		$user = self::get_user();
		$stripe = new PMProGateway_Stripe();
		$customer = $stripe->get_customer_for_user( $user->ID );

		// Link to the Stripe Customer if they have one.
		// TODO: Eventually make this a hook or filter so other gateways can add their own links.
		if ( ! empty( $customer ) ) {
			$this->title_link = '<a target="_blank" class="page-title-action pmpro-has-icon pmpro-has-icon-admin-users" href="' . esc_url( 'https://dashboard.stripe.com/' . ( get_option( 'pmpro_gateway_environment' ) == 'sandbox' ? 'test/' : '' ) . 'customers/' . $customer->id ) . '">' . esc_html__( 'Edit customer in Stripe', 'paid-memberships-pro' ) . '</a>';
		}
	}

	/**
	 * Display the panel contents.
	 */
	protected function display_panel_contents() {
		global $wpdb;

		$user = self::get_user();

		// Show all active subscriptions for the user.
		$active_subscriptions = PMPro_Subscription::get_subscriptions_for_user( $user->ID );
		if ( $active_subscriptions ) { ?>
			<h3>
				<?php
					printf(
						esc_html__( 'Active Subscriptions (%d)', 'paid-memberships-pro' ),
						number_format_i18n( count( $active_subscriptions ) )
					);
				?>
			</h3>
			<table class="wp-list-table widefat striped fixed" width="100%" cellpadding="0" cellspacing="0" border="0">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Level', 'paid-memberships-pro' ); ?></th>
						<th><?php esc_html_e( 'Created', 'paid-memberships-pro' ); ?></th>
						<th><?php esc_html_e( 'Next Payment', 'paid-memberships-pro' ); ?></th>
						<th><?php esc_html_e( 'Orders', 'paid-memberships-pro' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$user_levels = pmpro_getMembershipLevelsForUser($user->ID);
					$user_level_ids = wp_list_pluck( $user_levels, 'id' );
					foreach ( $active_subscriptions as $active_subscription ) {
						$level = pmpro_getLevel( $active_subscription->get_membership_level_id() );
						?>
						<tr>
							<td class="has-row-actions">
								<strong><?php echo esc_html( $level->name ); ?></strong>
								<?php
									// Show warning if the user does not have the level for this subscription.
									if ( ! in_array( $level->id, $user_level_ids ) ) { ?>
										<span class="pmpro_tag pmpro_tag-has_icon pmpro_tag-error">
											<?php esc_html_e( 'Membership Ended', 'paid-memberships-pro' ); ?>
										</span>
									<?php }
								?>
								<div class="row-actions">
									<?php
										$actions = [
											'view'   => sprintf(
												'<a href="%1$s">%2$s</a>',
												esc_url( add_query_arg( array( 'page' => 'pmpro-subscriptions', 'id' => $active_subscription->get_id() ), admin_url('admin.php' ) ) ),
												esc_html__( 'View Details', 'paid-memberships-pro' )
											)
										];

										$actions_html = [];

										foreach ( $actions as $action => $link ) {
											$actions_html[] = sprintf(
												'<span class="%1$s">%2$s</span>',
												esc_attr( $action ),
												$link
											);
										}

										if ( ! empty( $actions_html ) ) {
											echo implode( ' | ', $actions_html );
										}
									?>
								</div>
							</td>
							<td>
								<?php
									echo esc_html( sprintf(
										// translators: %1$s is the date and %2$s is the time.
										__( '%1$s at %2$s', 'paid-memberships-pro' ),
										esc_html( $active_subscription->get_startdate( get_option( 'date_format' ) ) ),
										esc_html( $active_subscription->get_startdate( get_option( 'time_format' ) ) )
									) );
								?>
							</td>
							<td>
								<?php 
									echo ! empty( $active_subscription->get_next_payment_date() ) 
										? esc_html( sprintf(
											// translators: %1$s is the date and %2$s is the time.
											__( '%1$s at %2$s', 'paid-memberships-pro' ),
											esc_html( $active_subscription->get_next_payment_date( get_option( 'date_format' ) ) ),
											esc_html( $active_subscription->get_next_payment_date( get_option( 'time_format' ) ) )
										) )
										: '&#8212;';
								?>
							</td>
							<td>
								<?php
								// Display the number of orders for this subscription and link to the orders page filtered by this subscription.
								$orders_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->pmpro_membership_orders WHERE subscription_transaction_id = %s", $active_subscription->get_subscription_transaction_id() ) );
								?>
								<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'pmpro-orders', 's' => $active_subscription->get_subscription_transaction_id() ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html( number_format_i18n( $orders_count ) ); ?></a>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<?php
		}

		// Show cancelled subscriptions for the user.
		$cancelled_subscriptions = PMPro_Subscription::get_subscriptions_for_user( $user->ID, null, array( 'cancelled' ) );

		if ( $cancelled_subscriptions ) {
			// Optionally wrap table in scrollable box.
			$subscriptions_classes = array();
			if ( ! empty( $cancelled_subscriptions ) && count( $cancelled_subscriptions ) > 10 ) {
				$subscriptions_classes[] = "pmpro_scrollable";
			}
			$subscriptions_class = implode( ' ', array_unique( $subscriptions_classes ) );
			?>
			<h3>
				<?php
					printf(
						esc_html__( 'Cancelled Subscriptions (%d)', 'paid-memberships-pro' ),
						number_format_i18n( count( $cancelled_subscriptions ) )
					);
				?>
			</h3>
			<div id="member-history-subscriptions" class="<?php echo esc_attr( $subscriptions_class ); ?>">
				<table class="wp-list-table widefat striped fixed" width="100%" cellpadding="0" cellspacing="0" border="0">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Level', 'paid-memberships-pro' ); ?></th>
							<th><?php esc_html_e( 'Created', 'paid-memberships-pro' ); ?></th>
							<th><?php esc_html_e( 'Ended', 'paid-memberships-pro' ); ?></th>
							<th><?php esc_html_e( 'Orders', 'paid-memberships-pro' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php
						foreach ( $cancelled_subscriptions as $cancelled_subscription ) {
							$level = pmpro_getLevel( $cancelled_subscription->get_membership_level_id() );
							?>
							<tr>
								<td>
									<?php if ( ! empty( $level ) ) {
										echo esc_html( $level->name );
									} elseif ( $cancelled_subscription->get_membership_level_id() > 0 ) {
										echo '['. esc_html( 'deleted', 'paid-memberships-pro' ).']';
									} else {
										esc_html_e( '&#8212;', 'paid-memberships-pro' );
									}
									?>
									<div class="row-actions">
										<?php
											$actions = [
												'view'   => sprintf(
													'<a href="%1$s">%2$s</a>',
													esc_url( add_query_arg( array( 'page' => 'pmpro-subscriptions', 'id' => $cancelled_subscription->get_id() ), admin_url('admin.php' ) ) ),
													esc_html__( 'View Details', 'paid-memberships-pro' )
												)
											];

											$actions_html = [];

											foreach ( $actions as $action => $link ) {
												$actions_html[] = sprintf(
													'<span class="%1$s">%2$s</span>',
													esc_attr( $action ),
													$link
												);
											}

											if ( ! empty( $actions_html ) ) {
												echo implode( ' | ', $actions_html );
											}
										?>
									</div>
								</td>
								<td>
									<?php
										echo esc_html( sprintf(
											// translators: %1$s is the date and %2$s is the time.
											__( '%1$s at %2$s', 'paid-memberships-pro' ),
											esc_html( $cancelled_subscription->get_startdate( get_option( 'date_format' ) ) ),
											esc_html( $cancelled_subscription->get_startdate( get_option( 'time_format' ) ) )
										) );
									?>
								</td>
								<td>
									<?php 
										echo ! empty( $cancelled_subscription->get_enddate() ) 
											? esc_html( sprintf(
												// translators: %1$s is the date and %2$s is the time.
												__( '%1$s at %2$s', 'paid-memberships-pro' ),
												esc_html( $cancelled_subscription->get_enddate( get_option( 'date_format' ) ) ),
												esc_html( $cancelled_subscription->get_enddate( get_option( 'time_format' ) ) )
											) )
											: '&#8212;';
									?>
								</td>
								<td>
									<?php
									// Display the number of orders for this subscription and link to the orders page filtered by this subscription.
									$orders_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->pmpro_membership_orders WHERE subscription_transaction_id = %s", $cancelled_subscription->get_subscription_transaction_id() ) );
									?>
									<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'pmpro-orders', 's' => $cancelled_subscription->get_subscription_transaction_id() ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html( number_format_i18n( $orders_count ) ); ?></a>
								</td>
							</tr>
							<?php
						}
					?>
					</tbody>
				</table>
			</div>
			<?php
		}
		// Show a message if there are no active or cancelled subscriptions.
		if ( empty( $active_subscriptions ) && empty( $cancelled_subscriptions ) ) {
			?>
			<p><?php esc_html_e( 'This user does not have any subscriptions.', 'paid-memberships-pro' ); ?></p>
			<?php
		}
	}
}