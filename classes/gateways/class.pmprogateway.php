<?php	
/**
 * Base class for payment gateway classes.
 * Holds code that is used for the "testing" gateway and can be used as a starting point for other gateways.
 */
class PMProGateway {
	/**
	 * Check if the gateway supports a certain feature.
	 * 
	 * @since 3.0
	 * 
	 * @param string $feature The feature to check for.
	 * @return bool|string Whether the gateway supports the requested. A string may be returned in cases where a feature has different variations of support.
	 */
	public static function supports( $feature ) {
		// The base gateway doesn't support anything.			
		$supports = array(
			'subscription_sync' => false,     // bool - Whether the gateway supports subscription synchronization.
			'payment_method_updates' => false // bool|'all'|'individual' - Whether the gateway supports payment method updates. 'all' means all subscriptions are updated when a payment method is updated. 'individual' means each subscription is updated individually.
		);

		if ( empty( $supports[$feature] ) ) {
			return false;
		}

		return $supports[$feature];
	}

	/**
	 * Processes any payments and sets up subscriptions for an order at checkout.
	 *
	 * @param MemberOrder $order The order object to process.
	 *
	 * @return bool True if successful, false if not.
	 */
	function process( &$order ) {
		// Process initial payment if neeeded.
		if ( ! empty( $order->InitialPayment ) ) {
			// Simulate a successful charge. Would return false if the charge failed.
			$order->payment_transaction_id = 'TEST' . $order->code;
		}

		// Set up the subscription if needed.
		if ( pmpro_isLevelRecurring( $order->membership_level ) ) {
			// Simulate a successful subscription processing. Would return false if the subscription creation failed.
			$order->subscription_transaction_id = 'TEST' . $order->code;
		}

		// Both the initial payment and subscription setup were successful.
		$order->status = 'success';
		return true;
	}

	/**
	 * Updates billing information for the subscription associted with the passed order.
	 *
	 * @param MemberOrder $order The order object to update.
	 *
	 * @return bool True if successful, false if not.
	 */
	function update( &$order ) {
		// Simulate a successful billing update.
		return true;
	}

	/**
	 * Cancels the subscription associated with the passed order at the gateway.
	 *
	 * @param MemberOrder $order The order object to cancel.
	 *
	 * @return bool True if successful, false if not.
	 */
	function cancel( &$order ) {
		//require a subscription id
		if(empty($order->subscription_transaction_id))
			return false;
		
		//simulate a successful cancel			
		$order->updateStatus( 'cancelled' );					
		return true;
	}

	/**
	 * Synchronizes a subscription with this payment gateway.
	 *
	 * @since 3.0
	 *
	 * @param PMPro_Subscription $subscription The subscription to synchronize.
	 * @return string|null Error message is returned if update fails.
	 */
	public function update_subscription_info( $subscription ) {
		// Track the fields that need to be updated.
		$update_array = array();

		// Update the start date to the date of the first order for this subscription if it
		// it is earlier than the current start date.
		$oldest_orders = $subscription->get_orders( [
			'limit'   => 1,
			'orderby' => '`timestamp` ASC, `id` ASC',
		] );
		if ( ! empty( $oldest_orders ) ) {
			$oldest_order = current( $oldest_orders );
			if ( empty( $subscription->get_startdate() ) || $oldest_order->getTimestamp( true ) < strtotime( $subscription->get_startdate() ) ) {
				$update_array['startdate'] = date_i18n( 'Y-m-d H:i:s', $oldest_order->getTimestamp( true ) );
			}
		}

		// If the next payment date has passed, update the next payment date based on the most recent order.
		if ( strtotime( $subscription->get_next_payment_date() ) < time() && ! empty( $subscription->get_cycle_number() ) ) {
			// Only update the next payment date if we are not at checkout or if we don't have a next payment date yet.
			// We don't want to update profile start dates set at checkout.
			if ( ! pmpro_is_checkout() || empty( $subscription->get_next_payment_date() ) ) {
				$newest_orders = $subscription->get_orders( array( 'limit' => 1 ) );
				if ( ! empty( $newest_orders ) ) {
					// Get the most recent order.
					$newest_order = current( $newest_orders );

					// Calculate the next payment date.
					$update_array['next_payment_date'] = date_i18n( 'Y-m-d H:i:s', strtotime( '+ ' . $subscription->get_cycle_number() . ' ' . $subscription->get_cycle_period(), $newest_order->getTimestamp( true ) ) );
				}
			}
		}

		// Update the subscription.
		$subscription->set( $update_array );
	}

	/**
	 * Deprecated method.
	 *
	 * @deprecated TBD.
	 */
	function authorize( &$order ) {
		_deprecated_function( __METHOD__, 'TBD' );
		//create a code for the order
		if(empty($order->code))
			$order->code = $order->getRandomCode();
		
		//simulate a successful authorization
		$order->payment_transaction_id = "TEST" . $order->code;
		$order->updateStatus("authorized");													
		return true;					
	}

	/**
	 * Deprecated method.
	 *
	 * @deprecated TBD.
	 */
	function void( &$order ) {
		_deprecated_function( __METHOD__, 'TBD' );
		//need a transaction id
		if(empty($order->payment_transaction_id))
			return false;
			
		//simulate a successful void
		$order->payment_transaction_id = "TEST" . $order->code;
		$order->updateStatus("voided");					
		return true;
	}

	/**
	 * Deprecated method.
	 *
	 * @deprecated TBD.
	 */
	function charge( &$order ) {
		_deprecated_function( __METHOD__, 'TBD' );
		//create a code for the order
		if(empty($order->code))
			$order->code = $order->getRandomCode();
		
		//simulate a successful charge
		$order->payment_transaction_id = "TEST" . $order->code;
		$order->updateStatus("success");					
		return true;						
	}

	/**
	 * Deprecated method.
	 *
	 * @deprecated TBD.
	 */
	function subscribe( &$order ) {
		_deprecated_function( __METHOD__, 'TBD' );
		//create a code for the order
		if(empty($order->code))
			$order->code = $order->getRandomCode();
		
		//filter order before subscription. use with care.
		$order = apply_filters("pmpro_subscribe_order", $order, $this);
					
		//simulate a successful subscription processing
		$order->status = "success";		
		$order->subscription_transaction_id = "TEST" . $order->code;				
		return true;
	}

	/**
	 * Deprecated method.
	 *
	 * @deprecated TBD.
	 */
	function getSubscriptionStatus( &$order ) {
		_deprecated_function( __METHOD__, 'TBD' );
		//require a subscription id
		if(empty($order->subscription_transaction_id))
			return false;
		
		//this looks different for each gateway, but generally an array of some sort
		return array();
	}

	/**
	 * Deprecated method.
	 *
	 * @deprecated TBD.
	 */
	function getTransactionStatus( &$order ) {
		_deprecated_function( __METHOD__, 'TBD' );
		//this looks different for each gateway, but generally an array of some sort
		return array();
	}
}
