<?php
/**
 * Guest Checkout functionality for PMPro.
 *
 * Allows completing a standalone checkout (level_id=0) without creating
 * a WordPress account. Defaults to off. Add Ons enable it via the
 * `pmpro_allow_guest_checkout` filter.
 *
 * @since TBD
 */

/*
 * ----------------------------------------
 * Helper Functions
 * ----------------------------------------
 */

/**
 * Check if guest checkout is allowed for the current checkout.
 *
 * @since TBD
 *
 * @return bool Whether guest checkout is allowed.
 */
function pmpro_allow_guest_checkout() {
	global $pmpro_level;

	/**
	 * Filter whether guest checkout (no account creation) is allowed.
	 *
	 * Defaults to false. Add Ons hook this to enable guest checkout.
	 * Only applies to standalone checkouts (level_id=0).
	 *
	 * @since TBD
	 *
	 * @param bool   $allowed Whether guest checkout is allowed.
	 * @param object $pmpro_level The level being purchased.
	 */
	return (bool) apply_filters( 'pmpro_allow_guest_checkout', false, $pmpro_level );
}

/**
 * Check if the current checkout is a guest checkout.
 *
 * A guest checkout requires: the checkbox is checked, the user is
 * not logged in, it's a standalone checkout, and guest checkout is allowed.
 *
 * @since TBD
 *
 * @return bool Whether the current checkout is a guest checkout.
 */
function pmpro_is_guest_checkout() {
	if ( empty( $_REQUEST['pmpro_guest_checkout'] ) ) {
		return false;
	}

	if ( is_user_logged_in() ) {
		return false;
	}

	$level = pmpro_getLevelAtCheckout();
	if ( empty( $level ) || intval( $level->id ) !== 0 ) {
		return false;
	}

	if ( ! pmpro_allow_guest_checkout() ) {
		return false;
	}

	return true;
}

/**
 * Check if an order was a guest checkout.
 *
 * @since TBD
 *
 * @param MemberOrder $order The order to check.
 * @return bool Whether the order was a guest checkout.
 */
function pmpro_order_is_guest_checkout( $order ) {
	if ( empty( $order->id ) ) {
		return false;
	}
	return ! empty( get_pmpro_membership_order_meta( $order->id, 'guest_email', true ) );
}

/**
 * Skip user creation (and validation) for guest checkouts.
 *
 * @since TBD
 *
 * @param bool   $skip  Whether to skip user creation.
 * @param object $level The level being purchased.
 * @return bool
 */
function pmpro_guest_checkout_skip_user_creation( $skip, $level ) {
	if ( pmpro_is_guest_checkout() ) {
		return true;
	}
	return $skip;
}
add_filter( 'pmpro_skip_user_creation', 'pmpro_guest_checkout_skip_user_creation', 10, 2 );

/**
 * Save guest checkout data to order meta after checkout.
 *
 * @since TBD
 *
 * @param int         $user_id The user ID (0 for guests).
 * @param MemberOrder $order   The order that was created.
 */
function pmpro_guest_checkout_after_checkout( $user_id, $order ) {
	if ( ! pmpro_is_guest_checkout() ) {
		return;
	}

	$guest_email = ! empty( $_REQUEST['bemail'] ) ? sanitize_email( $_REQUEST['bemail'] ) : '';
	update_pmpro_membership_order_meta( $order->id, 'guest_email', $guest_email );
}
add_action( 'pmpro_after_checkout', 'pmpro_guest_checkout_after_checkout', 5, 2 );

/**
 * Provide a WP_User object for guest checkout emails.
 *
 * When there's no WordPress user (user_id=0), build a WP_User
 * from the checkout form data so the standard email flow works.
 *
 * @since TBD
 *
 * @param WP_User|false $user  The user object, or false if not found.
 * @param MemberOrder   $order The order being processed.
 * @return WP_User|false
 */
