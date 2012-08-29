<?php

class WB_Radio_Element extends WB_Form_Element {
	public function element( $args = array() ) {
		$defaults = array(
			'prefix' => '',
			'value'  => ''
		);
		
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		extract( $this->_params, EXTR_SKIP );
		
		$html = '';
		
		foreach ( $opts as $opt ) {
			$key = wordbench_sanitize( $opt );
			
			$id   = $this->_get_id( $prefix ) . '-' . $key,
			$name = $this->_get_name( $prefix ),
			
			$attr = array( 'id' => $id );
			
			if ( $opt == $value )
				$attr['checked'] = 'checked';
			
			$input = $this->_html_input( 'radio', $name, $value, $attr );
			$label = $this->_html_label( $opt, $id );
			
			$html .= '<li>' . $input . $label . '</li>';
		}
		
		echo $this->_html_tag( 'ul', $html, array(
			'class' => 'form-element radio-element'
		) );
	}
	
	public function validate( $value ) {
		if ( ! in_array( $value, $this->_params['opts'] ) )
			$value = '';
		
		return $value;
	}
}

?>