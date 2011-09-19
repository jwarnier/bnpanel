<?php
/* For licensing terms, see /license.txt */

define("PAGE", "Credits");

class page extends Controller {	
	public function content() { # Displays the page 
		global $style;
		global $db;
		global $main;
		
		echo $style->replaceVar("credits.tpl");
	}
}
