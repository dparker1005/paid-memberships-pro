<?php

class PMPro_Email_Template_Billing_Failure extends PMPro_Email_Template {

	/**
	 * The user object of the user to send the email to.
	 *
	 * @var WP_User
	 */
	protected $user;

	/**
	 * The {@link MemberOrder} object of the order that was updated.
	 *
	 * @var MemberOrder
	 */
	protected $order;

	/**
	 * Constructor.
	 *
	 * @since 3.4
	 *
	 * @param WP_User $user The user object of the user to send the email to.
	 * @param MemberOrder $order The order object that is associated to the member.
	 */
	public function __construct( WP_User $user,  MemberOrder $order ) {
		$this->user = $user;
		$this->order = $order;
	}

	/**
	 * Get the email template slug.
	 *
	 * @since 3.4
	 *
	 * @return string The email template slug.
	 */
	public static function get_template_slug() {
		return 'billing_failure';
	}

	/**
	 * Get the "nice name" of the email template.
	 *
	 * @since 3.4
	 *
	 * @return string The "nice name" of the email template.
	 */
	public static function get_template_name() {
		return esc_html__( 'Payment Failure', 'paid-memberships-pro' );
	}

	/**
	 * Get "help text" to display to the admin when editing the email template.
	 *
	 * @since 3.4
	 *
	 * @return string The help text.
	 */
	public static function get_template_description() {
		return esc_html__( 'This email is sent out if a recurring payment has failed, usually due to an expired or cancelled credit card. This email is sent to the member to allowing them time to update payment information without a disruption in access to your site.', 'paid-memberships-pro' );
	}

	/**
	 * Get the email subject.
	 *
	 * @since 3.4
	 *
	 * @return string The email subject.
	 */
	public static function get_default_subject() {
		return esc_html__( "Membership payment failed at !!sitename!!", 'paid-memberships-pro' );
	}

	/**
	 * Get the email body.
	 *
	 * @since 3.4
	 *
	 * @return string The email body.
	 */
	public static function get_default_body() {
		return wp_kses_post( '<p>The current subscription payment for level !!membership_level_name!! at !!sitename!! membership has failed. <strong>Please click the following link to log in and update your billing information to avoid account suspension.</strong><br/> !!login_url!! </p>
		<p>Account: !!display_name!! (!!user_email!!)</p>', 'paid-memberships-pro' );
	}

		/**
	 * Get the email address to send the email to.
	 *
	 * @since 3.4
	 *
	 * @return string The email address to send the email to.
	 */
	public function get_recipient_email() {
		return $this->user->user_email;
	}

	/**
	 * Get the name of the email recipient.
	 *
	 * @since 3.4
	 *
	 * @return string The name of the email recipient.
	 */
	public function get_recipient_name() {
		return $this->user->display_name;
	}

	/**
	 * Get the email template variables for the email paired with a description of the variable.
	 *
	 * @since 3.4
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public static function get_email_template_variables_with_description() {
		return array(
			'!!subject!!' => esc_html__( 'The default subject for the email. This will be removed in a future version.', 'paid-memberships-pro' ),
			'!!name!!' => esc_html__( 'The display name of the user.', 'paid-memberships-pro' ),
			'!!user_login!!' => esc_html__( 'The username of the user.', 'paid-memberships-pro' ),
			'!!user_email!!' => esc_html__( 'The email address of the user billing failed', 'paid-memberships-pro' ),
			'!!display_name!!' => esc_html__( 'The display name of the user billing failed', 'paid-memberships-pro' ),
			'!!membership_id!!' => esc_html__( 'The ID of the membership level.', 'paid-memberships-pro' ),
			'!!membership_level_name!!' => esc_html__( 'The name of the membership level.', 'paid-memberships-pro' ),
			'!!billing_name!!' => esc_html__( 'Billing Info Name', 'paid-memberships-pro' ),
			'!!billing_street!!' => esc_html__( 'Billing Info Street', 'paid-memberships-pro' ),
			'!!billing_street2!!' => esc_html__( 'Billing Info Street 2', 'paid-memberships-pro' ),
			'!!billing_city!!' => esc_html__( 'Billing Info City', 'paid-memberships-pro' ),
			'!!billing_state!!' => esc_html__( 'Billing Info State', 'paid-memberships-pro' ),
			'!!billing_zip!!' => esc_html__( 'Billing Info Zip', 'paid-memberships-pro' ),
			'!!billing_country!!' => esc_html__( 'Billing Info Country', 'paid-memberships-pro' ),
			'!!billing_phone!!' => esc_html__( 'Billing Info Phone', 'paid-memberships-pro' ),
			'!!billing_address!!' => esc_html__( 'Billing Info Complete Address', 'paid-memberships-pro' ),
			'!!cardtype!!' => esc_html__( 'Credit Card Type', 'paid-memberships-pro' ),
			'!!accountnumber!!' => esc_html__( 'Credit Card Number (last 4 digits)', 'paid-memberships-pro' ),
			'!!expirationmonth!!' => esc_html__( 'Credit Card Expiration Month (mm format)', 'paid-memberships-pro' ),
			'!!expirationyear!!' => esc_html__( 'Credit Card Expiration Year (yyyy format)', 'paid-memberships-pro' ),

		);
	}

	/**
	 * Get the email template variables for the email.
	 *
	 * @since 3.4
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public function get_email_template_variables() {
		$order = $this->order;
		$user = $this->user;
		$membership_level = pmpro_getLevel( $order->membership_id );
		return array(
			'subject' => $this->get_default_subject(),
			'name' => $user->display_name,
			'user_login' => $user->user_login,
			'membership_id' => $membership_level->id,
			'membership_level_name' => $membership_level->name,
			'display_name' => $user->display_name,
			'user_email' => $user->user_email,
			'billing_name' => $order->billing->name,
			'billing_street' => $order->billing->street,
			'billing_street2' => $order->billing->street2,
			'billing_city' => $order->billing->city,
			'billing_state' => $order->billing->state,
			'billing_zip' => $order->billing->zip,
			'billing_country' => $order->billing->country,
			'billing_phone' => $order->billing->phone,
			'billing_address'=> pmpro_formatAddress( $order->billing->name,
				$order->billing->street,
				$order->billing->street2,
				$order->billing->city,
				$order->billing->state,
				$order->billing->zip,
				$order->billing->country,
				$order->billing->phone ),
			'cardtype' => $order->cardtype,
			'accountnumber' => hideCardNumber( $order->accountnumber ),
			'expirationmonth' => $order->expirationmonth,
			'expirationyear' => $order->expirationyear,
		);
	}
}

/**
 * Register the email template.
 *
 * @since 3.4
 *
 * @param array $email_templates The email templates (template slug => email template class name)
 * @return array The modified email templates array.
 */
function pmpro_email_templates_billing_failure( $email_templates ) {
	$email_templates['billing_failure'] = 'PMPro_Email_Template_Billing_Failure';
	return $email_templates;
}
add_filter( 'pmpro_email_templates', 'pmpro_email_templates_billing_failure' );