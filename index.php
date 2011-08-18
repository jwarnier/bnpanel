<?php
/* For licensing terms, see /license.txt */
#Include the compiler, creates everything
require_once 'includes/compiler.php';

#Retrieve default page and redirect to it
$page = $db->config('default');
if($page != "") {
	$main->redirect($page);
}