<?php
/* For licensing terms, see /license.txt */

class page extends Controller {	
	public function content() { # Displays the page 
		global $style, $db, $main;
		if(!$db->config("delacc")) {
			die('Disabled.');
		} else {
			$_SESSION['cdelete'] = true;
			$array['USER'] = $main->getCurrentUserId();
			$this->replaceVar("tpl/user/cdelete.tpl", $array);
		}
	}
}