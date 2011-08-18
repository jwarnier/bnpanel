<?php
/* For licensing terms, see /license.txt */

//not implemented yet for BNPanel
exit;
define("LINK", "../includes/");
include(LINK ."compiler.php");

define("PAGE", "Confirm");

global $main, $server, $style;

echo $style->get("header.tpl"); #Output Header
echo '<div align="center">';
if(!isset($main->getvar['u'])) {
	echo "Please use the link provided in your e-mail.";
} else {
	$username 	= $main->getvar['u'];
	$confirm 	= $main->getvar['c'];
	$command 	= $server->confirm($username, $confirm);	
	if($command == false) {
		echo 'Confirmation failed, please try to copy and paste the link into your browser.';
	} else {
		echo 'Account confirmed.';
	}
}
echo '</div>'; #End it
echo $style->get("footer.tpl"); #Output Footer

//Output
include(LINK ."output.php");

?>