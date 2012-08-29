<?php

/**
 * Sanitizes strings for use in key/value pairs
 * Replaces dashes and white space characters with underscores
 */
function wordbench_sanitize( $string ) {
	return str_replace( '-', '_', sanitize_title( $string ) );
}

/**
 * Converts sanitized strings back to book-title strings
 */
function wordbench_labelize( $string ) {
	return ucwords( str_replace( array( '_', '-' ), ' ', strtolower( $string ) ) );
}

/**
 * Writes debugging data to the specified log file
 */
function wordbench_log( $data, $file = 'wordbench' ) {
	$file = WORDBENCH_PATH . 'logs/' . $file . '.log';
	
	if ( $log = fopen( $file, 'a' ) ) {
		$line = sprintf( "%s\t%s\n", date( DATE_RSS ),
			str_replace( "\n", "\n\t", $data ) );
		
		fwrite( $log, $line );
		fclose( $log );
	}
}

/**
 * Writes a debugging backtrace to the specified log file
 */
function wordbench_log_backtrace( $data, $file = 'wordbench' ) {
	ob_start();
	debug_print_backtrace();
	$backtrace = ob_get_contents();
	ob_end_clean();
	
	wordbench_log( "BACKTRACE:\n$backtrace", $file );
}

/**
 * Returns whether or not the current request was created by a WordPress cron.
 */
function wordbench_is_cron() {
	return 'wp-cron.php' == basename( $_SERVER['SCRIPT_FILENAME'] );
}

/**
 * Loads styles and scripts used in admin interfaces
 */
function wordbench_enqueue_scripts() {
	wp_enqueue_style(  'wordbench_forms',  plugins_url( 'css/forms.css', __FILE__ ) );
	wp_enqueue_script( 'wordbench_forms',  plugins_url( 'js/forms.js',   __FILE__ ) );
	wp_enqueue_script( 'wordbench_string', plugins_url( 'js/string.js',  __FILE__ ) );
}

?>