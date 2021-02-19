<?php

class PMPro_Log {

	protected $slug;
	protected $entries;

	// Settings.
	protected $enabled;
	protected $max_entries;

	public function __construct( $slug ) {
		$this->slug    = $slug;
		$this->entries = null;

		$settings = pmpro_get_settings_for_log( $slug );
		$this->enabled     = ! empty( $settings['enabled'] );
		$this->max_entries = intval( $settings['max_entries'] );
	}

	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			$value = $this->$key;
		} else {
			$value = null;
		}
		return $value;
	}

	public function __isset( $key ){
        return isset( $this->$key );
    }

	private function pull_entries() {
		if ( null !== $this->entries ) {
			// Already pulled.
			return;
		}

		// Get entries from database.
		$this->entries = get_option( 'pmpro_log_' . $this->slug, array() );
		if ( 0 !== $this->max_entries ) {
			$this->entries = array_slice( $this->entries, $this->max_entries * -1 , $this->max_entries );
		}
	}

	private function push_entries() {
		if ( null === $this->entries ) {
			// Nothing to update
			return;
		}

		// Update entries in database.
		if ( 0 !== $this->max_entries ) {
			$this->entries = array_slice( $this->entries, $this->max_entries * -1 , $this->max_entries );
		}
		$this->entries = update_option( 'pmpro_log_' . $this->slug, $this->entries );
	}

	public function add_entry( $message, $tags = array() ) {
		if ( empty( $this->enabled ) ) {
			// Log is not enabled. Do nothing.
			return;
		}

		if ( is_string( $tags ) ) {
			$tags = array( $tags );
		}

		$this->pull_entries();
		$this->entries[] = array(
			'timestamp' => date('Y-m-d H:i:s'),
			'message'   => $message,
			'tags'      => $tags
		);
		$this->push_entries();
	}

	public function clear_log() {
		$this->entries = array();
		$this->push_entries();
	}

	public function get_entries_json() {
		$this->pull_entries();
		return json_encode( $this->entries );
	}

	public function print_entries() {
		$this->pull_entries();
		$newline = "&#13;&#10";
		foreach ( $this->entries as $entry ) {
			$output = '-----------------' . $newline;
			$output .= 'Logged on ' . $entry['timestamp'] . $newline;
			$output .= 'Tags: ' . implode( ', ', $entry['tags'] ) . $newline;
			$output .= 'Message: ' . $entry['message'] . $newline;
			echo $output;
		}
	}
} // end of class