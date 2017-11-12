<?php # -*- coding: utf-8 -*-

namespace TheDramatist\WPFlashMessage;

/**
 * Class FlashMessages
 *
 * @author  Khan M Rashedun-Naby, TheDramatist
 * @link    http://thedramatist.me
 * @version 1.0.0
 * @since   1.0.0
 * @package TheDramatist\WPFlashMessage
 * @license MIT
 */
class FlashMessages {
	// Message types and shortcuts
	const INFO     = 'i';
	const SUCCESS  = 's';
	const WARNING  = 'w';
	const ERROR    = 'e';

	// Default message type
	const DEFAULT_TYPE = self::INFO;

	// Message types and order
	//
	// Note:  The order that message types are listed here is the same order
	// they will be printed on the screen (ie: when displaying all messages)
	//
	// This can be overridden with the display() method by manually defining
	// the message types in the order you want to display them. For example:
	//
	protected $msg_types = [
		self::ERROR   => 'error',
		self::WARNING => 'warning',
		self::SUCCESS => 'success',
		self::INFO    => 'info',
	];

	// Each message gets wrapped in this
	protected $msg_wrapper = "<div class='%s'>%s</div>\n";

	// Prepend and append to each message (inside of the wrapper)
	protected $msg_before = '';

	protected $msg_after = '';

	// HTML for the close button
	protected $close_btn = '';

	// CSS Classes
	protected $sticky_css_class = 'sticky';

	protected $msg_css_class = 'alert dismissable';

	protected $css_class_map = [
		self::INFO    => 'alert-info',
		self::SUCCESS => 'alert-success',
		self::WARNING => 'alert-warning',
		self::ERROR   => 'alert-danger',
	];

	// Where to redirect the user after a message is queued
	protected $redirect_url = null;

	// The unique ID for the session/messages (do not edit)
	protected $msg_id;

	/**
	 * __construct
	 *
	 */
	public function __construct() {
		// Generate a unique ID for this user and session
		$this->msg_id = sha1( uniqid() );
		// Create session array to hold our messages if it doesn't already exist
		if (
			! isset( $_SESSION ) ||
			! array_key_exists( 'flash_messages', $_SESSION )
		) {
			$_SESSION['flash_messages'] = [];
		}

	}

	/**
	 * Add an info message
	 *
	 * @param  string  $message      The message text
	 * @param  string  $redirect_url Where to redirect once the message is added
	 * @param  boolean $sticky       Sticky the message (hides the close button)
	 *
	 * @return object
	 *
	 */
	public function info(
		$message,
		$redirect_url = null,
		$sticky = false
	) {
		return $this->add( $message, self::INFO, $redirect_url, $sticky );
	}

	/**
	 * Add a flash message to the session data
	 *
	 * @param  string  $message         The message text
	 * @param  string  $type            The $msg_type
	 * @param  string  $redirect_url    Where to redirect once the message is added
	 * @param  boolean $sticky          Whether or not the message is stickied
	 *
	 * @return object
	 *
	 */
	public function add(
		$message,
		$type = self::DEFAULT_TYPE,
		$redirect_url = null,
		$sticky = false
	) {
		// Make sure a message and valid type was passed
		if ( ! isset( $message[0] ) ) {
			return false;
		}
		if ( strlen( trim( $type ) ) > 1 ) {
			$type = strtolower( $type[0] );
		}
		if ( ! array_key_exists( $type, $this->msg_types ) ) {
			$type = $this->DEFAULT_TYPE;
		}

		// Add the message to the session data
		if (
			! is_array( $_SESSION['flash_messages'] ) ||
			! array_key_exists( $type, $_SESSION['flash_messages'] )
		) {
			$_SESSION['flash_messages'][ $type ] = [];
		}
		$_SESSION[ 'flash_messages' ][ $type ][] = [
			'sticky'  => $sticky,
			'message' => $message,
		];
		// Handle the redirect if needed
		if ( ! is_null( $redirect_url ) ) {
			$this->redirect_url = $redirect_url;
		}
		$this->do_redirect();
	}

	/**
	 * Redirect the user if a URL was given
	 *
	 * @return object
	 *
	 */
	protected function do_redirect() {
		if ( $this->redirect_url ) {
			header( 'Location: ' . $this->redirect_url );
			exit();
		} else {
			wp_die(
				esc_html__(
					'Something went wrong. Please try again.',
					'trial-add-on-for-woocommerce'
				)
			);
		}
	}

