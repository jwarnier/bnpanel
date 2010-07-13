<?php
/* For licensing terms, see /license.txt */
//Check if called by script
if(THT != 1){die();}

class page {
	
	public $navtitle;
	public $navlist = array();
	
	public function content() { # Displays the page 
		global $style;
		global $db;
		global $main;
		global $type;
		if(!$main->getvar['type'] || !$main->getvar['sub']) {
			echo "Not all variables set!";	
		}
		$user = $main->getCurrentStaffId();
		if($user == 1) {
			$php = $type->classes[$main->getvar['type']];
			$php->acpPage();
		}
		else {
			echo "You don't have access to this page.";
		}
	}
}
?>