<?php # -*- coding: utf-8 -*-

namespace CodeMascot\WPFlashMessage;

/**
 * Class FlashMessages
 *
 * @author  Khan M Rashedun-Naby, CodeMascot
 * @link    https://www.codemascot.com
 * @version 1.0.1
 * @since   1.0.0
 * @package CodeMascot\WPFlashMessage
 * @license MIT
 */
class FlashMessage {
	/**
	 * Message types and shortcuts
	 */
	const INFO     = 'i';
	const SUCCESS  = 's';
	const WARNING  = 'w';
	const ERROR    = 'e';

	/**
	 * Default message type
	 */
	const DEFAULT_TYPE = self::INFO;
	
	/**
	 * Message types and order
	 *
	 * Note:  The order that message types are listed here is the same order
	 * they will be printed on the screen (ie: when displaying all messages)
	 * This can be overridden with the display() method by manually defining
	 * the message types in the order you want to display them.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $msg_types = [
		self::ERROR   => 'error',
		self::WARNING => 'warning',
		self::SUCCESS => 'success',
		self::INFO    => 'info',
	];
	
	/**
	 * Each message gets wrapped in this
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $msg_wrapper = "<div class='%s'>%s</div>\n";
	
	/**
	 * Prepend to each message (inside of the wrapper)
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $msg_before = '';
	
	/**
	 * append to each message (inside of the wrapper)
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $msg_after = '';
	
	/**
	 * HTML for the close button
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $close_btn = '';

	/**
	 * CSS Classes
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $sticky_css_class = 'sticky';
	protected $msg_css_class = 'alert dismissable';
	protected $css_class_map = [
		self::INFO    => 'alert-info',
		self::SUCCESS => 'alert-success',
		self::WARNING => 'alert-warning',
		self::ERROR   => 'alert-danger',
	];

	/**
	 * Where to redirect the user after a message is queued
	 *
	 * @since 1.0.0
	 *
	 * @var null
	 */
	protected $redirect_url = null;

	/**
	 * The unique ID for the session/messages (do not edit)
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $msg_id;
	
	/**
	 * FlashMessage constructor.
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct() {
		/**
		 * Generate a unique ID for this user and session
		 */
		$this->msg_id = sha1( uniqid() );
		/**
		 * Create session array to hold our messages if it doesn't already exist
		 */
		if (
			! isset( $_SESSION ) ||
			! array_key_exists( 'flash_messages', $_SESSION )
		) {
			$_SESSION['flash_messages'] = [];
		}

	}
	
	/**
	 * Starting session.
	 */
	public function start_session() {
		add_action( 'init', function() {
			// Starting session
			if ( ! session_id() ) {
				session_start();
			}
		} );
	}

	/**
	 * Add an info message
	 *
	 * @param  string  $message      The message text
	 * @param  string  $redirect_url Where to redirect once the message is added
	 * @param  boolean $sticky       Sticky the message (hides the close button)
	 *
	 * @since  1.0.0
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
	 * @since  1.0.0
	 *
	 * @return object | bool
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
			$type = self::DEFAULT_TYPE;
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
	 * @since  1.0.0
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
	 * @since  1.0.0
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
	 * @since  1.0.0
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
	 * @since  1.0.0
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
	 * @since  1.0.0
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
	 * @since  1.0.0
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
		if (
			is_null( $types ) ||
			! $types ||
			( is_array( $types ) && empty( $types ) )
		) {
			$types = array_keys( $this->msg_types );
			// Print multiple message types (as defined by an array)
		} elseif ( is_array( $types ) && ! empty( $types ) ) {
			$the_types = $types;
			$types     = [];
			foreach ( $the_types as $type ) {
				$types[] = strtolower( $type[0] );
			}

			// Print only a single message type
		} else {
			$types = [ strtolower( $types[0] ) ];
		}

		// Retrieve and format the messages, then remove them from session data
		foreach ( $types as $type ) {
			if (
				! isset( $_SESSION['flash_messages'][ $type ] ) ||
				empty( $_SESSION['flash_messages'][ $type ] )
			) {
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
			return wp_kses_post( $output );
		}
	}

	/**
	 * Format a message
	 *
	 * @param  array  $msg_data_array  Array of message data
	 * @param  string $type            The $msg_type
	 *
	 * @since  1.0.0
	 *
	 * @return string                  The formatted message
	 *
	 */
	protected function format_message( $msg_data_array, $type ) {
		$msg_type   = isset( $this->msg_types[ $type ] )
			? $type : self::DEFAULT_TYPE;
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
	 * @since  1.0.0
	 *
	 * @return object
	 *
	 */
	protected function clear( $types = [] ) {
		if (
			( is_array( $types ) && empty( $types ) ) ||
			is_null( $types ) || ! $types
		) {
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
	 * @since  1.0.0
	 *
	 * @return boolean
	 *
	 */
	public function has_errors() {
		return empty( $_SESSION['flash_messages'][ self::ERROR ] ) ? false : true;
	}

	/**
	 * See if there are any queued message
	 *
	 * @param  string $type The $msg_type
	 *
	 * @since  1.0.0
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
					isset( $_SESSION['flash_messages'][ $type ] ) &&
					! empty( $_SESSION['flash_messages'][ $type ] )
				) {
					return $_SESSION['flash_messages'][ $type ];
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
	 * @since  1.0.0
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
	 * @param  string $msg_before string to prepend to the message
	 *
	 * @since  1.0.0
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
	 * @param  string $msg_after string to append to the message
	 *
	 * @since  1.0.0
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
	 * @param  string $close_btn HTML to use for the close button
	 *
	 * @since  1.0.0
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
	 * @param  string $sticky_css_class the CSS class to use for sticky messages
	 *
	 * @since  1.0.0
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
	 * @param  string $msg_css_class The CSS class to use for messages
	 *
	 * @since  1.0.0
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
	 * @param  mixed $msg_type     (string) The message type
	 *                            (array) key/value pairs for the class map
	 * @param  mixed $css_class    (string) the CSS class to use
	 *
	 * @since  1.0.0
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