	/**
	 * Add a success message
	 *
	 * @param  string  $message      The message text
	 * @param  string  $redirect_url Where to redirect once the message is added
	 * @param  boolean $sticky       Sticky the message (hides the close button)
	 *
	 * @return object
	 *
	 */
	public function success(
		$message,
		$redirect_url = null,
		$sticky = false
	) {
		return $this->add( $message, self::SUCCESS, $redirect_url, $sticky );
	}

	/**
	 * Add a warning message
	 *
	 * @param  string  $message      The message text
	 * @param  string  $redirect_url Where to redirect once the message is added
	 * @param  boolean $sticky       Sticky the message (hides the close button)
	 *
	 * @return object
	 *
	 */
	public function warning(
		$message,
		$redirect_url = null,
		$sticky = false
	) {
		return $this->add( $message, self::WARNING, $redirect_url, $sticky );
	}

	/**
	 * Add an error message
	 *
	 * @param  string  $message      The message text
	 * @param  string  $redirect_url Where to redirect once the message is added
	 * @param  boolean $sticky       Sticky the message (hides the close button)
	 *
	 * @return object
	 *
	 */
	public function error(
		$message,
		$redirect_url = null,
		$sticky = false
	) {
		return $this->add( $message, self::ERROR, $redirect_url, $sticky );
	}

	/**
	 * Add a sticky message
	 *
	 * @param  bool   $message      The message text
	 * @param  string $redirect_url Where to redirect once the message is added
	 * @param  string $type         The $msg_type
	 *
	 * @return object
	 *
	 */
	public function sticky(
		$message = true,
		$redirect_url = null,
		$type = self::DEFAULT_TYPE
	) {
		return $this->add( $message, $type, $redirect_url, true );
	}

	/**
	 * Display the flash messages
	 *
	 * @param  mixed   $types   (null)  print all of the message types
	 *                          (array)  print the given message types
	 *                          (string)   print a single message type
	 * @param  boolean $print   Whether to print the data or return it
	 *
	 * @return string
	 *
	 */
	public function display( $types = null, $print = true ) {
		if ( ! isset( $_SESSION['flash_messages'] ) ) {
			return false;
		}

		$output = '';
		// Print all the message types
		if ( is_null( $types ) || ! $types || ( is_array( $types ) && empty( $types ) ) ) {
			$types = array_keys( $this->msg_types );
			// Print multiple message types (as defined by an array)
		} elseif ( is_array( $types ) && ! empty( $types ) ) {
			$the_types = $types;
			$types    = [];
			foreach ( $the_types as $type ) {
				$types[] = strtolower( $type[0] );
			}

			// Print only a single message type
		} else {
			$types = [ strtolower( $types[0] ) ];
		}

		// Retrieve and format the messages, then remove them from session data
		foreach ( $types as $type ) {
			if ( ! isset( $_SESSION['flash_messages'][ $type ] ) || empty( $_SESSION['flash_messages'][ $type ] ) ) {
				continue;
			}
			foreach ( $_SESSION['flash_messages'][ $type ] as $msg_data ) {
				$output .= $this->format_message( $msg_data, $type );
			}
			$this->clear( $type );
		}

		// Print everything to the screen (or return the data)
		if ( $print ) {
			echo wp_kses_post( $output );
		} else {
			return $output;
		}
	}

	/**
	 * Format a message
	 *
	 * @param  array  $msg_data_array  Array of message data
	 * @param  string $type           The $msg_type
	 *
	 * @return string                 The formatted message
	 *
	 */
	protected function format_message( $msg_data_array, $type ) {

		$msg_type   = isset( $this->msg_types[ $type ] )
			? $type : $this->DEFAULT_TYPE;
		$css_class  = $this->msg_css_class . ' ' . $this->css_class_map[ $type ];
		$msg_before = $this->msg_before;

		// If sticky then append the sticky CSS class
		if ( $msg_data_array['sticky'] ) {
			$css_class .= ' ' . $this->sticky_css_class;

			// If it's not sticky then add the close button
		} else {
			$msg_before = $this->close_btn . $msg_before;
		}

		// Wrap the message if necessary
		$formatted_message = $msg_before . $msg_data_array['message'] . $this->msg_after;

		return sprintf(
			$this->msg_wrapper,
			$css_class,
			$formatted_message
		);
	}

