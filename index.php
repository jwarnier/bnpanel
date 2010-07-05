<?php
/* For licensing terms, see /license.txt */

#Include the compiler, creates everything
define("LINK", "includes/");
include(LINK ."compiler.php");

#Retrieve default page and redirect to it
$page = $db->config("default");
if($page != "") {
	$main->redirect($page);
}
?>