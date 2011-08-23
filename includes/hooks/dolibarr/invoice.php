<?php

class DolibarrInvoice extends Hook {

	function pre_save() {
		require_once LINK.'nusoap/nusoap.php';
		
		$url = "http://localhost/dolibar/webservices/server_invoice.php";		
		$client = new nusoap_client($url);
		echo 'Calling function '.get_class().':: '.__FUNCTION__.' - BEFORE the save invoice is loaded<br />';
	}
	
	function post_save() {
		if (!empty($this->data) && !empty($this->data['id'])) {
			require_once LINK.'nusoap/nusoap.php';
			//require_once MAIN.'nusoap.php';
		}
		echo 'Calling function '.get_class().':: '.__FUNCTION__.' - AFTER the save invoice is loaded<br />';
	}	
	
} 