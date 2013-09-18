<?php

class WB_Checkbox_Element extends WB_Form_Element {
	public function __construct( $args = array() ) {
		parent::__construct( $args );
		
		$this->_params['opts'] = array( 'on', 'off' );
	}
	
	public function element( $args = array() ) {
		$defaults = array(
			'prefix' => '',
			'value'  => ''
		);
		
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		extract( $this->_params, EXTR_SKIP );
		
		$id   = $this->_get_id( $prefix );
		$name = $this->_get_name( $prefix );
		
		$attr = array( 'id' => $id );
		
		if ( 'on' == $value )
			$attr['checked'] = 'checked';
		
		echo $this->_html_input( 'checkbox', $name, 'on', $attr );
		
		echo $this->_html_label( $title, $id );
	}
	
	public function validate( $value ) {
		if ( ! in_array( $value, $this->_params['opts'] ) )
			$value = 'off';
		
		return $value;
	}
}

?> 