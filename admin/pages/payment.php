<?php
/* For licensing terms, see /license.txt */
/**
	BNPanel
		
	@author 	Julio Montoya <gugli100@gmail.com> Beeznest 2010
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
		return "<strong>Clients</strong><br />
		This is the area where you can manage all your clients that have signed up for your service. You can perform a variety of tasks like suspend, terminate, email and also check up on their requirements and stats.". $newest;	
	}
	
	public function content() { # Displays the page		
		
	}
}
?>