function pmpro_guest_checkout_email_user( $user, $order ) {
	if ( ! empty( $user ) ) {
		return $user;
	}

	if ( ! pmpro_is_guest_checkout() ) {
		return $user;
	}

	$user               = new WP_User();
	$user->user_email   = ! empty( $_REQUEST['bemail'] ) ? sanitize_email( $_REQUEST['bemail'] ) : '';
	$user->display_name = ! empty( $order->billing->name ) ? $order->billing->name : '';
	$user->user_login   = '';

	return $user;
}
add_filter( 'pmpro_checkout_email_user', 'pmpro_guest_checkout_email_user', 10, 2 );

/**
 * Redirect guest checkouts to the invoice page instead of the confirmation page.
 *
 * The confirmation page expects a logged-in user with an active membership level.
 * Guest checkouts have neither, so we redirect to the invoice page using the
 * order code and guest email for access.
 *
 * @since TBD
 *
 * @param string $url     The confirmation URL.
 * @param int    $user_id The user ID (0 for guests).
 * @param object $level   The membership level.
 * @return string The redirect URL.
 */
function pmpro_guest_checkout_confirmation_url( $url, $user_id, $level ) {
	if ( ! empty( $user_id ) ) {
		return $url;
	}

	global $pmpro_review;
	if ( empty( $pmpro_review ) || empty( $pmpro_review->code ) ) {
		return $url;
	}

	$guest_email = ! empty( $_REQUEST['bemail'] ) ? sanitize_email( $_REQUEST['bemail'] ) : '';
	return add_query_arg(
		array(
			'invoice' => $pmpro_review->code,
			'email'   => rawurlencode( $guest_email ),
		),
		pmpro_url( 'invoice' )
	);
}
add_filter( 'pmpro_confirmation_url', 'pmpro_guest_checkout_confirmation_url', 10, 3 );

/**
 * Allow guests to view their own guest orders on the invoice page.
 *
 * Validates both the order code and the guest email to prevent enumeration.
 *
 * @since TBD
 *
 * @param bool             $can_view Whether the visitor can view the order.
 * @param MemberOrder|null $order    The order being viewed.
 * @return bool
 */
function pmpro_guest_checkout_allow_viewing_order( $can_view, $order ) {
	if ( $can_view ) {
		return $can_view;
	}

	if ( empty( $order ) || ! pmpro_order_is_guest_checkout( $order ) ) {
		return $can_view;
	}

	$provided_email = ! empty( $_REQUEST['email'] ) ? sanitize_email( $_REQUEST['email'] ) : '';
	$guest_email    = get_pmpro_membership_order_meta( $order->id, 'guest_email', true );

	if ( empty( $provided_email ) || empty( $guest_email ) ) {
		return false;
	}

	if ( strtolower( $provided_email ) !== strtolower( $guest_email ) ) {
		return false;
	}

	// Set a guest user stub on the order so the invoice template can render
	// without null reference errors on $order->user properties.
	$order->user                  = new stdClass();
	$order->user->ID              = 0;
	$order->user->display_name    = ! empty( $order->billing->name ) ? $order->billing->name : __( 'Guest', 'paid-memberships-pro' );
	$order->user->user_email      = $guest_email;
	$order->user->user_login      = '';
	$order->user->user_registered = '';

	return true;
}
add_filter( 'pmpro_allow_viewing_order', 'pmpro_guest_checkout_allow_viewing_order', 10, 2 );

/*
 * ========================================
 * Admin Display
 * ========================================
 */

/**
 * Show guest checkout info after the user column in the orders list.
 *
 * @since TBD
 *
 * @param MemberOrder $item The order being displayed.
 */
function pmpro_guest_checkout_orders_column_after_user( $item ) {
	if ( ! pmpro_order_is_guest_checkout( $item ) ) {
		return;
	}

	$guest_email = get_pmpro_membership_order_meta( $item->id, 'guest_email', true );
	if ( empty( $guest_email ) ) {
		return;
	}

	echo '<br /><em>' . esc_html__( 'Guest Checkout', 'paid-memberships-pro' ) . '</em>';
	echo '<br />' . esc_html( $guest_email );
}
add_action( 'pmpro_orders_column_after_user', 'pmpro_guest_checkout_orders_column_after_user' );

