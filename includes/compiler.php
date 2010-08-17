<?php
/* For licensing terms, see /license.txt */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

#Define the main THT
define('THT', 1);

//Billing types
define('BILLING_TYPE_ADDON', 					'addon');
define('BILLING_TYPE_PACKAGE', 					'package');

//Addong status
define('ADDON_STATUS_ACTIVE', 					1);
define('ADDON_STATUS_INACTIVE', 				0);

//Billing cycle status
define('BILLING_CYCLE_STATUS_ACTIVE', 			1);
define('BILLING_CYCLE_STATUS_INACTIVE', 		0);

// Relation between packages and users a.k.a Orders 
define('ORDER_STATUS_ACTIVE', 					1);
define('ORDER_STATUS_WAITING_USER_VALIDATION', 	2); //before ORDER_STATUS_INACTIVE
define('ORDER_STATUS_WAITING_ADMIN_VALIDATION', 3); //Awaiting Validation
define('ORDER_STATUS_CANCELLED', 				4); //Awaiting Payment
define('ORDER_STATUS_FAILED',					5); //Means that an order try
define('ORDER_STATUS_DELETED', 					9);

// Invoices
define('INVOICE_STATUS_PAID', 					1); // Active in THT 1
define('INVOICE_STATUS_CANCELLED', 				2); // Suspended in THT 2
define('INVOICE_STATUS_WAITING_PAYMENT', 		3); // Awaiting Payment 4
define('INVOICE_STATUS_DELETED', 				9); // Cancelled in 9

//Domain Options
define('DOMAIN_OPTION_DOMAIN', 					1); // Cancelled in 9
define('DOMAIN_OPTION_SUBDOMAIN', 				2); // Cancelled in 9
define('DOMAIN_OPTION_BOTH', 					3); // Cancelled in 9

//Server status
define('SERVER_STATUS', 						'test'); //test or production 
define('PAYPAL_STATUS_LIVE', 					1);
define('PAYPAL_STATUS_SANDBOX', 				0);
//define(SERVER_STATUS, 'test'); //show mysql errors + user paypal sandbox

// User status 
define('USER_STATUS_ACTIVE', 					1);// Active users
define('USER_STATUS_SUSPENDED', 				2);// Suspend users can't login
define('USER_STATUS_WAITING_ADMIN_VALIDATION',	3);// Waiting can't login till admin validation 
define('USER_STATUS_WAITING_USER_VALIDATION', 	4); //before ORDER_STATUS_INACTIVE
//define(USER_STATUS_WAITING_PAYMENT, 			4); //should not be use is useless!! 
define('USER_STATUS_DELETED', 					9); //cancelled deleted users

//Used in admin/billing.php
define('MAX_NUMBER_MONTHS',						48);

#Page generated
$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

#Start us up
if(CRON != 1) {
	session_start();
}

#Stop the output
ob_start();

#Check for Dependencies
$d = checkForDependencies();
if($d !== true) {
	die((string)$d);
}

#Check PHP Version
$version = explode(".", phpversion());

//Grab DB First
require LINK."/class_db.php"; # Get the file
if(file_exists(LINK."/conf.inc.php")) {
	include LINK."/conf.inc.php"; # Get the config
	define("NOCONFIG", false);
} else {
	define("NOCONFIG", true);
}

if($sql['install']) {
	define("INSTALL", 1);
	$db = new db(); # Create the class	
	global $db; # Globalise it
}

$folder = LINK;
require_once LINK.'/model.php'; # Get the file
require LINK.'/class_main.php'; # Get the file
					
$main = new main(); # Create the class
if (isset($main) && !empty($main)) {
	global $main;		
} else {
	//$main->redirect('install');
	echo 'Something is wrong';
}

if ($handle = opendir($folder)) { # Open the folder
	while (false !== ($file = readdir($handle))) { # Read the files
		if($file != "." && $file != "..") { # Check aren't these names
			$base = explode(".", $file); # Explode the file name, for checking
			if($base[1] == "php") { # Is it a php?
				$base2 = explode("_", $base[0]);
				if($base2[0] == "class" && $base2[1] != "db" && $base2[1] != "main") {
					require $folder."/".$file; # Get the file					
					${$base2[1]} = new $base2[1]; # Create the class
					global ${$base2[1]}; # Globalise it
				}
			}
		}
	}
}
closedir($handle); #Close the folder

//Not generate if it comes from AJAX


if (!$is_ajax_load) {
	$token =  $main->generateToken();		
} else {
	$token =  $_GET['_get_token'];
}

if(INSTALL == 1) {
	define("THEME", $db->config("theme")); # Set the default theme
	define("URL", 	$db->config("url")); # Sets the URL THT is located at
	define("NAME", 	$db->config("name")); # Sets the name of the website
	//Converts all POSTS into variable - DB Friendly.
	if($_POST) {
		foreach($_POST as $key => $value) {
			$main->postvar[$key] = $db->strip($value);
		}
		$main->postvar['_post_token'] =	$token;
	}
}
//Converts all GET into variable - DB Friendly.
foreach($_GET as $key => $value) {
	if(INSTALL == 1) {
		$main->getvar[$key] = $db->strip($value);
	} else {
		$main->getvar[$key] = $value;	
	}
	$main->getvar['_get_token'] = $token;
}

$path = dirname($main->removeXSS($_SERVER['PHP_SELF']));
$position = strrpos($path,'/') + 1;
define("FOLDER", substr($path,$position)); # Add current folder name to global
if(FOLDER != "install" && FOLDER != "includes" && INSTALL != 1) { # Are we installing?	
	//Lets just redirect to the installer, shall we?	
	$installURL = LINK . "../install";
	header("Location: $installURL");
	exit;
}

//Resets the error.
$_SESSION['ecount'] = 0;
$_SESSION['errors'] = 0;

//If payment..
if(FOLDER == "client" && $main->getvar['page'] == "invoices" && $main->getvar['iid'] && $_SESSION['clogged'] == 1) {
	if ($main->checkToken()) {
		$invoice->pay($main->getvar['iid'], "client/index.php?page=invoices");
	}
	echo "You made it this far.. something went wrong.";
}

function checkForDependencies() {
	//Here, we're going to see if we have the functions that we need. :D
	$needed = array();
	//First things first:
	$version = explode(".", phpversion());
	if($version[0] < 5) {
		die("PHP Version 5 or over is required! You're currently running: " . phpversion());
	}
	if(!function_exists("curl_init")) {
		$needed[] = "cURL";
	}
	if(!function_exists("mysql_connect")) {
		$needed[] = "MySQL";
	}
	if(count($needed) == 0) {
		return true;
	}
	else {
		$output = "The following function(s) are/is needed for
		BNPanel to run properly: <ul>";
		foreach($needed as $key => $value) {
			$output .= "<li>$value</li>";
		}
		$output .= "</ul>";
		return $output;
	}
}
