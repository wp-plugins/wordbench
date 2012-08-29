<?php

class WB_Form_Element {
	protected $_params;
	
	public function __construct( $args = array() ) {
		$defaults = array(
			'title' => null,
			'name'  => null,
			'opts'  => array()
		);
		
		$this->_params = wp_parse_args( $args, $defaults );
	}
	
	/**
	 * Returns the validated input for this element
	 * child classes SHOULD extend this method
	 */
	public function validate( $value ) {
		return $value;
	}
	
	/**
	 * Renders the form element
	 * child classes MUST extend this method
	 */
	public function element( $args = array() ) {
		die( __CLASS__ . ' must override WB_Form_Element::element()' );
	}
	
	/**
	 * Returns a properly formed element ID with the given prefix
	 * 
	 * @param string $prefix The prefix to be prepended to the element ID
	 *
	 * @return string The properly formed element ID
	 */
	protected function _get_id( $prefix = '' ) {
		extract( $this->_params, EXTR_SKIP );
		
		$name   = wordbench_sanitize( $name );
		$prefix = wordbench_sanitize( $prefix );
		
		return empty( $prefix ) ? $name : $prefix . '-' . $name;
	}
	
	/**
	 * Returns a properly formed element name with the given prefix
	 * 
	 * @param string $prefix The prefix to be prepended to the element name
	 * 
	 * @return string The properly formed element name
	 */
	protected function _get_name( $prefix = '' ) {
		extract( $this->_params, EXTR_SKIP );
		
		$name   = wordbench_sanitize( $name );
		$prefix = wordbench_sanitize( $prefix );
		
		return empty( $prefix ) ? $name : $prefix . '[' . $name . ']';
	}
	
	protected function _html_tag( $name, $body = null, $attr = array(), $close = false ) {
		foreach ( (array) $attr as $key => $value ) {
			if ( true === $value ) $value = $name;
			elseif ( false === $value ) continue;
						
			$attr_str = sprintf( '%s %s="%s"', $attr_str,
				$key, esc_attr( $value ) );
		}
		
		$html = sprintf( '<%s%s>', $name, $attr_str );
		
		if ( ! empty( $body ) || $close )
			$html .= sprintf( '%s</%s>', $body, $name );
		
		return $html;
	}
	
	protected function _html_label( $body, $for = '', $attr = array() ) {
		$attr['for'] = $for;
		
		return $this->_html_tag( 'label', $title, $attr );
	}
	
	protected function _html_input ( $type, $name, $value, $attr = array() ) {
		$attr['type']  = $type;
		$attr['name']  = $name;
		$attr['value'] = $value;
		
		return $this->_html_tag( 'input', null, $attr );
	}
}

?>