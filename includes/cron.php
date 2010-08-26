<?php
/* For licensing terms, see /license.txt */

define("CRON", 1);
require "compiler.php";
	


#Stop the output
ob_start(); # Damn, I swear I use too much of these. Their like crack. So fuckin addictive.

$type->createAll(); #Create all the types
$classes = $type->classes; #Because I'm a tad lazy, I set the types to a shorter variable

#Scans through each type
foreach($classes as $key => $value) {
	#Has the type got a cron?
	if($classes[$key]->cron) {
		#Well run it then...
		$classes[$key]->cron();	
	}
}

#1.2 Run the Paid CronJob. 
$invoice->cron();

//1.3 Maintenance cron jobs
$main->cron();

#Now we get the data
$data = ob_get_contents(); # Get all the HTML created by the script
ob_end_clean(); # Erase that data
if (SERVER_STATUS == 'test') {
	echo $data; # Lets just show it. Tickles my pickle. Don't have to keep checking emails.}
}

#Now we mo truckin email it. Yeah I said it. Aren't I smart?
if($data != "") {
	$email->staff("Cron Job results", $data);
}