<?php
/* For licensing terms, see /license.txt */
//Check if called by script
if(THT != 1){die();}

class page {
	
	public function content() { # Displays the page 
		global $style, $db, $main;
		if(!$db->config("delacc")) {
			die('Disabled.');
		}
		else {
			$_SESSION['cdelete'] = true;
			$array['USER'] = $main->getCurrentUserId();
			echo $style->replaceVar("tpl/cdelete.tpl", $array);
		}
	}
}
?>