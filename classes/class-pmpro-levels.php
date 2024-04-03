<?php

class PMPro_Level {

	/**
	 * Membership level ID.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Membership level name.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Membership level description.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Membership level confirmation message.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $confirmation = '';

	/**
	 * Membership level initial payment.
	 *
	 * @since TBD
	 *
	 * @var float
	 */
	protected $initial_payment = 0.00;

	/**
	 * Membership level billing amount.
	 *
	 * @since TBD
	 *
	 * @var float
	 */
	protected $billing_amount = 0.00;

	/**
	 * Membership level cycle number.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	protected $cycle_number = 0;

	/**
	 * Membership level cycle period.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $cycle_period = '';

	/**
	 * Membership level billing limit.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	protected $billing_limit = 0;

	/**
	 * Membership level trial amount.
	 *
	 * @since TBD
	 *
	 * @var float
	 */
	protected $trial_amount = 0.00;

	/**
	 * Membership level trial limit.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	protected $trial_limit = 0;

	/**
	 * Membership level expiration number.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	protected $expiration_number = 0;

	/**
	 * Membership level expiration period.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $expiration_period = '';

	/**
	 * Membership level allow signups.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	protected $allow_signups = 1;

	public function __construct( $id = NULL ) {
		if ( $id ) {
			$this->get_membership_level( $id );
		}
	}

	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			$value = $this->$key;
		} elseif ( 'ID' == $key ) {
			// For backwards compatibility.
			_doing_it_wrong( __FUNCTION__, 'Use $this->id instead of $this->ID', 'TBD' );
			$value = $this->id;
		} elseif( 'categories' == $key ) {
			_doing_it_wrong( __FUNCTION__, 'Use pmpro_getMembershipCategories() instead of $this->categories', 'TBD' );
			$value = pmpro_getMembershipCategories( $this->id );
		} else {
			$value = get_pmpro_membership_level_meta( $this->ID, $key, true );
		}
		
		return $value;
	}

	public function __set( $key, $value ) {
		if ( isset( $this->$key ) ) {
			if ( is_int( $this->{$key} ) ) {
				$value = (int) $value;
			} elseif ( is_float( $this->{$key} ) ) {
				$value = (float) $value;
			} elseif ( is_string( $this->{$key} ) ) {
				$value = (string) $value;
			} elseif ( is_array( $this->{$key} ) ) {
				// This must be "categories".
				_doing_it_wrong( __FUNCTION__, 'Use pmpro_updateMembershipCategories() instead of $this->categories', 'TBD' );
				pmpro_updateMembershipCategories( $this->id, $value );
			}
		}
	}

	public function __isset( $property ) {
		return property_exists( $this, $property );
	}

	public static function get_level( $args ) {
		// At least one argument is required.
		if ( empty( $args ) ) {
			return null;
		}

		if ( is_numeric( $args ) ) {
			$args = [
				'id' => $args,
			];
		}

		// Invalid arguments.
		if ( ! is_array( $args ) ) {
			return null;
		}

		// Force returning of one subscription.
		$args['limit'] = 1;

		// Get the subscriptions using query arguments.
		$levels = self::get_levels( $args );

		// Check if we found any subscriptions.
		if ( empty( $levels ) ) {
			return null;
		}

		// Get the first subscription in the array.
		return reset( $levels );
	}

	/**
	 * Get the list of level objects based on query arguments.
	 *
	 * Defaults to returning the first 100 levels.
	 *
	 * @since TBD
	 *
	 * @param array $args The query arguments to use.
	 *
	 * @return PMPro_Level[] The list of level objects.
	 */
	public static function get_levels( $args = array() ) {
		global $wpdb;

		$sql_query = "SELECT ID FROM $wpdb->pmpro_membership_levels ml";
	
		$prepared = [];
		$where = [];
		$orderby = isset( $args['orderby'] ) ? $args['orderby'] : '`ml`.`id` ASC';
		$limit = isset( $args['limit'] ) ? $args['limit'] : 100;

		// Detect unsupported orderby usage (in the future we may support better syntax).
		if ( $orderby !== preg_replace( '/[^a-zA-Z0-9\s,.`]/', ' ', $orderby ) ) {
			return array();
		}

		// Now filter the query based on the arguments provided.
		// filter by ID(s).
		if ( isset( $args['id'] ) ) {
			if ( is_array( $args['id'] ) ) {
				$where[] = 'ml.id IN (' . implode( ',', array_fill( 0, count( $args['id'] ), '%d' ) ) . ')';
				$prepared = array_merge( $prepared, $args['id'] );
			} else {
				$where[] = 'ml.id = %d';
				$prepared[] = $args['id'];
			}
		}

		// filter by name.
		if ( isset( $args['name'] ) ) {
			$where[] = 'ml.name = %s';
			$prepared[] = $args['name'];
		}

		// Filter by initial payment.
		if ( isset( $args['initial_payment'] ) ) {
			$where[] = 'ml.initial_payment = %f';
			$prepared[] = $args['initial_payment'];
		}

		// Filter by billing amount.
		if ( isset( $args['billing_amount'] ) ) {
			$where[] = 'ml.billing_amount = %f';
			$prepared[] = $args['billing_amount'];
		}

		// Filter by cycle number.
		if ( isset( $args['cycle_number'] ) ) {
			$where[] = 'ml.cycle_number = %d';
			$prepared[] = $args['cycle_number'];
		}

		// Filter by cycle period.
		if ( isset( $args['cycle_period'] ) ) {
			$where[] = 'ml.cycle_period = %s';
			$prepared[] = $args['cycle_period'];
		}

		// Filter by billing limit.
		if ( isset( $args['billing_limit'] ) ) {
			$where[] = 'ml.billing_limit = %d';
			$prepared[] = $args['billing_limit'];
		}

		// Filter by trial amount.
		if ( isset( $args['trial_amount'] ) ) {
			$where[] = 'ml.trial_amount = %f';
			$prepared[] = $args['trial_amount'];
		}

		// Filter by trial limit.
		if ( isset( $args['trial_limit'] ) ) {
			$where[] = 'ml.trial_limit = %d';
			$prepared[] = $args['trial_limit'];
		}

		// Filter by expiration number.
		if ( isset( $args['expiration_number'] ) ) {
			$where[] = 'ml.expiration_number = %d';
			$prepared[] = $args['expiration_number'];
		}

		// Filter by expiration period.
		if ( isset( $args['expiration_period'] ) ) {
			$where[] = 'ml.expiration_period = %s';
			$prepared[] = $args['expiration_period'];
		}

		// Filter by allow signups.
		if ( isset( $args['allow_signups'] ) ) {
			$where[] = 'ml.allow_signups = %d';
			$prepared[] = $args['allow_signups'];
		}

		// Maybe filter the data.
		if ( ! empty( $where ) ) {
			$sql_query .= ' WHERE ' . implode( ' AND ', $where );
		}

		// Handle the orderby and limit.
		$sql_query .= " ORDER BY $orderby";
		if ( $limit ) {
			$sql_query .= " LIMIT $limit";
		}

		// Maybe prepare the query.
		if ( ! empty( $prepared ) ) {
			$sql_query = $wpdb->prepare( $sql_query, $prepared );
		}

		$level_ids = $wpdb->get_col( $sql_query );

		if ( empty( $level_ids ) ) {
			return [];
		}

		$levels = [];

		foreach ( $level_ids as $level_id ) {
			$level = new PMPro_Level( $level_id );
			if ( ! empty( $level->id ) ) {
				$levels[] = $level;
			}
		}

		return $levels;
	}