/**
 * Show guest checkout info after the member info in the order view sidebar.
 *
 * @since TBD
 *
 * @param MemberOrder $order The order being viewed.
 */
function pmpro_guest_checkout_order_view_after_member_info( $order ) {
	if ( ! pmpro_order_is_guest_checkout( $order ) ) {
		return;
	}

	$guest_email = get_pmpro_membership_order_meta( $order->id, 'guest_email', true );
	if ( empty( $guest_email ) ) {
		return;
	}
	?>
	<p>
		<strong><?php esc_html_e( 'Guest Checkout', 'paid-memberships-pro' ); ?></strong><br />
		<?php echo esc_html( $guest_email ); ?>
	</p>
	<?php
}
add_action( 'pmpro_order_view_after_member_info', 'pmpro_guest_checkout_order_view_after_member_info' );

/*
 * ========================================
 * Checkout Page UI
 * ========================================
 */

/**
 * Add guest checkout checkbox to the checkout page.
 *
 * Shows a "Check out as a guest" checkbox for standalone checkouts
 * when the user is not logged in and guest checkout is allowed.
 *
 * @since TBD
 */
function pmpro_guest_checkout_add_checkout_toggle() {
	global $pmpro_level;

	// Only show for logged-out users on standalone checkouts where guest checkout is allowed.
	if ( is_user_logged_in() || empty( $pmpro_level ) || intval( $pmpro_level->id ) !== 0 || ! pmpro_allow_guest_checkout() ) {
		return;
	}
	?>
	<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_field pmpro_form_field-checkbox' ) ); ?>">
		<label class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_label pmpro_form_label-inline pmpro_clickable' ) ); ?>">
			<input type="checkbox" id="pmpro_guest_checkout" name="pmpro_guest_checkout" value="1" <?php checked( ! empty( $_REQUEST['pmpro_guest_checkout'] ) ); ?> />
			<?php esc_html_e( 'Check out as a guest', 'paid-memberships-pro' ); ?>
		</label>
	</div>
	<script>
		jQuery(document).ready(function($) {
			var $checkbox = $('#pmpro_guest_checkout');
			var $userFieldset = $('#pmpro_user_fields');
			var $formFields = $userFieldset.find('.pmpro_form_fields');
			var $loginLink = $userFieldset.find('.pmpro_card_actions');

			// Fields to hide in guest mode.
			var $usernameField = $formFields.find('.pmpro_form_field-username');
			var $passwordFields = $formFields.find('.pmpro_form_field-password');
			var $passwordCols = $passwordFields.first().closest('.pmpro_cols-2');
			var $confirmEmailField = $formFields.find('.pmpro_form_field-bconfirmemail');
			var $emailField = $formFields.find('.pmpro_form_field-bemail');

			// The email and confirm email may share a pmpro_cols-2 wrapper.
			var $emailCols = $emailField.closest('.pmpro_cols-2');
			var isGuest = false;

			function setGuestMode(guest) {
				isGuest = guest;
				$checkbox.prop('checked', isGuest);

				if (isGuest) {
					$usernameField.hide();
					$passwordFields.hide();
					$passwordCols.hide();
					$confirmEmailField.hide();
					$loginLink.hide();

					// Move email field out of the cols-2 wrapper so hiding confirm doesn't hide email too.
					if ($emailCols.length) {
						$emailField.insertBefore($emailCols);
						$emailCols.hide();
					}
				} else {
					// Move email field back into the cols-2 wrapper.
					if ($emailCols.length) {
						$emailCols.prepend($emailField);
						$emailCols.show();
					}

					$usernameField.show();
					$passwordFields.show();
					$passwordCols.show();
					$confirmEmailField.show();
					$loginLink.show();
				}
			}

			$checkbox.on('change', function() {
				setGuestMode($(this).is(':checked'));
			});

			// If reloading with guest checkout already checked.
			if ($checkbox.is(':checked')) {
				setGuestMode(true);
			}
		});
	</script>
	<?php
}
add_action( 'pmpro_checkout_before_account_fields', 'pmpro_guest_checkout_add_checkout_toggle' );
