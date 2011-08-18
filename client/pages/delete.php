<?php
/* For licensing terms, see /license.txt */

class page {	
	public function content() { # Displays the page 
		global $style, $db, $main;
		if(!$db->config("delacc")) {
			die('Disabled.');
		} else {
			$_SESSION['cdelete'] = true;
			$array['USER'] = $main->getCurrentUserId();
			echo $style->replaceVar("tpl/user/cdelete.tpl", $array);
		}
	}
}