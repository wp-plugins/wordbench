<?php

function wordbench_get_form_element( $args = array() ) {
	$defaults = array(
		'type' => 'text'
	);
	
	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
	
	require_once WORDBENCH_INC . 'forms/class-form-element.php';
	
	$file  = WORDBENCH_INC . 'forms/class-' . $type . '-element.php';
	$class = 'WB_' . str_replace( ' ', '_', wordbench_labelize( $type ) ) . '_Element';
	
	if ( is_file( $file ) ) require_once $file;
	
	if ( class_exists( $class ) ) return new $class( $args );
	
	return false;
}

function wordbench_the_form_element( $args = array(), $params = array() ) {
	if ( $form_element = wordbench_get_form_element( $args ) ) {
		$form_element->element( $params );
	}
}

?>