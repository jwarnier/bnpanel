<?php
/* For licensing terms, see /license.txt */

//this page should be removed
exit;
class page extends Controller {
	
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
		else {
			$type->classes[$main->getvar['type']]->clientPage();
		}
	}
}