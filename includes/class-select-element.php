<?php

class WB_Select_Element extends WB_Form_Element {
	public function element( $args = array() ) {
		$defaults = array(
			'show_label' => true,
			'prefix'     => '',
			'value'      => ''
		);
		
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		extract( $this->_params, EXTR_SKIP );
		
		$id   = $this->_get_id( $prefix );
		$name = $this->_get_name( $prefix );
		
		$attr = array( 'id' => $id );
		
		if ( $show_label )
			echo $this->_html_label( $title, $id );
		
		echo $this->_html_select( $name, $value, $opts, $attr );
	}
	
	public function validate( $value ) {
		if ( ! in_array( $value, $this->_params['opts'] ) )
			$value = null;
		
		return $value;
	}
	
	protected function _html_select( $name, $value, $opts = array(), $attr = array() ) {
		$body = '';
		
		foreach ( $opts as $opt ) {
			$opt_attr = array( 'value' => $opt );
			
			if ( $opt == $value )
				$opt_attr['selected'] = 'selected';
			
			$body .= $this->_html_tag( 'option', $opt, $opt_attr );
		}
		
		$attr['name'] = $name;
		
		return $this->_html_tag( 'select', $body, $attr );
	}
}

?>