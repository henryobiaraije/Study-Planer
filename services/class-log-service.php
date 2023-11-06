<?php
/**
 * Log Service.
 *
 * @package StudyPlannerPro\Services
 */

namespace StudyPlannerPro\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

/**
 * Class Log_Service
 * A log service for WordPress that logs messages, objects, and arrays to a log file.
 */
class Log_Service {

	/**
	 * The path to the log file.
	 *
	 * @var string $log_file The path to the log file.
	 */
	private string $log_file = '';

	private array $log_info = array(
		'class_name' => '',
		'function'   => '',
		'line'       => '',
	);

	/**
	 * log_service constructor.
	 *
	 * @param string $log_file Path to the log file.
	 */
	public function __construct( string $log_file ) {
		$this->log_file = $log_file;
	}

	/**
	 * Log a message with the "log" log level.
	 *
	 * @param string $message The log message.
	 * @param string $log_level The log level, e.g. "error", "warning", "info", "debug".
	 * @param array $data The data to log.
	 */
	protected function log_default( string $message, string $log_level, array $data = array() ): void {
		//		$formatted_message = $this->format_data( $message, $log_level );
		$formatted_message = $this->format_any( $message );
		if ( ! empty( $data ) ) {
			$formatted_message =
				$this->format_any( $message )
				. PHP_EOL
				. $this->format_any( $data );
		}

		$formatted_message = $this->convert_to_text( $formatted_message, $log_level );
		$this->write_to_log( $formatted_message );
	}

	/**
	 * Log a message with the "log" log level.
	 *
	 * @param string $message The log message.
	 * @param array $data The data to log.
	 */
	public function log( string $message, array $data = array() ): void {
		$caller        = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[1];
		$class_name    = $caller['class'];
		$function_name = $caller['function'];
		$line          = $caller['line'];

		$this->log_info = array(
			'class_name' => $class_name,
			'function'   => $function_name,
			'line'       => $line,
		);

		$this->log_default( $message, 'LOG', $data );
	}

	/**
	 * Log a message with the "warn" log level.
	 *
	 * @param string $message The log message.
	 * @param array $data The data to log.
	 */
	public function warn( string $message, array $data = array() ): void {
		$caller        = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[1];
		$class_name    = $caller['class'];
		$function_name = $caller['function'];
		$line          = $caller['line'];

		$this->log_info = array(
			'class_name' => $class_name,
			'function'   => $function_name,
			'line'       => $line,
		);
		$this->log_default( $message, 'WARN', $data );
	}

	/**
	 * Log a message with the "error" log level.
	 *
	 * @param string $message The log message.
	 * @param array $data The data to log.
	 */
	public function error( string $message, array $data = array() ): void {
		$caller        = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[1];
		$class_name    = $caller['class'];
		$function_name = $caller['function'];
		$line          = $caller['line'];

		$this->log_info = array(
			'class_name' => $class_name,
			'function'   => $function_name,
			'line'       => $line,
		);
		$this->log_default( $message, 'ERROR', $data );
	}

	/**
	 * Log a message with the "debug" log level.
	 *
	 * @param string $message The log message.
	 * @param array $data The data to log.
	 */
	public function debug( string $message, array $data = array() ): void {
		$caller        = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[1];
		$class_name    = $caller['class'];
		$function_name = $caller['function'];
		$line          = $caller['line'];

		$this->log_info = array(
			'class_name' => $class_name,
			'function'   => $function_name,
			'line'       => $line,
		);
		$this->log_default( $message, 'DEBUG', $data );
	}

	/**
	 * Log an object with the "log" log level.
	 *
	 * @param mixed $object The object to log.
	 */
	public function log_object( $object ): void {
		$formatted_object = $this->format_object( $object );
		$this->write_to_log( $formatted_object );
	}

	/**
	 * Log an object with the "warn" log level.
	 *
	 * @param mixed $object The object to log.
	 */
	public function warn_object( $object ): void {
		$formatted_object = $this->format_object( $object, 'WARN' );
		$this->write_to_log( $formatted_object );
	}

	/**
	 * Log an object with the "error" log level.
	 *
	 * @param mixed $object The object to log.
	 */
	public function error_object( $object ): void {
		$formatted_object = $this->format_object( $object, 'ERROR' );
		$this->write_to_log( $formatted_object );
	}

