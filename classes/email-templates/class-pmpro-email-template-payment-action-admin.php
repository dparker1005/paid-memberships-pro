<?php
class PMPro_Email_Template_Payment_Action_Admin extends PMPro_Email_Template {

	/**
	 * The user object of the user to send the email to.
	 *
	 * @var WP_User
	 */
	protected $user;

	/**
	 * The URL of the order.
	 * 
	 */
	protected $order_url;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 *
	 * @param WP_User $user The user object of the user to send the email to.
	 * @param int $membership_id The membership level id of the membership level that expired.
	 * @param string $order_url The URL of the order.
	 */
	public function __construct( WP_User $user, string $order_url ) {
		$this->user = $user;
		$this->order_url = $order_url;
	}

	/**
	 * Get the email template slug.
	 *
	 * @since TBD
	 *
	 * @return string The email template slug.
	 */
	public static function get_template_slug() {
		return 'payment_action_admin';
	}

	/**
	 * Get the "nice name" of the email template.
	 *
	 * @since TBD
	 *
	 * @return string The "nice name" of the email template.
	 */
	public static function get_template_name() {
		return __( 'Payment Action Required (admin)', 'paid-memberships-pro' );
	}

	/**
	 * Get "help text" to display to the admin when editing the email template.
	 *
	 * @since TBD
	 *
	 * @return string The "help text" to display to the admin when editing the email template.
	 */
	public static function get_template_description() {
		return __( 'This email is sent to the site administrator when an attempted membership checkout requires additional customer authentication.', 'paid-memberships-pro' );
	}

	/**
	 * Get the default subject for the email.
	 *
	 * @since TBD
	 *
	 * @return string The default subject for the email.
	 */
	public static function get_default_subject() {
		return __( "Payment action required: membership for !!user_login!! at !!sitename!!", 'paid-memberships-pro' );
	}

	/**
	 * Get the default body content for the email.
	 *
	 * @since TBD
	 *
	 * @return string The default body content for the email.
	 */
	public static function get_default_body() {
		return __( '<p>A payment at !!sitename!! for !!user_login!! requires additional customer authentication to complete.</p>
		<p>Below is a copy of the email we sent to !!user_email!! to notify them that they need to complete their payment:</p>
		
		<p>Customer authentication is required to finish setting up your subscription at !!sitename!!.</p>
		
		<p>Please complete the verification steps issued by your payment provider at the following link:</p>
		<p>!!order_url!!</p>', 'paid-memberships-pro' );
	}

	/**
	 * Get the email address to send the email to.
	 *
	 * @since TBD
	 *
	 * @return string The email address to send the email to.
	 */
	public function get_recipient_email() {
		return get_bloginfo( 'admin_email' );
	}

	/**
	 * Get the name of the email recipient.
	 *
	 * @since TBD
	 *
	 * @return string The name of the email recipient.
	 */
	public function get_recipient_name() {
		//get user by email
		$user = get_user_by( 'email', $this->get_recipient_email() );
		return $user->display_name;
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
			'!!name!!' => __( 'The display name of the user.', 'paid-memberships-pro' ),
			'!!user_login!!' => __( 'The username of the user.', 'paid-memberships-pro' ),
			'!!display_name!!' => __( 'The display name of the user.', 'paid-memberships-pro' ),
			'!!order_link!!' => __( 'The URL of the order.', 'paid-memberships-pro' ),
			'!!order_url!!' => __( 'The URL of the order.', 'paid-memberships-pro' ),
			'!!invoice_url!!' => __( 'The URL of the order. Legacy purpose', 'paid-memberships-pro' ),
			'!!levels_url!!' => __( 'The URL of the membership levels page.', 'paid-memberships-pro' ),
		);
	}

	/**
	 * Get the email template variables for the email.
	 *
	 * @since TBD
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public function get_email_template_variables() {
		$user = $this->user;
		$order_url = $this->order_url;
		return array(
			"subject" => $this->subject,
			"name" => $user->display_name,
			"user_login" => $user->user_login,
			"display_name" => $user->display_name,
			"order_link" => $order_url,
			"order_url" => $order_url,
			"invoice_url" => $order_url, // Legacy purpose, remove in future version
			"levels_url" => pmpro_url( 'levels' )
		);
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
function pmpro_email_templates_payment_action_admin( $email_templates ) {
	$email_templates['payment_action_admin'] = 'PMPro_Email_Template_Payment_Action_Admin';
	return $email_templates;
}
add_filter( 'pmpro_email_templates', 'pmpro_email_templates_payment_action_admin' );

