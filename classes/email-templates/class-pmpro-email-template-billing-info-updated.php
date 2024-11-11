<?php

class PMPro_Email_Template_Billing_Info_Updated extends PMPro_Email_Template {

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
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return string The email template slug.
	 */
	public static function get_template_slug() {
		return 'billing';
	}

	/**
	 * Get the "nice name" of the email template.
	 *
	 * @since TBD
	 *
	 * @return string The "nice name" of the email template.
	 */
	public static function get_template_name() {
		return __( 'Billing Information Updated', 'paid-memberships-pro' );
	}

	/**
	 * Get "help text" to display to the admin when editing the email template.
	 *
	 * @since TBD
	 *
	 * @return string The help text.
	 */
	public static function get_template_description() {
		return __( 'Members can update the payment method associated with their recurring subscription. This email is sent to the member as a confirmation of a payment method update', 'paid-memberships-pro' );
	}

	/**
	 * Get the default subject for the email.
	 *
	 * @since TBD
	 *
	 * @return string The default subject for the email.
	 */
	public static function get_default_subject() {
		return __( 'Your billing information has been updated at !!sitename!!', 'paid-memberships-pro' );
	}

	/**
	 * Get the default body content for the email.
	 *
	 * @since TBD
	 *
	 * @return string The default body content for the email.
	 */
	public static function get_default_body() {
		return __( '<p>Your billing information at !!sitename!! has been changed.</p><p>Account: !!display_name!! (!!user_email!!)</p>
		<p>
			Billing Information:<br />
			!!billing_address!!
		</p>
		<p>
			!!cardtype!!: !!accountnumber!!<br />
			Expires: !!expirationmonth!!/!!expirationyear!!
		</p>
		<p>If you did not request a billing information change please contact us at !!siteemail!!</p>
		<p>Log in to your membership account here: !!login_url!!</p>', 'paid-memberships-pro' );
	}

	/**
	 * Get the email template variables for the email paired with a description of the variable.
	 *
	 * @since TBD
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public static function get_email_template_variables_with_description() {
		return array(
			'!!subject!!' => __( 'The default subject for the email. This will be removed in a future version.', 'paid-memberships-pro' ),
			'!!user_email!!' => __( 'The email address of the user who cancelled their membership.', 'paid-memberships-pro' ),
			'!!display_name!!' => __( 'The display name of the user who cancelled their membership.', 'paid-memberships-pro' ),
			'!!name!!' => __( 'The display name of the user.', 'paid-memberships-pro' ),
			'!!user_login!!' => __( 'The username of the user.', 'paid-memberships-pro' ),
			'!!membership_id!!' => __( 'The ID of the membership level.', 'paid-memberships-pro' ),
			'!!membership_level_name!!' => __( 'The name of the membership level.', 'paid-memberships-pro' ),			
			'!!billing_name!!' => __( 'Billing Info Name', 'paid-memberships-pro' ),
			'!!billing_street!!' => __( 'Billing Info Street', 'paid-memberships-pro' ),
			'!!billing_street2!!' => __( 'Billing Info Street 2', 'paid-memberships-pro' ),
			'!!billing_city!!' => __( 'Billing Info City', 'paid-memberships-pro' ),
			'!!billing_state!!' => __( 'Billing Info State', 'paid-memberships-pro' ),
			'!!billing_zip!!' => __( 'Billing Info Zip', 'paid-memberships-pro' ),
			'!!billing_country!!' => __( 'Billing Info Country', 'paid-memberships-pro' ),
			'!!billing_phone!!' => __( 'Billing Info Phone', 'paid-memberships-pro' ),
			'!!billing_address!!' => __( 'Billing Info Complete Address', 'paid-memberships-pro' ),
			'!!cardtype!!' => __( 'Credit Card Type', 'paid-memberships-pro' ),
			'!!accountnumber!!' => __( 'Credit Card Number (last 4 digits)', 'paid-memberships-pro' ),
			'!!expirationmonth!!' => __( 'Credit Card Expiration Month (mm format)', 'paid-memberships-pro' ),
			'!!expirationyear!!' => __( 'Credit Card Expiration Year (yyyy format)', 'paid-memberships-pro' ),
		);
	}

	/**
	 * Get the email address to send the email to.
	 *
	 * @since TBD
	 *
	 * @return string The email address to send the email to.
	 */
	public function get_recipient_email() {
		return $this->user->user_email;
	}

	/**
	 * Get the name of the email recipient.
	 *
	 * @since TBD
	 *
	 * @return string The name of the email recipient.
	 */
	public function get_recipient_name() {
		return $this->user->display_name;
	}

	/**
	 * Get the email template variables for the email.
	 *
	 * @since TBD
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public function get_email_template_variables() {
		$order = $this->order;
		$user = $this->user;
		$membership_level = pmpro_getLevel( $order->membership_id );
		$email_template_variables = array(
			'subject' => $this->get_default_subject(),
			'name'=> $this->get_recipient_name(),
			'user_login' => $user->user_login,
			'membership_id' => $membership_level->id,
			'membership_level_name' => $membership_level->name,
			'user_email' => $user->user_email,
			'display_name' => $this->get_recipient_name(),
			'billing_name' => $order->billing->name,
			'billing_street' => $order->billing->street,
			'billing_street2' => $order->billing->street2,
			'billing_city' => $order->billing->city,
			'billing_state' => $order->billing->state,
			'billing_zip' => $order->billing->zip,
			'billing_country' => $order->billing->country,
			'billing_phone' => $order->billing->phone,
			'billing_address' => pmpro_formatAddress( $order->billing->name,
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
		return $email_template_variables;
	}

}

/**
 * Register the email template.
 *
 * @since TBD
 *
 * @param array $email_templates The email templates (template slug => email template class name)
 * @return array The modified email templates array.
 */
function pmpro_email_templates_billing_info_updated( $email_templates ) {
	$email_templates['billing'] = 'PMPro_Email_Template_Billing_Info_Updated';

	return $email_templates;
}
add_filter( 'pmpro_email_templates', 'pmpro_email_templates_billing_info_updated' );