<?php
/* For licensing terms, see /license.txt */

define("PAGE", "Credits");

class page {	
	public function content() { # Displays the page 
		global $style;
		global $db;
		global $main;
		
		echo $style->replaceVar("tpl/credits.tpl");
	}
}
?>