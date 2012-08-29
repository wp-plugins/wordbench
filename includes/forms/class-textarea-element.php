<?php

class WB_Textarea_Element extends WB_Form_Element {
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
		
		if ( $show_label )
			echo $this->_html_label( $title, $id );
		
		echo $this->_html_textarea( $name, $value, array(
			'id' => $id
		) );
	}
	
	protected function _html_textarea( $name, $body, $attr = array() ) {
		$attr['name'] = $name;
		
		$this->_html_tag( 'textarea', $body, $attr, true );
	}
}

?>