<?php
/* For licensing terms, see /license.txt */
/**
	BNPanel
		
	@author 	Julio Montoya <gugli100@gmail.com> BeezNest 2010
	@package	tht.payment	
*/

//Check if called by script
if(THT != 1){die();}
exit;
require_once LINK.'plugins.php';

class page {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {		
	}
	
	public function description() {
	}
	
	public function content() { # Displays the page		
		
	}
}
?>
