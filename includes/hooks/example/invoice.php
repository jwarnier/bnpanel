<?php

class ExampleInvoice extends Hook {
	
	function pre_save() {
		//echo 'Calling function '.__FUNCTION__.' - BEFORE the save invoice is loaded';
	}
	
	function post_save() {
		//echo 'Calling function '.__FUNCTION__.' - AFTER the save invoice is loaded';
	}	
} 