	/**
	 * Fill the object with a membership level from the database.
	 * @since 2.3
	 */
	public function get_membership_level( $id ) {
		global $wpdb;

		// Get the discount code object.
		$dcobj = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * 
				FROM $wpdb->pmpro_membership_levels
				WHERE id = %s",
				$id
			),
			OBJECT   
		);

		if ( ! empty( $dcobj ) ) {
			$this->id = $dcobj->id;
			$this->name = $dcobj->name;
			$this->description = $dcobj->description;
			$this->confirmation = $dcobj->confirmation;
			$this->initial_payment = $dcobj->initial_payment;
			$this->billing_amount = $dcobj->billing_amount;
			$this->cycle_number = $dcobj->cycle_number;
			$this->cycle_period = $dcobj->cycle_period;
			$this->billing_limit = $dcobj->billing_limit;
			$this->trial_amount = $dcobj->trial_amount;
			$this->trial_limit = $dcobj->trial_limit;
			$this->allow_signups = $dcobj->allow_signups;
			$this->expiration_number = $dcobj->expiration_number;
			$this->expiration_period = $dcobj->expiration_period;
		} else {
			return false;
		}

		return $this;
	}

	/**
	 * Save or update an existing level if the level exists.
	 * @since 2.3
	 */
	public function save() {
		global $wpdb;

		if ( empty( $this->id ) ) {
			$before_action = 'pmpro_add_membership_level';
			$after_action = 'pmpro_added_membership_level';
		} else {
			$before_action = 'pmpro_update_membership_level';
			$after_action = 'pmpro_updated_membership_level';
		}

		/**
		 * @deprecated TBD Use pmpro_save_membership_level instead.
		 */
		do_action_deprecated( $before_action, array( $this ), 'TBD', 'pmpro_save_membership_level' );

		pmpro_insert_or_replace(
			$wpdb->pmpro_membership_levels,
			array(
				'id'=> $this->id,
				'name' => $this->name,
				'description' => $this->description,
				'confirmation' => $this->confirmation,
				'initial_payment' => $this->initial_payment,
				'billing_amount' => $this->billing_amount,
				'cycle_number' => $this->cycle_number,
				'cycle_period' => $this->cycle_period,
				'billing_limit' => $this->billing_limit,
				'trial_amount' => $this->trial_amount,
				'trial_limit' => $this->trial_limit,
				'expiration_number' => $this->expiration_number,
				'expiration_period' => $this->expiration_period,
				'allow_signups' => $this->allow_signups
			),
			array(
				'%d',		//id
				'%s',		//name
				'%s',		//description
				'%s',		//confirmation
				'%f',		//initial_payment
				'%f',		//billing_amount
				'%d',		//cycle_number
				'%s',		//cycle_period
				'%d',		//billing_limit
				'%f',		//trial_amount
				'%d',		//trial_limit
				'%d',		//expiration_number
				'%s',		//expiration_period
				'%d',		//allow_signups
			)
		);

		if ( $wpdb->insert_id ) {
		   $this->id = $wpdb->insert_id;
		}        

		/**
		 * @deprecated TBD Use pmpro_save_membership_level instead.
		 */
		do_action_deprecated( $after_action, array( $this ), 'TBD', 'pmpro_save_membership_level' );
	}

	/**
	 * Delete a membership level and categories.
	 * @since 2.3
	 */
	public function delete() {

		if ( empty( $this->id ) ) {
			return false;
		}

		global $wpdb;
		$r1 = false; // Remove level.
		$r2 = false; // Remove categories from level.
		$r3 = false; // Remove users from level.

		if ( $wpdb->delete( $wpdb->pmpro_membership_levels, array('id' => $this->id), array('%d') ) ) {
			$r1 = true;
		}

		if ( $wpdb->delete( $wpdb->pmpro_memberships_categories, array('membership_id' => $this->id), array('%d') ) ) {
			$r2 = true;
		}

		//Delete the memberships associated with this level - we're not cancelling them though
		$deleted_membership_users = $wpdb->delete(
			$wpdb->pmpro_memberships_users,
			array( 'membership_id' => $this->id ),
			array( '%d' )
		);
		
		if( $deleted_membership_users !== false ) {
			$r3 = true;
		}

		// Remove the level from the level group.
		$wpdb->delete( $wpdb->pmpro_membership_levels_groups, array( 'level' => $this->id ) );
			
		if ( $r1 == true && $r2 == true && $r3 == true ) {
			return true;
		} elseif ( $r1 == true && $r2 == false && $r3 == false ) {
			return 'Only the level was deleted. Users may still be assigned to this level';
		} elseif ( $r1 == false && $r2 == true && $r3 == false ) {
			return 'Only categories were deleted. Users may still be assigned to this level.';
		} elseif( $r1 == false && $r2 == false && $r3 == true ) {
			return 'Only users were removed from this level.';
		} else {
			return false;
		}

	}

	/**
	 * Get a list of category ID's that belong to a membership level.
	 * @since 2.3
	 * @deprecated TBD Use pmpro_getMembershipCategories() insetad.
	 * @return array An array of category ID's.
	 */
	public function get_membership_level_categories( $id ) {
		_deprecated_function( __FUNCTION__, 'TBD' );
		return pmpro_getMembershipCategories( $id );
	}

	/**
	 * Set the categories for a membership level.
	 * @deprecated TBD Use pmpro_updateMembershipCategories() instead.
	 */
	public function set_categories( $categories ) {
		_deprecated_function( __FUNCTION__, 'TBD' );

		// Update class value.
		$this->categories = $categories;

		// Update the database.
		pmpro_updateMembershipCategories( $this->id, $categories );
	}

	/**
	 * Function to get an empty membership level object.
	 *
	 * @deprecated TBD
	 */
	public function get_empty_membership_level() {
		_deprecated_function( __FUNCTION__, 'TBD' );
		
		$this->id = 0;
		$this->name = '';
		$this->description = '';
		$this->confirmation = '';
		$this->initial_payment = 0.00;
		$this->billing_amount = 0.00;
		$this->cycle_number = 0;
		$this->cycle_period = '';
		$this->billing_limit = 0;
		$this->trial_amount = 0.00;
		$this->trial_limit = 0;
		$this->expiration_number = 0;
		$this->expiration_period = '';
		$this->allow_signups = 1;
		$this->categories = array();

		return $this;
	}

	/**
	 * Get the object of a membership level from the database.
	 * @since 2.3
	 * @deprecated TBD
	 * @return object The level object.
	 */
	public function get_membership_level_object( $id ) {
		_deprecated_function( __FUNCTION__, 'TBD' );
		global $wpdb;

		// Get the discount code object.
		$dcobj = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * 
				FROM $wpdb->pmpro_membership_levels
				WHERE id = %s",
				$id
			),
			OBJECT   
		);

		return $dcobj;
	}
} // end of class

class PMPro_Membership_Level extends PMPro_Level {} // For backwards compatibility. We will eventually throw a deprecation warning and later remove this class.
