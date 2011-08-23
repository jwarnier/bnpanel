<?php
class Hook {
	var $data = null;
	
	function __construct($data = null) {
		if (isset($data)) {
			$this->data = $data;			
		}		
	}	
}