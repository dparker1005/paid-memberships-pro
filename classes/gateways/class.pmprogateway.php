<?php	
	//require_once(dirname(__FILE__) . "/class.pmprogateway.php");
	#[AllowDynamicProperties]
	abstract class PMProGateway
	{
		/**
		 * Process charges and set up subscriptions.
		 *
		 * @param MemberOrder $order The order object to process.
		 *
		 * @return bool True if successful, false otherwise.
		 */
		abstract function process(&$order);

		/**
		 * Update the payment information for a subscription.
		 *
		 * @param MemberOrder $order An order object for the susbscription to update.
		 * @return bool True if successful, false otherwise.
		 */
		function update( &$order ) {
			// If this method is being called, then the gateway does not support updating.
			$order->error  = __( 'Method update() does not exist.', 'paid-memberships-pro' );
			return false;
		}

		/**
		 * Cancels a subscription at the gateway.
		 *
		 * @param PMPro_Subscription $subscription to cancel.
		 */
		function cancel_subscription( $subscription ) {
			// If this method is being called, then the gateway does not have this function defined. Call the legacy cancel() method.
			$morder                              = new MemberOrder();
			$morder->user_id                     = $subscription->get_user_id();
			$morder->membership_id               = $subscription->get_membership_level_id();
			$morder->gateway                     = $subscription->get_gateway();
			$morder->gateway_environment         = $subscription->get_gateway_environment();
			$morder->subscription_transaction_id = $subscription->get_subscription_transaction_id();
			$this->cancel( $morder );
		}

		/**
		 * Cancel a subscription for a passed order.
		 * This is the legacy method for canceling subscriptions and will be deprecated in the future.
		 *
		 * @param MemberOrder $order The order object to cancel.
		 * @return bool True if successful, false otherwise.
		 */
		function cancel(&$order) {
			// If this method is being called, then the gateway does not support canceling subscriptions.
			$order->error = __( 'Method cancel() does not exist.', 'paid-memberships-pro' );
			return false;
		}

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
			return false;
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
		 * @deprecated TBD Define this method in individual gateways when needed.
		 */
		function authorize(&$order)
		{
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
		 * @deprecated TBD Define this method in individual gateways when needed.
		 */
		function void(&$order)
		{
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
		 * @deprecated TBD Define this method in individual gateways when needed.
		 */
		function charge(&$order)
		{
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
		 * @deprecated TBD Define this method in individual gateways when needed.
		 */
		function subscribe(&$order)
		{
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
		 * @deprecated TBD Use PMPro_Subscription instead.
		 */
		function getSubscriptionStatus(&$order)
		{
			_deprecated_function( __METHOD__, 'TBD' );
			//require a subscription id
			if(empty($order->subscription_transaction_id))
				return false;
			
			//this looks different for each gateway, but generally an array of some sort
			return array();
		}

		/**
		 * @deprecated TBD
		 */
		function getTransactionStatus(&$order)
		{			
			_deprecated_function( __METHOD__, 'TBD' );
			//this looks different for each gateway, but generally an array of some sort
			return array();
		}		
	}
