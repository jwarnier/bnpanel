<?php
/* For licensing terms, see /license.txt */

define("PAGE", "Index");

require_once 'includes/compiler.php';

#Retrieve default page and redirect to it
$page = $db->config('default');
if ($page != "") {	
	$main->redirect($page);
}