	/**
	 * Clear the messages from the session data
	 *
	 * @param  mixed $types   (array) Clear all of the message types in array
	 *                        (string)  Only clear the one given message type
	 *
	 * @return object
	 *
	 */
	protected function clear( $types = [] ) {

		if ( ( is_array( $types ) && empty( $types ) ) || is_null( $types ) || ! $types ) {
			unset( $_SESSION['flash_messages'] );
		} elseif ( ! is_array( $types ) ) {
			$types = [ $types ];
		}

		foreach ( $types as $type ) {
			unset( $_SESSION['flash_messages'][ $type ] );
		}

		return $this;
	}

	/**
	 * See if there are any queued error messages
	 *
	 * @return boolean
	 *
	 */
	public function has_errors() {
		return empty( $_SESSION['flash_messages'][ self::ERROR ] )
			? false : true;
	}

	/**
	 * See if there are any queued message
	 *
	 * @param  string $type The $msg_type
	 *
	 * @return boolean
	 *
	 */
	public function has_messages( $type = null ) {

		if ( ! is_null( $type ) ) {
			if ( ! empty( $_SESSION['flash_messages'][ $type ] ) ) {
				return $_SESSION['flash_messages'][ $type ];
			}
		} else {
			foreach ( array_keys( $this->msg_types ) as $type ) {
				if (
					isset( $_SESSION[ 'flash_messages' ][ $type ] ) &&
					! empty( $_SESSION[ 'flash_messages' ][ $type ] )
				) {
					return $_SESSION[ 'flash_messages' ][ $type ];
				}
			}
		}

		return false;
	}

	/**
	 * Set the HTML that each message is wrapped in
	 *
	 * @param string $msg_wrapper The HTML that each message is wrapped in.
	 *                            Note: Two placeholders (%s) are expected.
	 *                            The first is the $msg_css_class,
	 *                            The second is the message text.
	 *
	 * @return object
	 *
	 */
	public function set_msg_wrapper( $msg_wrapper = '' ) {
		$this->msg_wrapper = $msg_wrapper;
		return $this;
	}

	/**
	 * Prepend string to the message (inside of the message wrapper)
	 *
	 * @param string $msg_before string to prepend to the message
	 *
	 * @return object
	 *
	 */
	public function set_msg_before( $msg_before = '' ) {
		$this->msg_before = $msg_before;
		return $this;
	}

	/**
	 * Append string to the message (inside of the message wrapper)
	 *
	 * @param string $msg_after string to append to the message
	 *
	 * @return object
	 *
	 */
	public function set_msg_after( $msg_after = '' ) {
		$this->msg_after = $msg_after;
		return $this;
	}

	/**
	 * Set the HTML for the close button
	 *
	 * @param string $close_btn HTML to use for the close button
	 *
	 * @return object
	 *
	 */
	public function set_close_btn( $close_btn = '' ) {
		$this->close_btn = $close_btn;
		return $this;
	}

	/**
	 * Set the CSS class for sticky notes
	 *
	 * @param string $sticky_css_class the CSS class to use for sticky messages
	 *
	 * @return object
	 *
	 */
	public function set_sticky_css_class( $sticky_css_class = '' ) {
		$this->sticky_css_class = $sticky_css_class;
		return $this;
	}

	/**
	 * Set the CSS class for messages
	 *
	 * @param string $msg_css_class The CSS class to use for messages
	 *
	 * @return object
	 *
	 */
	public function set_msg_css_class( $msg_css_class = '' ) {
		$this->msg_css_class = $msg_css_class;
		return $this;
	}

	/**
	 * Set the CSS classes for message types
	 *
	 * @param mixed $msg_type     (string) The message type
	 *                            (array) key/value pairs for the class map
	 * @param mixed $css_class    (string) the CSS class to use
	 *                            (null) not used when $msg_type is an array
	 *
	 * @return object
	 *
	 */
	public function set_css_class_map( $msg_type, $css_class = null ) {
		if ( ! is_array( $msg_type ) ) {
			// Make sure there's a CSS class set
			if ( is_null( $css_class ) ) {
				return $this;
			}
			$msg_type = [
				$msg_type => $css_class,
			];
		}
		foreach ( $msg_type as $type => $css_class ) {
			$this->css_class_map[ $type ] = $css_class;
		}
		return $this;
	}

}