	/**
	 * Log an object with the "debug" log level.
	 *
	 * @param mixed $object The object to log.
	 */
	public function debug_object( $object ): void {
		$formatted_object = $this->format_object( $object, 'DEBUG' );
		$this->write_to_log( $formatted_object );
	}

	/**
	 * Log an array with the "log" log level.
	 *
	 * @param array $array The array to log.
	 */
	public function log_array( array $array ): void {
		$formatted_array = $this->format_array( $array );
		$this->write_to_log( $formatted_array );
	}

	/**
	 * Log an array with the "warn" log level.
	 *
	 * @param array $array The array to log.
	 */
	public function warn_array( array $array ): void {
		$formatted_array = $this->format_array( $array, 'WARN' );
		$this->write_to_log( $formatted_array );
	}

	/**
	 * Log an array with the "error" log level.
	 *
	 * @param array $array The array to log.
	 */
	public function error_array( array $array ): void {
		$formatted_array = $this->format_array( $array, 'ERROR' );
		$this->write_to_log( $formatted_array );
	}

	/**
	 * Log an array with the "debug" log level.
	 *
	 * @param array $array The array to log.
	 */
	public function debug_array( array $array ): void {
		$formatted_array = $this->format_array( $array, 'DEBUG' );
		$this->write_to_log( $formatted_array );
	}

	/**
	 * Format a log message.
	 *
	 * @param string $message The log message.
	 * @param string $log_level The log level (e.g., warn, error, debug).
	 *
	 * @return string The formatted log message.
	 */
	private function format_message( string $message, string $log_level = 'LOG' ): string {
		$timestamp = date( 'Y-m-d H:i:s' );

		return "[$log_level] $timestamp - $message" . PHP_EOL;
	}

	/**
	 * Format an object for logging.
	 *
	 * @param mixed $object The object to log.
	 * @param string $log_level The log level (e.g., warn, error, debug).
	 *
	 * @return string The formatted object for logging.
	 */
	private function format_object( $object, string $log_level = 'LOG' ): string {
		ob_start();
		var_dump( $object );
		$output = ob_get_clean();

		return $this->format_message( $output, $log_level );
	}

	/**
	 * Format an array for logging.
	 *
	 * @param array $array The array to log.
	 * @param string $log_level The log level (e.g., warn, error, debug).
	 *
	 * @return string The formatted array for logging.
	 */
	private function format_array( array $array, string $log_level = 'LOG' ): string {
		$output = print_r( $array, true );

		return $this->format_message( $output, $log_level );
	}

	/**
	 * Format any data for logging.
	 *
	 * @param mixed $data The data to format.
	 *
	 * @return string The formatted data for logging.
	 */
	private function format_any( $data ): string {
		if ( is_object( $data ) ) {
			$formatted_data = var_export( $data, true );
		} elseif ( is_array( $data ) ) {
			$formatted_data = print_r( $data, true );
		} else {
			$formatted_data = $data;
		}

		return $formatted_data;
	}

	/**
	 * Convert data to text for logging.
	 *
	 * @param mixed $data The data to format.
	 * @param string $log_level The log level (e.g., warn, error, debug).
	 *
	 * @return string The formatted data for logging.
	 */
	private function convert_to_text( string $formatted_data, string $log_level = 'LOG' ): string {
		$timestamp      = date( 'Y-m-d H:i:s' );
		$plugin_version = Initializer::PLUGIN_VERSION;

		$log_Info = $this->log_info;

		$class_name    = $log_Info['class_name'];
		$function_name = $log_Info['function'];
		$line          = $log_Info['line'];

		return "[$log_level][$plugin_version][$class_name][$function_name][$line][$timestamp] - $formatted_data";

		//		return "[$log_level] - $plugin_version - $timestamp - $formatted_data";
	}

	/**
	 * Format data for logging.
	 *
	 * @param mixed $data The data to format.
	 * @param string $log_level The log level (e.g., warn, error, debug).
	 *
	 * @return string The formatted data for logging.
	 */
	private function format_data( $data, string $log_level = 'LOG' ): string {
		$formatted_data = $this->format_any( $data );

		return $this->convert_to_text( $formatted_data, $log_level );
	}

	/**
	 * Write a formatted log message to the log file.
	 *
	 * @param string $formatted_message The formatted log message.
	 */
	private function write_to_log( string $formatted_message ): void {
		error_log( $formatted_message, 3, $this->log_file );
	}
}
